<?php

/**
 * LDAP Passwor Policy Expiration Checker
 *
 * Roundcube plugin to check the LDAP password policy for password expiration.
 * If the user's password is in the warning period or has expired it redirects
 * user to change password immediately after login and show the proper message.
 *
 * @version @package_version@
 * @license GNU GPLv3+
 * @author Zbigniew Szmyd (zbigniew.szmyd@linseco.pl)
 * @website http://roundcube.net
 */
class ppolicy_checker extends rcube_plugin {
	public $task = 'login';
	private $rc;
	private $ldap;
	private $ldap_config = array ();
	private $policies    = array ();
	private $ldap_connected = FALSE;
	private $binddn;
	private $policies_basedn;
	private $default_policy;
	private $log_file;
	private $debug      = TRUE;
	private $login_attr = 'uid';
	private $end_date;
	private $expired    = FALSE;
	private $uri;
	function init() {
		require_once 'Net/LDAP2.php';
		
		$this->rc = rcmail::get_instance ();
		$this->load_config ();
		$this->log_file = 'ppolicy_checker_log.txt';
		$this->debug    = $this->rc->config->get ( 'ppolicy_checker_debug' );
		
		$this->uri = $this->rc->config->get ( 'ppolicy_checker_uri' );
		
		$this->basedn = $this->rc->config->get ( 'ppolicy_checker_basedn' );
		$this->ppolicy_policies_basedn = $this->rc->config->get ( 'ppolicy_checker_policies_base_dn' );
		$this->default_policy = $this->rc->config->get ( 'ppolicy_checker_default_policy' );
		
		$this->ldap_config = array (
				'binddn'  => $this->rc->config->get ( 'ppolicy_checker_binddn' ),
				'bindpw'  => $this->rc->config->get ( 'ppolicy_checker_bindpw' ),
				'basedn'  => $this->basedn,
				'version' => 3 
		);
		
		$this->add_hook ( 'login_after', array (
				$this,
				'check_expired' 
		) );
	}
	function check_expired($args) {
		$username = $this->rc->user->get_username ();
		
		if ($this->connect_ldap_server ( $this->uri )) {
			
			$this->load_policies ();
			if ($this->get_user_info ( $username )) {
				if ($this->expired) {
					$args ['_passexpired']    = TRUE;
				} else {
					$args ['_passexpwarning'] = TRUE;
				}
								
				$args ['_passexpdate'] = $this->end_date;
				$args ['_task']        = 'settings';
				$args ['action']       = 'plugin.password';
			}
		}
		
		return $args;
	}
	function connect_ldap_server($uri) {
		$ldaps = preg_split ( "/[\s,]+/", $uri );
		$found = FALSE;
		while ( ($ldap = array_shift ( $ldaps )) && ! $found ) {
			$port = 389;
			$host = 'localhost';
			$tls  = FALSE;
			
			preg_match ( '@^(ldap(s?)://)([^/:]+)(:(\d+))?@i', $ldap, $matches );
			$host = $matches [3];
			if ($matches [5]) {
				$port = $matches [5];
			}
			if ($matches [2]) {
				$tls = TRUE;
			}
			
			// The configuration array:
			$this->ldap_config ['host']     = $host;
			$this->ldap_config ['port']     = $port;
			$this->ldap_config ['starttls'] = $tls;
			
			$this->_debug ( "LDAP: \n\thost: $host \n\tport: $port \n\ttls: $tls\n" );
			// Connecting using the configuration:
			$this->ldap = Net_LDAP2::connect ( $this->ldap_config );
			
			// Testing for connection error
			if (PEAR::isError ( $this->ldap )) {
				$this->_debug ( 'ldap connection error: ' . $this->ldap->getMessage () );
			} else {
				$this->_debug ( 'ldap bind OK' );
				$found = TRUE;
			}
		}
		return $found;
	}
	function load_policies() {
		$filter  = '(objectclass=pwdPolicy)';
		$options = array (
				'scope'      => 'sub',
				'attributes' => array (
						'cn',
						'pwdMaxAge',
						'pwdExpireWarning',
						'pwdGraceAuthnLimit' 
				) 
		);
		
		$result = $this->ldap->search ( $this->policies_basedn, $filter, $options );
		if (is_a ( $result, 'PEAR_Error' ) || ($result->count () == 0)) {
			$this->_debug ( 'policy not found: ' . $result->getMessage () );
			return 0;
		} else {
			while ( $entry = $result->shiftEntry () ) {
				$dn = $entry->dn ();
				$this->policies [$dn] ['pwdMaxAge'] = ($entry->getValue ( 'pwdMaxAge', 'single' )) ? $entry->getValue ( 'pwdMaxAge', 'single' ) : 0;
				$this->policies [$dn] ['pwdExpireWarning'] = ($entry->getValue ( 'pwdExpireWarning', 'single' )) ? $entry->getValue ( 'pwdExpireWarning', 'single' ) : 0;
				$this->policies [$dn] ['pwdGraceAuthnLimit'] = ($entry->getValue ( 'pwdGraceAuthnLimit', 'single' )) ? $entry->getValue ( 'pwdGraceAuthnLimit', 'single' ) : 0;
			}
		}
	}
	function get_user_info($login) {
		$filter  = '(' . $this->login_attr . '=' . $login . ')';
		$options = array (
				'scope'      => 'sub',
				'attributes' => array (
						'pwdChangedTime',
						'pwdGraceUseTime',
						'pwdPolicySubEntry' 
				) 
		);
		
		$result = $this->ldap->search ( $this->basedn, $filter, $options );
		
		if (is_a ( $result, 'PEAR_Error' ) || ($result->count () != 1)) {
			$this->_debug ( 'user not found, or found more than one: ' . $result->getMessage () );
			return FALSE;
		} else {
			$expiring = FALSE;
			$entry    = $result->shiftEntry ();
			$dn       = $entry->dn ();
			$pwd_ct   = $entry->getValue ( 'pwdChangedTime', 'single' );
			
			if (preg_match ( '/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})(\w+)/', $pwd_ct, $match )) {
				$now = new DateTime ( 'NOW' );
				$dct = new DateTime ( $match [1] . '-' . $match [2] . '-' . $match [3] . ' ' . $match [4] . ':' . $match [5] . ':' . $match [6] );
				
				$this->_debug ( 'DN: ' . $dn );
				$policy = ($entry->getValue ( 'pwdPolicySubEntry', 'single' )) ? $entry->getValue ( 'pwdPolicySubEntry', 'single' ) : $this->default_policy;
				$this->_debug ( 'policy: ' . $policy );
				
				if ($this->policies [$policy] ['pwdMaxAge'] > 0) {
					$end = $dct->add ( new DateInterval ( 'PT' . $this->policies [$policy] ['pwdMaxAge'] . 'S' ) );
					$this->end_date = $end->format ( 'Y-m-d h:m:s' );
					
					$this->_debug ( 'END: ' . $end_date . ' (' . $end->getTimestamp () . '), teraz: ' . $now->getTimestamp () . ", warning: " . $this->policies [$policy] ['pwdExpireWarning'] );
					if ($now > $end) {
						$this->expired = true;
					} elseif ($this->policies [$policy] ['pwdExpireWarning'] > $end->getTimestamp () - $now->getTimestamp ()) {
						$expiring = true;
					}
				}
			}
			
			return $expiring || $this->expired;
		}
	}
	private function _debug($str) {
		if ($this->debug) {
			rcube::write_log ( $this->log_file, $str );
		}
	}
}

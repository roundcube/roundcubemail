<?php

/**
 * Simple LDAP Password Driver
 *
 * Driver for passwords stored in LDAP
 * This driver is based on Edouard's LDAP Password Driver, but does not
 * require PEAR's Net_LDAP2 to be installed
 * 
 * @version 1.0 (2010-07-31)
 * @author Wout Decre <wout@canodus.be>
 */
function password_save($curpass, $passwd)
{
	$rcmail = rcmail::get_instance();

	// Connect
	if (!$ds = ldap_connect($rcmail->config->get('password_ldap_host'), $rcmail->config->get('password_ldap_port'))) {
		ldap_unbind($ds);
		return PASSWORD_CONNECT_ERROR;
	}

	// Set protocol version
	if (!ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $rcmail->config->get('password_ldap_version'))) {
		ldap_unbind($ds);
		return PASSWORD_CONNECT_ERROR;
	}

	// Start TLS
	if ($rcmail->config->get('password_ldap_starttls')) {
		if (!ldap_start_tls($ds)) {
			ldap_unbind($ds);
			return PASSWORD_CONNECT_ERROR;
		}
	}

	// Build user DN
	if ($user_dn = $rcmail->config->get('password_ldap_userDN_mask')) {
		$user_dn = ldap_simple_substitute_vars($user_dn);
	} else {
		$user_dn = ldap_simple_search_userdn($rcmail, $ds);
	}

	if (empty($user_dn)) {
		ldap_unbind($ds);
		return PASSWORD_CONNECT_ERROR;
	}

	// Connection method
	switch ($rcmail->config->get('password_ldap_method')) {
		case 'admin':
			$binddn = $rcmail->config->get('password_ldap_adminDN');
			$bindpw = $rcmail->config->get('password_ldap_adminPW');
			break;
		case 'user':
		default:
			$binddn = $user_dn;
			$bindpw = $curpass;
			break;
	}


	$crypted_pass = ldap_simple_hash_password($passwd, $rcmail->config->get('password_ldap_encodage'));
	$lchattr      = $rcmail->config->get('password_ldap_lchattr');
	$pwattr       = $rcmail->config->get('password_ldap_pwattr');
    $smbpwattr    = $rcmail->config->get('password_ldap_samba_pwattr');
    $smblchattr   = $rcmail->config->get('password_ldap_samba_lchattr');
    $samba        = $rcmail->config->get('password_ldap_samba');

    // Support password_ldap_samba option for backward compat.
    if ($samba && !$smbpwattr) {
        $smbpwattr  = 'sambaNTPassword';
        $smblchattr = 'sambaPwdLastSet';
    }

	// Crypt new password
	if (!$crypted_pass) {
		return PASSWORD_CRYPT_ERROR;
	}

    // Crypt new Samba password
    if ($smbpwattr && !($samba_pass = ldap_simple_hash_password($passwd, 'samba'))) {
	    return PASSWORD_CRYPT_ERROR;
    }

	// Bind
	if (!ldap_bind($ds, $binddn, $bindpw)) {
		ldap_unbind($ds);
		return PASSWORD_CONNECT_ERROR;
	}

	$entree[$pwattr] = $crypted_pass;

	// Update PasswordLastChange Attribute if desired
	if ($lchattr) {
		$entree[$lchattr] = (int)(time() / 86400);
	}

    // Update Samba password
    if ($smbpwattr) {
        $entree[$smbpwattr] = $samba_pass;
    }

    // Update Samba password last change
    if ($smblchattr) {
        $entree[$smblchattr] = time();
    }

	if (!ldap_modify($ds, $user_dn, $entree)) {
		ldap_unbind($ds);
		return PASSWORD_CONNECT_ERROR;
	}

	// All done, no error
	ldap_unbind($ds);
	return PASSWORD_SUCCESS;
}

/**
 * Bind with searchDN and searchPW and search for the user's DN
 * Use search_base and search_filter defined in config file
 * Return the found DN
 */
function ldap_simple_search_userdn($rcmail, $ds)
{
	/* Bind */
	if (!ldap_bind($ds, $rcmail->config->get('password_ldap_searchDN'), $rcmail->config->get('password_ldap_searchPW'))) {
		return false;
	}

	/* Search for the DN */
	if (!$sr = ldap_search($ds, $rcmail->config->get('password_ldap_search_base'), ldap_simple_substitute_vars($rcmail->config->get('password_ldap_search_filter')))) {
		return false;
	}

	/* If no or more entries were found, return false */
	if (ldap_count_entries($ds, $sr) != 1) {
		return false;
	}

	return ldap_get_dn($ds, ldap_first_entry($ds, $sr));
}

/**
 * Substitute %login, %name, %domain, %dc in $str
 * See plugin config for details
 */
function ldap_simple_substitute_vars($str)
{
	$str = str_replace('%login', $_SESSION['username'], $str);
	$str = str_replace('%l', $_SESSION['username'], $str);

	$parts = explode('@', $_SESSION['username']);

	if (count($parts) == 2) {
        $dc = 'dc='.strtr($parts[1], array('.' => ',dc=')); // hierarchal domain string

		$str = str_replace('%name', $parts[0], $str);
        $str = str_replace('%n', $parts[0], $str);
        $str = str_replace('%dc', $dc, $str);
		$str = str_replace('%domain', $parts[1], $str);
		$str = str_replace('%d', $parts[1], $str);
	}

	return $str;
}

/**
 * Code originaly from the phpLDAPadmin development team
 * http://phpldapadmin.sourceforge.net/
 *
 * Hashes a password and returns the hash based on the specified enc_type
 */
function ldap_simple_hash_password($password_clear, $encodage_type)
{
	$encodage_type = strtolower($encodage_type);
	switch ($encodage_type) {
		case 'crypt':
			$crypted_password = '{CRYPT}' . crypt($password_clear, ldap_simple_random_salt(2));
			break;
		case 'ext_des':
			/* Extended DES crypt. see OpenBSD crypt man page */
			if (!defined('CRYPT_EXT_DES') || CRYPT_EXT_DES == 0) {
				/* Your system crypt library does not support extended DES encryption */
				return false;
			}
			$crypted_password = '{CRYPT}' . crypt($password_clear, '_' . ldap_simple_random_salt(8));
			break;
		case 'md5crypt':
			if (!defined('CRYPT_MD5') || CRYPT_MD5 == 0) {
				/* Your system crypt library does not support md5crypt encryption */
				return false;
			}
			$crypted_password = '{CRYPT}' . crypt($password_clear, '$1$' . ldap_simple_random_salt(9));
			break;
		case 'blowfish':
			if (!defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH == 0) {
				/* Your system crypt library does not support blowfish encryption */
				return false;
			}
			/* Hardcoded to second blowfish version and set number of rounds */
			$crypted_password = '{CRYPT}' . crypt($password_clear, '$2a$12$' . ldap_simple_random_salt(13));
			break;
		case 'md5':
			$crypted_password = '{MD5}' . base64_encode(pack('H*', md5($password_clear)));
			break;
		case 'sha':
			if (function_exists('sha1')) {
				/* Use PHP 4.3.0+ sha1 function, if it is available */
				$crypted_password = '{SHA}' . base64_encode(pack('H*', sha1($password_clear)));
			} else if (function_exists('mhash')) {
				$crypted_password = '{SHA}' . base64_encode(mhash(MHASH_SHA1, $password_clear));
			} else {
				/* Your PHP install does not have the mhash() function */
				return false;
			}
			break;
		case 'ssha':
			if (function_exists('mhash') && function_exists('mhash_keygen_s2k')) {
				mt_srand((double) microtime() * 1000000 );
				$salt = mhash_keygen_s2k(MHASH_SHA1, $password_clear, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
				$crypted_password = '{SSHA}' . base64_encode(mhash(MHASH_SHA1, $password_clear . $salt) . $salt);
			} else {
				/* Your PHP install does not have the mhash() function */
				return false;
			}
			break;
		case 'smd5':
			if (function_exists('mhash') && function_exists('mhash_keygen_s2k')) {
				mt_srand((double) microtime() * 1000000 );
				$salt = mhash_keygen_s2k(MHASH_MD5, $password_clear, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
				$crypted_password = '{SMD5}' . base64_encode(mhash(MHASH_MD5, $password_clear . $salt) . $salt);
			} else {
				/* Your PHP install does not have the mhash() function */
				return false;
			}
			break;
        case 'samba':
            if (function_exists('hash')) {
                $crypted_password = hash('md4', rcube_charset_convert($password_clear, RCMAIL_CHARSET, 'UTF-16LE'));
                $crypted_password = strtoupper($crypted_password);
            } else {
				/* Your PHP install does not have the hash() function */
				return false;
            }
            break;
		case 'clear':
		default:
			$crypted_password = $password_clear;
	}

	return $crypted_password;
}

/**
 * Code originaly from the phpLDAPadmin development team
 * http://phpldapadmin.sourceforge.net/
 *
 * Used to generate a random salt for crypt-style passwords
 */
function ldap_simple_random_salt($length)
{
	$possible = '0123456789' . 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . './';
	$str = '';
	// mt_srand((double)microtime() * 1000000);
	while (strlen($str) < $length) {
		$str .= substr($possible, (rand() % strlen($possible)), 1);
	}

	return $str;
}

<?php

/**
 * Simple LDAP Password Driver
 *
 * Driver for passwords stored in LDAP
 * This driver is based on Edouard's LDAP Password Driver, but does not
 * require PEAR's Net_LDAP2 to be installed
 *
 * @version 2.1
 * @author Wout Decre <wout@canodus.be>
 * @author Aleksander Machniak <machniak@kolabsys.com>
 *
 * Copyright (C) The Roundcube Dev Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/.
 */

class rcube_ldap_simple_password
{
    protected $debug = false;
    protected $user;
    protected $conn;


    public function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();

        $lchattr      = $rcmail->config->get('password_ldap_lchattr');
        $pwattr       = $rcmail->config->get('password_ldap_pwattr', 'userPassword');
        $smbpwattr    = $rcmail->config->get('password_ldap_samba_pwattr');
        $smblchattr   = $rcmail->config->get('password_ldap_samba_lchattr');
        $samba        = $rcmail->config->get('password_ldap_samba');
        $pass_mode    = $rcmail->config->get('password_ldap_encodage', 'md5-crypt');
        $crypted_pass = password::hash_password($passwd, $pass_mode);

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
        if ($smbpwattr && !($samba_pass = password::hash_password($passwd, 'samba'))) {
            return PASSWORD_CRYPT_ERROR;
        }

        // Connect and bind
        $ret = $this->connect($curpass);
        if ($ret !== true) {
            return $ret;
        }

        $entry[$pwattr] = $crypted_pass;

        // Update PasswordLastChange Attribute if desired
        if ($lchattr) {
            $entry[$lchattr] = (int)(time() / 86400);
        }

        // Update Samba password
        if ($smbpwattr) {
            $entry[$smbpwattr] = $samba_pass;
        }

        // Update Samba password last change
        if ($smblchattr) {
            $entry[$smblchattr] = time();
        }

        $this->_debug("C: Modify {$this->user}: " . print_r($entry, true));

        if (!ldap_modify($this->conn, $this->user, $entry)) {
            $this->_debug("S: ".ldap_error($this->conn));

            $errno = ldap_errno($this->conn);

            ldap_unbind($this->conn);

            if ($errno == 0x13) {
                return PASSWORD_CONSTRAINT_VIOLATION;
            }

            return PASSWORD_CONNECT_ERROR;
        }

        $this->_debug("S: OK");

        // All done, no error
        ldap_unbind($this->conn);

        return PASSWORD_SUCCESS;
    }

    /**
     * Connect and bind to LDAP server
     */
    function connect($curpass)
    {
        $rcmail = rcmail::get_instance();

        $this->debug = $rcmail->config->get('ldap_debug');
        $ldap_host   = $rcmail->config->get('password_ldap_host', 'localhost');
        $ldap_port   = $rcmail->config->get('password_ldap_port', '389');
        $ldap_uri    = $this->_host2uri($ldap_host, $ldap_port);

        $this->_debug("C: Connect [{$ldap_uri}]");

        // Connect
        if (!($ds = ldap_connect($ldap_uri))) {
            $this->_debug("S: NOT OK");

            rcube::raise_error([
                    'code' => 100, 'type' => 'ldap',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Could not connect to LDAP server"
                ],
                true
            );

            return PASSWORD_CONNECT_ERROR;
        }

        $this->_debug("S: OK");

        // Set protocol version
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION,
            $rcmail->config->get('password_ldap_version', '3'));

        // Start TLS
        if ($rcmail->config->get('password_ldap_starttls')) {
            if (!ldap_start_tls($ds)) {
                ldap_unbind($ds);
                return PASSWORD_CONNECT_ERROR;
            }
        }

        // other plugins might want to modify user DN
        $plugin = $rcmail->plugins->exec_hook('password_ldap_bind',
            ['user_dn' => '', 'conn' => $ds]
        );

        // Build user DN
        if (!empty($plugin['user_dn'])) {
            $user_dn = $plugin['user_dn'];
        }
        else if ($user_dn = $rcmail->config->get('password_ldap_userDN_mask')) {
            $user_dn = self::substitute_vars($user_dn);
        }
        else {
            $user_dn = $this->search_userdn($rcmail, $ds);
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

        $this->_debug("C: Bind $binddn, pass: **** [" . strlen($bindpw) . "]");

        // Bind
        if (!ldap_bind($ds, $binddn, $bindpw)) {
            $this->_debug("S: ".ldap_error($ds));

            ldap_unbind($ds);

            return PASSWORD_CONNECT_ERROR;
        }

        $this->_debug("S: OK");

        $this->conn = $ds;
        $this->user = $user_dn;

        return true;
    }

    /**
     * Bind with searchDN and searchPW and search for the user's DN
     * Use search_base and search_filter defined in config file
     * Return the found DN
     */
    function search_userdn($rcmail, $ds)
    {
        $search_user   = $rcmail->config->get('password_ldap_searchDN');
        $search_pass   = $rcmail->config->get('password_ldap_searchPW');
        $search_base   = $rcmail->config->get('password_ldap_search_base');
        $search_filter = $rcmail->config->get('password_ldap_search_filter');

        if (empty($search_filter)) {
            return false;
        }

        $this->_debug("C: Bind " . ($search_user ? $search_user : '[anonymous]'));

        // Bind
        if (!ldap_bind($ds, $search_user, $search_pass)) {
            $this->_debug("S: ".ldap_error($ds));
            return false;
        }

        $this->_debug("S: OK");

        $search_base   = self::substitute_vars($search_base);
        $search_filter = self::substitute_vars($search_filter);

        $this->_debug("C: Search $search_base for $search_filter");

        // Search for the DN
        if (!($sr = ldap_search($ds, $search_base, $search_filter))) {
            $this->_debug("S: ".ldap_error($ds));
            return false;
        }

        $found = ldap_count_entries($ds, $sr);

        $this->_debug("S: OK [found $found records]");

        // If no or more entries were found, return false
        if ($found != 1) {
            return false;
        }

        return ldap_get_dn($ds, ldap_first_entry($ds, $sr));
    }

    /**
     * Substitute %login, %name, %domain, %dc in $str
     * See plugin config for details
     */
    public static function substitute_vars($str)
    {
        $str = str_replace('%login', $_SESSION['username'], $str);
        $str = str_replace('%l', $_SESSION['username'], $str);

        $parts = explode('@', $_SESSION['username']);

        if (count($parts) == 2) {
            $dc = 'dc='.strtr($parts[1], ['.' => ',dc=']); // hierarchal domain string

            $str = str_replace('%name', $parts[0], $str);
            $str = str_replace('%n', $parts[0], $str);
            $str = str_replace('%dc', $dc, $str);
            $str = str_replace('%domain', $parts[1], $str);
            $str = str_replace('%d', $parts[1], $str);
        }
        else if (count($parts) == 1) {
            $str = str_replace('%name', $parts[0], $str);
            $str = str_replace('%n', $parts[0], $str);
        }

        return $str;
    }

    /**
     * Prints debug info to the log
     */
    protected function _debug($str)
    {
        if ($this->debug) {
            rcube::write_log('ldap', $str);
        }
    }

    /**
     * Convert LDAP host/port into URI
     */
    private static function _host2uri($host, $port = null)
    {
        if (stripos($host, 'ldapi://') === 0) {
            return $host;
        }

        if (strpos($host, '://') === false) {
            $host = ($port == 636 ? 'ldaps' : 'ldap') . '://' . $host;
        }

        if ($port && !preg_match('/:[0-9]+$/', $host)) {
            $host .= ':' . $port;
        }

        return $host;
    }
}

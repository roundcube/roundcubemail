<?php

/**
 * LDAP - Password Modify Extended Operation Driver
 *
 * Driver for passwords stored in LDAP
 * This driver is based on Simple LDAP Password Driver, but uses
 * Password Modify Extended Operation
 *
 * @version 1.0
 * @author Peter Kubica <peter@kubica.ch>
 *
 * Copyright (C) 2005-2019, The Roundcube Dev Team
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
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

class rcube_ldap_exop_password
{
    private $debug = false;

    function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();

        $this->debug = $rcmail->config->get('ldap_debug');

        $ldap_host = $rcmail->config->get('password_ldap_host', 'localhost');
        $ldap_port = $rcmail->config->get('password_ldap_port', '389');

        $this->_debug("C: Connect to $ldap_host:$ldap_port");

        // Connect
        if (!$ds = ldap_connect($ldap_host, $ldap_port)) {
            $this->_debug("S: NOT OK");

            rcube::raise_error(array(
                    'code' => 100, 'type' => 'ldap',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Could not connect to LDAP server"
                ),
                true);

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

        // include 'ldap' driver, we share some static methods with it
        require_once INSTALL_PATH . 'plugins/password/drivers/ldap.php';

        // other plugins might want to modify user DN
        $plugin = $rcmail->plugins->exec_hook('password_ldap_bind', array(
            'user_dn' => '', 'conn' => $ds));

        // Build user DN
        if (!empty($plugin['user_dn'])) {
            $user_dn = $plugin['user_dn'];
        }
        else if ($user_dn = $rcmail->config->get('password_ldap_userDN_mask')) {
            $user_dn = rcube_ldap_password::substitute_vars($user_dn);
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

        if (!function_exists('ldap_exop_passwd')) {
            $this->_debug("ldap_exop_passwd not supported");
            return PASSWORD_CONNECT_ERROR;
        }

        if (!ldap_exop_passwd($ds, $user_dn, $curpass, $passwd)) {
            $this->_debug("S: ".ldap_error($ds));

            $errno = ldap_errno($ds);

            ldap_unbind($ds);

            if ($errno == 0x13) {   // LDAP_CONSTRAINT_VIOLATION
                return PASSWORD_CONSTRAINT_VIOLATION;
            }

            return PASSWORD_CONNECT_ERROR;
        }

        $this->_debug("S: OK");

        // All done, no error
        ldap_unbind($ds);

        return PASSWORD_SUCCESS;
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

        $search_base   = rcube_ldap_password::substitute_vars($search_base);
        $search_filter = rcube_ldap_password::substitute_vars($search_filter);

        $this->_debug("C: Search $search_base for $search_filter");

        // Search for the DN
        if (!$sr = ldap_search($ds, $search_base, $search_filter)) {
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
     * Prints debug info to the log
     */
    private function _debug($str)
    {
        if ($this->debug) {
            rcube::write_log('ldap', $str);
        }
    }
}

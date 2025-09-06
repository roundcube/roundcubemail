<?php

/*
 * LDAP - Password Modify Extended Operation Driver
 *
 * Driver for passwords stored in SAMBA Active Directory
 * This driver is based on Simple LDAP Password Driver, but
 * updates only single attribute: unicodePwd
 *
 * @version 1.0
 * @author Jonas Holm Bundgaard <jhb@jbweb.dk>
 * Based on code by Peter Kubica <peter@kubica.ch>
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

require_once __DIR__ . '/ldap_simple.php';

class rcube_ldap_samba_ad_password extends rcube_ldap_simple_password
{
    #[Override]
    public function save($curpass, $passwd)
    {
        if (!function_exists('ldap_mod_replace')) {
            rcube::raise_error([
                'code' => 100,
                'type' => 'ldap',
                'message' => 'Password plugin: ldap_mod_replace() not supported',
            ], true);

            return PASSWORD_ERROR;
        }

        // Connect and bind
        $ret = $this->connect($curpass);
        if ($ret !== true) {
            return $ret;
        }

        $hash = password::hash_password($passwd, 'ad');

        $entry = ['unicodePwd' => $hash];

        $this->_debug("C: Replace password for {$this->user}: " . print_r($entry, true));

        if (!ldap_mod_replace($this->conn, $this->user, $entry)) {
            $this->_debug('S: ' . ldap_error($this->conn));

            $errno = ldap_errno($this->conn);

            ldap_unbind($this->conn);

            if ($errno == 0x13) {
                return PASSWORD_CONSTRAINT_VIOLATION;
            }

            return PASSWORD_CONNECT_ERROR;
        }

        $this->_debug('S: OK');

        // All done, no error
        ldap_unbind($this->conn);

        return PASSWORD_SUCCESS;
    }
}

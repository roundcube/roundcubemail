<?php

/**
 * LDAP - Password Modify Extended Operation Driver
 *
 * Driver for passwords stored in LDAP
 * This driver is based on Simple LDAP Password Driver, but uses
 * Password Modify Extended Operation
 * PHP >= 7.2 required
 *
 * @version 1.0
 * @author Peter Kubica <peter@kubica.ch>
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

class rcube_ldap_exop_password extends rcube_ldap_simple_password
{
    function save($curpass, $passwd)
    {
        if (!function_exists('ldap_exop_passwd')) {
            rcube::raise_error([
                    'code' => 100, 'type' => 'ldap',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "ldap_exop_passwd not supported"
                ],
                true
            );

            return PASSWORD_ERROR;
        }

        // Connect and bind
        $ret = $this->connect($curpass);
        if ($ret !== true) {
            return $ret;
        }

        if (!ldap_exop_passwd($this->conn, $this->user, $curpass, $passwd)) {
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
}

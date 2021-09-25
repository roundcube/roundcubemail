<?php

/**
 * SQL Password Driver
 *
 * Driver for passwords stored in SQL database
 *
 * @version 2.1
 * @author Aleksander Machniak <alec@alec.pl>
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
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */
class rcube_sql_password
{
    /**
     * Update current user password
     *
     * @param string $curpass Current password
     * @param string $passwd  New password
     *
     * @return int Result
     */
    function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();

        if (!($sql = $rcmail->config->get('password_query'))) {
            $sql = 'SELECT update_passwd(%c, %u)';
        }

        if ($dsn = $rcmail->config->get('password_db_dsn')) {
            $db = rcube_db::factory(self::parse_dsn($dsn), '', false);
            $db->set_debug((bool)$rcmail->config->get('sql_debug'));
        }
        else {
            $db = $rcmail->get_dbh();
        }

        if ($db->is_error()) {
            return PASSWORD_ERROR;
        }

        // new password - default hash method
        if (strpos($sql, '%P') !== false) {
            $password = password::hash_password($passwd);

            if ($password === false) {
                return PASSWORD_CRYPT_ERROR;
            }

            $sql = str_replace('%P',  $db->quote($password), $sql);
        }

        // old password - default hash method
        if (strpos($sql, '%O') !== false) {
            $password = password::hash_password($curpass);

            if ($password === false) {
                return PASSWORD_CRYPT_ERROR;
            }

            $sql = str_replace('%O',  $db->quote($password), $sql);
        }

        // Handle clear text passwords securely (#1487034)
        $sql_vars = [];
        if (preg_match_all('/%[p|o]/', $sql, $m)) {
            foreach ($m[0] as $var) {
                if ($var == '%p') {
                    $sql = preg_replace('/%p/', '?', $sql, 1);
                    $sql_vars[] = (string) $passwd;
                }
                else { // %o
                    $sql = preg_replace('/%o/', '?', $sql, 1);
                    $sql_vars[] = (string) $curpass;
                }
            }
        }

        $local_part  = $rcmail->user->get_username('local');
        $domain_part = $rcmail->user->get_username('domain');
        $username    = $_SESSION['username'];
        $host        = $_SESSION['imap_host'];

        // convert domains to/from punycode
        if ($rcmail->config->get('password_idn_ascii')) {
            $domain_part = rcube_utils::idn_to_ascii($domain_part);
            $username    = rcube_utils::idn_to_ascii($username);
            $host        = rcube_utils::idn_to_ascii($host);
        }
        else {
            $domain_part = rcube_utils::idn_to_utf8($domain_part);
            $username    = rcube_utils::idn_to_utf8($username);
            $host        = rcube_utils::idn_to_utf8($host);
        }

        // at least we should always have the local part
        $sql = str_replace('%l', $db->quote($local_part, 'text'), $sql);
        $sql = str_replace('%d', $db->quote($domain_part, 'text'), $sql);
        $sql = str_replace('%u', $db->quote($username, 'text'), $sql);
        $sql = str_replace('%h', $db->quote($host, 'text'), $sql);

        $res = $db->query($sql, $sql_vars);

        if (!$db->is_error()) {
            if (strtolower(substr(trim($sql),0,6)) == 'select') {
                if ($db->fetch_array($res)) {
                    return PASSWORD_SUCCESS;
                }
            }
            else {
                // Note: Don't be tempted to check affected_rows = 1. For some queries
                // (e.g. INSERT ... ON DUPLICATE KEY UPDATE) the result can be 2.
                if ($db->affected_rows($res) > 0) {
                    return PASSWORD_SUCCESS;
                }
            }
        }

        return PASSWORD_ERROR;
    }

    /**
     * Parse DSN string and replace host variables
     *
     * @param string $dsn DSN string
     *
     * @return string DSN string
     */
    protected static function parse_dsn($dsn)
    {
        if (strpos($dsn, '%')) {
            // parse DSN and replace variables in hostname
            $parsed = rcube_db::parse_dsn($dsn);
            $host   = rcube_utils::parse_host($parsed['hostspec']);

            // build back the DSN string
            if ($host != $parsed['hostspec']) {
                $dsn = str_replace('@' . $parsed['hostspec'], '@' . $host, $dsn);
            }
        }

        return $dsn;
    }
}

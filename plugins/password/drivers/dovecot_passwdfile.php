<?php

/**
 * Dovecot passwdfile Password Driver
 *
 * Driver that adds functionality to change the passwords in dovecot v2 passwd-file files.
 *
 * @version 1.2
 *
 * Copyright (C) 2017, hostNET Medien GmbH, www.hostnet.de
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

class rcube_dovecot_passwdfile_password
{
    /**
     * This method is called from roundcube to change the password
     *
     * roundcube already validated the old password so we just need to change it at this point
     *
     * @param string $currpass Current password
     * @param string $newpass  New password
     * @param string $username Login username (configured form based on $config['password_username_format'])
     *
     * @return int PASSWORD_SUCCESS|PASSWORD_CONNECT_ERROR|PASSWORD_ERROR
     */
    public function save(string $currpass, string $newpass, string $username): int
    {
        $rcmail = rcmail::get_instance();

        $passwd_file = $rcmail->config->get('password_dovecot_passwdfile_path') ?: '/etc/mail/imap.passwd';
        $passwd_file = self::expand_config_value($passwd_file);

        $password = password::hash_password($newpass);
        $username = escapeshellcmd($username); // FIXME: Do we need this?
        $content = '';

        // read the entire mail user file
        $fp = fopen($passwd_file, 'r');

        if (empty($fp)) {
            rcube::raise_error("Password plugin: Unable to read password file {$passwd_file}.", true);
            return PASSWORD_CONNECT_ERROR;
        }

        if (flock($fp, \LOCK_EX)) {
            // Read the file and replace the user password
            while (($line = fgets($fp, 40960)) !== false) {
                if (str_starts_with($line, "{$username}:")) {
                    $tokens = explode(':', $line);
                    $tokens[1] = $password;
                    $line = implode(':', $tokens);
                }

                $content .= $line;
            }

            // Write back the entire file
            if (file_put_contents($passwd_file, $content)) {
                flock($fp, \LOCK_UN);
                fclose($fp);

                return PASSWORD_SUCCESS;
            }
        }

        fclose($fp);

        rcube::raise_error("Password plugin: Failed to save file {$passwd_file}.", true);

        return PASSWORD_ERROR;
    }

    private static function expand_config_value(string $subject): string
    {
        return strtr($subject, [
            '%l' => self::get_username_part_idn_aware('local'),
            '%d' => self::get_username_part_idn_aware('domain'),
            '%u' => $_SESSION['username'],
        ]);
    }

    private static function get_username_part_idn_aware(string $part): string
    {
        $rcmail = rcmail::get_instance();

        $part_value = $rcmail->user->get_username($part);

        if ($rcmail->config->get('password_idn_ascii')) {
            return rcube_utils::idn_to_ascii($part_value);
        }

        return rcube_utils::idn_to_utf8($part_value);
    }
}

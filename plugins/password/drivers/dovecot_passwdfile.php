<?php

/**
 * Dovecot passwdfile Password Driver
 *
 * Driver that adds functionality to change the passwords in dovecot v2 passwd-file files.
 * The code is derived from the Plugin examples by The Roundcube Dev Team
 *
 * On vanilla dovecot v2 environments, use the correct values for these config settings, too:
 *
 * $config['password_dovecot_passwdfile_path']: The path of your dovecot passwd-file '/path/to/filename'
 * $config['password_dovecotpw']: Full path and 'pw' command of doveadm binary - like '/usr/local/bin/doveadm pw'
 * $config['password_dovecotpw_method']: Dovecot hashing algo (https://doc.dovecot.org/2.3/configuration_manual/authentication/password_schemes/#authentication-password-schemes)
 * $config['password_dovecotpw_with_method']: True if you want the hashing algo as prefix in your passwd-file
 *
 * @version 1.1
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
    public function save($currpass, $newpass, $username)
    {
        $rcmail       = rcmail::get_instance();
        $mailuserfile = $rcmail->config->get('password_dovecot_passwdfile_path') ?: '/etc/mail/imap.passwd';

        $password = password::hash_password($newpass);
        $username = escapeshellcmd($username); // FIXME: Do we need this?
        $content  = '';

        // read the entire mail user file
        $fp = fopen($mailuserfile, 'r');

        if (empty($fp)) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Unable to read password file $mailuserfile."
                ],
                true, false
            );

            return PASSWORD_CONNECT_ERROR;
        }

        if (flock($fp, LOCK_EX)) {
            // Read the file and replace the user password
            while (($line = fgets($fp, 40960)) !== false) {
                if (strpos($line, "$username:") === 0) {
                    $pos  = strpos($line, ':', strlen("$username:") + 1);
                    $line = "$username:$password" . substr($line, $pos);
                }

                $content .= $line;
            }

            // Write back the entire file
            if (file_put_contents($mailuserfile, $content)) {
                flock($fp, LOCK_UN);
                fclose($fp);

                return PASSWORD_SUCCESS;
            }
        }

        fclose($fp);

        rcube::raise_error([
                'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Failed to save file $mailuserfile."
            ],
            true, false
        );

        return PASSWORD_ERROR;
    }
}

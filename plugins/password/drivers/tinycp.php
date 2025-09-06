<?php
/**
 * TinyCP driver
 *
 * Enable the password driver in Roundcube (https://roundcube.net/) for the
 * TinyCP Lightweight Linux Control Panel (https://tinycp.com/).
 * See README for instructions, Connector Required.
 *
 * @version 1.2
 * @author Ricky Mendoza (HelloWorld@rickymendoza.dev)
 * 
 * Copyright (C) 2020 Ricky Mendoza
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

class rcube_tinycp_password
{
    public function save($currpass, $newpass, $username)
    {
        require_once 'TinyCPConnector.php';

        $tinycp_host   = rcmail::get_instance()->config->get('password_tinycp_host');
        $tinycp_port   = rcmail::get_instance()->config->get('password_tinycp_port');
        $tinycp_user   = rcmail::get_instance()->config->get('password_tinycp_user');
        $tinycp_pass   = rcmail::get_instance()->config->get('password_tinycp_pass');
        $error_message = '';

        if ($tinycp_host && $tinycp_port && $tinycp_user && $tinycp_pass) {
            try {
                $tcp = new TinyCPConnector($tinycp_host, $tinycp_port);
                $tcp->Auth($tinycp_user, $tinycp_pass);
                $tcp->mail___mailserver___email_pass_change2($username, $newpass);
            }
            catch (Exception $e) {
                $error_message = $e->getMessage();
            }
        }
        else {
            $error_message = "Missing configuration value(s). ";
        }

        if ($error_message) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password driver: $error_message",
                ],
                true, false
            );

            return PASSWORD_ERROR;
        }

        return PASSWORD_SUCCESS;
    }
}

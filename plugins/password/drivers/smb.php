<?php

/**
 * smb Driver
 *
 * Driver that adds functionality to change the systems user password via
 * the 'smbpasswd' command.
 *
 * For installation instructions please read the README file.
 *
 * @version 2.0
 * @author Andy Theuninck <gohanman@gmail.com)
 *
 * Based on chpasswd roundcubemail password driver by
 * @author Alex Cartwright <acartwright@mutinydesign.co.uk)
 * and smbpasswd horde passwd driver by
 * @author  Rene Lund Jensen <Rene@lundjensen.net>
 *
 * Configuration settings:
 * password_smb_host    => samba host (default: localhost)
 * password_smb_cmd => smbpasswd binary (default: /usr/bin/smbpasswd)
 *
 * Copyright (C) 2005-2013, The Roundcube Dev Team
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

class rcube_smb_password
{

    public function save($currpass, $newpass)
    {
        $rcmail   = rcmail::get_instance();
        $host     = $rcmail->config->get('password_smb_host','localhost');
        $bin      = $rcmail->config->get('password_smb_cmd','/usr/bin/smbpasswd');
        $user     = $rcmail->config->get('password_smb_user', '%u');

        $local_part  = $rcmail->user->get_username('local');
        $domain_part = $rcmail->user->get_username('domain');

        $user     = str_replace('%l', $local_part, $user);
        $user     = str_replace('%d', $domain_part, $user);
        $user     = str_replace('%u', $_SESSION['username'], $user);
        $username = $user;

        $host     = rcube_utils::parse_host($host);
        $tmpfile  = tempnam(sys_get_temp_dir(),'smb');
        $cmd      = $bin . ' -r ' . $host . ' -s -U "' . $username . '" > ' . $tmpfile . ' 2>&1';
        $handle   = @popen($cmd, 'w');

        fputs($handle, $currpass."\n");
        fputs($handle, $newpass."\n");
        fputs($handle, $newpass."\n");
        @pclose($handle);
        $res = file($tmpfile);
        unlink($tmpfile);

        if (strstr($res[count($res) - 1], 'Password changed for user') !== false) {
            return PASSWORD_SUCCESS;
        }
        else {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to execute $cmd"
                ), true, false);
        }

        return PASSWORD_ERROR;
    }
}

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
 */

class rcube_smb_password
{

    public function save($currpass, $newpass)
    {
        $host = rcmail::get_instance()->config->get('password_smb_host','localhost');
        $bin = rcmail::get_instance()->config->get('password_smb_cmd','/usr/bin/smbpasswd');
        $username = $_SESSION['username'];

        $tmpfile = tempnam(sys_get_temp_dir(),'smb');
        $cmd = $bin . ' -r ' . $host . ' -s -U "' . $username . '" > ' . $tmpfile . ' 2>&1';
        $handle = @popen($cmd, 'w');
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
            raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to execute $cmd"
                ), true, false);
        }

        return PASSWORD_ERROR;
    }

}
?>

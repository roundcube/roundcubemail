<?php

/**
 * chpasswd Driver
 *
 * Driver that adds functionality to change the systems user password via
 * the 'chpasswd' command.
 *
 * For installation instructions please read the README file.
 *
 * @version 2.0
 * @author Alex Cartwright <acartwright@mutinydesign.co.uk>
 */

class rcube_chpasswd_password
{
    public function save($currpass, $newpass)
    {
        $cmd = rcmail::get_instance()->config->get('password_chpasswd_cmd');
        $username = $_SESSION['username'];

        $handle = popen($cmd, "w");
        fwrite($handle, "$username:$newpass\n");

        if (pclose($handle) == 0) {
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

<?php

/**
 * pw_usermod Driver
 *
 * Driver that adds functionality to change the systems user password via
 * the 'pw usermod' command.
 *
 * For installation instructions please read the README file.
 *
 * @version 2.0
 * @author Alex Cartwright <acartwright@mutinydesign.co.uk>
 * @author Adamson Huang <adomputer@gmail.com>
 */

class rcube_pw_usermod_password
{
    public function save($currpass, $newpass)
    {
        $username = $_SESSION['username'];
        $cmd = rcmail::get_instance()->config->get('password_pw_usermod_cmd');
        $cmd .= " $username > /dev/null";

        $handle = popen($cmd, "w");
        fwrite($handle, "$newpass\n");

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

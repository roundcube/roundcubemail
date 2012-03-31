<?php

/**
 * DBMail Password Driver
 *
 * Driver that adds functionality to change the users DBMail password.
 * The code is derrived from the Squirrelmail "Change SASL Password" Plugin
 * by Galen Johnson.
 *
 * It only works with dbmail-users on the same host where Roundcube runs
 * and requires shell access and gcc in order to compile the binary.
 *
 * For installation instructions please read the README file.
 *
 * @version 1.0
 */

class rcube_dbmail_password
{
    function password_save($currpass, $newpass)
    {
        $curdir   = INSTALL_PATH . 'plugins/password/helpers';
        $username = escapeshellcmd($_SESSION['username']);
        $args     = rcmail::get_instance()->config->get('password_dbmail_args', '');

        exec("$curdir/chgdbmailusers -c $username -w $newpass $args", $output, $returnvalue);

        if ($returnvalue == 0) {
            return PASSWORD_SUCCESS;
        }
        else {
            raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to execute $curdir/chgdbmailusers"
                ), true, false);
        }

        return PASSWORD_ERROR;
    }
}

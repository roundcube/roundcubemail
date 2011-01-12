<?php

/**
 * SASL Password Driver
 *
 * Driver that adds functionality to change the users Cyrus/SASL password.
 * The code is derrived from the Squirrelmail "Change SASL Password" Plugin
 * by Galen Johnson.
 *
 * It only works with saslpasswd2 on the same host where Roundcube runs
 * and requires shell access and gcc in order to compile the binary.
 *
 * For installation instructions please read the README file.
 *
 * @version 1.0
 * @author Thomas Bruederli
 */
 
function password_save($currpass, $newpass)
{
    $curdir = realpath(dirname(__FILE__));
    $username = escapeshellcmd($_SESSION['username']);
    $args = rcmail::get_instance()->config->get('password_saslpasswd_args', '');

    if ($fh = popen("$curdir/chgsaslpasswd -p $args $username", 'w')) {
        fwrite($fh, $newpass."\n");
        $code = pclose($fh);

        if ($code == 0)
            return PASSWORD_SUCCESS;
    }
    else {
        raise_error(array(
            'code' => 600,
            'type' => 'php',
            'file' => __FILE__, 'line' => __LINE__,
            'message' => "Password plugin: Unable to execute $curdir/chgsaslpasswd"
            ), true, false);
    }

    return PASSWORD_ERROR;
}

?>

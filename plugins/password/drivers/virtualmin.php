<?php

/**
 * Virtualmin Password Driver
 *
 * Driver that adds functionality to change the users Virtualmin password.
 * The code is derrived from the Squirrelmail "Change Cyrus/SASL Password" Plugin
 * by Thomas Bruederli.
 *
 * It only works with virtualmin on the same host where Roundcube runs
 * and requires shell access and gcc in order to compile the binary.
 *
 * @version 1.0
 * @author Martijn de Munnik
 */

function password_save($currpass, $newpass)
{
    $curdir = realpath(dirname(__FILE__));
    $username = escapeshellcmd($_SESSION['username']);
    $domain = substr(strrchr($username, "@"), 1);

    exec("$curdir/chgvirtualminpasswd modify-user --domain $domain --user $username --pass $newpass", $output, $returnvalue);

    if ($returnvalue == 0) {
        return PASSWORD_SUCCESS;
    }
    else {
        raise_error(array(
            'code' => 600,
            'type' => 'php',
            'file' => __FILE__, 'line' => __LINE__,
            'message' => "Password plugin: Unable to execute $curdir/chgvirtualminpasswd"
            ), true, false);
    }

    return PASSWORD_ERROR;
}

?>

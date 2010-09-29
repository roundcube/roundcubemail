<?php

/**
 * PAM Password Driver
 *
 * @version 1.0
 * @author Aleksander Machniak
 */
 
function password_save($currpass, $newpass)
{
    $user = $_SESSION['username'];

    if (extension_loaded('pam')) {
        if (pam_auth($user, $currpass, $error, false)) {
            if (pam_chpass($user, $currpass, $newpass)) {
                return PASSWORD_SUCCESS;
            }
        }
        else {
            raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__,
                'message' => "Password plugin: PAM authentication failed for user $user: $error"
                ), true, false);
        }
    }
    else {
        raise_error(array(
            'code' => 600,
            'type' => 'php',
            'file' => __FILE__,
            'message' => "Password plugin: PECL-PAM module not loaded"
            ), true, false);
    }

    return PASSWORD_ERROR;
}

?>

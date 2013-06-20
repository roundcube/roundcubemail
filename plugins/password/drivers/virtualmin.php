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
 * @version 3.0
 * @author Martijn de Munnik
 */

class rcube_virtualmin_password
{
    function save($currpass, $newpass)
    {
        $rcmail = rcmail::get_instance();

        $format   = $rcmail->config->get('password_virtualmin_format', 0);
        $username = $_SESSION['username'];

        switch ($format) {
        case 1: // username%domain
            $domain = substr(strrchr($username, "%"), 1);
            break;
        case 2: // username.domain (could be bogus)
            $pieces = explode(".", $username);
            $domain = $pieces[count($pieces)-2]. "." . end($pieces);
            break;
        case 3: // domain.username (could be bogus)
            $pieces = explode(".", $username);
            $domain = $pieces[0]. "." . $pieces[1];
            break;
        case 4: // username-domain
            $domain = substr(strrchr($username, "-"), 1);
            break;
        case 5: // domain-username
            $domain = str_replace(strrchr($username, "-"), "", $username);
            break;
        case 6: // username_domain
            $domain = substr(strrchr($username, "_"), 1);
            break;
        case 7: // domain_username
            $pieces = explode("_", $username);
            $domain = $pieces[0];
            break;
        case 8: // domain taken from alias, username left as it was
            $email = $rcmail->user->data['alias'];
            $domain = substr(strrchr($email, "@"), 1);
            break;
        default: // username@domain
            $domain = substr(strrchr($username, "@"), 1);
        }

        $username = escapeshellcmd($username);
        $domain   = escapeshellcmd($domain);
        $newpass  = escapeshellcmd($newpass);
        $curdir   = RCUBE_PLUGINS_DIR . 'password/helpers';

        exec("$curdir/chgvirtualminpasswd modify-user --domain $domain --user $username --pass $newpass", $output, $returnvalue);

        if ($returnvalue == 0) {
            return PASSWORD_SUCCESS;
        }
        else {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to execute $curdir/chgvirtualminpasswd"
                ), true, false);
        }

        return PASSWORD_ERROR;
    }
}

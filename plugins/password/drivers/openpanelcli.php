<?php
/**
 * openpanel-cli Driver
 *
 * Driver that adds functionality to change the email user password via
 * the 'openpanel-cli' command.
 *
 * For installation instructions please read the README file.
 *
 * @version 1.0
 * @author Nicola Sarobba <nicola.sarobba AT gmail.com>
 * Thanks to exel and Habbie on IRC #openpanel.
 *
 * Configuration settings:
 * password_openpanelcli_cmd => default: /usr/bin/openpanel-cli
 *
 * WARNING: the following could be a security risk.
 * Add the this to sudoers file:
 * www-data ALL = NOPASSWD: /usr/bin/openpanel-cli
 * 
 */

class rcube_openpanelcli_password
{

    public function save($currpass, $newpass)
    {
        $bin_openpanelcli = rcmail::get_instance()->config->get('password_openpanelcli_cmd','/usr/bin/openpanel-cli');
              
        $username = $_SESSION['username'];
        //$newpass  = escapeshellcmd($newpass);
        $newpass = str_replace("'", "'\''", $newpass);
        
        $splitted = explode('@', $username, 2); // Get domain part of username
        
        if (count($splitted) != 2) {
            raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: could not get domain part"
                ), true, false);
            return PASSWORD_ERROR;
        }
        
        $domain = $splitted[1];

        $cmd = 'sudo ' . $bin_openpanelcli . " 'configure domain $domain'" . " 'configure email $domain'" . " 'update address $username password=$newpass'";
        
        exec($cmd, $output, $code);

        if ($code == 0) {
            return PASSWORD_SUCCESS;
        }
        else {
            raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to execute $cmd: $output"
                ), true, false);
        }

        return PASSWORD_ERROR;
    }

}
?>
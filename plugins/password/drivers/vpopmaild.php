<?php

/**
 * vpopmail Password Driver
 *
 * Driver to change passwords via vpopmaild
 *
 * @version 1.0
 * @author Johannes Hessellund
 *
 */

function password_save($curpass, $passwd)
{
    $rcmail = rcmail::get_instance();
//    include('Net/Socket.php');
    $vpopmaild = new Net_Socket();

    if (PEAR::isError($vpopmaild->connect($rcmail->config->get('password_vpopmaild_host'), $rcmail->config->get('password_vpopmaild_port'), null))) {
        return PASSWORD_CONNECT_ERROR;
    }
    else {
        $result = $vpopmaild->readLine();
        if(!preg_match('/^\+OK/', $result)) {
            $vpopmaild->disconnect();
            return PASSWORD_CONNECT_ERROR;
        }
        else {
            $vpopmaild->writeLine("slogin ". $_SESSION['username'] . " " . $curpass);
            $result = $vpopmaild->readLine();
            if(!preg_match('/^\+OK/', $result) ) {
                $vpopmaild->writeLine("quit");
                $vpopmaild->disconnect();
                return PASSWORD_ERROR;
            }
            else {
                $vpopmaild->writeLine("mod_user ". $_SESSION['username']);
                $result = $vpopmaild->readLine();
                if(!preg_match('/^\+OK/', $result) ) {
                    $vpopmaild->writeLine("quit");
                    $vpopmaild->disconnect();
                    return PASSWORD_ERROR;
                }
                else {
                    $vpopmaild->writeLine("clear_text_password ". $passwd);
                    $vpopmaild->writeLine(".");
                    $result = $vpopmaild->readLine();
                    $vpopmaild->writeLine("quit");
                    $vpopmaild->disconnect();
                    if (!preg_match('/^\+OK/', $result))
                        return PASSWORD_ERROR;
                    else
                        return PASSWORD_SUCCESS;
                }
            }
        }
    }
}

?>

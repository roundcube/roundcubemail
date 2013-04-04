<?php

/**
 * vpopmail Password Driver
 *
 * Driver to change passwords via vpopmaild
 *
 * @version 2.0
 * @author Johannes Hessellund
 *
 */

class rcube_vpopmaild_password
{
    function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();
    //    include('Net/Socket.php');
        $vpopmaild = new Net_Socket();

        if (PEAR::isError($vpopmaild->connect($rcmail->config->get('password_vpopmaild_host'),
            $rcmail->config->get('password_vpopmaild_port'), null))) {
            return PASSWORD_CONNECT_ERROR;
        }

        $result = $vpopmaild->readLine();
        if(!preg_match('/^\+OK/', $result)) {
            $vpopmaild->disconnect();
            return PASSWORD_CONNECT_ERROR;
        }

        $vpopmaild->writeLine("slogin ". $_SESSION['username'] . " " . $curpass);
        $result = $vpopmaild->readLine();

        if(!preg_match('/^\+OK/', $result) ) {
            $vpopmaild->writeLine("quit");
            $vpopmaild->disconnect();
            return PASSWORD_ERROR;
        }

        $vpopmaild->writeLine("mod_user ". $_SESSION['username']);
        $vpopmaild->writeLine("clear_text_password ". $passwd);
        $vpopmaild->writeLine(".");
        $result = $vpopmaild->readLine();
        $vpopmaild->writeLine("quit");
        $vpopmaild->disconnect();

        if (!preg_match('/^\+OK/', $result))
            return PASSWORD_ERROR;

        return PASSWORD_SUCCESS;
    }
}

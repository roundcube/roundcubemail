<?php

/**
 * Poppassd Password Driver
 *
 * Driver to change passwords via Poppassd/Courierpassd
 *
 * @version 1.0
 * @author Philip Weir
 *
 */

function password_save($curpass, $passwd)
{
    $rcmail = rcmail::get_instance();
//    include('Net/Socket.php');
    $poppassd = new Net_Socket();

    if (PEAR::isError($poppassd->connect($rcmail->config->get('password_pop_host'), $rcmail->config->get('password_pop_port'), null))) {
        return PASSWORD_CONNECT_ERROR;
    }
    else {
        $result = $poppassd->readLine();
        if(!preg_match('/^2\d\d/', $result)) {
            $poppassd->disconnect();
            return PASSWORD_ERROR;
        }
        else {
            $poppassd->writeLine("user ". $_SESSION['username']);
            $result = $poppassd->readLine();
            if(!preg_match('/^[23]\d\d/', $result) ) {
                $poppassd->disconnect();
                return PASSWORD_CONNECT_ERROR;
            }
            else {
                $poppassd->writeLine("pass ". $curpass);
                $result = $poppassd->readLine();
                if(!preg_match('/^[23]\d\d/', $result) ) {
                    $poppassd->disconnect();
                    return PASSWORD_ERROR;
                }
                else {
                    $poppassd->writeLine("newpass ". $passwd);
                    $result = $poppassd->readLine();
                    $poppassd->disconnect();
                    if (!preg_match('/^2\d\d/', $result))
                        return PASSWORD_ERROR;
                    else
                        return PASSWORD_SUCCESS;
                }
            }
        }
    }
}

?>

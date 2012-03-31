<?php

/**
 * Poppassd Password Driver
 *
 * Driver to change passwords via Poppassd/Courierpassd
 *
 * @version 2.0
 * @author Philip Weir
 *
 */

class rcube_poppassd_password
{
    function format_error_result($code, $line)
    {
        if (preg_match('/^\d\d\d\s+(\S.*)\s*$/', $line, $matches)) {
            return array('code' => $code, 'message' => $matches[1]);
        } else {
            return $code;
        }
    }

    function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();
//    include('Net/Socket.php');
        $poppassd = new Net_Socket();

        $result = $poppassd->connect($rcmail->config->get('password_pop_host'), $rcmail->config->get('password_pop_port'), null);
        if (PEAR::isError($result)) {
            return $this->format_error_result(PASSWORD_CONNECT_ERROR, $result->getMessage());
        }
        else {
            $result = $poppassd->readLine();
            if(!preg_match('/^2\d\d/', $result)) {
                $poppassd->disconnect();
                return $this->format_error_result(PASSWORD_ERROR, $result);
            }
            else {
                $poppassd->writeLine("user ". $_SESSION['username']);
                $result = $poppassd->readLine();
                if(!preg_match('/^[23]\d\d/', $result) ) {
                    $poppassd->disconnect();
                    return $this->format_error_result(PASSWORD_CONNECT_ERROR, $result);
                }
                else {
                    $poppassd->writeLine("pass ". $curpass);
                    $result = $poppassd->readLine();
                    if(!preg_match('/^[23]\d\d/', $result) ) {
                        $poppassd->disconnect();
                        return $this->format_error_result(PASSWORD_ERROR, $result);
                    }
                    else {
                        $poppassd->writeLine("newpass ". $passwd);
                        $result = $poppassd->readLine();
                        $poppassd->disconnect();
                        if (!preg_match('/^2\d\d/', $result))
                            return $this->format_error_result(PASSWORD_ERROR, $result);
                        else
                            return PASSWORD_SUCCESS;
                    }
                }
            }
        }
    }
}

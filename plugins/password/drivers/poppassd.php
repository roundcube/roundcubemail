<?php

/**
 * Poppassd Password Driver
 *
 * Driver to change passwords via Poppassd/Courierpassd
 *
 * @version 2.0
 * @author Philip Weir
 *
 * Copyright (C) 2005-2013, The Roundcube Dev Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

class rcube_poppassd_password
{
    function format_error_result($code, $line)
    {
        if (preg_match('/^\d\d\d\s+(\S.*)\s*$/', $line, $matches)) {
            return array('code' => $code, 'message' => $matches[1]);
        }

        return $code;
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
                if (!preg_match('/^[23]\d\d/', $result) ) {
                    $poppassd->disconnect();
                    return $this->format_error_result(PASSWORD_CONNECT_ERROR, $result);
                }
                else {
                    $poppassd->writeLine("pass ". $curpass);
                    $result = $poppassd->readLine();
                    if (!preg_match('/^[23]\d\d/', $result) ) {
                        $poppassd->disconnect();
                        return $this->format_error_result(PASSWORD_ERROR, $result);
                    }
                    else {
                        $poppassd->writeLine("newpass ". $passwd);
                        $result = $poppassd->readLine();
                        $poppassd->disconnect();
                        if (!preg_match('/^2\d\d/', $result)) {
                            return $this->format_error_result(PASSWORD_ERROR, $result);
                        }

                        return PASSWORD_SUCCESS;
                    }
                }
            }
        }
    }
}

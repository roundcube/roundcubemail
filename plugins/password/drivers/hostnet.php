<?php

/**
 * hostNET Password Driver
 *
 * Driver that adds functionality to change the mailuser password on hostNET Servers.
 * The code is derrived from the Plugin examples by The Roundcube Dev Team
 *
 * It only works on hostnet.de Managed-Root Server Systems.
 *
 * @version 1.0; Jimmy Koerting
 *
 * Copyright (C) 2017, hostNET Medien GmbH
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

class rcube_hostnet_password
{
    public function save($currpass, $newpass)
    {
        $username         = escapeshellcmd($_SESSION['username']);
        $password         = escapeshellarg($newpass);
        $mailuserfile     = rcmail::get_instance()->config->get('password_hostnet_mailuserfile', '/etc/mail/mailuser');
        $createhash       = "/usr/iports/bin/doveadm pw -s SHA512-CRYPT -p '$password' | sed s/\{SHA512-CRYPT\}//";

        // create new SHA512 hash
        exec($createhash, $newhash, $return_value);

        if ($return_value != 0) {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to execute doveadm for $username"
                ), true, false);
            return PASSWORD_CRYPT_ERROR;
        }

        $newhash = escapeshellcmd(implode($newhash));

        // read the entire mailuser file
        $mailusercontent = file_get_contents($mailuserfile);

        if (empty($mailusercontent)) {
            // Error if the mailuserfile is empty/not accessible.
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to get old password file - $mailuserfile."
                ), true, false);
            return PASSWORD_CONNECT_ERROR;
        }

        // build the search/replace pattern.
        $pattern = "/$username:[^:]+:/i";
        $replace = "$username:$newhash:";

        // replace
        $mailusercontent = preg_replace($pattern, $replace, $mailusercontent);
        
        if (preg_last_error()){
            $error = $this->preg_error_message(preg_last_error());
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to replace the old password: $error."
                ), true, false);
            return PASSWORD_ERROR;
        }
 
        // write back the entire file
        if (file_put_contents($mailuserfile, $mailusercontent)) {
            return PASSWORD_SUCCESS;
        }
        else {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to save new password."
                ), true, false);
        }
  
        return PASSWORD_ERROR;
    }


    /**
     * Fire TEXT errors
     * Derrived from
     * @author Ivan Tcholakov, 2016
     * @license MIT
     */
    function preg_error_message($code = null)
    {
        $code = (int) $code;
        switch ($code) {
            case PREG_NO_ERROR:
                $result = 'No error, probably invalid regular expression?';
                break;
            case PREG_INTERNAL_ERROR:
                $result = 'PCRE: Internal error.';
                break;
            case PREG_BACKTRACK_LIMIT_ERROR:
                $result = 'PCRE: Backtrack limit has been exhausted.';
                break;
            case PREG_RECURSION_LIMIT_ERROR:
                $result = 'PCRE: Recursion limit has been exhausted.';
                break;
            case PREG_BAD_UTF8_ERROR:
                $result = 'PCRE: Malformed UTF-8 data.';
                break;
            default:
                if (is_php('5.3') && $code == PREG_BAD_UTF8_OFFSET_ERROR) {
                    $result = 'PCRE: Did not end at a valid UTF-8 codepoint.';
                } elseif (is_php('7') && $code == PREG_JIT_STACKLIMIT_ERROR) {
                    $result = 'PCRE: Failed because of limited JIT stack space.';
                } else {
                    $result = 'PCRE: Error ' . $code . '.';
                }
                break;
        }
        return $result;
    }
}

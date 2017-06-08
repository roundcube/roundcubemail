<?php

/**
 * Dovecot passwdfile Password Driver
 *
 * Driver that adds functionality to change the passwords in dovecot 2 passwd-file files.
 * The code is derrived from the Plugin examples by The Roundcube Dev Team
 *
 * Intentionally written for hostnet.de Managed-Root Server Systems, it determines
 * the hostBSD System to preset any needed settings. hostNET Managed-Root Customers only need to set
 *
 * $config['password_driver'] = 'dovecot_passwdfile'
 *
 * in roundcubes plugins/password/config.inc.php to have all set.
 * (But don't forget to enable the 'password' plugin at all...)
 *
 * On vanilla dovecot 2 environments, use the correct values for these config settings:
 *
 * $config['password_dovecot_passwdfile_path']: The path of your dovecot passwd-file '/path/to/filename' as set in dovecot/conf.d/auth-passwdfile.conf.ext
 * $config['password_dovecotpw']: Full path and 'pw' command of doveadm binary - like '/usr/local/bin/doveadm pw'
 * $config['password_dovecotpw_method']: Dovecot hashing algo (http://wiki2.dovecot.org/Authentication/PasswordSchemes)
 * $config['password_dovecotpw_with_method']: True if you want the hashing algo as prefix in your passwd-file
 *
 * @version 1.0; Jimmy Koerting
 *
 * Copyright (C) 2017, hostNET Medien GmbH, www.hostnet.de
 *
 *
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


class rcube_dovecot_passwdfile_password
{
    public function save($currpass, $newpass)
    {
        $rcmail           = rcmail::get_instance();
        $username         = escapeshellcmd($_SESSION['username']);
        $password         = $newpass;
        $mailuserfile     = $rcmail->config->get('password_dovecot_passwdfile_path', '/etc/mail/mailuser');
        $dovecotpw        = $rcmail->config->get('password_dovecotpw', '/usr/local/bin/doveadm pw');
        $method           = $rcmail->config->get('password_dovecotpw_method', 'SHA512-CRYPT');
        $with_method      = $rcmail->config->get('password_dovecotpw_with_method', false);

        // BEGIN hostNET.de specific code - ignored elsewhere
        $uname_call = "/usr/bin/uname -v";
        exec($uname_call, $systemname, $return_value);
        $systemname = implode($systemname);
        if (strncmp ($systemname, 'hostBSD', 7) === 0) {
                $mailuserfile     = '/etc/mail/mailuser';
                $dovecotpw = '/usr/iports/bin/doveadm pw';
                $method  = 'SHA512-CRYPT';
                $with_method = false;
        }
        // END hostNET.de specific code


        // simplified version from password::hash_password
        $tmp_dir = $rcmail->config->get('temp_dir');
        $tmpfile = tempnam($tmp_dir, 'roundcube-');

        $pipe = popen("$dovecotpw -s '$method' > '$tmpfile'", "w");
        if (!$pipe) {
                unlink($tmpfile);
                rcube::raise_error(array(
                    'code' => 600,
                    'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Can't create tempfile $tmpfile"
                    ), true, false);
                return PASSWORD_CRYPT_ERROR;
        }
        else {
                fwrite($pipe, $password . "\n", 1+strlen($password)); usleep(1000);
                fwrite($pipe, $password . "\n", 1+strlen($password));
                pclose($pipe);

                $newhash = trim(file_get_contents($tmpfile), "\n");
                unlink($tmpfile);

                if (!preg_match('/^\{' . $method . '\}/', $newhash)) {
                    rcube::raise_error(array(
                        'code' => 600,
                        'type' => 'php',
                        'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Password hashing failed -> $newhash"
                        ), true, false);
                    return PASSWORD_CRYPT_ERROR;
                }

                if(!$with_method) {
                        $newhash = trim(str_replace('{' . $method . '}', '', $newhash));
                }
        }

        $newhash = escapeshellcmd($newhash);

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

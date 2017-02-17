<?php

/**
 * chpasswd driver
 *
 * Driver that adds functionality to change the systems user password via
 * the 'chpasswd' command.
 *
 * For installation instructions please read the README file.
 *
 * @version 3.0
 * @author Alex Cartwright <acartwright@mutinydesign.co.uk>
 * @author rewrtitten by KaM <kay@rrr.de>
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

class rcube_chpasswd_password
{
    public function save($currpass, $newpass)
    {
        $cmd = rcmail::get_instance()->config->get('password_chpasswd_cmd');
        $username = $_SESSION['username'];

        // change popen to proc_open to chatch responses from command
        $desc = array(
            0 => array('pipe', 'r'), // 0 is STDIN for process
            1 => array('pipe', 'w'), // 1 is STDOUT for process
            2 => array('file', strtok(cmd,  ' ') . '-error.log', 'a') // 2 is STDERR for process
        );

        // spawn the sub process
        $process = proc_open($cmd, $desc, $pipes);

        // does sub prescess exist?
        if (is_resource($process)) {
            // send send username and new pass to chpasswd command / warpper 
            fwrite($pipes[0], "$username:$newpass\n");
            // new: send old passwd for expect sript, works also with chpasswd
            fwrite($pipes[0], "$currpass\n");
            fclose($pipes[0]);

            // read response from sub process
            $message = stream_get_contents($pipes[1]);

            // all done! Clean up
            fclose($pipes[1]);
            fclose($pipes[2]);

            if (!proc_close($process)) {
              return PASSWORD_SUCCESS;
            }
            else {
                // return response in case of error
                return array(
                    'code'    => PASSWORD_ERROR,
                    'message' => "<br>" . $message
                );
            }
        }
        // sub process failed!
        else {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to execute $cmd"
                ), true, false);
        }
}

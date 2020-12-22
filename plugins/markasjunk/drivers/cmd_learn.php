<?php

/**
 * Command line learn driver
 *
 * @version 3.0
 *
 * @author Philip Weir
 * Patched by Julien Vehent to support DSPAM
 * Enhanced support for DSPAM by Stevan Bajic <stevan@bajic.ch>
 *
 * Copyright (C) 2009-2018 Philip Weir
 *
 * This driver is part of the MarkASJunk plugin for Roundcube.
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
 * along with Roundcube. If not, see https://www.gnu.org/licenses/.
 */
class markasjunk_cmd_learn
{
    public function spam($uids, $src_mbox, $dst_mbox)
    {
        $this->_do_salearn($uids, true, $src_mbox);
    }

    public function ham($uids, $src_mbox, $dst_mbox)
    {
        $this->_do_salearn($uids, false, $src_mbox);
    }

    private function _do_salearn($uids, $spam, $src_mbox)
    {
        $rcube    = rcube::get_instance();
        $temp_dir = realpath($rcube->config->get('temp_dir'));
        $command  = $rcube->config->get($spam ? 'markasjunk_spam_cmd' : 'markasjunk_ham_cmd');
        $debug    = $rcube->config->get('markasjunk_debug');

        if (!$command) {
            return;
        }

        // backwards compatibility %xds removed in markasjunk v1.12
        $command = str_replace('%xds', '%h:x-dspam-signature', $command);
        $command = str_replace('%u', $_SESSION['username'], $command);
        $command = str_replace('%l', $rcube->user->get_username('local'), $command);
        $command = str_replace('%d', $rcube->user->get_username('domain'), $command);
        if (strpos($command, '%i') !== false) {
            $identity = $rcube->user->get_identity();
            $command  = str_replace('%i', $identity['email'], $command);
        }

        foreach ($uids as $uid) {
            // reset command for next message
            $tmp_command = $command;

            if (strpos($tmp_command, '%s') !== false) {
                $message     = new rcube_message($uid);
                $tmp_command = str_replace('%s', escapeshellarg($message->sender['mailto']), $tmp_command);
            }

            if (strpos($command, '%h') !== false) {
                $storage = $rcube->get_storage();
                $storage->check_connection();
                $storage->conn->select($src_mbox);

                preg_match_all('/%h:([\w_-]+)/', $tmp_command, $header_names, PREG_SET_ORDER);
                foreach ($header_names as $header) {
                    $val = null;
                    if ($msg = $storage->conn->fetchHeader($src_mbox, $uid, true, false, [$header[1]])) {
                        $val = !empty($msg->{$header[1]}) ? $msg->{$header[1]} : $msg->others[$header[1]];
                    }

                    if (!empty($val)) {
                        $tmp_command = str_replace($header[0], escapeshellarg($val), $tmp_command);
                    }
                    else {
                        if ($debug) {
                            rcube::write_log('markasjunk', 'header ' . $header[1] . ' not found in message ' . $src_mbox . '/' . $uid);
                        }

                        continue 2;
                    }
                }
            }

            if (strpos($command, '%f') !== false) {
                $tmpfname = tempnam($temp_dir, 'rcmSALearn');
                file_put_contents($tmpfname, $rcube->storage->get_raw_body($uid));
                $tmp_command = str_replace('%f', escapeshellarg($tmpfname), $tmp_command);
            }

            $output = shell_exec($tmp_command);

            if ($debug) {
                if ($output) {
                    $tmp_command .= "\n$output";
                }

                rcube::write_log('markasjunk', $tmp_command);
            }

            if (isset($tmpfname)) {
                unlink($tmpfname);
            }
        }
    }
}

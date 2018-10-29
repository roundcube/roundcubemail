<?php

/**
 * AmavisD Blacklist driver
 *
 * @version 1.0
 * @requires Amacube plugin
 *
 * @author Der-Jan
 *
 * Copyright (C) 2014 Der-Jan
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

/* #   'SELECT wb'.
 * #   ' FROM wblist JOIN mailaddr ON wblist.sid=mailaddr.id'.
 * #   ' WHERE wblist.rid=? AND mailaddr.email IN (%k)'.
 * #   ' ORDER BY mailaddr.priority DESC';
 */

class markasjunk_amavis_blacklist
{
    private $user_email = '';

    public function spam($uids, $src_mbox, $dst_mbox)
    {
        $this->_do_list($uids, true);
    }

    public function ham($uids, $src_mbox, $dst_mbox)
    {
        $this->_do_list($uids, false);
    }

    private function _do_list($uids, $spam)
    {
        $rcube = rcube::get_instance();
        $this->user_email = $rcube->user->data['username'];

        if (is_file($rcube->config->get('markasjunk_amacube_config')) && !$rcube->config->load_from_file($rcube->config->get('markasjunk_amacube_config'))) {
            rcube::raise_error(array('code' => 527, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Failed to load config from " . $rcube->config->get('markasjunk_amacube_config')
            ), true, false);

            return false;
        }

        $db = rcube_db::factory($rcube->config->get('amacube_db_dsn'), '', true);
        $db->set_debug((bool) $rcube->config->get('sql_debug'));
        $db->db_connect('w');

        $debug = $rcube->config->get('markasjunk_debug');

        // check DB connections and exit on failure
        if ($err_str = $db->is_error()) {
            rcube::raise_error(array(
                'code'    => 603,
                'type'    => 'db',
                'message' => $err_str
            ), false, true);
        }

        $sql_result = $db->query("SELECT `id` FROM `users` WHERE `email` = ?", $this->user_email);
        if ($sql_result && ($res_array = $db->fetch_assoc($sql_result))) {
            $rid = $res_array['id'];
        }
        else {
            if ($debug) {
                rcube::write_log('markasjunk', $this->user_email . ' not found in users table');
            }

            return false;
        }

        foreach ($uids as $uid) {
            $message = new rcube_message($uid);
            $email = $message->sender['mailto'];
            $sql_result = $db->query("SELECT `id` FROM `mailaddr` WHERE `email` = ? ORDER BY `priority` DESC", $email);

            if ($sql_result && ($res_array = $db->fetch_assoc($sql_result))) {
                $sid = $res_array['id'];
            }
            else {
                if ($debug) {
                    rcube::write_log('markasjunk', $email . ' not found in mailaddr table - add it');
                }

                $sql_result = $db->query("INSERT INTO `mailaddr` ( `priority`, `email` ) VALUES ( 20, ? )", $email);
                if ($sql_result) {
                    $sid = $db->insert_id();
                }
                else {
                    if ($debug) {
                        rcube::write_log('markasjunk', 'Cannot add ' . $email . ' to mailaddr table: ' . $db->is_error($sql_result));
                    }

                    return false;
                }
            }

            $wb = '';
            $sql_result = $db->query("SELECT `wb` FROM `wblist` WHERE `sid` = ? AND `rid` =?", $sid, $rid);
            if ($sql_result && ($res_array = $db->fetch_assoc($sql_result))) {
                $wb = $res_array['wb'];
            }

            if (!$wb || (!$spam && preg_match('/^([BbNnFf])[\s]*\z/', $wb)) || ($spam && preg_match('/^([WwYyTt])[\s]*\z/', $wb))) {
                $newwb = 'w';

                if ($spam) {
                    $newwb = 'b';
                }

                if ($wb) {
                    $sql_result = $db->query('UPDATE `wblist` SET `wb` = ? WHERE `sid` = ? AND `rid` = ?',
                    $newwb, $sid, $rid);
                }
                else {
                    $sql_result = $db->query('INSERT INTO `wblist` (`sid`, `rid`, `wb`) VALUES (?,?,?)',
                    $sid, $rid, $newwb);
                }

                if (!$sql_result) {
                    if ($debug) {
                        rcube::write_log('markasjunk', 'Cannot update wblist for user ' . $this->user_email . ' with ' . $email);
                    }

                    return false;
                }
            }
        }
    }
}

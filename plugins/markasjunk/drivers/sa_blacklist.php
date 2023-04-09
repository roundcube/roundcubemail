<?php

/**
 * SpamAssassin Blacklist driver
 *
 * @version 2.0
 * @requires SAUserPrefs plugin
 *
 * @author Philip Weir
 *
 * Copyright (C) 2010-2014 Philip Weir
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
class markasjunk_sa_blacklist
{
    private $sa_user;
    private $sa_table;
    private $sa_username_field;
    private $sa_preference_field;
    private $sa_value_field;

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
        $this->sa_user             = $rcube->config->get('sauserprefs_userid', "%u");
        $this->sa_table            = $rcube->config->get('sauserprefs_sql_table_name');
        $this->sa_username_field   = $rcube->config->get('sauserprefs_sql_username_field');
        $this->sa_preference_field = $rcube->config->get('sauserprefs_sql_preference_field');
        $this->sa_value_field      = $rcube->config->get('sauserprefs_sql_value_field');

        // SAv4 compatibility
        $blocklist_pref_name       = $rcube->config->get('sauserprefs_sav4', false) ? "blocklist_from" : "blacklist_from";
        $welcomelist_pref_name     = $rcube->config->get('sauserprefs_sav4', false) ? "welcomelist_from" : "whitelist_from";

        $identity = $rcube->user->get_identity();
        $identity = $identity['email'];

        $this->sa_user = str_replace('%u', $_SESSION['username'], $this->sa_user);
        $this->sa_user = str_replace('%l', $rcube->user->get_username('local'), $this->sa_user);
        $this->sa_user = str_replace('%d', $rcube->user->get_username('domain'), $this->sa_user);
        $this->sa_user = str_replace('%i', $identity, $this->sa_user);

        $config_file = $rcube->config->get('markasjunk_sauserprefs_config');
        $debug       = $rcube->config->get('markasjunk_debug');

        if (is_file($config_file) && !$rcube->config->load_from_file($config_file)) {
            rcube::raise_error([
                    'code' => 527, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Failed to load config from $config_file"
                ], true, false
            );

            return false;
        }

        $db = rcube_db::factory($rcube->config->get('sauserprefs_db_dsnw'), $rcube->config->get('sauserprefs_db_dsnr'), $rcube->config->get('sauserprefs_db_persistent'));
        $db->set_debug((bool) $rcube->config->get('sql_debug'));
        $db->db_connect('w');

        // check DB connections and exit on failure
        if ($err_str = $db->is_error()) {
            rcube::raise_error([
                    'code' => 603,
                    'type' => 'db',
                    'message' => $err_str
                ], false, true
            );
        }

        foreach ($uids as $uid) {
            $message = new rcube_message($uid);
            $email   = $message->sender['mailto'];

            // skip invalid emails
            if (!rcube_utils::check_email($email, false)) {
                continue;
            }

            if ($spam) {
                // delete any welcomelisting for this address
                $db->query(
                    "DELETE FROM `{$this->sa_table}` WHERE `{$this->sa_username_field}` = ? "
                        . "AND `{$this->sa_preference_field}` = ? AND `{$this->sa_value_field}` = ?",
                    $this->sa_user,
                    $welcomelist_pref_name,
                    $email
                );

                // check address is not already blocklisted
                $sql_result = $db->query(
                    "SELECT `value` FROM `{$this->sa_table}` WHERE `{$this->sa_username_field}` = ? "
                        . "AND `{$this->sa_preference_field}` = ? AND `{$this->sa_value_field}` = ?",
                    $this->sa_user,
                    $blocklist_pref_name,
                    $email
                );

                if (!$db->fetch_array($sql_result)) {
                    $db->query(
                        "INSERT INTO `{$this->sa_table}` (`{$this->sa_username_field}`, `{$this->sa_preference_field}`, `{$this->sa_value_field}`)"
                            . " VALUES (?, ?, ?)",
                        $this->sa_user,
                        $blocklist_pref_name,
                        $email
                    );

                    if ($debug) {
                        rcube::write_log('markasjunk', $this->sa_user . ' blocklist ' . $email);
                    }
                }
            }
            else {
                // delete any blocklisting for this address
                $db->query(
                    "DELETE FROM `{$this->sa_table}` WHERE `{$this->sa_username_field}` = ? AND "
                        . "`{$this->sa_preference_field}` = ? AND `{$this->sa_value_field}` = ?",
                    $this->sa_user,
                    $blocklist_pref_name,
                    $email
                );

                // check address is not already welcomelisted
                $sql_result = $db->query(
                    "SELECT `value` FROM `{$this->sa_table}` WHERE `{$this->sa_username_field}` = ? "
                        . "AND `{$this->sa_preference_field}` = ? AND `{$this->sa_value_field}` = ?",
                    $this->sa_user,
                    $welcomelist_pref_name,
                    $email
                );

                if (!$db->fetch_array($sql_result)) {
                    $db->query(
                        "INSERT INTO `{$this->sa_table}` (`{$this->sa_username_field}`, `{$this->sa_preference_field}`, `{$this->sa_value_field}`)"
                            . " VALUES (?, ?, ?)",
                        $this->sa_user,
                        $welcomelist_pref_name,
                        $email);

                    if ($debug) {
                        rcube::write_log('markasjunk', $this->sa_user . ' welcomelist ' . $email);
                    }
                }
            }
        }
    }
}

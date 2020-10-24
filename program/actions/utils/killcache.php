<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delete rows from cache tables                                       |
 +-----------------------------------------------------------------------+
 | Author: Dennis P. Nikolaenko <dennis@nikolaenko.ru>                   |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_utils_killcache extends rcmail_action
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // don't allow public access if not in devel_mode
        if (!$rcmail->config->get('devel_mode')) {
            header("HTTP/1.0 401 Access denied");
            die("Access denied!");
        }

        // @TODO: transaction here (if supported by DB) would be a good thing
        $res = $rcmail->db->query("DELETE FROM " . $rcmail->db->table_name('cache', true));
        if ($err = $rcmail->db->is_error($res)) {
            exit($err);
        }

        $res = $rcmail->db->query("DELETE FROM " . $rcmail->db->table_name('cache_shared', true));
        if ($err = $rcmail->db->is_error($res)) {
            exit($err);
        }

        $res = $rcmail->db->query("DELETE FROM " . $rcmail->db->table_name('cache_messages', true));
        if ($err = $rcmail->db->is_error($res)) {
            exit($err);
        }

        $res = $rcmail->db->query("DELETE FROM " . $rcmail->db->table_name('cache_index', true));
        if ($err = $rcmail->db->is_error($res)) {
            exit($err);
        }

        $res = $rcmail->db->query("DELETE FROM " . $rcmail->db->table_name('cache_thread', true));
        if ($err = $rcmail->db->is_error($res)) {
            exit($err);
        }

        echo "Cache cleared\n";
        exit;
    }
}

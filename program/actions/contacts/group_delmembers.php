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
 |   Removing members from a contact group                               |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_group_delmembers extends rcmail_action_contacts_index
{
    // only process ajax requests
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail   = rcmail::get_instance();
        $source   = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);
        $contacts = self::contact_source($source);

        if ($contacts->readonly || !$contacts->groups) {
            $rcmail->output->show_message('sourceisreadonly', 'warning');
            $rcmail->output->send();
        }

        $gid = rcube_utils::get_input_string('_gid', rcube_utils::INPUT_POST);
        $ids = self::get_cids($source);

        if ($gid && $ids) {
            $plugin = $rcmail->plugins->exec_hook('group_delmembers', [
                    'group_id' => $gid,
                    'ids'      => $ids,
                    'source'   => $source,
            ]);

            if (empty($plugin['abort'])) {
                $result = $contacts->remove_from_group($gid, $plugin['ids']);
            }
            else {
                $result = $plugin['result'];
            }
        }

        if (!empty($result)) {
            $rcmail->output->show_message('contactremovedfromgroup', 'confirmation');
            $rcmail->output->command('remove_group_contacts', ['source' => $source, 'gid' => $gid]);
        }
        else {
            $error = !empty($plugin['message']) ? $plugin['message'] : 'errorsaving';
            $rcmail->output->show_message($error, 'error');
        }

        // send response
        $rcmail->output->send();
    }
}

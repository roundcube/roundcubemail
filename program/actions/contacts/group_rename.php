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
 |   A handler for contact groups rename action                          |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_group_rename extends rcmail_action_contacts_index
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

        if (
            ($gid = rcube_utils::get_input_string('_gid', rcube_utils::INPUT_POST))
            && ($name = trim(rcube_utils::get_input_string('_name', rcube_utils::INPUT_POST, true)))
        ) {
            $newgid = null;
            $plugin = $rcmail->plugins->exec_hook('group_rename', [
                    'group_id' => $gid,
                    'name'     => $name,
                    'source'   => $source,
            ]);

            if (empty($plugin['abort'])) {
                $newname = $contacts->rename_group($gid, $plugin['name'], $newgid);
            }
            else {
                $newname = $plugin['result'];
            }
        }

        if (!empty($newname)) {
            $rcmail->output->show_message('grouprenamed', 'confirmation');
            $rcmail->output->command('update_contact_group', [
                    'source' => $source,
                    'id'     => $gid,
                    'name'   => $newname,
                    'newid'  => $newgid ?? null
            ]);
        }
        else {
            $error = !empty($plugin['message']) ? $plugin['message'] : 'errorsaving';
            $rcmail->output->show_message($error, 'error');
        }

        // send response
        $rcmail->output->send();
    }
}

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
 |   Handle adding members to a contact group                            |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_group_addmembers extends rcmail_action_contacts_index
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

        $gid    = rcube_utils::get_input_string('_gid', rcube_utils::INPUT_POST);
        $ids    = self::get_cids($source);
        $result = false;

        if ($gid && $ids) {
            $plugin = $rcmail->plugins->exec_hook('group_addmembers', [
                    'group_id' => $gid,
                    'ids'      => $ids,
                    'source'   => $source,
            ]);

            $contacts->set_group($gid);
            $num2add = count($plugin['ids']);

            if (empty($plugin['abort'])) {
                if (
                    ($maxnum = $rcmail->config->get('max_group_members'))
                    && ($contacts->count()->count + $num2add > $maxnum)
                ) {
                    $rcmail->output->show_message('maxgroupmembersreached', 'warning', ['max' => $maxnum]);
                    $rcmail->output->send();
                }

                $result = $contacts->add_to_group($gid, $plugin['ids']);
            }
            else {
                $result = $plugin['result'];
            }
        }

        if ($result) {
            $rcmail->output->show_message('contactaddedtogroup', 'confirmation');
        }
        else if (!empty($plugin['abort']) || $contacts->get_error()) {
            $error = !empty($plugin['message']) ? $plugin['message'] : 'errorsaving';
            $rcmail->output->show_message($error, 'error');
        }
        else {
            $message = !empty($plugin['message']) ? $plugin['message'] : 'nogroupassignmentschanged';
            $rcmail->output->show_message($message);
        }

        // send response
        $rcmail->output->send();
    }
}

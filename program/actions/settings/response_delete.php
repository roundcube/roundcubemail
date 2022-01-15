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
 |   A handler for canned response deletion                              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_response_delete extends rcmail_action
{
    static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        if ($id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GP)) {
            $plugin = $rcmail->plugins->exec_hook('response_delete', ['id' => $id]);

            $deleted = !$plugin['abort'] ? $rcmail->user->delete_response($id) : $plugin['result'];

            if (!empty($deleted)) {
                $rcmail->output->command('display_message', $rcmail->gettext('deletedsuccessfully'), 'confirmation');
                $rcmail->output->command('remove_response', $id);
            }
            else {
                $msg = !empty($plugin['message']) ? $plugin['message'] : 'errorsaving';
                $rcmail->output->show_message($msg, 'error');
            }
        }

        $rcmail->output->send();
    }
}

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
 |   A handler for identity delete action                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_identity_delete extends rcmail_action
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail  = rcmail::get_instance();
        $iid     = rcube_utils::get_input_string('_iid', rcube_utils::INPUT_POST);
        $deleted = 0;

        if ($iid && preg_match('/^[0-9]+(,[0-9]+)*$/', $iid)) {
            $plugin = $rcmail->plugins->exec_hook('identity_delete', ['id' => $iid]);

            $deleted = !$plugin['abort'] ? $rcmail->user->delete_identity($iid) : $plugin['result'];
        }

        if ($deleted > 0 && $deleted !== false) {
            $rcmail->output->show_message('deletedsuccessfully', 'confirmation', null, false);
            $rcmail->output->command('remove_identity', $iid);
        }
        else {
            $msg = !empty($plugin['message']) ? $plugin['message'] : ($deleted < 0 ? 'nodeletelastidentity' : 'errorsaving');
            $rcmail->output->show_message($msg, 'error', null, false);
        }

        $rcmail->output->send();
    }
}

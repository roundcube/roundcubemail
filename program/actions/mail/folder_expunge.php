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
 |   Implement folder EXPUNGE request                                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_folder_expunge extends rcmail_action
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
        $rcmail = rcmail::get_instance();
        $mbox   = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_POST, true);

        $success = $rcmail->storage->expunge_folder($mbox);

        // reload message list if current mailbox
        if ($success) {
            $rcmail->output->show_message('folderexpunged', 'confirmation');

            if (!empty($_REQUEST['_reload'])) {
                $rcmail->output->command('set_quota', self::quota_content(null, $mbox));
                $rcmail->output->command('message_list.clear');
                $rcmail->action = 'list';
                return;
            }
        }
        else {
            self::display_server_error();
        }

        $rcmail->output->send();
    }
}

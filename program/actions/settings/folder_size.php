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
 |   Provide functionality of getting folder size                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_folder_size extends rcmail_action
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
        $storage = $rcmail->get_storage();
        $name    = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_POST, true);

        $size = $storage->folder_size($name);

        // @TODO: check quota and show percentage usage of specified mailbox?

        if ($size !== false) {
            $rcmail->output->command('folder_size_update', self::show_bytes($size));
        }
        else {
            self::display_server_error();
        }

        $rcmail->output->send();
    }
}

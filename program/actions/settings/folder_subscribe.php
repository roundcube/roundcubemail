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
 |   Handler for folder subscribe action                                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_folder_subscribe extends rcmail_action
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
        $mbox    = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_POST, true);

        if (strlen($mbox)) {
            $result = $storage->subscribe([$mbox]);

            // Handle virtual (non-existing) folders
            if (
                !$result
                && $storage->get_error_code() == -1
                && $storage->get_response_code() == rcube_storage::TRYCREATE
            ) {
                $result = $storage->create_folder($mbox, true);
                if ($result) {
                    // @TODO: remove 'virtual' class of folder's row
                }
            }
        }

        if (!empty($result)) {
            // Handle subscription of protected folder (#1487656)
            if ($rcmail->config->get('protect_default_folders') && $storage->is_special_folder($mbox)) {
                $rcmail->output->command('disable_subscription', $mbox);
            }

            $rcmail->output->show_message('foldersubscribed', 'confirmation');
        }
        else {
            self::display_server_error('errorsaving');
            $rcmail->output->command('reset_subscription', $mbox, false);
        }

        $rcmail->output->send();
    }
}

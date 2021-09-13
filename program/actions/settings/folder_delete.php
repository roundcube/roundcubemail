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
 |   Provide functionality to delete a folder                            |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_folder_delete extends rcmail_action
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
            $plugin = $rcmail->plugins->exec_hook('folder_delete', ['name' => $mbox]);

            if (empty($plugin['abort'])) {
                $deleted = $storage->delete_folder($plugin['name']);
            }
            else {
                $deleted = $plugin['result'];
            }

            // #1488692: update session
            if ($deleted && isset($_SESSION['mbox']) && $_SESSION['mbox'] === $mbox) {
                $rcmail->session->remove('mbox');
            }
        }

        if (!empty($deleted)) {
            // Remove folder and subfolders rows
            $rcmail->output->command('remove_folder_row', $mbox);
            $rcmail->output->show_message('folderdeleted', 'confirmation');
            // Clear content frame
            $rcmail->output->command('subscription_select');
            $rcmail->output->command('set_quota', self::quota_content());
        }
        else {
            self::display_server_error('errorsaving');
        }

        $rcmail->output->send();
    }
}

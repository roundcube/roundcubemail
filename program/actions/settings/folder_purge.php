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
 |   Provide functionality of folder purge                               |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_folder_purge extends rcmail_action
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail       = rcmail::get_instance();
        $storage      = $rcmail->get_storage();
        $mbox         = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_POST, true);
        $delimiter    = $storage->get_hierarchy_delimiter();
        $trash_mbox   = $rcmail->config->get('trash_mbox');
        $trash_regexp = '/^' . preg_quote($trash_mbox . $delimiter, '/') . '/';

        // we should only be purging trash (or their subfolders)
        if (!strlen($trash_mbox) || $mbox === $trash_mbox || preg_match($trash_regexp, $mbox)) {
            $success = $storage->delete_message('*', $mbox);
            $delete  = true;
        }
        // move to Trash
        else {
            $success = $storage->move_message('1:*', $trash_mbox, $mbox);
            $delete  = false;
        }

        if (!empty($success)) {
            $rcmail->output->set_env('messagecount', 0);

            if ($delete) {
                $rcmail->output->show_message('folderpurged', 'confirmation');
                $rcmail->output->command('set_quota', self::quota_content(null, $mbox));
            }
            else {
                $rcmail->output->show_message('messagemoved', 'confirmation');
            }

            $_SESSION['unseen_count'][$mbox] = 0;
            $rcmail->output->command('show_folder', $mbox, null, true);
        }
        else {
            self::display_server_error('errorsaving');
        }

        $rcmail->output->send();
    }
}

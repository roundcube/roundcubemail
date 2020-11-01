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
 |   Check all mailboxes for unread messages and update GUI              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_getunread extends rcmail_action_mail_index
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
        $rcmail    = rcmail::get_instance();
        $a_folders = $rcmail->storage->list_folders_subscribed('', '*', 'mail');

        if (!empty($a_folders)) {
            $current   = $rcmail->storage->get_folder();
            $inbox     = $current == 'INBOX';
            $trash     = $rcmail->config->get('trash_mbox');
            $check_all = (bool) $rcmail->config->get('check_all_folders');

            foreach ($a_folders as $mbox) {
                $unseen_old = self::get_unseen_count($mbox);

                if (!$check_all && $unseen_old !== null && $mbox != $current) {
                    $unseen = $unseen_old;
                }
                else {
                    $unseen = $rcmail->storage->count($mbox, 'UNSEEN', $unseen_old === null);
                }

                // call it always for current folder, so it can update counter
                // after possible message status change when opening a message
                // not in preview frame
                if ($unseen || $unseen_old === null || $mbox == $current) {
                    $rcmail->output->command('set_unread_count', $mbox, $unseen, $inbox && $mbox == 'INBOX');
                }

                self::set_unseen_count($mbox, $unseen);

                // set trash folder state
                if ($mbox === $trash) {
                    $rcmail->output->command('set_trash_count', $rcmail->storage->count($mbox, 'EXISTS'));
                }
            }
        }

        $rcmail->output->send();
    }
}

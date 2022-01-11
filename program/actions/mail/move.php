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
 |   Handler for mail move operation                                     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_move extends rcmail_action_mail_index
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // count messages before changing anything
        $threading = (bool) $rcmail->storage->get_threading();
        $trash     = $rcmail->config->get('trash_mbox');
        $old_count = 0;

        if (empty($_POST['_from']) || $_POST['_from'] != 'show') {
            $old_count = $rcmail->storage->count(null, $threading ? 'THREADS' : 'ALL');
        }

        $target  = rcube_utils::get_input_string('_target_mbox', rcube_utils::INPUT_POST, true);

        if (empty($_POST['_uid']) || !strlen($target)) {
            $rcmail->output->show_message('internalerror', 'error');
            $rcmail->output->send();
        }

        $success = true;
        $addrows = false;
        $count   = 0;
        $sources = [];

        foreach (rcmail::get_uids(null, null, $multifolder, rcube_utils::INPUT_POST) as $mbox => $uids) {
            if ($mbox === $target) {
                $count += is_array($uids) ? count($uids) : 1;
            }
            else if ($rcmail->storage->move_message($uids, $target, $mbox)) {
                $count += is_array($uids) ? count($uids) : 1;
                $sources[] = $mbox;
            }
            else {
                $success = false;
            }
        }

        if (!$success) {
            // send error message
            if (empty($_POST['_from']) || $_POST['_from'] != 'show') {
                $rcmail->output->command('list_mailbox');
            }

            self::display_server_error('errormoving', null, $target == $trash ? 'delete' : '');
            $rcmail->output->send();
        }
        else {
            $rcmail->output->show_message($target == $trash ? 'messagemovedtotrash' : 'messagemoved', 'confirmation');
        }

        if (!empty($_POST['_refresh'])) {
            // FIXME: send updated message rows instead of reloading the entire list
            $rcmail->output->command('refresh_list');
        }
        else {
            $addrows = true;
        }

        $search_request = rcube_utils::get_input_string('_search', rcube_utils::INPUT_GPC);

        // refresh saved search set after moving some messages
        if ($search_request && $rcmail->storage->get_search_set()) {
            $_SESSION['search'] = $rcmail->storage->refresh_search();
        }

        if (!empty($_POST['_from']) && $_POST['_from'] == 'show') {
            if ($next = rcube_utils::get_input_string('_next_uid', rcube_utils::INPUT_GPC)) {
                $rcmail->output->command('show_message', $next);
            }
            else {
                $rcmail->output->command('command', 'list');
            }

            $rcmail->output->send();
        }

        $mbox           = $rcmail->storage->get_folder();
        $msg_count      = $rcmail->storage->count(null, $threading ? 'THREADS' : 'ALL');
        $exists         = $rcmail->storage->count($mbox, 'EXISTS', true);
        $page_size      = $rcmail->storage->get_pagesize();
        $page           = $rcmail->storage->get_page();
        $pages          = ceil($msg_count / $page_size);
        $nextpage_count = $old_count - $page_size * $page;
        $remaining      = $msg_count - $page_size * ($page - 1);

        // jump back one page (user removed the whole last page)
        if ($page > 1 && $remaining == 0) {
            $page -= 1;
            $rcmail->storage->set_page($page);
            $_SESSION['page'] = $page;
            $jump_back = true;
        }

        // update unseen messages counts for all involved folders
        foreach ($sources as $source) {
            self::send_unread_count($source, true);
        }

        self::send_unread_count($target, true);

        // update message count display
        $rcmail->output->set_env('messagecount', $msg_count);
        $rcmail->output->set_env('current_page', $page);
        $rcmail->output->set_env('pagecount', $pages);
        $rcmail->output->set_env('exists', $exists);
        $rcmail->output->command('set_quota', self::quota_content(null, $multifolder ? $sources[0] : 'INBOX'));
        $rcmail->output->command('set_rowcount', self::get_messagecount_text($msg_count), $mbox);

        if ($threading) {
            $count = rcube_utils::get_input_string('_count', rcube_utils::INPUT_POST);
        }

        // add new rows from next page (if any)
        if ($addrows && $count && $_POST['_uid'] != '*' && (!empty($jump_back) || $nextpage_count > 0)) {
            // #5862: Don't add more rows than it was on the next page
            $count = !empty($jump_back) ? null : min($nextpage_count, $count);

            $a_headers = $rcmail->storage->list_messages($mbox, NULL,
                self::sort_column(), self::sort_order(), $count);

            self::js_message_list($a_headers, false);
        }

        // set trash folder state
        if ($mbox === $trash) {
            $rcmail->output->command('set_trash_count', $exists);
        }
        else if ($target === $trash) {
            $rcmail->output->command('set_trash_count', $rcmail->storage->count($trash, 'EXISTS', true));
        }

        // send response
        $rcmail->output->send();
    }
}

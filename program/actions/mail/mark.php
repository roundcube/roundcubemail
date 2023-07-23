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
 |   Mark the submitted messages with the specified flag                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_mark extends rcmail_action_mail_index
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
        $_uids   = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
        $flag    = rcube_utils::get_input_string('_flag', rcube_utils::INPUT_POST);
        $folders = rcube_utils::get_input_string('_folders', rcube_utils::INPUT_POST);
        $mbox    = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_POST);

        if (empty($_uids) || empty($flag)) {
            $rcmail->output->show_message('internalerror', 'error');
            $rcmail->output->send();
        }

        $rcmail       = rcmail::get_instance();
        $threading    = (bool) $rcmail->storage->get_threading();
        $skip_deleted = (bool) $rcmail->config->get('skip_deleted');
        $read_deleted = (bool) $rcmail->config->get('read_when_deleted');
        $flag         = self::imap_flag($flag);
        $old_count    = 0;
        $from         = $_POST['_from'] ?? null;

        if ($flag == 'DELETED' && $skip_deleted && $from != 'show') {
            // count messages before changing anything
            $old_count = $rcmail->storage->count(null, $threading ? 'THREADS' : 'ALL');
        }

        if ($folders == 'all') {
            $mboxes = $rcmail->storage->list_folders_subscribed('', '*', 'mail');
            $input  = array_combine($mboxes, array_fill(0, count($mboxes), '*'));
        }
        else if ($folders == 'sub') {
            $delim  = $rcmail->storage->get_hierarchy_delimiter();
            $mboxes = $rcmail->storage->list_folders_subscribed($mbox . $delim, '*', 'mail');
            array_unshift($mboxes, $mbox);
            $input = array_combine($mboxes, array_fill(0, count($mboxes), '*'));
        }
        else if ($folders == 'cur') {
            $input = [$mbox => '*'];
        }
        else {
            $input = self::get_uids(null, null, $dummy, rcube_utils::INPUT_POST);
        }

        $marked = 0;
        $count  = 0;
        $read   = 0;

        foreach ($input as $mbox => $uids) {
            $marked += (int) $rcmail->storage->set_flag($uids, $flag, $mbox);
            $count  += is_array($uids) ? count($uids) : 1;
        }

        if (!$marked) {
            // send error message
            if ($from != 'show') {
                $rcmail->output->command('list_mailbox');
            }

            self::display_server_error('errormarking');
            $rcmail->output->send();
        }
        else if (empty($_POST['_quiet'])) {
            $rcmail->output->show_message('messagemarked', 'confirmation');
        }

        if ($flag == 'DELETED' && $read_deleted && !empty($_POST['_ruid'])) {
            if ($ruids = rcube_utils::get_input_value('_ruid', rcube_utils::INPUT_POST)) {
                foreach (self::get_uids($ruids) as $mbox => $uids) {
                    $read += (int) $rcmail->storage->set_flag($uids, 'SEEN', $mbox);
                }
            }

            if ($read && !$skip_deleted) {
                $rcmail->output->command('flag_deleted_as_read', $ruids);
            }
        }

        if ($flag == 'SEEN' || $flag == 'UNSEEN' || ($flag == 'DELETED' && !$skip_deleted)) {
            foreach ($input as $mbox => $uids) {
                self::send_unread_count($mbox);
            }

            $rcmail->output->set_env('last_flag', $flag);
        }
        else if ($flag == 'DELETED' && $skip_deleted) {
            if ($from == 'show') {
                if ($next = rcube_utils::get_input_value('_next_uid', rcube_utils::INPUT_GPC)) {
                    $rcmail->output->command('show_message', $next);
                }
                else {
                    $rcmail->output->command('command', 'list');
                }
            }
            else {
                $search_request = rcube_utils::get_input_value('_search', rcube_utils::INPUT_GPC);

                // refresh saved search set after moving some messages
                if ($search_request && $rcmail->storage->get_search_set()) {
                    $_SESSION['search'] = $rcmail->storage->refresh_search();
                }

                $msg_count      = $rcmail->storage->count(NULL, $threading ? 'THREADS' : 'ALL');
                $page_size      = $rcmail->storage->get_pagesize();
                $page           = $rcmail->storage->get_page();
                $pages          = ceil($msg_count / $page_size);
                $nextpage_count = $old_count - $page_size * $page;
                $remaining      = $msg_count - $page_size * ($page - 1);
                $jump_back      = false;

                // jump back one page (user removed the whole last page)
                if ($page > 1 && $remaining == 0) {
                    $page -= 1;
                    $rcmail->storage->set_page($page);
                    $_SESSION['page'] = $page;
                    $jump_back = true;
                }

                foreach ($input as $mbox => $uids) {
                    self::send_unread_count($mbox, true);
                }

                // update message count display
                $rcmail->output->set_env('messagecount', $msg_count);
                $rcmail->output->set_env('current_page', $page);
                $rcmail->output->set_env('pagecount', $pages);
                $rcmail->output->command('set_rowcount', self::get_messagecount_text($msg_count), $mbox);

                if ($threading) {
                    $count = rcube_utils::get_input_value('_count', rcube_utils::INPUT_POST);
                }

                // add new rows from next page (if any)
                if ($old_count && $_uids != '*' && ($jump_back || $nextpage_count > 0)) {
                    // #5862: Don't add more rows than it was on the next page
                    $count = $jump_back ? null : min($nextpage_count, $count);

                    $a_headers = $rcmail->storage->list_messages($mbox, null,
                        self::sort_column(), self::sort_order(), $count);

                    self::js_message_list($a_headers, false);
               }
            }
        }

        $rcmail->output->send();
    }

    /**
     * Map Roundcube UI's flag label into IMAP flag
     *
     * @param string $flag Flag label
     *
     * @return string Uppercase IMAP flag
     */
    public static function imap_flag($flag)
    {
        $flags_map = [
            'undelete'  => 'UNDELETED',
            'delete'    => 'DELETED',
            'read'      => 'SEEN',
            'unread'    => 'UNSEEN',
            'flagged'   => 'FLAGGED',
            'unflagged' => 'UNFLAGGED',
        ];

        return !empty($flags_map[$flag]) ? $flags_map[$flag] : strtoupper($flag);
    }
}

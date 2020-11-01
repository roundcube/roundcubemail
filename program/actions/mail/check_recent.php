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
 |   Check for recent messages, in all mailboxes                         |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_check_recent extends rcmail_action_mail_index
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

        // If there's no folder or messages list, there's nothing to update
        // This can happen on 'refresh' request
        if (empty($_POST['_folderlist']) && empty($_POST['_list'])) {
            return;
        }

        $trash     = $rcmail->config->get('trash_mbox');
        $current   = $rcmail->storage->get_folder();
        $check_all = $rcmail->action != 'refresh' || (bool) $rcmail->config->get('check_all_folders');
        $page      = $rcmail->storage->get_page();
        $page_size = $rcmail->storage->get_pagesize();

        $search_request = rcube_utils::get_input_value('_search', rcube_utils::INPUT_GPC);
        if ($search_request && $_SESSION['search_request'] != $search_request) {
            $search_request = null;
        }

        // list of folders to check
        if ($check_all) {
            $a_mailboxes = $rcmail->storage->list_folders_subscribed('', '*', 'mail');
        }
        else if ($search_request && is_object($_SESSION['search'][1])) {
            $a_mailboxes = (array) $_SESSION['search'][1]->get_parameters('MAILBOX');
        }
        else {
            $a_mailboxes = (array) $current;
            if ($current != 'INBOX') {
                $a_mailboxes[] = 'INBOX';
            }
        }

        // Control folders list from a plugin
        $plugin      = $rcmail->plugins->exec_hook('check_recent', ['folders' => $a_mailboxes, 'all' => $check_all]);
        $a_mailboxes = $plugin['folders'];

        self::storage_fatal_error();

        // check recent/unseen counts
        foreach ($a_mailboxes as $mbox_name) {
            $is_current = $mbox_name == $current
                || (
                    !empty($search_request)
                    && is_object($_SESSION['search'][1])
                    && in_array($mbox_name, (array)$_SESSION['search'][1]->get_parameters('MAILBOX'))
                );

            if ($is_current) {
                // Synchronize mailbox cache, handle flag changes
                $rcmail->storage->folder_sync($mbox_name);
            }

            // Get mailbox status
            $status = $rcmail->storage->folder_status($mbox_name, $diff);

            if ($is_current) {
                self::storage_fatal_error();
            }

            if ($status & 1) {
                // trigger plugin hook
                $rcmail->plugins->exec_hook('new_messages', [
                        'mailbox'    => $mbox_name,
                        'is_current' => $is_current,
                        'diff'       => $diff
                ]);
            }

            self::send_unread_count($mbox_name, true, null, (!$is_current && ($status & 1)) ? 'recent' : '');

            if ($status && $is_current) {
                // refresh saved search set
                if (!empty($search_request) && isset($_SESSION['search'])) {
                    unset($search_request);  // only do this once
                    $_SESSION['search'] = $rcmail->storage->refresh_search();
                    if ($_SESSION['search'][1]->multi) {
                        $mbox_name = '';
                    }
                }

                if (!empty($_POST['_quota'])) {
                    $rcmail->output->command('set_quota', self::quota_content(null, $mbox_name));
                }

                $rcmail->output->set_env('exists', $rcmail->storage->count($mbox_name, 'EXISTS', true));

                // "No-list" mode, don't get messages
                if (empty($_POST['_list'])) {
                    continue;
                }

                // get overall message count; allow caching because rcube_storage::folder_status()
                // did a refresh but only in list mode
                $list_mode = $rcmail->storage->get_threading() ? 'THREADS' : 'ALL';
                $all_count = $rcmail->storage->count($mbox_name, $list_mode, $list_mode == 'THREADS', false);

                // check current page if we're not on the first page
                if ($all_count && $page > 1) {
                    $remaining = $all_count - $page_size * ($page - 1);
                    if ($remaining <= 0) {
                        $page -= 1;
                        $rcmail->storage->set_page($page);
                        $_SESSION['page'] = $page;
                    }
                }

                $rcmail->output->set_env('messagecount', $all_count);
                $rcmail->output->set_env('pagecount', ceil($all_count/$page_size));
                $rcmail->output->command('set_rowcount', self::get_messagecount_text($all_count), $mbox_name);
                $rcmail->output->set_env('current_page', $all_count ? $page : 1);

                // remove old rows (and clear selection if new list is empty)
                $rcmail->output->command('message_list.clear', $all_count ? false : true);

                if ($all_count) {
                    $a_headers = $rcmail->storage->list_messages($mbox_name, null, self::sort_column(), self::sort_order());
                    // add message rows
                    self::js_message_list($a_headers, false);
                    // remove messages that don't exists from list selection array
                    $rcmail->output->command('update_selection');
                }
            }
            // handle flag updates
            else if ($is_current && ($uids = rcube_utils::get_input_value('_uids', rcube_utils::INPUT_GPC)) && empty($search_request)) {
                $data = $rcmail->storage->folder_data($mbox_name);

                if (empty($_SESSION['list_mod_seq']) || $_SESSION['list_mod_seq'] != $data['HIGHESTMODSEQ']) {
                    $flags = $rcmail->storage->list_flags($mbox_name, explode(',', $uids), $_SESSION['list_mod_seq']);
                    foreach ($flags as $idx => $row) {
                        $flags[$idx] = array_change_key_case(array_map('intval', $row));
                    }

                    // remember last HIGHESTMODSEQ value (if supported)
                    if (!empty($data['HIGHESTMODSEQ'])) {
                        $_SESSION['list_mod_seq'] = $data['HIGHESTMODSEQ'];
                    }

                    $rcmail->output->set_env('recent_flags', $flags);
                }
            }

            // set trash folder state
            if ($mbox_name === $trash) {
                $rcmail->output->command('set_trash_count', $rcmail->storage->count($mbox_name, 'EXISTS', true));
            }
        }

        // trigger refresh hook
        $rcmail->plugins->exec_hook('refresh', []);

        $rcmail->output->send();
    }
}

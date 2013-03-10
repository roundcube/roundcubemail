<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/check_recent.inc                                   |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Check for recent messages, in all mailboxes                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

// If there's no folder or messages list, there's nothing to update
// This can happen on 'refresh' request
if (empty($_REQUEST['_folderlist']) && empty($_REQUEST['_list'])) {
    return;
}

$current   = $RCMAIL->storage->get_folder();
$check_all = $RCMAIL->action != 'refresh' || (bool)$RCMAIL->config->get('check_all_folders');

// list of folders to check
if ($check_all) {
    $a_mailboxes = $RCMAIL->storage->list_folders_subscribed('', '*', 'mail');
}
else {
    $a_mailboxes = (array) $current;
    if ($current != 'INBOX') {
        $a_mailboxes[] = 'INBOX';
    }
}

// Control folders list from a plugin
$plugin      = $RCMAIL->plugins->exec_hook('check_recent', array('folders' => $a_mailboxes, 'all' => $check_all));
$a_mailboxes = $plugin['folders'];

// check recent/unseen counts
foreach ($a_mailboxes as $mbox_name) {
    $is_current = $mbox_name == $current;
    if ($is_current) {
        // Synchronize mailbox cache, handle flag changes
        $RCMAIL->storage->folder_sync($mbox_name);
    }

    // Get mailbox status
    $status = $RCMAIL->storage->folder_status($mbox_name);

    if ($status & 1) {
        // trigger plugin hook
        $RCMAIL->plugins->exec_hook('new_messages',
            array('mailbox' => $mbox_name, 'is_current' => $is_current));
    }

    rcmail_send_unread_count($mbox_name, true, null,
      (!$is_current && ($status & 1)) ? 'recent' : '');

    if ($status && $is_current) {
        // refresh saved search set
        $search_request = get_input_value('_search', RCUBE_INPUT_GPC);
        if ($search_request && isset($_SESSION['search'])
            && $_SESSION['search_request'] == $search_request
        ) {
            $_SESSION['search'] = $RCMAIL->storage->refresh_search();
        }

        if (!empty($_GET['_quota']))
            $OUTPUT->command('set_quota', rcmail_quota_content());

        $OUTPUT->set_env('exists', $RCMAIL->storage->count($mbox_name, 'EXISTS'));

        // "No-list" mode, don't get messages
        if (empty($_GET['_list']))
            continue;

        // get overall message count; allow caching because rcube_storage::folder_status() did a refresh
        $list_mode = $RCMAIL->storage->get_threading() ? 'THREADS' : 'ALL';
        $all_count = $RCMAIL->storage->count($mbox_name, $list_mode, false, false);
        $page      = $RCMAIL->storage->get_page();
        $page_size = $RCMAIL->storage->get_pagesize();

        // check current page if we're not on the first page
        if ($all_count && $page > 1) {
            $remaining = $all_count - $page_size * ($page - 1);
            if ($remaining <= 0) {
                $page -= 1;
                $RCMAIL->storage->set_page($page);
                $_SESSION['page'] = $page;
            }
        }

        $OUTPUT->set_env('messagecount', $all_count);
        $OUTPUT->set_env('pagecount', ceil($all_count/$page_size));
        $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($all_count), $mbox_name);
        $OUTPUT->set_env('current_page', $all_count ? $page : 1);

        // remove old rows (and clear selection if new list is empty)
        $OUTPUT->command('message_list.clear', $all_count ? false : true);

        if ($all_count) {
            $a_headers = $RCMAIL->storage->list_messages($mbox_name, null, rcmail_sort_column(), rcmail_sort_order());
            // add message rows
            rcmail_js_message_list($a_headers, false);
            // remove messages that don't exists from list selection array
            $OUTPUT->command('update_selection');
        }
    }
}

// trigger refresh hook
$RCMAIL->plugins->exec_hook('refresh', array());

$OUTPUT->send();

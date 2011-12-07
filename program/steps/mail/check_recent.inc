<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/check_recent.inc                                   |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Check for recent messages, in all mailboxes                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$current = $RCMAIL->imap->get_mailbox_name();
$check_all = !empty($_GET['_refresh']) || (bool)$RCMAIL->config->get('check_all_folders');

// list of folders to check
if ($check_all) {
    $a_mailboxes = $RCMAIL->imap->list_mailboxes('', '*', 'mail');
}
else {
    $a_mailboxes = (array) $current;
    if ($a_mailboxes[0] != 'INBOX')
        $a_mailboxes[] = 'INBOX';
}

// check recent/unseen counts
foreach ($a_mailboxes as $mbox_name) {
    $is_current = $mbox_name == $current;
    if ($is_current) {
        // Synchronize mailbox cache, handle flag changes
        $RCMAIL->imap->mailbox_sync($mbox_name);
    }

    // Get mailbox status
    $status = $RCMAIL->imap->mailbox_status($mbox_name);

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
            $_SESSION['search'] = $RCMAIL->imap->refresh_search();
        }

        if (!empty($_GET['_quota']))
            $OUTPUT->command('set_quota', rcmail_quota_content());

        // "No-list" mode, don't get messages
        if (empty($_GET['_list']))
            continue;

        // get overall message count; allow caching because rcube_imap::mailbox_status() did a refresh
        $all_count = $RCMAIL->imap->messagecount(null, $RCMAIL->imap->threading ? 'THREADS' : 'ALL');

        // check current page if we're not on the first page
        if ($all_count && $RCMAIL->imap->list_page > 1) {
            $remaining = $all_count - $RCMAIL->imap->page_size * ($RCMAIL->imap->list_page - 1);
            if ($remaining <= 0) {
                $RCMAIL->imap->set_page($RCMAIL->imap->list_page-1);
                $_SESSION['page'] = $RCMAIL->imap->list_page;
            }
        }

        $OUTPUT->set_env('messagecount', $all_count);
        $OUTPUT->set_env('pagecount', ceil($all_count/$RCMAIL->imap->page_size));
        $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($all_count), $mbox_name);
        $OUTPUT->set_env('current_page', $all_count ? $RCMAIL->imap->list_page : 1);

        // remove old rows (and clear selection if new list is empty)
        $OUTPUT->command('message_list.clear', $all_count ? false : true);

        if ($all_count) {
            $a_headers = $RCMAIL->imap->list_headers($mbox_name, null, $_SESSION['sort_col'], $_SESSION['sort_order']);
            // add message rows
            rcmail_js_message_list($a_headers, false);
            // remove messages that don't exists from list selection array
            $OUTPUT->command('update_selection');
        }
    }
}

$RCMAIL->plugins->exec_hook('keep_alive', array());

$OUTPUT->send();

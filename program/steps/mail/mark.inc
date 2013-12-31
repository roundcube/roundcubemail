<?php
/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/mark.inc                                           |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Mark the submitted messages with the specified flag                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

// only process ajax requests
if (!$OUTPUT->ajax_call) {
    return;
}

$threading    = (bool) $RCMAIL->storage->get_threading();
$skip_deleted = (bool) $RCMAIL->config->get('skip_deleted');
$read_deleted = (bool) $RCMAIL->config->get('read_when_deleted');

$a_flags_map  = array(
    'undelete'  => 'UNDELETED',
    'delete'    => 'DELETED',
    'read'      => 'SEEN',
    'unread'    => 'UNSEEN',
    'flagged'   => 'FLAGGED',
    'unflagged' => 'UNFLAGGED',
);

if (($uids = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST))
    && ($flag = rcube_utils::get_input_value('_flag', rcube_utils::INPUT_POST))
) {
    $flag = $a_flags_map[$flag] ? $a_flags_map[$flag] : strtoupper($flag);

    if ($flag == 'DELETED' && $skip_deleted && $_POST['_from'] != 'show') {
        // count messages before changing anything
        $old_count = $RCMAIL->storage->count(NULL, $threading ? 'THREADS' : 'ALL');
        $old_pages = ceil($old_count / $RCMAIL->storage->get_pagesize());
        $count     = sizeof(explode(',', $uids));
    }

    $marked = $RCMAIL->storage->set_flag($uids, $flag);

    if (!$marked) {
        // send error message
        if ($_POST['_from'] != 'show') {
            $OUTPUT->command('list_mailbox');
        }

        $RCMAIL->display_server_error('errormarking');
        $OUTPUT->send();
        exit;
    }
    else if (empty($_POST['_quiet'])) {
        $OUTPUT->show_message('messagemarked', 'confirmation');
    }

    if ($flag == 'DELETED' && $read_deleted && !empty($_POST['_ruid'])) {
        $ruids = rcube_utils::get_input_value('_ruid', rcube_utils::INPUT_POST);
        $read  = $RCMAIL->storage->set_flag($ruids, 'SEEN');

        if ($read && !$skip_deleted) {
            $OUTPUT->command('flag_deleted_as_read', $ruids);
        }
    }

    if ($flag == 'SEEN' || $flag == 'UNSEEN' || ($flag == 'DELETED' && !$skip_deleted)) {
        rcmail_send_unread_count($RCMAIL->storage->get_folder());
    }
    else if ($flag == 'DELETED' && $skip_deleted) {
        if ($_POST['_from'] == 'show') {
            if ($next = rcube_utils::get_input_value('_next_uid', rcube_utils::INPUT_GPC))
                $OUTPUT->command('show_message', $next);
            else
                $OUTPUT->command('command', 'list');
        }
        else {
            $search_request = rcube_utils::get_input_value('_search', rcube_utils::INPUT_GPC);

            // refresh saved search set after moving some messages
            if ($search_request && $RCMAIL->storage->get_search_set()) {
                $_SESSION['search'] = $RCMAIL->storage->refresh_search();
            }

            $msg_count      = $RCMAIL->storage->count(NULL, $threading ? 'THREADS' : 'ALL');
            $page_size      = $RCMAIL->storage->get_pagesize();
            $page           = $RCMAIL->storage->get_page();
            $pages          = ceil($msg_count / $page_size);
            $nextpage_count = $old_count - $page_size * $page;
            $remaining      = $msg_count - $page_size * ($page - 1);

            // jump back one page (user removed the whole last page)
            if ($page > 1 && $remaining == 0) {
                $page -= 1;
                $RCMAIL->storage->set_page($page);
                $_SESSION['page'] = $page;
                $jump_back = true;
            }

            // update message count display
            $OUTPUT->set_env('messagecount', $msg_count);
            $OUTPUT->set_env('current_page', $page);
            $OUTPUT->set_env('pagecount', $pages);

            // update mailboxlist
            $mbox         = $RCMAIL->storage->get_folder();
            $unseen_count = $msg_count ? $RCMAIL->storage->count($mbox, 'UNSEEN') : 0;
            $old_unseen   = rcmail_get_unseen_count($mbox);

            if ($old_unseen != $unseen_count) {
                $OUTPUT->command('set_unread_count', $mbox, $unseen_count, ($mbox == 'INBOX'));
                rcmail_set_unseen_count($mbox, $unseen_count);
            }

            $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($msg_count), $mbox);

            if ($threading) {
                $count = rcube_utils::get_input_value('_count', rcube_utils::INPUT_POST);
            }

            // add new rows from next page (if any)
            if ($count && $uids != '*' && ($jump_back || $nextpage_count > 0)) {
                $a_headers = $RCMAIL->storage->list_messages($mbox, NULL,
                    rcmail_sort_column(), rcmail_sort_order(), $jump_back ? NULL : $count);

                rcmail_js_message_list($a_headers, false);
            }
        }
    }
}
else {
    $OUTPUT->show_message('internalerror', 'error');
}

$OUTPUT->send();

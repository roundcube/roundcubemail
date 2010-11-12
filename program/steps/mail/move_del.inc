<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/move_del.inc                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Move the submitted messages to a specific mailbox or delete them    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// only process ajax requests
if (!$OUTPUT->ajax_call)
  return;

// count messages before changing anything
$old_count = $IMAP->messagecount(NULL, $IMAP->threading ? 'THREADS' : 'ALL');
$old_pages = ceil($old_count / $IMAP->page_size);

// move messages
if ($RCMAIL->action=='moveto' && !empty($_POST['_uid']) && strlen($_POST['_target_mbox'])) {
    $count = sizeof(explode(',', ($uids = get_input_value('_uid', RCUBE_INPUT_POST))));
    $target = get_input_value('_target_mbox', RCUBE_INPUT_POST);
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST);

    $moved = $IMAP->move_message($uids, $target, $mbox);

    if (!$moved) {
        // send error message
	    if ($_POST['_from'] != 'show')
            $OUTPUT->command('list_mailbox');
        $OUTPUT->show_message('errormoving', 'error');
        $OUTPUT->send();
        exit;
    }
    else {
      $OUTPUT->show_message('messagemoved', 'confirmation');
    }

    $addrows = true;
}
// delete messages 
else if ($RCMAIL->action=='delete' && !empty($_POST['_uid'])) {
    $count = sizeof(explode(',', ($uids = get_input_value('_uid', RCUBE_INPUT_POST))));
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST);

    $del = $IMAP->delete_message($uids, $mbox);

    if (!$del) {
        // send error message
	    if ($_POST['_from'] != 'show')
            $OUTPUT->command('list_mailbox');
        $OUTPUT->show_message('errordeleting', 'error');
        $OUTPUT->send();
        exit;
    }
    else {
      $OUTPUT->show_message('messagedeleted', 'confirmation');
    }

    $addrows = true;
}
// unknown action or missing query param
else {
    exit;
}

// refresh saved search set after moving some messages
if (($search_request = get_input_value('_search', RCUBE_INPUT_GPC)) && $IMAP->search_set) {
    $_SESSION['search'] = $IMAP->refresh_search();
}

if ($_POST['_from'] == 'show')
{
  if ($next = get_input_value('_next_uid', RCUBE_INPUT_GPC))
    $OUTPUT->command('show_message', $next);
  else
    $OUTPUT->command('command', 'list');
}
else
{
  $msg_count      = $IMAP->messagecount(NULL, $IMAP->threading ? 'THREADS' : 'ALL');
  $pages          = ceil($msg_count / $IMAP->page_size);
  $nextpage_count = $old_count - $IMAP->page_size * $IMAP->list_page;
  $remaining      = $msg_count - $IMAP->page_size * ($IMAP->list_page - 1);

  // jump back one page (user removed the whole last page)
  if ($IMAP->list_page > 1 && $remaining == 0) {
    $IMAP->set_page($IMAP->list_page-1);
    $_SESSION['page'] = $IMAP->list_page;
    $jump_back = true;
  }

  // update message count display
  $OUTPUT->set_env('messagecount', $msg_count);
  $OUTPUT->set_env('current_page', $IMAP->list_page);
  $OUTPUT->set_env('pagecount', $pages);

  // update mailboxlist
  $mbox = $IMAP->get_mailbox_name();
  $unseen_count = $msg_count ? $IMAP->messagecount($mbox, 'UNSEEN') : 0;
  $old_unseen = $_SESSION['unseen_count'][$mbox];
  
  if ($old_unseen != $unseen_count) {
    $OUTPUT->command('set_unread_count', $mbox, $unseen_count, ($mbox == 'INBOX'));
    $_SESSION['unseen_count'][$mbox] = $unseen_count;
  }

  if ($RCMAIL->action=='moveto' && strlen($target)) {
    rcmail_send_unread_count($target, true);
  }

  $OUTPUT->command('set_quota', rcmail_quota_content());
  $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($msg_count));

  if ($IMAP->threading)
    $count = get_input_value('_count', RCUBE_INPUT_POST);

  // add new rows from next page (if any)
  if ($addrows && $count && $uids != '*' && ($jump_back || $nextpage_count > 0)) {
    $sort_col   = isset($_SESSION['sort_col'])   ? $_SESSION['sort_col']   : $CONFIG['message_sort_col'];
    $sort_order = isset($_SESSION['sort_order']) ? $_SESSION['sort_order'] : $CONFIG['message_sort_order'];

    $a_headers = $IMAP->list_headers($mbox, NULL, $sort_col, $sort_order,
      $jump_back ? NULL : $count);

    rcmail_js_message_list($a_headers, false);
  }
}

// send response
$OUTPUT->send();



<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/move_del.inc                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
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
$threading = (bool) $RCMAIL->storage->get_threading();
$old_count = $RCMAIL->storage->count(NULL, $threading ? 'THREADS' : 'ALL');
$old_pages = ceil($old_count / $RCMAIL->storage->get_pagesize());

// move messages
if ($RCMAIL->action=='moveto' && !empty($_POST['_uid']) && strlen($_POST['_target_mbox'])) {
    $count = sizeof(explode(',', ($uids = get_input_value('_uid', RCUBE_INPUT_POST))));
    $target = get_input_value('_target_mbox', RCUBE_INPUT_POST, true);
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST, true);

    $moved = $RCMAIL->storage->move_message($uids, $target, $mbox);

    if (!$moved) {
        // send error message
	    if ($_POST['_from'] != 'show')
            $OUTPUT->command('list_mailbox');
        rcmail_display_server_error('errormoving');
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
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST, true);

    $del = $RCMAIL->storage->delete_message($uids, $mbox);

    if (!$del) {
        // send error message
	    if ($_POST['_from'] != 'show')
            $OUTPUT->command('list_mailbox');
        rcmail_display_server_error('errordeleting');
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

$search_request = get_input_value('_search', RCUBE_INPUT_GPC);

// refresh saved search set after moving some messages
if ($search_request && $RCMAIL->storage->get_search_set()) {
    $_SESSION['search'] = $RCMAIL->storage->refresh_search();
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
  $mbox = $RCMAIL->storage->get_folder();
  $unseen_count = $msg_count ? $RCMAIL->storage->count($mbox, 'UNSEEN') : 0;
  $old_unseen = rcmail_get_unseen_count($mbox);

  if ($old_unseen != $unseen_count) {
    $OUTPUT->command('set_unread_count', $mbox, $unseen_count, ($mbox == 'INBOX'));
    rcmail_set_unseen_count($mbox, $unseen_count);
  }

  if ($RCMAIL->action == 'moveto' && strlen($target)) {
    rcmail_send_unread_count($target, true);
  }

  $OUTPUT->command('set_quota', rcmail_quota_content());
  $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($msg_count), $mbox);

  if ($threading) {
    $count = get_input_value('_count', RCUBE_INPUT_POST);
  }

  // add new rows from next page (if any)
  if ($addrows && $count && $uids != '*' && ($jump_back || $nextpage_count > 0)) {
    $a_headers = $RCMAIL->storage->list_messages($mbox, NULL,
      rcmail_sort_column(), rcmail_sort_order(), $jump_back ? NULL : $count);

    rcmail_js_message_list($a_headers, false);
  }
}

// send response
$OUTPUT->send();



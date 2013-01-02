<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/list.inc                                           |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send message list to client (as remote response)                    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

if (!$OUTPUT->ajax_call) {
  return;
}

$save_arr       = array();
$dont_override  = (array) $RCMAIL->config->get('dont_override');

// is there a sort type for this request?
if ($sort = get_input_value('_sort', RCUBE_INPUT_GET)) {
  // yes, so set the sort vars
  list($sort_col, $sort_order) = explode('_', $sort);

  // set session vars for sort (so next page and task switch know how to sort)
  if (!in_array('message_sort_col', $dont_override)) {
    $_SESSION['sort_col'] = $save_arr['message_sort_col'] = $sort_col;
  }
  if (!in_array('message_sort_order', $dont_override)) {
    $_SESSION['sort_order'] = $save_arr['message_sort_order'] = $sort_order;
  }
}

// is there a set of columns for this request?
if ($cols = get_input_value('_cols', RCUBE_INPUT_GET)) {
  if (!in_array('list_cols', $dont_override)) {
    $save_arr['list_cols'] = explode(',', $cols);
  }
}

if (!empty($save_arr)) {
  $RCMAIL->user->save_prefs($save_arr);
}

$mbox_name = $RCMAIL->storage->get_folder();
$threading = (bool) $RCMAIL->storage->get_threading();

// Synchronize mailbox cache, handle flag changes
$RCMAIL->storage->folder_sync($mbox_name);

// initialize searching result if search_filter is used
if ($_SESSION['search_filter'] && $_SESSION['search_filter'] != 'ALL') {
  $search_request = md5($mbox_name.$_SESSION['search_filter']);
  $RCMAIL->storage->search($mbox_name, $_SESSION['search_filter'], RCMAIL_CHARSET, rcmail_sort_column());
  $_SESSION['search'] = $RCMAIL->storage->get_search_set();
  $_SESSION['search_request'] = $search_request;
  $OUTPUT->set_env('search_request', $search_request);
}

// fetch message headers
if ($count = $RCMAIL->storage->count($mbox_name, $threading ? 'THREADS' : 'ALL', !empty($_REQUEST['_refresh'])))
  $a_headers = $RCMAIL->storage->list_messages($mbox_name, NULL, rcmail_sort_column(), rcmail_sort_order());

// update search set (possible change of threading mode)
if (!empty($_REQUEST['_search']) && isset($_SESSION['search'])
    && $_SESSION['search_request'] == $_REQUEST['_search']
) {
  $_SESSION['search'] = $RCMAIL->storage->get_search_set();
}
// remove old search data
else if (empty($_REQUEST['_search']) && isset($_SESSION['search'])) {
  $RCMAIL->session->remove('search');
}

// empty result? we'll skip UNSEEN counting in rcmail_send_unread_count()
if (empty($search_request) && empty($a_headers)) {
    $unseen = 0;
}

// update mailboxlist
rcmail_send_unread_count($mbox_name, !empty($_REQUEST['_refresh']), $unseen);

// update message count display
$pages = ceil($count/$RCMAIL->storage->get_pagesize());
$OUTPUT->set_env('messagecount', $count);
$OUTPUT->set_env('pagecount', $pages);
$OUTPUT->set_env('threading', $threading);
$OUTPUT->set_env('current_page', $count ? $RCMAIL->storage->get_page() : 1);
$OUTPUT->set_env('exists', $RCMAIL->storage->count($mbox_name, 'EXISTS'));
$OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($count), $mbox_name);
$OUTPUT->command('set_mailboxname', rcmail_get_mailbox_name_text());

// add message rows
rcmail_js_message_list($a_headers, FALSE, $cols);
if (isset($a_headers) && count($a_headers)) {
  if ($search_request) {
    $OUTPUT->show_message('searchsuccessful', 'confirmation', array('nr' => $count));
  }
}
else {
  // handle IMAP errors (e.g. #1486905)
  if ($err_code = $RCMAIL->storage->get_error_code()) {
    rcmail_display_server_error();
  }
  else if ($search_request)
    $OUTPUT->show_message('searchnomatch', 'notice');
  else
    $OUTPUT->show_message('nomessagesfound', 'notice');
}

// send response
$OUTPUT->send();

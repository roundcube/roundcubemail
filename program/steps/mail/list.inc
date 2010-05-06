<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/list.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send message list to client (as remote response)                    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

if (!$OUTPUT->ajax_call) {
  return;
}

// is there a sort type for this request?
if ($sort = get_input_value('_sort', RCUBE_INPUT_GET))
{
  // yes, so set the sort vars
  list($sort_col, $sort_order) = explode('_', $sort);

  // set session vars for sort (so next page and task switch know how to sort)
  $save_arr = array();
  $_SESSION['sort_col'] = $save_arr['message_sort_col'] = $sort_col;
  $_SESSION['sort_order'] = $save_arr['message_sort_order'] = $sort_order;
}
else
{
  // use session settings if set, defaults if not
  $sort_col   = isset($_SESSION['sort_col'])   ? $_SESSION['sort_col']   : $CONFIG['message_sort_col'];
  $sort_order = isset($_SESSION['sort_order']) ? $_SESSION['sort_order'] : $CONFIG['message_sort_order'];
}

// is there a set of columns for this request?
if ($cols = get_input_value('_cols', RCUBE_INPUT_GET))
{
  $save_arr = array();
  $save_arr['list_cols'] = explode(',', $cols);
}

if ($save_arr)  
  $RCMAIL->user->save_prefs($save_arr);

$mbox_name = $IMAP->get_mailbox_name();

// initialize searching result if search_filter is used
if ($_SESSION['search_filter'] && $_SESSION['search_filter'] != 'ALL')
{
  $search_request = md5($mbox_name.$_SESSION['search_filter']);
  $IMAP->search($mbox_name, $_SESSION['search_filter'], RCMAIL_CHARSET, $sort_col);
  $_SESSION['search'][$search_request] = $IMAP->get_search_set();
  $OUTPUT->set_env('search_request', $search_request);
}

// fetch message headers
if ($count = $IMAP->messagecount($mbox_name, $IMAP->threading ? 'THREADS' : 'ALL', !empty($_REQUEST['_refresh'])))
  $a_headers = $IMAP->list_headers($mbox_name, NULL, $sort_col, $sort_order);

// update search set (possible change of threading mode)
if (!empty($_REQUEST['_search']) && isset($_SESSION['search'][$_REQUEST['_search']]))
  $_SESSION['search'][$_REQUEST['_search']] = $IMAP->get_search_set();

// update mailboxlist
rcmail_send_unread_count($mbox_name, !empty($_REQUEST['_refresh']));

// update message count display
$pages = ceil($count/$IMAP->page_size);
$OUTPUT->set_env('messagecount', $count);
$OUTPUT->set_env('pagecount', $pages);
$OUTPUT->set_env('threading', (bool) $IMAP->threading);
$OUTPUT->set_env('current_page', $count ? $IMAP->list_page : 1);
$OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($count));
$OUTPUT->command('set_mailboxname', rcmail_get_mailbox_name_text());

// add message rows
rcmail_js_message_list($a_headers, FALSE, TRUE, (bool) $cols);
if (isset($a_headers) && count($a_headers))
{
  if ($search_request)
    $OUTPUT->show_message('searchsuccessful', 'confirmation', array('nr' => $count));
}
else if ($search_request)
  $OUTPUT->show_message('searchnomatch', 'notice');
else
  $OUTPUT->show_message('nomessagesfound', 'notice');

// send response
$OUTPUT->send();

?>


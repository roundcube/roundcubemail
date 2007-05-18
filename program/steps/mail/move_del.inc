<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/move_del.inc                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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

// move messages
if ($_action=='moveto' && !empty($_POST['_uid']) && !empty($_POST['_target_mbox']))
{
  $count = sizeof(explode(',', ($uids = get_input_value('_uid', RCUBE_INPUT_POST))));
  $target = get_input_value('_target_mbox', RCUBE_INPUT_POST);
  $moved = $IMAP->move_message($uids, $target, get_input_value('_mbox', RCUBE_INPUT_POST));
  
  if (!$moved)
  {
    // send error message
    $OUTPUT->command('list_mailbox');
    $OUTPUT->show_message('errormoving', 'error');
    $OUTPUT->send();
    exit;
  }
}

// delete messages 
else if ($_action=='delete' && !empty($_POST['_uid']))
{
  $count = sizeof(explode(',', ($uids = get_input_value('_uid', RCUBE_INPUT_POST))));
  $del = $IMAP->delete_message($uids, get_input_value('_mbox', RCUBE_INPUT_POST));
  
  if (!$del)
  {
    // send error message
    $OUTPUT->command('list_mailbox');
    $OUTPUT->show_message('errordeleting', 'error');
    $OUTPUT->send();
    exit;
  }
}
  
// unknown action or missing query param
else
  exit;

// refresh saved seach set after moving some messages
if (($search_request = get_input_value('_search', RCUBE_INPUT_GPC)) && $IMAP->search_set)
  $_SESSION['search'][$search_request] = $IMAP->refresh_search();


// update message count display
$msg_count = $IMAP->messagecount();
$pages = ceil($msg_count / $IMAP->page_size);
$OUTPUT->set_env('pagecount', $pages);
$OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($msg_count));


// update mailboxlist
$mbox = $IMAP->get_mailbox_name();
$OUTPUT->command('set_unread_count', $mbox, $IMAP->messagecount($mbox, 'UNSEEN'));

if ($_action=='moveto' && $target)
  $OUTPUT->command('set_unread_count', $target, $IMAP->messagecount($target, 'UNSEEN'));

$OUTPUT->command('set_quota', $IMAP->get_quota());

// add new rows from next page (if any)
if ($_POST['_from']!='show' && $pages>1 && $IMAP->list_page < $pages)
{
  $sort_col   = isset($_SESSION['sort_col'])   ? $_SESSION['sort_col']   : $CONFIG['message_sort_col'];
  $sort_order = isset($_SESSION['sort_order']) ? $_SESSION['sort_order'] : $CONFIG['message_sort_order'];
  
  $a_headers = $IMAP->list_headers($mbox, NULL, $sort_col, $sort_order);
  $a_headers = array_slice($a_headers, -$count, $count);

  rcmail_js_message_list($a_headers);
}


// send response
$OUTPUT->send();

?>

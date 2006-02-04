<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/move_del.inc                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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

$REMOTE_REQUEST = TRUE;

// move messages
if ($_action=='moveto' && $_GET['_uid'] && $_GET['_target_mbox'])
  {
  $count = sizeof(explode(',', $_GET['_uid']));  
  $moved = $IMAP->move_message($_GET['_uid'], $_GET['_target_mbox'], $_GET['_mbox']);
  
  if (!$moved)
    {
    // send error message
    $commands = "this.list_mailbox();\n";
    $commands .= show_message('errormoving', 'error');
    rcube_remote_response($commands);
    exit;
    }
  }

// delete messages 
else if ($_action=='delete' && $_GET['_uid'])
  {
  $count = sizeof(explode(',', $_GET['_uid']));
  $del = $IMAP->delete_message($_GET['_uid'], $_GET['_mbox']);
  
  if (!$del)
    {
    // send error message
    $commands = "this.list_mailbox();\n";
    $commands .= show_message('errordeleting', 'error');
    rcube_remote_response($commands);
    exit;
    }
  }
  
// unknown action or missing query param
else
  {
  exit;
  }


// update message count display
$pages = ceil($IMAP->messagecount()/$IMAP->page_size);
$commands = sprintf("this.set_rowcount('%s');\n", rcmail_get_messagecount_text());
$commands .= sprintf("this.set_env('pagecount', %d);\n", $pages);


// update mailboxlist
$mbox = $IMAP->get_mailbox_name();
$commands .= sprintf("this.set_unread_count('%s', %d);\n", $mbox, $IMAP->messagecount($mbox, 'UNSEEN'));

if ($_action=='moveto')
  $commands .= sprintf("this.set_unread_count('%s', %d);\n", $_GET['_target_mbox'], $IMAP->messagecount($_GET['_target_mbox'], 'UNSEEN'));

$commands .= sprintf("this.set_quota('%s');\n", $IMAP->get_quota()); 

// add new rows from next page (if any)
if ($_GET['_from']!='show' && $pages>1 && $IMAP->list_page < $pages)
  {
  $a_headers = $IMAP->list_headers($mbox, null, $_SESSION['sort_col'], $_SESSION['sort_order']);
  $a_headers = array_slice($a_headers, -$count, $count);
  $commands .= rcmail_js_message_list($a_headers);
  }

  
// send response
rcube_remote_response($commands);

exit;
?>
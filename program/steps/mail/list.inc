<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/list.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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

$REMOTE_REQUEST = TRUE;
$OUTPUT_TYPE = 'js';

$unseen = $IMAP->messagecount($mbox, 'UNSEEN', !empty($_GET['_refresh']) ? TRUE : FALSE);
$count = $IMAP->messagecount();

// is there a sort type for this request?
if ($sort = isset($_GET['_sort']) ? $_GET['_sort'] : false)
  {
  // yes, so set the sort vars
  list($sort_col, $sort_order) = explode('_', $sort);

  // set session vars for sort (so next page and task switch know how to sort)
  $_SESSION['sort_col'] = $sort_col;
  $_SESSION['sort_order'] = $sort_order;
  }
else
  {        
  // use session settings if set, defaults if not
  $sort_col   = isset($_SESSION['sort_col'])   ? $_SESSION['sort_col']   : $CONFIG['message_sort_col'];
  $sort_order = isset($_SESSION['sort_order']) ? $_SESSION['sort_order'] : $CONFIG['message_sort_order'];
  }
  

// update message count display
$pages = ceil($count/$IMAP->page_size);
$commands = sprintf("this.set_env('messagecount', %d);\n", $count);
$commands .= sprintf("this.set_env('pagecount', %d);\n", $pages);
$commands .= sprintf("this.set_rowcount('%s');\n", rcmail_get_messagecount_text());

// update mailboxlist
$mbox = $IMAP->get_mailbox_name();
$commands .= sprintf("this.set_unread_count('%s', %d);\n", addslashes($mbox), $unseen);


// add message rows
if ($count)
  {
  $a_headers = $IMAP->list_headers($mbox, null, $sort_col, $sort_order);
  $commands .= rcmail_js_message_list($a_headers);
  }

  
// send response
rcube_remote_response($commands);

exit;
?>
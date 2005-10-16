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
  $a_headers = $IMAP->list_headers($mbox);
  $commands .= rcmail_js_message_list($a_headers);
  }

  
// send response
rcube_remote_response($commands);

exit;
?>
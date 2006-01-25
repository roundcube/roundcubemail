<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/check_recent.inc                                   |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Check for recent messages                                           |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$REMOTE_REQUEST = TRUE;
$mbox = $IMAP->get_mailbox_name();

if ($recent_count = $IMAP->messagecount(NULL, 'RECENT', TRUE))
  {
  $count = $IMAP->messagecount();
  $unread_count = $IMAP->messagecount(NULL, 'UNSEEN', TRUE);
    
  $commands = sprintf("this.set_unread_count('%s', %d, true);\n", addslashes($mbox), $unread_count);
  $commands .= sprintf("this.set_env('messagecount', %d);\n", $count);
  $commands .= sprintf("this.set_rowcount('%s');\n", rcmail_get_messagecount_text());
  
  // add new message headers to list
  $a_headers = array();
  for ($i=$recent_count, $id=$count-$recent_count+1; $i>0; $i--, $id++)
    $a_headers[] = $IMAP->get_headers($id, NULL, FALSE);
    
  $commands .= rcmail_js_message_list($a_headers, TRUE);
  }

if (strtoupper($mbox)!='INBOX' && $IMAP->messagecount('INBOX', 'RECENT'))
  $commands = sprintf("this.set_unread_count('INBOX', %d);\n", $IMAP->messagecount('INBOX', 'UNSEEN'));


rcube_remote_response($commands);
?>
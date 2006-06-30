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
 |   Check for recent messages, in all mailboxes                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: check_recent.inc 233 2006-06-26 17:31:20Z richs $

*/

$REMOTE_REQUEST = TRUE;

$a_mailboxes = $IMAP->list_mailboxes();

foreach ($a_mailboxes as $mbox_name)
  {
  if ($mbox_name == $IMAP->get_mailbox_name())
    {
    if ($recent_count = $IMAP->messagecount(NULL, 'RECENT', TRUE))
      {
      $count = $IMAP->messagecount();
      $unread_count = $IMAP->messagecount(NULL, 'UNSEEN', TRUE);

      $commands .= sprintf("this.set_unread_count('%s', %d);\n", addslashes($mbox_name), $unread_count);
      $commands .= sprintf("this.set_env('messagecount', %d);\n", $count);
      $commands .= sprintf("this.set_rowcount('%s');\n", rcmail_get_messagecount_text());
      $commands .= sprintf("this.set_quota('%s');\n", $IMAP->get_quota());

      // add new message headers to list
      $a_headers = array();
      for ($i=$recent_count, $id=$count-$recent_count+1; $i>0; $i--, $id++)
        $a_headers[] = $IMAP->get_headers($id, NULL, FALSE);

      $commands .= rcmail_js_message_list($a_headers, TRUE);
      }
    }
  else
    {
    if ($IMAP->messagecount($mbox_name, 'RECENT'))
      $commands .= sprintf("this.set_unread_count('%s', %d);\n", addslashes($mbox_name), $IMAP->messagecount($mbox_name, 'UNSEEN'));
    }
  }

rcube_remote_response($commands);
?>

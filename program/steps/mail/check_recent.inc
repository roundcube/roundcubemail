<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/check_recent.inc                                   |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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

$a_mailboxes = $IMAP->list_mailboxes();

foreach ($a_mailboxes as $mbox_name)
  {
  if ($mbox_name == $IMAP->get_mailbox_name())
    {
    if ($recent_count = $IMAP->messagecount(NULL, 'RECENT', TRUE))
      {
      $count = $IMAP->messagecount(NULL, 'ALL', TRUE);
      $unread_count = $IMAP->messagecount(NULL, 'UNSEEN', TRUE);

      $OUTPUT->set_env('messagecount', $count);
      $OUTPUT->command('set_unread_count', $mbox_name, $unread_count, true);
      $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text());
      $OUTPUT->command('set_quota', $IMAP->get_quota());

      // add new message headers to list
      $a_headers = array();
      for ($i=$recent_count, $id=$count-$recent_count+1; $i>0; $i--, $id++)
        {
        $header = $IMAP->get_headers($id, NULL, FALSE);
        if ($header->recent)
          $a_headers[] = $header;
        }

      rcmail_js_message_list($a_headers, TRUE);
      }
    }
  else
    {
    if ($IMAP->messagecount($mbox_name, 'RECENT'))
      $OUTPUT->command('set_unread_count', $mbox_name, $IMAP->messagecount($mbox_name, 'UNSEEN'));
    }
  }

$OUTPUT->send();

?>

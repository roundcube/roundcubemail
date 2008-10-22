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
      // refresh saved search set
      if (($search_request = get_input_value('_search', RCUBE_INPUT_GPC)) && $IMAP->search_set)
        $_SESSION['search'][$search_request] = $IMAP->refresh_search();
        
      $count_all = $IMAP->messagecount(NULL, 'ALL', TRUE);
      $unread_count = $IMAP->messagecount(NULL, 'UNSEEN', TRUE);

      $OUTPUT->set_env('messagecount', $IMAP->messagecount());
      $OUTPUT->set_env('pagesize', $IMAP->page_size);
      $OUTPUT->set_env('pagecount', ceil($IMAP->messagecount()/$IMAP->page_size));
      $OUTPUT->command('set_unread_count', $mbox_name, $unread_count, ($mbox_name == 'INBOX'));
      $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text());
      $OUTPUT->command('set_quota', rcmail_quota_content($IMAP->get_quota()));

      if (rcmail::get_instance()->config->get('focus_on_new_message',true))
        $OUTPUT->command('new_message_focus');

      // add new message headers to list
      $a_headers = array();
      for ($i=$recent_count, $id=$count_all-$recent_count+1; $i>0; $i--, $id++)
        {
        // skip message if it does not match the current search
        if (!$IMAP->in_searchset($id))
          continue;
        
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

<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/check_recent.inc                                   |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2010, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Check for recent messages, in all mailboxes                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$a_mailboxes = $IMAP->list_mailboxes();
$check_all = !empty($_GET['_refresh']) || (bool)$RCMAIL->config->get('check_all_folders');

// check recent/unseen counts for all mailboxes
foreach ($a_mailboxes as $mbox_name) {
  if ($mbox_name == $IMAP->get_mailbox_name()) {
    if ($recents = $IMAP->recent_uids($mbox_name)) {
      // refresh saved search set
      if (($search_request = get_input_value('_search', RCUBE_INPUT_GPC)) && isset($_SESSION['search'][$search_request])) {
        $_SESSION['search'][$search_request] = $IMAP->refresh_search();
      }
      
      // get overall message count; allow caching because rcube_imap::recent_uids() did a refresh
      $all_count = $IMAP->messagecount();
      
      $unread_count = $IMAP->messagecount(NULL, 'UNSEEN', TRUE);
      $_SESSION['unseen_count'][$mbox_name] = $unread_count;

      $OUTPUT->set_env('messagecount', $all_count);
      $OUTPUT->set_env('pagesize', $IMAP->page_size);
      $OUTPUT->set_env('pagecount', ceil($all_count/$IMAP->page_size));
      $OUTPUT->command('set_unread_count', $mbox_name, $unread_count, ($mbox_name == 'INBOX'));
      $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($all_count));

      if ($RCMAIL->config->get('focus_on_new_message',true))
        $OUTPUT->command('new_message_focus');

      if (!empty($_GET['_quota']))
        $OUTPUT->command('set_quota', rcmail_quota_content());

      // trigger plugin hook
      $RCMAIL->plugins->exec_hook('new_messages', array('mailbox' => $mbox_name, 'count' => count($recents)));

      // "No-list" mode, don't get messages
      if (empty($_GET['_list']))
        continue;

      // use SEARCH/SORT to find recent messages
      $search_str = 'UID '.min($recents).':'.max($recents);
      if ($search_request)
        $search_str .= ' '.$IMAP->search_string;

      if ($IMAP->search($mbox_name, $search_str, NULL, 'date')) {
        // get the headers and add them to the list
        $result_h = $IMAP->list_headers($mbox_name, 1, 'date', 'DESC');
        rcmail_js_message_list($result_h, true, false);
      }
    }
    else {
      rcmail_send_unread_count($mbox_name, true);
    }
  }
  else if ($check_all) {
    rcmail_send_unread_count($mbox_name, true);
  }
}

$OUTPUT->send();

?>

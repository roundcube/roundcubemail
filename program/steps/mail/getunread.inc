<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/getunread.inc                                      |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Check all mailboxes for unread messages and update GUI              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$a_folders = $IMAP->list_mailboxes();

if (!empty($a_folders))
{
  $current = $IMAP->get_mailbox_name();
  $inbox = ($current == 'INBOX');
  $check_all = (bool)$RCMAIL->config->get('check_all_folders');

  foreach ($a_folders as $mbox_row) {
    if (!$check_all && isset($_SESSION['unseen_count'][$mbox_row]) && $mbox_row != $current)
      $unseen = $_SESSION['unseen_count'][$mbox_row];
    else
      $unseen = $IMAP->messagecount($mbox_row, 'UNSEEN', !isset($_SESSION['unseen_count'][$mbox_row]));

    if ($unseen || !isset($_SESSION['unseen_count'][$mbox_row])) {
      $OUTPUT->command('set_unread_count', $mbox_row, $unseen, $inbox && $mbox_row == 'INBOX');
    }
    $_SESSION['unseen_count'][$mbox_row] = $unseen;
  }
}

$OUTPUT->send();

?>

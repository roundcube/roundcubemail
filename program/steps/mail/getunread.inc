<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/getunread.inc                                      |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Check all mailboxes for unread messages and update GUI              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

$a_folders = $RCMAIL->storage->list_folders_subscribed('', '*', 'mail');

if (!empty($a_folders))
{
  $current = $RCMAIL->storage->get_folder();
  $inbox = ($current == 'INBOX');
  $check_all = (bool)$RCMAIL->config->get('check_all_folders');

  foreach ($a_folders as $mbox_row) {
    $unseen_old = rcmail_get_unseen_count($mbox_row);

    if (!$check_all && $unseen_old !== null && $mbox_row != $current)
      $unseen = $unseen_old;
    else
      $unseen = $RCMAIL->storage->count($mbox_row, 'UNSEEN', $unseen_old === null);

    if ($unseen || $unseen_old === null) {
      $OUTPUT->command('set_unread_count', $mbox_row, $unseen, $inbox && $mbox_row == 'INBOX');
    }

    rcmail_set_unseen_count($mbox_row, $unseen);
  }
}

$OUTPUT->send();



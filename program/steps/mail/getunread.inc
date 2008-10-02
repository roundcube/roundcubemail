<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/getunread.inc                                      |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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
  $inbox = ($IMAP->get_mailbox_name() == 'INBOX');
  foreach ($a_folders as $mbox_row)
    $OUTPUT->command('set_unread_count', $mbox_row, $IMAP->messagecount($mbox_row, 'UNSEEN'), $inbox && $mbox_row == 'INBOX');
}

$OUTPUT->send();
?>

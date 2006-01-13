<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/getunread.inc                                      |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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

$REMOTE_REQUEST = TRUE;

$a_folders = $IMAP->list_mailboxes();

if (!empty($a_folders))
  {
  foreach ($a_folders as $mbox)
    {
    $commands = sprintf("this.set_unread_count('%s', %d);\n", $mbox, $IMAP->messagecount($mbox, 'UNSEEN'));
    rcube_remote_response($commands, TRUE);
    ob_flush();
    }
  }

exit;
?>
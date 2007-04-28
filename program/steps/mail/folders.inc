<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/folders.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Implement folder operations line EXPUNGE and Clear                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$
*/

$mbox_name = $IMAP->get_mailbox_name();

// send EXPUNGE command
if ($_action=='expunge')
{
  $success = $IMAP->expunge(get_input_value('_mbox', RCUBE_INPUT_GET));

  // reload message list if current mailbox  
  if ($success && !empty($_GET['_reload']))
  {
    $OUTPUT->command('message_list.clear');
    $_action = 'list';
    return;
  }
  else
    $commands = "// expunged: $success\n";
}

// clear mailbox
else if ($_action=='purge')
{
  $success = $IMAP->clear_mailbox(get_input_value('_mbox', RCUBE_INPUT_GET));
  
  if ($success && !empty($_GET['_reload']))
  {
    $OUTPUT->set_env('messagecount', 0);
    $OUTPUT->set_env('pagecount', 0);
    $OUTPUT->command('message_list.clear');
    $OUTPUT->command('set_rowcount', rcmail_get_messagecount_text());
    $OUTPUT->command('set_unread_count', $mbox_name, 0);
  }
  else
    $commands = "// purged: $success";
}

$OUTPUT->send($commands);
?>
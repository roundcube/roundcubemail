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

$REMOTE_REQUEST = TRUE;
$mbox_name = $IMAP->get_mailbox_name();


// send EXPUNGE command
if ($_action=='expunge')
  {
  $success = $IMAP->expunge($_GET['_mbox']);

  // reload message list if current mailbox  
  if ($success && $_GET['_reload'])
    {
    rcube_remote_response('this.message_list.clear();', TRUE);
    $_action = 'list';
    return;
    }
  else
    $commands = "// expunged: $success\n";
  }

// clear mailbox
else if ($_action=='purge')
  {
  $success = $IMAP->clear_mailbox($_GET['_mbox']);
  
  if ($success && $_GET['_reload'])
    {
    $commands = "this.message_list.clear();\n";
    $commands .= "this.set_env('messagecount', 0);\n";
    $commands .= "this.set_env('pagecount', 0);\n";
    $commands .= sprintf("this.set_rowcount('%s');\n", rcmail_get_messagecount_text());
    $commands .= sprintf("this.set_unread_count('%s', 0);\n", addslashes($mbox_name));
    }
  else
    $commands = "// purged: $success";
  }



rcube_remote_response($commands);
?>
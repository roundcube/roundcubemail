<?php
/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/mark.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Mark the submitted messages with the specified flag                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$REMOTE_REQUEST = TRUE;

$a_flags_map = array('undelete' => 'UNDELETED',
                     'delete' => 'DELETED',
                     'read' => 'SEEN',
                     'unread' => 'UNSEEN');

if (($uids = get_input_value('_uid', RCUBE_INPUT_GET)) && ($flag = get_input_value('_flag', RCUBE_INPUT_GET)))
  {
  $flag = $a_flags_map[$flag] ? $a_flags_map[$flag] : strtoupper($flag);
  $marked = $IMAP->set_flag($uids, $flag);
  if ($marked != -1)
    {
    $mbox_name = $IMAP->get_mailbox_name();
    $commands = sprintf("this.set_unread_count('%s', %d);\n", $mbox_name, $IMAP->messagecount($mbox_name, 'UNSEEN'));
    rcube_remote_response($commands);
    }
  }
  
exit;
?>
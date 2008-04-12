<?php
/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/mark.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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

$a_flags_map = array(
  'undelete' => 'UNDELETED',
  'delete' => 'DELETED',
  'read' => 'SEEN',
  'unread' => 'UNSEEN');

if (($uids = get_input_value('_uid', RCUBE_INPUT_POST)) && ($flag = get_input_value('_flag', RCUBE_INPUT_POST)))
{
  $flag = $a_flags_map[$flag] ? $a_flags_map[$flag] : strtoupper($flag);
  $marked = $IMAP->set_flag($uids, $flag);

  if($flag == 'DELETED' && $CONFIG['read_when_deleted'] && !empty($_POST['_ruid']))
    {
    $uids = get_input_value('_ruid', RCUBE_INPUT_POST);
    $read = $IMAP->set_flag($uids, 'SEEN');
    
    if ($read != -1)
      $OUTPUT->command('flag_deleted_as_read', $uids);
    }

  if ($marked != -1)
  {
    $mbox_name = $IMAP->get_mailbox_name();
    $OUTPUT->command('set_unread_count', $mbox_name, $IMAP->messagecount($mbox_name, 'UNSEEN'), ($mbox_name == 'INBOX'));
    $OUTPUT->send();
  }
}
  
exit;
?>
<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/copy.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2007, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Copy a contact record from one direcotry to another                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: copy.inc 471 2007-02-09 21:25:50Z thomasb $

*/

// only process ajax requests
if (!$OUTPUT->ajax_call)
  return;

$cid = get_input_value('_cid', RCUBE_INPUT_POST);
$target = get_input_value('_to', RCUBE_INPUT_POST);

if ($cid && preg_match('/^[a-z0-9\-_=]+(,[a-z0-9\-_=]+)*$/i', $cid) && strlen($target) && $target !== $source)
{
  $success = 0;
  $TARGET = $RCMAIL->get_address_book($target);

  if ($TARGET && $TARGET->ready && !$TARGET->readonly) {
    $arr_cids = explode(',', $cid);
    foreach ($arr_cids as $cid) {
      $plugin = $RCMAIL->plugins->exec_hook('create_contact', array('record' => $CONTACTS->get_record($cid, true), 'source' => $target));
    $a_record = $plugin['record'];

    if (!$plugin['abort'])
        if ($TARGET->insert($a_record, true))
          $success++;
    }
  }

  if ($success == 0)
    $OUTPUT->show_message('copyerror', 'error');
  else
    $OUTPUT->show_message('copysuccess', 'notice', array('nr' => $success));
    
  // close connection to second address directory
  $TARGET->close();
}

// send response
$OUTPUT->send();

?>

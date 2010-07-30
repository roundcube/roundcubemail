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
$target_group = get_input_value('_togid', RCUBE_INPUT_POST);

if ($cid && preg_match('/^[a-zA-Z0-9\+\/=_-]+(,[a-zA-Z0-9\+\/=_-]+)*$/', $cid) && strlen($target) && $target !== $source)
{
  $success = 0;
  $TARGET = $RCMAIL->get_address_book($target);

  if ($TARGET && $TARGET->ready && !$TARGET->readonly) {
    $arr_cids = explode(',', $cid);
    $ids = array();

    foreach ($arr_cids as $cid) {
      $plugin = $RCMAIL->plugins->exec_hook('contact_create', array(
        'record' => $CONTACTS->get_record($cid, true),
        'source' => $target,
        'group' => $target_group,
      ));
      $a_record = $plugin['record'];

      if (!$plugin['abort']) {
        // check if contact exists, if so, we'll need it's ID
        $result = $TARGET->search('email', $a_record['email'], true, true);

        // insert contact record
        if (!$result->count) {
          if ($insert_id = $TARGET->insert($a_record, false)) {
            $ids[] = $insert_id;
            $success++;
          }
        }
        else {
          $record = $result->first();
          $ids[] = $record['ID'];
        }
      }
    }

    // assign to group
    if ($target_group && $TARGET->groups && !empty($ids)) {
      $plugin = $RCMAIL->plugins->exec_hook('group_addmembers', array(
        'group_id' => $target_group, 'ids' => $ids, 'source' => $target));

      if (!$plugin['abort']) {
        $TARGET->reset();
        $TARGET->set_group($target_group);

        if (($maxnum = $RCMAIL->config->get('max_group_members', 0)) && ($TARGET->count()->count + count($plugin['ids']) > $maxnum)) {
          $OUTPUT->show_message('maxgroupmembersreached', 'warning', array('max' => $maxnum));
          $OUTPUT->send();
        }

        if (($cnt = $TARGET->add_to_group($target_group, $plugin['ids'])) && $cnt > $success)
          $success = $cnt;
      }
    }
  }

  if ($success == 0)
    $OUTPUT->show_message('copyerror', 'error');
  else
    $OUTPUT->show_message('copysuccess', 'notice', array('nr' => $success));
}

// send response
$OUTPUT->send();


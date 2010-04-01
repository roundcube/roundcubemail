<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/groups.inc                                  |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2010, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Create/delete/rename contact groups and assign/remove contacts      |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


if ($CONTACTS->readonly || !$CONTACTS->groups) {
  $OUTPUT->show_message('sourceisreadonly', 'warning');
  $OUTPUT->send();
}

$source = get_input_value('_source', RCUBE_INPUT_GPC);

if ($RCMAIL->action == 'group-addmembers') {
  if (($gid = get_input_value('_gid', RCUBE_INPUT_POST)) && ($ids = get_input_value('_cid', RCUBE_INPUT_POST))) {
    $plugin = $RCMAIL->plugins->exec_hook('group_addmember', array('group_id' => $gid, 'ids' => $ids, 'source' => $source));
    
    if (!$plugin['abort'] && $CONTACTS->add_to_group($gid, $plugin['ids']))
      $OUTPUT->show_message('contactaddedtogroup');
    else if ($plugin['message'])
      $OUTPUT->show_message($plugin['message'], 'warning');
  }
}

else if ($RCMAIL->action == 'group-delmembers') {
  if (($gid = get_input_value('_gid', RCUBE_INPUT_POST)) && ($ids = get_input_value('_cid', RCUBE_INPUT_POST))) {
    $plugin = $RCMAIL->plugins->exec_hook('group_delmembers', array('group_id' => $gid, 'ids' => $ids, 'source' => $source));
    $ids = $plugin['ids'];
    
    if (!$plugin['abort'] && $CONTACTS->remove_from_group($gid, $ids))
      $OUTPUT->show_message('contactremovedfromgroup');
    else if ($plugin['message'])
      $OUTPUT->show_message($plugin['message'], 'warning');
  }
}

else if ($RCMAIL->action == 'group-create') {
  if ($name = trim(get_input_value('_name', RCUBE_INPUT_POST))) {
    $plugin = $RCMAIL->plugins->exec_hook('group_create', array('name' => $name, 'source' => $source));
    if (!$plugin['abort'])
      $created = $CONTACTS->create_group($plugin['name']);
  }
  
  if ($created && $OUTPUT->ajax_call) {
    $OUTPUT->command('insert_contact_group', $created);
  }
  else if (!$created) {
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error');
  }
}

else if ($RCMAIL->action == 'group-rename') {
  if (($gid = get_input_value('_gid', RCUBE_INPUT_POST)) && ($name = trim(get_input_value('_name', RCUBE_INPUT_POST)))) {
    $plugin = $RCMAIL->plugins->exec_hook('group_rename', array('group_id' => $gid, 'name' => $name, 'source' => $source));
    if (!$plugin['abort'])
      $newname = $CONTACTS->rename_group($gid, $plugin['name']);
  }

  if ($newname && $OUTPUT->ajax_call)
    $OUTPUT->command('update_contact_group', $gid, $newname);
  else if (!$newname)
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error');
}

else if ($RCMAIL->action == 'group-delete') {
  if ($gid = get_input_value('_gid', RCUBE_INPUT_POST)) {
    $plugin = $RCMAIL->plugins->exec_hook('group_delete', array('group_id' => $gid, 'source' => $source));
    if (!$plugin['abort'])
      $deleted = $CONTACTS->delete_group($gid);
  }

  if ($deleted)
    $OUTPUT->command('remove_group_item', $gid);
  else
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error');
}

// send response
$OUTPUT->send();

?>
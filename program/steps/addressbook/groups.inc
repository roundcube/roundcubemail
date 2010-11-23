<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/groups.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2010, Roundcube Dev. - Switzerland                      |
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
    $plugin = $RCMAIL->plugins->exec_hook('group_addmembers', array('group_id' => $gid, 'ids' => $ids, 'source' => $source));

    $CONTACTS->set_group($gid);
    $num2add = count(explode(',', $plugin['ids']));

    if (!$plugin['abort']) {
      if (($maxnum = $RCMAIL->config->get('max_group_members', 0)) && ($CONTACTS->count()->count + $num2add > $maxnum)) {
        $OUTPUT->show_message('maxgroupmembersreached', 'warning', array('max' => $maxnum));
        $OUTPUT->send();
      }
      $result = $CONTACTS->add_to_group($gid, $plugin['ids']);
    }
    else {
      $result = $plugin['result'];
    }

    if ($result)
      $OUTPUT->show_message('contactaddedtogroup');
    else
      $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error');
  }
}

else if ($RCMAIL->action == 'group-delmembers') {
  if (($gid = get_input_value('_gid', RCUBE_INPUT_POST)) && ($ids = get_input_value('_cid', RCUBE_INPUT_POST))) {
    $plugin = $RCMAIL->plugins->exec_hook('group_delmembers', array('group_id' => $gid, 'ids' => $ids, 'source' => $source));

    if (!$plugin['abort'])
      $result = $CONTACTS->remove_from_group($gid, $plugin['ids']);
    else
      $result = $plugin['result'];

    if ($result)
      $OUTPUT->show_message('contactremovedfromgroup');
    else
      $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error');
  }
}

else if ($RCMAIL->action == 'group-create') {
  if ($name = trim(get_input_value('_name', RCUBE_INPUT_POST))) {
    $plugin = $RCMAIL->plugins->exec_hook('group_create', array('name' => $name, 'source' => $source));

    if (!$plugin['abort'])
      $created = $CONTACTS->create_group($plugin['name']);
    else
      $created = $plugin['result'];
  }

  if ($created && $OUTPUT->ajax_call) {
    $OUTPUT->show_message('groupcreated', 'confirmation');
    $OUTPUT->command('insert_contact_group', array(
      'source' => $source, 'id' => $created['id'], 'name' => $created['name']));
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
    else
      $newname = $plugin['result'];
  }

  if ($newname && $OUTPUT->ajax_call) {
    $OUTPUT->show_message('grouprenamed', 'confirmation');
    $OUTPUT->command('update_contact_group', array(
      'source' => $source, 'id' => $gid, 'name' => $newname));
  }
  else if (!$newname)
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error');
}

else if ($RCMAIL->action == 'group-delete') {
  if ($gid = get_input_value('_gid', RCUBE_INPUT_POST)) {
    $plugin = $RCMAIL->plugins->exec_hook('group_delete', array('group_id' => $gid, 'source' => $source));

    if (!$plugin['abort'])
      $deleted = $CONTACTS->delete_group($gid);
    else
      $deleted = $plugin['result'];
  }

  if ($deleted) {
    $OUTPUT->show_message('groupdeleted', 'confirmation');
    $OUTPUT->command('remove_group_item', array('source' => $source, 'id' => $gid));
  }
  else
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error');
}

// send response
$OUTPUT->send();


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

if ($RCMAIL->action == 'group-addmember') {
  if (($gid = get_input_value('_gid', RCUBE_INPUT_POST)) && ($ids = get_input_value('_cid', RCUBE_INPUT_POST)))
  if ($CONTACTS->add_to_group($gid, $ids))
    $OUTPUT->show_message('contactaddedtogroup');
  //else
  //  $OUTPUT->show_message('erroraddingcontact', 'warning');
}

else if ($RCMAIL->action == 'group-delmember') {
  if (($gid = get_input_value('_gid', RCUBE_INPUT_POST)) && ($ids = get_input_value('_cid', RCUBE_INPUT_POST)))
  if ($CONTACTS->remove_from_group($gid, $ids))
    $OUTPUT->show_message('contactremovedfromgroup');
  //else
  //  $OUTPUT->show_message('erroraddingcontact', 'warning');
}

else if ($RCMAIL->action == 'group-create') {
  if (!empty($_POST['_name'])) {
    $name = trim(get_input_value('_name', RCUBE_INPUT_POST));
    $created = $CONTACTS->create_group($name);
  }
  
  if ($created && $OUTPUT->ajax_call) {
    $OUTPUT->command('insert_contact_group', $created);
  }
  else if (!$create) {
    $OUTPUT->show_message('errorsaving', 'error');
  }
}

else if ($RCMAIL->action == 'group-rename') {
  if (($gid = get_input_value('_gid', RCUBE_INPUT_POST)) && ($name = trim(get_input_value('_name', RCUBE_INPUT_POST))))
    $newname = $CONTACTS->rename_group($gid, $name);

  if ($newname && $OUTPUT->ajax_call)
    $OUTPUT->command('update_contact_group', $gid, $newname);
  else if (!$newname)
    $OUTPUT->show_message('errorsaving', 'error');
}

else if ($RCMAIL->action == 'group-delete') {
  if ($gid = get_input_value('_gid', RCUBE_INPUT_POST))
    $deleted = $CONTACTS->delete_group($gid);

  if ($deleted)
    $OUTPUT->command('remove_group_item', $gid);
  else
    $OUTPUT->show_message('errorsaving', 'error');
}

// send response
$OUTPUT->send();

?>
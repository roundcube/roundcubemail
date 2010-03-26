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

if ($RCMAIL->action == 'create-group') {
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

else if ($RCMAIL->action == 'add2group') {
  if (($gid = get_input_value('_gid', RCUBE_INPUT_POST)) && ($ids = get_input_value('_cid', RCUBE_INPUT_POST)))
  if ($CONTACTS->add_to_group($gid, $ids))
    $OUTPUT->show_message('contactaddedtogroup');
  //else
  //  $OUTPUT->show_message('erroraddingcontact', 'warning');
}

else if ($RCMAIL->action == 'removefromgroup') {
  if (($gid = get_input_value('_gid', RCUBE_INPUT_POST)) && ($ids = get_input_value('_cid', RCUBE_INPUT_POST)))
  if ($CONTACTS->remove_from_group($gid, $ids))
    $OUTPUT->show_message('contactremovedfromgroup');
  //else
  //  $OUTPUT->show_message('erroraddingcontact', 'warning');
}

// send response
$OUTPUT->send();

?>
<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/show.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show contact details                                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


// read contact record
if (($cid = get_input_value('_cid', RCUBE_INPUT_GPC)) && ($record = $CONTACTS->get_record($cid, true))) {
  $OUTPUT->set_env('cid', $record['ID']);
}

$GROUPS = $CONTACTS->list_groups();
$OUTPUT->set_env('groups', !empty($GROUPS));


function rcmail_contact_details($attrib)
{
  global $CONTACTS, $OUTPUT;

  // check if we have a valid result
  if (!(($result = $CONTACTS->get_result()) && ($record = $result->first()))) {
    $OUTPUT->show_message('contactnotfound');
    return false;
  }
  
  // a specific part is requested
  if ($attrib['part']) {
    return Q($record[$attrib['part']]);
  }

  // return the complete address record as table
  $table = new html_table(array('cols' => 2));

  $a_show_cols = array('name', 'firstname', 'surname', 'email');
  $microformats = array('name' => 'fn', 'email' => 'email');

  foreach ($a_show_cols as $col) {
    if ($col == 'email' && !empty($record[$col])) {
      $value = html::a(array(
        'href' => 'mailto:' . $record[$col],
        'onclick' => sprintf("return %s.command('compose','%s',this)", JS_OBJECT_NAME, JQ($record[$col])),
        'title' => rcube_label('composeto'),
        'class' => $microformats[$col],
      ), Q($record[$col]));
    }
    else if (!empty($record[$col])) {
      $value = html::span($microformats[$col], Q($record[$col]));
    }
    else
      $value = '';
    
    $table->add('title', Q(rcube_label($col)));
    $table->add(null, $value);
  }
  
  return $table->show($attrib + array('class' => 'vcard'));
}


function rcmail_contact_record_groups($attrib)
{
  global $RCMAIL, $CONTACTS, $GROUPS;
  
  // check if we have a valid result
  if (!(($result = $CONTACTS->get_result()) && ($record = $result->first())))
    return false;
  
  $table = new html_table(array('cols' => 2, 'cellspacing' => 0, 'border' => 0));
  
  $members = $CONTACTS->get_record_groups($record['ID']);
  $checkbox = new html_checkbox(array('name' => '_gid[]', 'class' => 'groupmember', 'disabled' => $CONTACTS->readonly));
  foreach ($GROUPS as $group) {
    $gid = $group['ID'];
    $table->add(null, $checkbox->show($members[$gid] ? $gid : null, array('value' => $gid, 'id' => 'ff_gid' . $gid)));
    $table->add(null, html::label('ff_gid' . $gid, Q($group['name'])));
  }
  
  $hiddenfields = new html_hiddenfield(array('name' => '_source', 'value' => get_input_value('_source', RCUBE_INPUT_GPC)));
  $hiddenfields->add(array('name' => '_cid', 'value' => $record['ID']));
  
  $form_start = $RCMAIL->output->request_form(array('name' => "form", 'method' => "post", 'task' => $RCMAIL->task, 'action' => 'save', 'request' => 'save.'.intval($record['ID']), 'noclose' => true) + $attrib, $hiddenfields->show());
  $form_end = !strlen($attrib['form']) ? '</form>' : '';

  $RCMAIL->output->add_gui_object('editform', !empty($attrib['form']) ? $attrib['form'] : 'form');
  
  return $form_start . $table->show($attrib) . $form_end;
}


//$OUTPUT->framed = $_framed;
$OUTPUT->add_handler('contactdetails', 'rcmail_contact_details');
$OUTPUT->add_handler('contactgroups', 'rcmail_contact_record_groups');
$OUTPUT->send('showcontact');

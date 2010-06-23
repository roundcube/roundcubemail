<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/save.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Save a contact entry or to add a new one                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$cid = get_input_value('_cid', RCUBE_INPUT_POST);
$return_action = empty($cid) ? 'add' : 'show';

// cannot edit record
if ($CONTACTS->readonly)
{
  $OUTPUT->show_message('contactreadonly', 'error');
  rcmail_overwrite_action($return_action);
  return;
}

// check input
if ((!get_input_value('_name', RCUBE_INPUT_POST) || !get_input_value('_email', RCUBE_INPUT_POST)))
{
  $OUTPUT->show_message('formincomplete', 'warning');
  rcmail_overwrite_action($return_action);
  return;
}


// setup some vars we need
$a_save_cols = array('name', 'firstname', 'surname', 'email');
$a_record = array();

// read POST values into hash array
foreach ($a_save_cols as $col)
{
  $fname = '_'.$col;
  if (isset($_POST[$fname]))
    $a_record[$col] = get_input_value($fname, RCUBE_INPUT_POST);
}

// update an existing contact
if (!empty($cid))
{
  $plugin = $RCMAIL->plugins->exec_hook('save_contact', array('id' => $cid, 'record' => $a_record, 'source' => get_input_value('_source', RCUBE_INPUT_GPC)));
  $a_record = $plugin['record'];
  
  if (!$plugin['abort'] && ($result = $CONTACTS->update($cid, $a_record)))
  {
    // LDAP DN change
    if (is_string($result) && strlen($result)>1) {
      $newcid = $result;
      // change cid in POST for 'show' action
      $_POST['_cid'] = $newcid;
    }
    
    // define list of cols to be displayed
    $a_js_cols = array();
    $record = $CONTACTS->get_record($newcid ? $newcid : $cid, true);

    foreach (array('name', 'email') as $col)
      $a_js_cols[] = (string)$record[$col];

    // update the changed col in list
    $OUTPUT->command('parent.update_contact_row', $cid, $a_js_cols, $newcid);
      
    // show confirmation
    $OUTPUT->show_message('successfullysaved', 'confirmation', null, false);
    rcmail_overwrite_action('show');
  }
  else
  {
    // show error message
    $OUTPUT->show_message('errorsaving', 'error', null, false);
    rcmail_overwrite_action('show');
  }
}

// insert a new contact
else
{
  // check for existing contacts
  $existing = $CONTACTS->search('email', $a_record['email'], true, false);

  // show warning message
  if ($existing->count)
  {
    $OUTPUT->show_message('contactexists', 'warning', null, false);
    rcmail_overwrite_action('add');
    return;
  }

  $plugin = $RCMAIL->plugins->exec_hook('create_contact', array('record' => $a_record, 'source' => get_input_value('_source', RCUBE_INPUT_GPC)));
  $a_record = $plugin['record'];

  // insert record and send response
  if (!$plugin['abort'] && ($insert_id = $CONTACTS->insert($a_record)))
  {
    // add contact row or jump to the page where it should appear
    $CONTACTS->reset();
    $result = $CONTACTS->search($CONTACTS->primary_key, $insert_id);

    rcmail_js_contacts_list($result, 'parent.');
    $OUTPUT->command('parent.contact_list.select', $insert_id);

    // update record count display
    $CONTACTS->reset();
    $OUTPUT->command('parent.set_rowcount', rcmail_get_rowcount_text());

    // show confirmation
    $OUTPUT->show_message('successfullysaved', 'confirmation', null, false);
    $OUTPUT->send('iframe');
  }
  else
  {
    // show error message
    $OUTPUT->show_message('errorsaving', 'error', null, false);
    rcmail_overwrite_action('add');
  }
}



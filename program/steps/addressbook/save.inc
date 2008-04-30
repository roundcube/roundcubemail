<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/save.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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

// cannot edit record
if ($CONTACTS->readonly)
{
  $OUTPUT->show_message('contactreadonly', 'error');
  rcmail_overwrite_action(empty($_POST['_cid']) ? 'add' : 'show');
  return;
}

// check input
if ((!get_input_value('_name', RCUBE_INPUT_POST) || !get_input_value('_email', RCUBE_INPUT_POST)) && $OUTPUT->action)
{
  $OUTPUT->show_message('formincomplete', 'warning');
  rcmail_overwrite_action(empty($_POST['_cid']) ? 'add' : 'show');
  return;
}


// setup some vars we need
$a_save_cols = array('name', 'firstname', 'surname', 'email');
$a_record = array();
$cid = get_input_value('_cid', RCUBE_INPUT_POST);

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
  if ($CONTACTS->update($cid, $a_record))
  {
    if ($OUTPUT->action)
    {
      // define list of cols to be displayed
      $a_js_cols = array();
      $record = $CONTACTS->get_record($cid, true);

      foreach (array('name', 'email') as $col)
        $a_js_cols[] = (string)$record[$col];

      // update the changed col in list
      $OUTPUT->command('parent.update_contact_row', $cid, $a_js_cols);
    }
      
    // show confirmation
    $OUTPUT->show_message('successfullysaved', 'confirmation');    
    rcmail_overwrite_action('show');
  }
  else
  {
    // show error message
    $OUTPUT->show_message('errorsaving', 'error');
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
    $OUTPUT->show_message('contactexists', 'warning');
    rcmail_overwrite_action('add');
    return;
  }

  // insert record and send response
  if ($insert_id = $CONTACTS->insert($a_record))
  {
    if ($OUTPUT->action)
    {
      // add contact row or jump to the page where it should appear
      $CONTACTS->reset();
      $result = $CONTACTS->search($CONTACTS->primary_key, $insert_id);

      rcmail_js_contacts_list($result, 'parent.');
      $OUTPUT->command('parent.contact_list.select', $insert_id);

      // update record count display
      $CONTACTS->reset();
      $OUTPUT->command('parent.set_rowcount', rcmail_get_rowcount_text());
    }

    // show confirmation
    $OUTPUT->show_message('successfullysaved', 'confirmation');
    rcmail_overwrite_action('show');
    $_GET['_cid'] = $insert_id;
  }
  else
  {
    // show error message
    $OUTPUT->show_message('errorsaving', 'error');
    rcmail_overwrite_action('add');
  }
}

?>

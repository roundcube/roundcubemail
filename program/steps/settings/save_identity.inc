<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/save_identity.inc                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Save an identity record or to add a new one                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$a_save_cols = array('name', 'email', 'organization', 'reply-to', 'bcc', 'standard', 'signature', 'html_signature');
$a_html_cols = array('signature');
$a_boolean_cols = array('standard', 'html_signature');
$updated = $default_id = false;

// check input
if (empty($_POST['_name']) || empty($_POST['_email']))
  {
  $OUTPUT->show_message('formincomplete', 'warning');
  rcmail_overwrite_action('edit-identitiy');
  return;
  }


$save_data = array();
foreach ($a_save_cols as $col)
{
  $fname = '_'.$col;
  if (isset($_POST[$fname]))
    $save_data[$col] = get_input_value($fname, RCUBE_INPUT_POST, in_array($col, $a_html_cols));
}

// set "off" values for checkboxes that were not checked, and therefore
// not included in the POST body.
foreach ($a_boolean_cols as $col)
{
  $fname = '_' . $col;
  if (!isset($_POST[$fname]))
    $save_data[$col] = 0;
}


// update an existing contact
if ($_POST['_iid'])
{
  if ($updated = $USER->update_identity(get_input_value('_iid', RCUBE_INPUT_POST), $save_data))
  {
    $OUTPUT->show_message('successfullysaved', 'confirmation');
    
    if (!empty($_POST['_standard']))
      $default_id = get_input_value('_iid', RCUBE_INPUT_POST);
    
    if ($_POST['_framed'])
    {
      // update the changed col in list
      // ...      
    }
  }
  else if ($DB->is_error())
  {
    // show error message
    $OUTPUT->show_message('errorsaving', 'error');
    rcmail_overwrite_action('edit-identitiy');
    return;
  }
}

// insert a new contact
else
{
  if ($insert_id = $USER->insert_identity($save_data))
  {
    $_GET['_iid'] = $insert_id;

    if (!empty($_POST['_standard']))
      $default_id = $insert_id;

    if ($_POST['_framed'])
    {
      // add contact row or jump to the page where it should appear
      // ....
    }
  }
  else
  {
    // show error message
    $OUTPUT->show_message('errorsaving', 'error');
    rcmail_overwrite_action('edit-identity');
    return;
  }
}


// mark all other identities as 'not-default'
if ($default_id)
  $USER->set_default($default_id);

// go to next step
rcmail_overwrite_action($OUTPUT->action ? 'edit-identity' : 'identities');

?>
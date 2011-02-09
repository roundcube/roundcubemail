<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/save_identity.inc                              |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, Roundcube Dev. - Switzerland                 |
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

define('IDENTITIES_LEVEL', intval($RCMAIL->config->get('identities_level', 0)));

$a_save_cols = array('name', 'email', 'organization', 'reply-to', 'bcc', 'standard', 'signature', 'html_signature');
$a_html_cols = array('signature');
$a_boolean_cols = array('standard', 'html_signature');
$updated = $default_id = false;

// check input
if (empty($_POST['_name']) || (empty($_POST['_email']) && IDENTITIES_LEVEL != 1 && IDENTITIES_LEVEL != 3))
  {
  $OUTPUT->show_message('formincomplete', 'warning');
  rcmail_overwrite_action('edit-identity');
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

// unset email address if user has no rights to change it
if (IDENTITIES_LEVEL == 1 || IDENTITIES_LEVEL == 3)
  unset($save_data['email']);

// Validate e-mail addresses
foreach (array('email', 'reply-to', 'bcc') as $item) {
  if ($email = $save_data[$item]) {
    $ascii_email = rcube_idn_to_ascii($email);
    if (!check_email($ascii_email)) {
      // show error message
      $OUTPUT->show_message('emailformaterror', 'error', array('email' => $email), false);
      rcmail_overwrite_action('edit-identity');
      return;
    }
  }
}

// update an existing contact
if ($_POST['_iid'])
{
  $iid = get_input_value('_iid', RCUBE_INPUT_POST);
  $plugin = $RCMAIL->plugins->exec_hook('identity_update', array('id' => $iid, 'record' => $save_data));
  $save_data = $plugin['record'];

  if ($save_data['email'])
    $save_data['email'] = rcube_idn_to_ascii($save_data['email']);
  if ($save_data['bcc'])
    $save_data['bcc'] = rcube_idn_to_ascii($save_data['bcc']);
  if ($save_data['reply-to'])
    $save_data['reply-to'] = rcube_idn_to_ascii($save_data['reply-to']);

  if (!$plugin['abort'])
    $updated = $USER->update_identity($iid, $save_data);
  else
    $updated = $plugin['result'];

  if ($updated) {
    $OUTPUT->show_message('successfullysaved', 'confirmation');

    if (!empty($_POST['_standard']))
      $default_id = get_input_value('_iid', RCUBE_INPUT_POST);

    if ($_POST['_framed']) {
      // update the changed col in list
      // ...
    }
  }
  else {
    // show error message
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error', null, false);
    rcmail_overwrite_action('edit-identity');
    return;
  }
}

// insert a new identity record
else if (IDENTITIES_LEVEL < 2)
{
  if (IDENTITIES_LEVEL == 1)
    $save_data['email'] = $RCMAIL->user->get_username();

  $plugin = $RCMAIL->plugins->exec_hook('identity_create', array('record' => $save_data));
  $save_data = $plugin['record'];

  $save_data['email']    = rcube_idn_to_ascii($save_data['email']);
  $save_data['bcc']      = rcube_idn_to_ascii($save_data['bcc']);
  $save_data['reply-to'] = rcube_idn_to_ascii($save_data['reply-to']);

  if (!$plugin['abort'])
    $insert_id = $save_data['email'] ? $USER->insert_identity($save_data) : null;
  else
    $insert_id = $plugin['result'];

  if ($insert_id) {
    $OUTPUT->show_message('successfullysaved', 'confirmation', null, false);
    
    $_GET['_iid'] = $insert_id;

    if (!empty($_POST['_standard']))
      $default_id = $insert_id;
  }
  else {
    // show error message
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error', null, false);
    rcmail_overwrite_action('edit-identity');
    return;
  }
}
else
  $OUTPUT->show_message('opnotpermitted', 'error');


// mark all other identities as 'not-default'
if ($default_id)
  $USER->set_default($default_id);

// go to next step
rcmail_overwrite_action('identities');

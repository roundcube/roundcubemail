<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/save_identity.inc                              |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
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
    $save_data[$col] = get_input_value($fname, RCUBE_INPUT_POST, true);
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
$email_checks = array(rcube_idn_to_ascii($save_data['email']));
foreach (array('reply-to', 'bcc') as $item) {
  foreach (rcube_mime::decode_address_list($save_data[$item], null, false) as $rcpt)
    $email_checks[] = rcube_idn_to_ascii($rcpt['mailto']);
}

foreach ($email_checks as $email) {
  if ($email && !check_email($email)) {
    // show error message
    $OUTPUT->show_message('emailformaterror', 'error', array('email' => rcube_idn_to_utf8($email)), false);
    rcmail_overwrite_action('edit-identity');
    return;
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
  if (!$plugin['abort'])
    $updated = $RCMAIL->user->update_identity($iid, $save_data);
  else
    $updated = $plugin['result'];

  if ($updated) {
    $OUTPUT->show_message('successfullysaved', 'confirmation');

    if (!empty($_POST['_standard']))
      $default_id = get_input_value('_iid', RCUBE_INPUT_POST);

    if ($_POST['_framed']) {
      // update the changed col in list
      $OUTPUT->command('parent.update_identity_row', $iid, Q(trim($save_data['name'] . ' <' . rcube_idn_to_utf8($save_data['email']) .'>')));
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

  if ($save_data['email'])
    $save_data['email'] = rcube_idn_to_ascii($save_data['email']);

  if (!$plugin['abort'])
    $insert_id = $save_data['email'] ? $RCMAIL->user->insert_identity($save_data) : null;
  else
    $insert_id = $plugin['result'];

  if ($insert_id) {
    $OUTPUT->show_message('successfullysaved', 'confirmation', null, false);

    $_GET['_iid'] = $insert_id;

    if (!empty($_POST['_standard']))
      $default_id = $insert_id;

    if ($_POST['_framed']) {
      // add a new row to the list
      $OUTPUT->command('parent.update_identity_row', $insert_id, Q(trim($save_data['name'] . ' <' . rcube_idn_to_utf8($save_data['email']) .'>')), true);
    }
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
  $RCMAIL->user->set_default($default_id);

// go to next step
if (!empty($_REQUEST['_framed'])) {
  rcmail_overwrite_action('edit-identity');
}
else
  rcmail_overwrite_action('identities');

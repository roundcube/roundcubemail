<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/save.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Save a contact entry or to add a new one                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

$CONTACTS = rcmail_contact_source(null, true, true);
$cid      = get_input_value('_cid', RCUBE_INPUT_POST);
$return_action = empty($cid) ? 'add' : 'edit';

// Source changed, display the form again
if (!empty($_GET['_reload'])) {
   rcmail_overwrite_action($return_action);
   return;
}

// cannot edit record
if ($CONTACTS->readonly) {
  $OUTPUT->show_message('contactreadonly', 'error');
  rcmail_overwrite_action($return_action);
  return;
}

// read POST values into hash array
$a_record = array();
foreach ($GLOBALS['CONTACT_COLTYPES'] as $col => $colprop) {
  $fname = '_'.$col;
  if ($colprop['composite'])
    continue;
  // gather form data of composite fields
  if ($colprop['childs']) {
    $values = array();
    foreach ($colprop['childs'] as $childcol => $cp) {
      $vals = get_input_value('_'.$childcol, RCUBE_INPUT_POST, true);
      foreach ((array)$vals as $i => $val)
        $values[$i][$childcol] = $val;
    }
    $subtypes = isset($_REQUEST['_subtype_' . $col]) ? (array)get_input_value('_subtype_' . $col, RCUBE_INPUT_POST) : array('');
    foreach ($subtypes as $i => $subtype) {
      $suffix = $subtype ? ':'.$subtype : '';
      if ($values[$i])
        $a_record[$col.$suffix][] = $values[$i];
     }
  }
  // assign values and subtypes
  else if (is_array($_POST[$fname])) {
    $values = get_input_value($fname, RCUBE_INPUT_POST, true);
    $subtypes = get_input_value('_subtype_' . $col, RCUBE_INPUT_POST);
    foreach ($values as $i => $val) {
      $subtype = $subtypes[$i] ? ':'.$subtypes[$i] : '';
      $a_record[$col.$subtype][] = $val;
    }
  }
  else if (isset($_POST[$fname])) {
    $a_record[$col] = get_input_value($fname, RCUBE_INPUT_POST, true);
  }
}

// Generate contact's display name (must be before validation)
if (empty($a_record['name'])) {
    $a_record['name'] = rcube_addressbook::compose_display_name($a_record, true);
    // Reset it if equals to email address (from compose_display_name())
    if ($a_record['name'] == $a_record['email'][0])
        $a_record['name'] = '';
}

// do input checks (delegated to $CONTACTS instance)
if (!$CONTACTS->validate($a_record)) {
    $err = (array)$CONTACTS->get_error();
    $OUTPUT->show_message($err['message'] ? Q($err['message']) : 'formincomplete', 'warning');
    $GLOBALS['EDIT_RECORD'] = $a_record;  // store submitted data to be used in edit form
    rcmail_overwrite_action($return_action);
    return;
}

// get raw photo data if changed
if (isset($a_record['photo'])) {
    if ($a_record['photo'] == '-del-') {
        $a_record['photo'] = '';
    }
    else if ($tempfile = $_SESSION['contacts']['files'][$a_record['photo']]) {
        $tempfile = $RCMAIL->plugins->exec_hook('attachment_get', $tempfile);
        if ($tempfile['status'])
            $a_record['photo'] = $tempfile['data'] ? $tempfile['data'] : @file_get_contents($tempfile['path']);
    }
    else
        unset($a_record['photo']);

    // cleanup session data
    $RCMAIL->plugins->exec_hook('attachments_cleanup', array('group' => 'contact'));
    $RCMAIL->session->remove('contacts');
}

$source = get_input_value('_source', RCUBE_INPUT_GPC);

// update an existing contact
if (!empty($cid))
{
  $plugin = $RCMAIL->plugins->exec_hook('contact_update',
    array('id' => $cid, 'record' => $a_record, 'source' => $source));
  $a_record = $plugin['record'];

  if (!$plugin['abort'])
    $result = $CONTACTS->update($cid, $a_record);
  else
    $result = $plugin['result'];

  if ($result) {
    // LDAP DN change
    if (is_string($result) && strlen($result)>1) {
      $newcid = $result;
      // change cid in POST for 'show' action
      $_POST['_cid'] = $newcid;
    }

    // define list of cols to be displayed
    $a_js_cols = array();
    $record = $CONTACTS->get_record($newcid ? $newcid : $cid, true);
    $record['email'] = reset($CONTACTS->get_col_values('email', $record, true));
    $record['name']  = rcube_addressbook::compose_list_name($record);

    foreach (array('name', 'email') as $col)
      $a_js_cols[] = Q((string)$record[$col]);

    // update the changed col in list
    $OUTPUT->command('parent.update_contact_row', $cid, $a_js_cols, $newcid, $source);

    // show confirmation
    $OUTPUT->show_message('successfullysaved', 'confirmation', null, false);
    rcmail_overwrite_action('show');
  }
  else {
    // show error message
    $err = $CONTACTS->get_error();
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : ($err['message'] ? $err['message'] : 'errorsaving'), 'error', null, false);
    rcmail_overwrite_action('show');
  }
}

// insert a new contact
else {
  // Name of the addressbook already selected on the list
  $orig_source = get_input_value('_orig_source', RCUBE_INPUT_GPC);

  if (!strlen($source))
    $source = $orig_source;

  // show notice if existing contacts with same e-mail are found
  foreach ($CONTACTS->get_col_values('email', $a_record, true) as $email) {
      if ($email && ($res = $CONTACTS->search('email', $email, 1, false, true)) && $res->count) {
          $OUTPUT->show_message('contactexists', 'notice', null, false);
          break;
      }
  }

  $plugin = $RCMAIL->plugins->exec_hook('contact_create', array(
    'record' => $a_record, 'source' => $source));
  $a_record = $plugin['record'];

  // insert record and send response
  if (!$plugin['abort'])
    $insert_id = $CONTACTS->insert($a_record);
  else
    $insert_id = $plugin['result'];

  if ($insert_id) {
    $CONTACTS->reset();

    // add new contact to the specified group
    if ($CONTACTS->groups && $CONTACTS->group_id) {
      $plugin = $RCMAIL->plugins->exec_hook('group_addmembers', array(
        'group_id' => $CONTACTS->group_id, 'ids' => $insert_id, 'source' => $source));

      $counts = $CONTACTS->count();

      if (!$plugin['abort']) {
        if (($maxnum = $RCMAIL->config->get('max_group_members', 0)) && ($counts->count + 1 > $maxnum))
          $OUTPUT->show_message('maxgroupmembersreached', 'warning', array('max' => $maxnum));

        $CONTACTS->add_to_group($gid, $plugin['ids']);
      }
    }
    else
      $counts = $CONTACTS->count();

    if ((string)$source === (string)$orig_source) {
      // add contact row or jump to the page where it should appear
      $CONTACTS->reset();
      $result = $CONTACTS->search($CONTACTS->primary_key, $insert_id);

      rcmail_js_contacts_list($result, 'parent.');
      $OUTPUT->command('parent.contact_list.select', html_identifier($insert_id));

      // update record count display
      $CONTACTS->reset();
      $OUTPUT->command('parent.set_rowcount', rcmail_get_rowcount_text($counts));
    }
    else {
      // re-set iframe
      $OUTPUT->command('parent.show_contentframe');
    }

    // show confirmation
    $OUTPUT->show_message('successfullysaved', 'confirmation', null, false);
    $OUTPUT->send('iframe');
  }
  else {
    // show error message
    $err = $CONTACTS->get_error();
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : ($err['message'] ? $err['message'] : 'errorsaving'), 'error', null, false);
    rcmail_overwrite_action('add');
  }
}

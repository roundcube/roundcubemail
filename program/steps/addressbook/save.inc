<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/save.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
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
$return_action = empty($cid) ? 'add' : 'edit';

// cannot edit record
if ($CONTACTS->readonly) {
  $OUTPUT->show_message('contactreadonly', 'error');
  rcmail_overwrite_action($return_action);
  return;
}


// handle photo upload for contacts
if ($RCMAIL->action == 'upload-photo') {
    // clear all stored output properties (like scripts and env vars)
    $OUTPUT->reset();

    if ($filepath = $_FILES['_photo']['tmp_name']) {
        // check file type and resize image
        $imageprop = rcmail::imageprops($_FILES['_photo']['tmp_name']);
        
        if ($imageprop['width'] && $imageprop['height']) {
            $maxsize = intval($RCMAIL->config->get('contact_photo_size', 160));
            $tmpfname = tempnam($RCMAIL->config->get('temp_dir'), 'rcmImgConvert');
            $save_hook = 'attachment_upload';
            
            // scale image to a maximum size
            if (($imageprop['width'] > $maxsize || $imageprop['height'] > $maxsize) &&
                  (rcmail::imageconvert(array('in' => $filepath, 'out' => $tmpfname, 'size' => $maxsize.'x'.$maxsize, 'type' => $imageprop['type'])) !== false)) {
                $filepath = $tmpfname;
                $save_hook = 'attachment_save';
            }
            
            // save uploaded file in storage backend
            $attachment = $RCMAIL->plugins->exec_hook($save_hook, array(
                'path' => $filepath,
                'size' => $_FILES['_photo']['size'],
                'name' => $_FILES['_photo']['name'],
                'mimetype' => 'image/' . $imageprop['type'],
            ));
        }
        else
            $attachment['error'] = rcube_label('invalidimageformat');

        if ($attachment['status'] && !$attachment['abort']) {
            $file_id = $attachment['id'];
            $_SESSION['contacts']['files'][$file_id] = $attachment;
            $OUTPUT->command('replace_contact_photo', $file_id);
        }
        else {  // upload failed
            $err = $_FILES['_photo']['error'];
            if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE)
                $msg = rcube_label(array('name' => 'filesizeerror', 'vars' => array('size' => show_bytes(parse_bytes(ini_get('upload_max_filesize'))))));
            else if ($attachment['error'])
                $msg = $attachment['error'];
            else
                $msg = rcube_label('fileuploaderror');
            
            $OUTPUT->command('display_message', $msg, 'error');
        }
    }
    else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // if filesize exceeds post_max_size then $_FILES array is empty,
        // show filesizeerror instead of fileuploaderror
        if ($maxsize = ini_get('post_max_size'))
            $msg = rcube_label(array('name' => 'filesizeerror', 'vars' => array('size' => show_bytes(parse_bytes($maxsize)))));
        else
            $msg = rcube_label('fileuploaderror');
            
        $OUTPUT->command('display_message', $msg, 'error');
    }
    
    $OUTPUT->command('photo_upload_end');
    $OUTPUT->send('iframe');
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
      $vals = get_input_value('_'.$childcol, RCUBE_INPUT_POST);
      foreach ((array)$vals as $i => $val)
        $values[$i][$childcol] = $val;
    }
    $subtypes = get_input_value('_subtype_' . $col, RCUBE_INPUT_POST);
    foreach ($subtypes as $i => $subtype)
      if ($values[$i])
        $a_record[$col.':'.$subtype][] = $values[$i];
  }
  // assign values and subtypes
  else if (is_array($_POST[$fname])) {
    $values = get_input_value($fname, RCUBE_INPUT_POST);
    $subtypes = get_input_value('_subtype_' . $col, RCUBE_INPUT_POST);
    foreach ($values as $i => $val) {
      $subtype = $subtypes[$i] ? ':'.$subtypes[$i] : '';
      $a_record[$col.$subtype][] = $val;
    }
  }
  else if (isset($_POST[$fname])) {
    $a_record[$col] = get_input_value($fname, RCUBE_INPUT_POST);
  }
}

if (empty($a_record['name']))
  $a_record['name'] = join(' ', array_filter(array($a_record['prefix'], $a_record['firstname'], $a_record['middlename'], $a_record['surname'], $a_record['suffix'],)));

#var_dump($a_record);

// Basic input checks (TODO: delegate to $CONTACTS instance)
if (empty($a_record['name'])/* || empty($a_record['email'])*/) {
  $OUTPUT->show_message('formincomplete', 'warning');
  rcmail_overwrite_action($return_action);
  return;
}

// Validity checks
foreach ($CONTACTS->get_col_values('email', $a_record, true) as $email) {
  if (strlen($email)) {
    $_email = idn_to_ascii($email);
    if (!check_email($_email, false)) {
      $OUTPUT->show_message('emailformaterror', 'warning', array('email' => $email));
      rcmail_overwrite_action($return_action);
      return;
    }
  }
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
    $RCMAIL->plugins->exec_hook('attachments_cleanup', array());
    $RCMAIL->session->remove('contacts');
}

// update an existing contact
if (!empty($cid))
{
  $plugin = $RCMAIL->plugins->exec_hook('contact_update',
    array('id' => $cid, 'record' => $a_record, 'source' => get_input_value('_source', RCUBE_INPUT_GPC)));
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

    foreach (array('name', 'email') as $col)
      $a_js_cols[] = (string)$record[$col];

    // update the changed col in list
    $OUTPUT->command('parent.update_contact_row', $cid, $a_js_cols, $newcid);

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
  // check for existing contacts
  $existing = false;
  foreach ($CONTACTS->get_col_values('email', $a_record, true) as $email) {
      if (($res = $CONTACTS->search('email', $email, true, false)) && $res->count) {
          $existing = true;
          break;
      }
  }

  // show warning message
  if ($existing) {
    $OUTPUT->show_message('contactexists', 'warning', null, false);
    rcmail_overwrite_action('add');
    return;
  }

  $plugin = $RCMAIL->plugins->exec_hook('contact_create', array(
    'record' => $a_record, 'source' => get_input_value('_source', RCUBE_INPUT_GPC)));
  $a_record = $plugin['record'];

  // insert record and send response
  if (!$plugin['abort'])
    $insert_id = $CONTACTS->insert($a_record);
  else
    $insert_id = $plugin['result'];


  if ($insert_id) {
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
  else {
    // show error message
    $err = $CONTACTS->get_error();
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : ($err['message'] ? $err['message'] : 'errorsaving'), 'error', null, false);
    rcmail_overwrite_action('add');
  }
}

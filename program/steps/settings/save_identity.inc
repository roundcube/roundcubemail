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


// update an existing contact
if ($_POST['_iid'])
  {
  $a_write_sql = array();

  foreach ($a_save_cols as $col)
    {
    $fname = '_'.$col;
    if (isset($_POST[$fname]))
      $a_write_sql[] = sprintf("%s=%s",
        $DB->quoteIdentifier($col),
        $DB->quote(get_input_value($fname, RCUBE_INPUT_POST, in_array($col, $a_html_cols))));
    }

  // set "off" values for checkboxes that were not checked, and therefore
  // not included in the POST body.
  foreach ($a_boolean_cols as $col)
    {
    $fname = '_' . $col;
    if (!isset($_POST[$fname]))
      $a_write_sql[] = sprintf("%s=0", $DB->quoteIdentifier($col));
    }

  if (sizeof($a_write_sql))
    {
    $DB->query(
      "UPDATE ".get_table_name('identities')."
       SET ".join(', ', $a_write_sql)."
       WHERE  identity_id=?
       AND    user_id=?
       AND    del<>1",
      get_input_value('_iid', RCUBE_INPUT_POST),
      $_SESSION['user_id']);
                       
    $updated = $DB->affected_rows();
    }
       
  if ($updated)
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
  $a_insert_cols = $a_insert_values = array();

  foreach ($a_save_cols as $col)
    {
    $fname = '_'.$col;
    if (!isset($_POST[$fname]))
      continue;
    
    $a_insert_cols[] = $DB->quoteIdentifier($col);
    $a_insert_values[] = $DB->quote(get_input_value($fname, RCUBE_INPUT_POST, in_array($col, $a_html_cols)));
    }
    
  if (sizeof($a_insert_cols))
    {
    $DB->query("INSERT INTO ".get_table_name('identities')."
                (user_id, ".join(', ', $a_insert_cols).")
                VALUES (?, ".join(', ', $a_insert_values).")",
                $_SESSION['user_id']);

    $insert_id = $DB->insert_id(get_sequence_name('identities'));
    }
    
  if ($insert_id)
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
  $DB->query(
    "UPDATE ".get_table_name('identities')."
     SET ".$DB->quoteIdentifier('standard')."='0'
     WHERE  user_id=?
     AND    identity_id<>?
     AND    del<>1",
    $_SESSION['user_id'],
    $default_id);

// go to next step
rcmail_overwrite_action($_framed ? 'edit-identity' : 'identities');

?>
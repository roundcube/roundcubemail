<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/save.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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

// check input
if ((empty($_POST['_name']) || empty($_POST['_email'])) && empty($_GET['_framed']))
  {
  show_message('formincomplete', 'warning');
  rcmail_overwrite_action(empty($_POST['_cid']) ? 'add' : 'show');
  return;
  }

// setup some vars we need
$a_save_cols = array('name', 'firstname', 'surname', 'email');
$contacts_table = get_table_name('contacts');

// update an existing contact
if (!empty($_POST['_cid']))
  {
  $a_write_sql = array();

  foreach ($a_save_cols as $col)
    {
    $fname = '_'.$col;
    if (!isset($_POST[$fname]))
      continue;
    
    $a_write_sql[] = sprintf("%s=%s",
                             $DB->quoteIdentifier($col),
                             $DB->quote(get_input_value($fname, RCUBE_INPUT_POST)));
    }

  if (sizeof($a_write_sql))
    {
    $DB->query("UPDATE $contacts_table
                SET    changed=".$DB->now().", ".join(', ', $a_write_sql)."
                WHERE  contact_id=?
                AND    user_id=?
                AND    del<>1",
                $_POST['_cid'],
                $_SESSION['user_id']);
                       
    $updated = $DB->affected_rows();
    }
       
  if ($updated)
    {
    $_action = 'show';
    show_message('successfullysaved', 'confirmation');    
    
    if ($_framed)
      {
      // define list of cols to be displayed
      $a_show_cols = array('name', 'email');
      $a_js_cols = array();
  
      $sql_result = $DB->query("SELECT * FROM $contacts_table
                                WHERE  contact_id=?
                                AND    user_id=?
                                AND    del<>1",
                               $_POST['_cid'],
                               $_SESSION['user_id']);
                         
      $sql_arr = $DB->fetch_assoc($sql_result);
      foreach ($a_show_cols as $col)
        $a_js_cols[] = (string)$sql_arr[$col];

      // update the changed col in list
      $OUTPUT->add_script(sprintf("if(parent.%s)parent.%s.update_contact_row('%d', %s);",
                          $JS_OBJECT_NAME,
                          $JS_OBJECT_NAME,
                          $_POST['_cid'],
                          array2js($a_js_cols)));

      // show confirmation
      show_message('successfullysaved', 'confirmation');
      }
    }
  else
    {
    // show error message
    show_message('errorsaving', 'error');
    rcmail_overwrite_action('show');
    }
  }

// insert a new contact
else
  {
  $a_insert_cols = $a_insert_values = array();

  // check for existing contacts
  $sql = "SELECT 1 FROM $contacts_table
          WHERE  user_id = {$_SESSION['user_id']}
          AND del <> '1' ";

  // get email and name, build sql for existing user check
  if (isset($_GET['_emails']) && isset($_GET['_names']))
    {
    $sql   .= "AND email IN (";
    $emails = explode(',', get_input_value('_emails', RCUBE_INPUT_GET));
    $names  = explode(',', get_input_value('_names', RCUBE_INPUT_GET));
    $count  = count($emails);
    $n = 0;
    foreach ($emails as $email)
      {
      $end  = (++$n == $count) ? '' : ',';
      $sql .= $DB->quote($email) . $end;
      }
    $sql .= ")";
    $ldap_form = true; 
    }
  else if (isset($_POST['_email'])) 
    $sql  .= "AND email = " . $DB->quote(get_input_value('_email', RCUBE_INPUT_POST));

  $sql_result = $DB->query($sql);

  // show warning message
  if ($DB->num_rows($sql_result))
    {
    show_message('contactexists', 'warning');

    if ($ldap_form)
      rcmail_overwrite_action('ldappublicsearch');
    else
      rcmail_overwrite_action('add');

    return;
    }

  if ($ldap_form)
    {
    $n = 0; 
    foreach ($emails as $email) 
      {
      $DB->query("INSERT INTO $contacts_table 
                 (user_id, name, email)
                 VALUES ({$_SESSION['user_id']}," . $DB->quote($names[$n++]) . "," . 
                                      $DB->quote($email) . ")");
      $insert_id[] = $DB->insert_id();
      }
    }
  else
    {
    foreach ($a_save_cols as $col)
      {
      $fname = '_'.$col;
      if (!isset($_POST[$fname]))
        continue;
    
      $a_insert_cols[] = $col;
      $a_insert_values[] = $DB->quote(get_input_value($fname, RCUBE_INPUT_POST));
      }
    
    if (sizeof($a_insert_cols))
      {
      $DB->query("INSERT INTO $contacts_table
                  (user_id, changed, del, ".join(', ', $a_insert_cols).")
                  VALUES (?, ".$DB->now().", 0, ".join(', ', $a_insert_values).")",
                $_SESSION['user_id']);
                       
      $insert_id = $DB->insert_id(get_sequence_name('contacts'));
      }
    }
    
  if ($insert_id)
    {
    if (!$ldap_form)
      {
      $_action = 'show';
      $_GET['_cid'] = $insert_id;

      if ($_framed)
        {
        // add contact row or jump to the page where it should appear
        $commands = sprintf("if(parent.%s)parent.", $JS_OBJECT_NAME);
        $sql_result = $DB->query("SELECT * FROM $contacts_table
                                  WHERE  contact_id=?
                                  AND    user_id=?",
                                  $insert_id,
                                  $_SESSION['user_id']);
        $commands .= rcmail_js_contacts_list($sql_result, $JS_OBJECT_NAME);

        $commands .= sprintf("if(parent.%s)parent.%s.select('%d');\n",
                             $JS_OBJECT_NAME, 
                             $JS_OBJECT_NAME,
                             $insert_id);
      
        // update record count display
        $commands .= sprintf("if(parent.%s)parent.%s.set_rowcount('%s');\n",
                             $JS_OBJECT_NAME, 
                             $JS_OBJECT_NAME,
                             rcmail_get_rowcount_text());

        $OUTPUT->add_script($commands);
        }

      // show confirmation
      show_message('successfullysaved', 'confirmation');      
      }
    else 
      {
      // add contact row or jump to the page where it should appear
      $commands = '';
      foreach ($insert_id as $id) 
        {
        $sql_result = $DB->query("SELECT * FROM $contacts_table
                                  WHERE  contact_id = $id
                                  AND    user_id    = {$_SESSION['user_id']}");
        
        $commands .= sprintf("if(parent.%s)parent.", $JS_OBJECT_NAME);
        $commands .= rcmail_js_contacts_list($sql_result, $JS_OBJECT_NAME);
        $last_id = $id;
        }

      // display the last insert id
      $commands .= sprintf("if(parent.%s)parent.%s.select('%d');\n",
                            $JS_OBJECT_NAME, 
                            $JS_OBJECT_NAME,
                            $last_id);

      // update record count display
      $commands .= sprintf("if(parent.%s)parent.%s.set_rowcount('%s');\n",
                           $JS_OBJECT_NAME, 
                           $JS_OBJECT_NAME,
                           rcmail_get_rowcount_text());

      $OUTPUT->add_script($commands);
      rcmail_overwrite_action('ldappublicsearch');
      }

    // show confirmation
    show_message('successfullysaved', 'confirmation');      
    }
  else
    {
    // show error message
    show_message('errorsaving', 'error');
    rcmail_overwrite_action('add');
    }
  }

?>

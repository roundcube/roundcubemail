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


$a_save_cols = array('name', 'firstname', 'surname', 'email');


// update an existing contact
if ($_POST['_cid'])
  {
  $a_write_sql = array();

  foreach ($a_save_cols as $col)
    {
    $fname = '_'.$col;
    if (!isset($_POST[$fname]))
      continue;
    
    $a_write_sql[] = sprintf("%s='%s'", $col, addslashes($_POST[$fname]));
    }

  if (sizeof($a_write_sql))
    {
    $DB->query(sprintf("UPDATE %s
                        SET    %s
                        WHERE  contact_id=%d
                        AND    user_id=%d
                        AND    del!='1'",
                       get_table_name('contacts'),
                       join(', ', $a_write_sql),
                       $_POST['_cid'],
                       $_SESSION['user_id']));
                       
    $updated = $DB->affected_rows();
    }
       
  if ($updated)
    {
    $_action = 'show';
    show_message('successfullysaved', 'confirmation');    
    
    if ($_POST['_framed'])
      {
      // define list of cols to be displayed
      $a_show_cols = array('name', 'email');
      $a_js_cols = array();
  
      $sql_result = $DB->query(sprintf("SELECT * FROM %s
                                        WHERE  contact_id=%d
                                        AND    user_id=%d
                                        AND    del!='1'",
                               get_table_name('contacts'),
                               $_POST['_cid'],
                               $_SESSION['user_id']));
                         
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
    $_action = 'show';
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
    
    $a_insert_cols[] = $col;
    $a_insert_values[] = sprintf("'%s'", addslashes($_POST[$fname]));
    }
    
  if (sizeof($a_insert_cols))
    {
    $DB->query(sprintf("INSERT INTO %s
                        (user_id, %s)
                        VALUES (%d, %s)",
                       get_table_name('contacts'),
                       join(', ', $a_insert_cols),
                       $_SESSION['user_id'],
                       join(', ', $a_insert_values)));
                       
    $insert_id = $DB->insert_id();
    }
    
  if ($insert_id)
    {
    $_action = 'show';
    $_GET['_cid'] = $insert_id;

    if ($_POST['_framed'])
      {
      // add contact row or jump to the page where it should appear
      $commands = sprintf("if(parent.%s)parent.", $JS_OBJECT_NAME);
      $sql_result = $DB->query(sprintf("SELECT * FROM %s
                                        WHERE  contact_id=%d
                                        AND    user_id=%d",
                                       get_table_name('contacts'),
                                       $insert_id,
                                       $_SESSION['user_id']));
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
      
      // show confirmation
      show_message('successfullysaved', 'confirmation');      
      }
    }
  else
    {
    // show error message
    show_message('errorsaving', 'error');
    $_action = 'add';
    }
  }


?>
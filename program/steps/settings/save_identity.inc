<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/save_identity.inc                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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

$a_save_cols = array('name', 'email', 'organization', 'reply-to', 'bcc', 'default');


// update an existing contact
if ($_POST['_iid'])
  {
  $a_write_sql = array();

  foreach ($a_save_cols as $col)
    {
    $fname = '_'.$col;
    if (!isset($_POST[$fname]))
      continue;

    $a_write_sql[] = sprintf("`%s`='%s'", $col, addslashes(strip_tags($_POST[$fname])));
    }

  if (sizeof($a_write_sql))
    {
    $DB->query("UPDATE ".get_table_name('identities')."
                SET ".join(', ', $a_write_sql)."
                WHERE  identity_id=?
                AND    user_id=?
                AND    del<>'1'",
                $_POST['_iid'],
                $_SESSION['user_id']);
                       
    $updated = $DB->affected_rows();
    }
       
  if ($updated)
    {
    show_message('successfullysaved', 'confirmation');

    // mark all other identities as 'not-default'
    $DB->query("UPDATE ".get_table_name('identities')."
                SET ".$DB->quoteIdentifier('default')."='0'
                WHERE  identity_id!=?
                AND    user_id=?
                AND    del<>'1'",
                $_POST['_iid'],
                $_SESSION['user_id']);
    
    if ($_POST['_framed'])
      {
      // update the changed col in list
      // ...      
      }
    }
  else
    {
    // show error message

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
    $a_insert_values[] = sprintf("'%s'", addslashes(strip_tags($_POST[$fname])));
    }
    
  if (sizeof($a_insert_cols))
    {
    $DB->query("INSERT INTO ".get_table_name('identities')."
                (user_id, ".join(', ', $a_insert_cols).")
                VALUES (?, ".join(', ', $a_insert_values).")",
                $_SESSION['user_id']);
                       
    $insert_id = $DB->insert_id();
    }
    
  if ($insert_id)
    {
    $_GET['_iid'] = $insert_id;

    if ($_POST['_framed'])
      {
      // add contact row or jump to the page where it should appear
      // ....
      }
    }
  else
    {
    // show error message
    }
  }


// go to next step
if ($_POST['_framed'])
  $_action = 'edit-identitiy';
else
  $_action = 'identities';
  

// overwrite action variable  
$OUTPUT->add_script(sprintf("\n%s.set_env('action', '%s');", $JS_OBJECT_NAME, $_action));  

?>
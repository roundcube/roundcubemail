<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/edit.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show edit form for a contact entry or to add a new one              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


if (($_GET['_cid'] || $_POST['_cid']) && $_action=='edit')
  {
  $cid = $_POST['_cid'] ? $_POST['_cid'] : $_GET['_cid'];
  $DB->query(sprintf("SELECT * FROM %s
                      WHERE  contact_id=%d
                      AND    user_id=%d
                      AND    del!='1'",
                     get_table_name('contacts'),
                     $cid,
                     $_SESSION['user_id']));
  
  $CONTACT_RECORD = $DB->fetch_assoc();
  
  if (is_array($CONTACT_RECORD))
    $OUTPUT->add_script(sprintf("%s.set_env('cid', '%s');", $JS_OBJECT_NAME, $CONTACT_RECORD['contact_id']));
  }



function rcmail_contact_editform($attrib)
  {
  global $CONTACT_RECORD, $JS_OBJECT_NAME;

  if (!$CONTACT_RECORD && $GLOBALS['_action']!='add')
    return rcube_label('contactnotfound');

  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  

  // a specific part is requested
  if ($attrib['part'])
    {
    $out = $form_start;
    $out .= rcmail_get_edit_field($attrib['part'], $CONTACT_RECORD[$attrib['part']], $attrib); 
    return $out;
    }


  // return the complete address edit form as table
  $out = "$form_start<table>\n\n";

  $a_show_cols = array('name', 'firstname', 'surname', 'email');
  foreach ($a_show_cols as $col)
    {
    $attrib['id'] = 'rcmfd_'.$col;
    $title = rcube_label($col);
    $value = rcmail_get_edit_field($col, $CONTACT_RECORD[$col], $attrib);
    $out .= sprintf("<tr><td class=\"title\"><label for=\"%s\">%s</label></td><td>%s</td></tr>\n",
                    $attrib['id'],
                    $title,
                    $value);
    }

  $out .= "\n</table>$form_end";

  return $out;  
  }


// similar function as in /steps/settings/edit_identity.inc
function get_form_tags($attrib)
  {
  global $CONTACT_RECORD, $OUTPUT, $JS_OBJECT_NAME, $EDIT_FORM, $SESS_HIDDEN_FIELD;  

  $form_start = '';
  if (!strlen($EDIT_FORM))
    {
    $hiddenfields = new hiddenfield(array('name' => '_task', 'value' => $GLOBALS['_task']));
    $hiddenfields->add(array('name' => '_action', 'value' => 'save'));
    
    if ($_GET['_framed'] || $_POST['_framed'])
      $hiddenfields->add(array('name' => '_framed', 'value' => 1));
    
    if ($CONTACT_RECORD['contact_id'])
      $hiddenfields->add(array('name' => '_cid', 'value' => $CONTACT_RECORD['contact_id']));
    
    $form_start = !strlen($attrib['form']) ? '<form name="form" action="./" method="post">' : '';
    $form_start .= "\n$SESS_HIDDEN_FIELD\n";
    $form_start .= $hiddenfields->show();
    }
    
  $form_end = (strlen($EDIT_FORM) && !strlen($attrib['form'])) ? '</form>' : '';
  $form_name = strlen($attrib['form']) ? $attrib['form'] : 'form';
  
  if (!strlen($EDIT_FORM))
    $OUTPUT->add_script("$JS_OBJECT_NAME.gui_object('editform', '$form_name');");
  
  $EDIT_FORM = $form_name;

  return array($form_start, $form_end);  
  }



if (!$CONTACT_RECORD && template_exists('addcontact'))
  parse_template('addcontact');

// this will be executed if no template for addcontact exists
parse_template('editcontact');
?>
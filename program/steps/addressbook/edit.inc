<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/edit.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show edit form for a contact entry or to add a new one              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


if (($cid = get_input_value('_cid', RCUBE_INPUT_GPC)) && ($record = $CONTACTS->get_record($cid, true)))
  $OUTPUT->set_env('cid', $record['ID']);

// adding not allowed here
if ($CONTACTS->readonly)
{
  $OUTPUT->show_message('sourceisreadonly');
  rcmail_overwrite_action('show');
  return;
}

function rcmail_contact_editform($attrib)
{
  global $CONTACTS, $OUTPUT;

  // check if we have a valid result
  if ($GLOBALS['_action'] != 'add' && !(($result = $CONTACTS->get_result()) && ($record = $result->first())))
  {
    $OUTPUT->show_message('contactnotfound');
    return false;
  }

  // add some labels to client
  rcube_add_label('noemailwarning');
  rcube_add_label('nonamewarning');

  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);

  // a specific part is requested
  if ($attrib['part'])
  {
    $out = $form_start;
    $out .= rcmail_get_edit_field($attrib['part'], $record[$attrib['part']], $attrib); 
    return $out;
  }


  // return the complete address edit form as table
  $out = "$form_start<table>\n\n";

  $a_show_cols = array('name', 'firstname', 'surname', 'email');
  foreach ($a_show_cols as $col)
  {
    $attrib['id'] = 'rcmfd_'.$col;
    $value = rcmail_get_edit_field($col, $record[$col], $attrib);
    $out .= sprintf("<tr><td class=\"title\"><label for=\"%s\">%s</label></td><td>%s</td></tr>\n",
                    $attrib['id'],
                    Q(rcube_label($col)),
                    $value);
  }

  $out .= "\n</table>$form_end";

  return $out;  
}

$OUTPUT->add_handler('contacteditform', 'rcmail_contact_editform');


// similar function as in /steps/settings/edit_identity.inc
function get_form_tags($attrib)
  {
  global $CONTACTS, $OUTPUT, $EDIT_FORM, $SESS_HIDDEN_FIELD;  

  $result = $CONTACTS->get_result();
  $form_start = '';
  if (!strlen($EDIT_FORM))
    {
    $hiddenfields = new html_hiddenfield(array('name' => '_task', 'value' => $GLOBALS['_task']));
    $hiddenfields->add(array('name' => '_action', 'value' => 'save', 'source' => get_input_value('_source', RCUBE_INPUT_GPC)));
    
    if (($result = $CONTACTS->get_result()) && ($record = $result->first()))
      $hiddenfields->add(array('name' => '_cid', 'value' => $record['ID']));
    
    $form_start = !strlen($attrib['form']) ? '<form name="form" action="./" method="post">' : '';
    $form_start .= "\n$SESS_HIDDEN_FIELD\n";
    $form_start .= $hiddenfields->show();
    }
    
  $form_end = (strlen($EDIT_FORM) && !strlen($attrib['form'])) ? '</form>' : '';
  $form_name = strlen($attrib['form']) ? $attrib['form'] : 'form';
  
  if (!strlen($EDIT_FORM))
    $OUTPUT->add_gui_object('editform', $form_name);
  
  $EDIT_FORM = $form_name;

  return array($form_start, $form_end);  
  }



if (!$CONTACTS->get_result() && template_exists('addcontact'))
  $OUTPUT->send('addcontact');

// this will be executed if no template for addcontact exists
$OUTPUT->send('editcontact');
?>
<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/edit.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, Roundcube Dev. - Switzerland                 |
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
  global $RCMAIL, $CONTACTS, $OUTPUT;

  // check if we have a valid result
  if ($RCMAIL->action != 'add' && !(($result = $CONTACTS->get_result()) && ($record = $result->first())))
  {
    $OUTPUT->show_message('contactnotfound');
    return false;
  }

  // add some labels to client
  $OUTPUT->add_label('noemailwarning', 'nonamewarning');

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
  global $CONTACTS, $EDIT_FORM, $RCMAIL;

  $form_start = $form_end = '';
  
  if (empty($EDIT_FORM)) {
    $hiddenfields = new html_hiddenfield(array('name' => '_source', 'value' => get_input_value('_source', RCUBE_INPUT_GPC)));
    $hiddenfields->add(array('name' => '_gid', 'value' => $CONTACTS->group_id));
    
    if (($result = $CONTACTS->get_result()) && ($record = $result->first()))
      $hiddenfields->add(array('name' => '_cid', 'value' => $record['ID']));
    
    $form_start = $RCMAIL->output->request_form(array('name' => "form", 'method' => "post", 'task' => $RCMAIL->task, 'action' => 'save', 'request' => 'save.'.intval($record['ID']), 'noclose' => true) + $attrib, $hiddenfields->show());
    $form_end = !strlen($attrib['form']) ? '</form>' : '';

    $EDIT_FORM = !empty($attrib['form']) ? $attrib['form'] : 'form';
    $RCMAIL->output->add_gui_object('editform', $EDIT_FORM);
  }

  return array($form_start, $form_end); 
}



if (!$CONTACTS->get_result() && $OUTPUT->template_exists('addcontact'))
  $OUTPUT->send('addcontact');

// this will be executed if no template for addcontact exists
$OUTPUT->send('editcontact');


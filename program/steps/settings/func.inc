<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/func.inc                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality for user's settings & preferences             |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

if (!$OUTPUT->ajax_call)
  $OUTPUT->set_pagetitle(rcube_label('preferences'));

// define sections list
$SECTIONS['general'] = array('id' => 'general', 'section' => rcube_label('uisettings'));
$SECTIONS['mailbox'] = array('id' => 'mailbox', 'section' => rcube_label('mailboxview'));
$SECTIONS['compose'] = array('id' => 'compose', 'section' => rcube_label('messagescomposition'));
$SECTIONS['mailview'] = array('id' => 'mailview','section' => rcube_label('messagesdisplaying'));
$SECTIONS['folders'] = array('id' => 'folders', 'section' => rcube_label('specialfolders'));
$SECTIONS['server'] = array('id' => 'server',  'section' => rcube_label('serversettings'));


// similar function as /steps/settings/identities.inc::rcmail_identity_frame()
function rcmail_preferences_frame($attrib)
  {
  global $OUTPUT;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmprefsframe';

  $attrib['name'] = $attrib['id'];

  $OUTPUT->set_env('contentframe', $attrib['name']);
  $OUTPUT->set_env('blankpage', $attrib['src'] ? $OUTPUT->abs_url($attrib['src']) : 'program/blank.gif');
  
  return html::iframe($attrib);
  }


function rcmail_sections_list($attrib)
  {
  global $RCMAIL, $SECTIONS;
  
  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmsectionslist';

  // hook + define list cols
  $plugin = $RCMAIL->plugins->exec_hook('list_prefs_sections',
    array('list' => $SECTIONS, 'cols' => array('section')));
	    
  // create XHTML table
  $out = rcube_table_output($attrib, $plugin['list'], $plugin['cols'], 'id');

  // set client env
  $RCMAIL->output->add_gui_object('sectionslist', $attrib['id']);
  $RCMAIL->output->include_script('list.js');

  return $out;
  }


function rcmail_identities_list($attrib)
  {
  global $OUTPUT, $USER, $RCMAIL;

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmIdentitiesList';

  // get all identites from DB and define list of cols to be displayed
  $plugin = $RCMAIL->plugins->exec_hook('list_identities', array(
    'list' => $USER->list_identities(),
    'cols' => array('name', 'email')));

  // create XHTML table  
  $out = rcube_table_output($attrib, $plugin['list'], $plugin['cols'], 'identity_id');
  
  // set client env
  $OUTPUT->add_gui_object('identitieslist', $attrib['id']);

  return $out;
  }


// similar function as in /steps/addressbook/edit.inc
function get_form_tags($attrib, $action, $id = null, $hidden = null)
  {
  global $EDIT_FORM, $RCMAIL;

  $form_start = $form_end = '';
  
  if (empty($EDIT_FORM)) {
    $request_key = $action . (isset($id) ? '.'.$id : '');
    $form_start = $RCMAIL->output->request_form(array(
	'name' => 'form', 'method' => 'post',
	'task' => $RCMAIL->task, 'action' => $action,
	'request' => $request_key, 'noclose' => true) + $attrib);
    
    if (is_array($hidden)) {
      $hiddenfields = new html_hiddenfield($hidden);
      $form_start .= $hiddenfields->show();
    }
    
    $form_end = !strlen($attrib['form']) ? '</form>' : '';

    $EDIT_FORM = !empty($attrib['form']) ? $attrib['form'] : 'form';
    $RCMAIL->output->add_gui_object('editform', $EDIT_FORM);
  }

  return array($form_start, $form_end);
  }


// register UI objects
$OUTPUT->add_handlers(array(
  'prefsframe' => 'rcmail_preferences_frame',
  'sectionslist' => 'rcmail_sections_list',
  'identitieslist' => 'rcmail_identities_list',
));

?>

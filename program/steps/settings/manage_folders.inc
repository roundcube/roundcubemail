<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/manage_folders.inc                             |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality to create/delete/rename folders               |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// init IMAP connection
$RCMAIL->imap_init(true);

// subscribe to one or more mailboxes
if ($RCMAIL->action=='subscribe')
  {
  if ($mbox = get_input_value('_mbox', RCUBE_INPUT_POST))
    $IMAP->subscribe(array($mbox));

  if ($OUTPUT->ajax_call)
    $OUTPUT->remote_response('// subscribed');
  }

// unsubscribe one or more mailboxes
else if ($RCMAIL->action=='unsubscribe')
  {
  if ($mbox = get_input_value('_mbox', RCUBE_INPUT_POST))
    $IMAP->unsubscribe(array($mbox));

  if ($OUTPUT->ajax_call)
    $OUTPUT->remote_response('// unsubscribed');
  }

// create a new mailbox
else if ($RCMAIL->action=='create-folder')
  {
  if (!empty($_POST['_name']))
    $create = $IMAP->create_mailbox(trim(get_input_value('_name', RCUBE_INPUT_POST, FALSE, 'UTF-7')), TRUE);

  if ($create && $OUTPUT->ajax_call)
    {
    $delimiter = $IMAP->get_hierarchy_delimiter();
    $foldersplit = explode($delimiter, $create);
    $display_create = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', substr_count($create, $delimiter)) . rcube_charset_convert($foldersplit[count($foldersplit)-1], 'UTF-7');
    $OUTPUT->command('add_folder_row', $create, $display_create);
    }
  else if (!$create)
    {
    $OUTPUT->show_message('errorsaving', 'error');
    }
  }

// rename a mailbox
else if ($RCMAIL->action=='rename-folder')
  {
  if (!empty($_POST['_folder_oldname']) && !empty($_POST['_folder_newname']))
    $rename = $IMAP->rename_mailbox(($oldname = get_input_value('_folder_oldname', RCUBE_INPUT_POST)), trim(get_input_value('_folder_newname', RCUBE_INPUT_POST, FALSE, 'UTF-7')));
    
  if ($rename && $OUTPUT->ajax_call)
    {
    $a_mboxes = array_unique(array_merge($IMAP->list_mailboxes(), $IMAP->list_unsubscribed()));
    $delimiter = $IMAP->get_hierarchy_delimiter();
    $foldersplit = explode($delimiter, $rename);
    $level = count($foldersplit) - 1;
    $display_rename = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . rcube_charset_convert($foldersplit[$level], 'UTF-7');
    $OUTPUT->command('replace_folder_row', $oldname, $rename, $display_rename);

    foreach ($a_mboxes as $mbox)
      if (preg_match('/^'.preg_quote($oldname . $delimiter, '/').'/', $mbox))
        {
        $c_rename = preg_replace('/^'.preg_quote($oldname, '/').'/', $rename, $mbox);
        $foldersplit = explode($delimiter, $c_rename);
        $level = count($foldersplit) - 1;
        $display_rename = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . rcube_charset_convert($foldersplit[$level], 'UTF-7');
        $OUTPUT->command('replace_folder_row', $mbox, $c_rename, $display_rename);
        }
    $OUTPUT->command('reset_folder_rename');
    }
  else if (!$rename && $OUTPUT->ajax_call)
    {
    $OUTPUT->command('reset_folder_rename');
    $OUTPUT->show_message('errorsaving', 'error');
    }
  else if (!$rename)
    $OUTPUT->show_message('errorsaving', 'error');
  }

// delete an existing IMAP mailbox
else if ($RCMAIL->action=='delete-folder')
  {
  $a_mboxes = array_merge($IMAP->list_mailboxes(), $IMAP->list_unsubscribed());
  $delimiter = $IMAP->get_hierarchy_delimiter();

  if ($mboxes = get_input_value('_mboxes', RCUBE_INPUT_POST))
    $deleted = $IMAP->delete_mailbox(array($mboxes));

  if ($OUTPUT->ajax_call && $deleted)
    {
    $OUTPUT->command('remove_folder_row', get_input_value('_mboxes', RCUBE_INPUT_POST));
    foreach ($a_mboxes as $mbox)
      {
      $regex = get_input_value('_mboxes', RCUBE_INPUT_POST) . $delimiter;
      $regex = preg_quote($regex, '/');
      if (preg_match('/^'. $regex .'/', $mbox))
        {
        $OUTPUT->command('remove_folder_row', $mbox);
        }
      }
    $OUTPUT->show_message('folderdeleted', 'confirmation');
    }
  else if (!$deleted)
    {
    $OUTPUT->show_message('errorsaving', 'error');
    }
  }

if ($OUTPUT->ajax_call)
  $OUTPUT->send();


// build table with all folders listed by server
function rcube_subscription_form($attrib)
  {
  global $IMAP, $CONFIG, $OUTPUT;

  list($form_start, $form_end) = get_form_tags($attrib, 'folders');
  unset($attrib['form']);
  
  
  if (!$attrib['id'])
    $attrib['id'] = 'rcmSubscriptionlist';

  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));

  $out = "$form_start\n<table" . $attrib_str . ">\n";


  // add table header
  $out .= "<thead><tr>\n";
  $out .= sprintf('<td class="name">%s</td><td class="msgcount">%s</td><td class="subscribed">%s</td>'.
                  '<td class="rename">&nbsp;</td><td class="delete">&nbsp;</td>',
                  rcube_label('foldername'), rcube_label('messagecount'), rcube_label('subscribed'));
                  
  $out .= "\n</tr></thead>\n<tbody>\n";


  // get folders from server
  $IMAP->clear_cache('mailboxes');

  $a_unsubscribed = $IMAP->list_unsubscribed();
  $a_subscribed = $IMAP->list_mailboxes();
  $delimiter = $IMAP->get_hierarchy_delimiter();
  $a_js_folders = array();
 
  $checkbox_subscribe = new html_checkbox(array('name' => '_subscribed[]', 'onclick' => JS_OBJECT_NAME.".command(this.checked?'subscribe':'unsubscribe',this.value)"));
  
  if (!empty($attrib['deleteicon']))
    $del_button = sprintf('<img src="%s%s" alt="%s" border="0" />', $CONFIG['skin_path'], $attrib['deleteicon'], rcube_label('delete'));
  else
    $del_button = rcube_label('delete');

  if (!empty($attrib['renameicon']))
    $edit_button = sprintf('<img src="%s%s" alt="%s" border="0" />', $CONFIG['skin_path'], $attrib['renameicon'], rcube_label('rename'));
  else
    $del_button = rcube_label('rename');

  // create list of available folders
  foreach ($a_unsubscribed as $i => $folder)
    {
    $subscribed = in_array($folder, $a_subscribed);
    $protected = ($CONFIG['protect_default_folders'] == TRUE && in_array($folder,$CONFIG['default_imap_folders']));
    $zebra_class = $i%2 ? 'even' : 'odd';
    $folder_js = JQ($folder);
    $foldersplit = explode($delimiter, $folder);
    $level = count($foldersplit) - 1;
    $display_folder = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . rcube_charset_convert($foldersplit[$level], 'UTF-7');
    $folder_html = $CONFIG['protect_default_folders'] && in_array($folder, $CONFIG['default_imap_folders']) ? rcmail_localize_foldername($folder) : $display_folder;
    
    if (!$protected)
      $a_js_folders['rcmrow'.($i+1)] = array($folder, rcube_charset_convert($folder, 'UTF-7'));

    $out .= sprintf('<tr id="rcmrow%d" class="%s"><td class="name">%s</td><td class="msgcount">%d</td>',
                    $i+1,
                    $zebra_class,
                    Q($folder_html),
                    $IMAP->messagecount($folder));
                    
    if ($protected)
      $out .= '<td class="subscribed">&nbsp;'.($subscribed ? '&#x2022;' : '-').'</td>';
    else
      $out .= '<td class="subscribed">'.$checkbox_subscribe->show($subscribed?$folder:'', array('value' => $folder)).'</td>';

    // add rename and delete buttons
    if (!$protected)
      $out .= sprintf('<td class="rename"><a href="#rename" title="%s">%s</a>'.
                      '<td class="delete"><a href="#delete" title="%s">%s</a></td>',
                      rcube_label('renamefolder'),
                      $edit_button,
                      rcube_label('deletefolder'),
                      $del_button);
    else
      $out .= '<td></td><td></td>';
    
    $out .= "</tr>\n";
    }

  $out .= "</tbody>\n</table>";
  $out .= "\n$form_end";

  $OUTPUT->add_gui_object('subscriptionlist', $attrib['id']);
  $OUTPUT->set_env('subscriptionrows', $a_js_folders);
  $OUTPUT->set_env('defaultfolders', $CONFIG['default_imap_folders']);
  $OUTPUT->set_env('delimiter', $delimiter);

  return $out;  
  }


function rcube_create_folder_form($attrib)
  {
  global $OUTPUT;
  
  list($form_start, $form_end) = get_form_tags($attrib, 'create-folder');
  unset($attrib['form']);

  if ($attrib['hintbox'])
    $OUTPUT->add_gui_object('createfolderhint', $attrib['hintbox']);

  // return the complete edit form as table
  $out = "$form_start\n";

  $input = new html_inputfield(array('name' => '_folder_name'));
  $out .= $input->show();
  
  if (get_boolean($attrib['button']))
    {
    $button = new html_inputfield(array('type' => 'button',
                                    'value' => rcube_label('create'),
                                    'onclick' => JS_OBJECT_NAME.".command('create-folder',this.form)"));
    $out .= $button->show();
    }

  $out .= "\n$form_end";

  return $out;
  }

function rcube_rename_folder_form($attrib)
  {
  global $CONFIG, $IMAP;

  list($form_start, $form_end) = get_form_tags($attrib, 'rename-folder');
  unset($attrib['form']);

  // return the complete edit form as table
  $out = "$form_start\n";

  $a_unsubscribed = $IMAP->list_unsubscribed();
  $select_folder = new html_select(array('name' => '_folder_oldname', 'id' => 'rcmfd_oldfolder'));

  foreach ($a_unsubscribed as $i => $folder)
    {
    if ($CONFIG['protect_default_folders'] == TRUE && in_array($folder,$CONFIG['default_imap_folders'])) 
      continue;

    $select_folder->add($folder);
    }

  $out .= $select_folder->show();

  $out .= " to ";
  $inputtwo = new html_inputfield(array('name' => '_folder_newname'));
  $out .= $inputtwo->show();

  if (get_boolean($attrib['button']))
    {
    $button = new html_inputfield(array('type' => 'button',
                                    'value' => rcube_label('rename'),
                                    'onclick' => JS_OBJECT_NAME.".command('rename-folder',this.form)"));
    $out .= $button->show();
    }

  $out .= "\n$form_end";
  
  return $out;
  }

$OUTPUT->include_script('list.js');

// register UI objects
$OUTPUT->add_handlers(array(
  'foldersubscription' => 'rcube_subscription_form',
  'createfolder' => 'rcube_create_folder_form',
  'renamefolder' => 'rcube_rename_folder_form'
));

// add some labels to client
rcube_add_label('deletefolderconfirm','addsubfolderhint');

$OUTPUT->send('managefolders');
?>

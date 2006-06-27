<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/manage_folders.inc                             |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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
rcmail_imap_init(TRUE);


// subscribe to one or more mailboxes
if ($_action=='subscribe')
  {
  if (strlen($_GET['_mboxes']))
    $IMAP->subscribe(array($_GET['_mboxes']));

  if ($REMOTE_REQUEST)
    rcube_remote_response('// subscribed');
  }

// unsubscribe one or more mailboxes
else if ($_action=='unsubscribe')
  {
  if (strlen($_GET['_mboxes']))
    $IMAP->unsubscribe(array($_GET['_mboxes']));

  if ($REMOTE_REQUEST)
    rcube_remote_response('// unsubscribed');
  }

// create a new mailbox
else if ($_action=='create-folder')
  {
  if (!empty($_GET['_name']))
    $create = $IMAP->create_mailbox(trim(get_input_value('_name', RCUBE_INPUT_GET)), TRUE);

  if ($create && $REMOTE_REQUEST)
    {
    $commands = sprintf("this.add_folder_row('%s')", rep_specialchars_output($create, 'js'));
    rcube_remote_response($commands);
    }
  else if (!$create && $REMOTE_REQUEST)
    {
    $commands = show_message('errorsaving', 'error');
    rcube_remote_response($commands);
    }
  else if (!$create)
    show_message('errorsaving', 'error');
  }

// rename a mailbox
else if ($_action=='rename-folder')
  {
  if (!empty($_GET['_folder_oldname']) && !empty($_GET['_folder_newname']))
    $rename = $IMAP->rename_mailbox(get_input_value('_folder_oldname', RCUBE_INPUT_GET), trim(get_input_value('_folder_newname', RCUBE_INPUT_GET)), TRUE);

  if ($rename && $REMOTE_REQUEST)
    {
    $commands = sprintf("this.add_folder_row('%s');", rep_specialchars_output($rename, 'js')); 
    $commands .= sprintf("this.remove_folder_row('%s')", rep_specialchars_output($_GET['_folder_oldname'], 'js'));
    rcube_remote_response($commands);
    }
  else if (!$rename && $REMOTE_REQUEST)
    {
    $commands = show_message('errorsaving', 'error');
    rcube_remote_response($commands);
    }
  else if (!$rename)
    show_message('errorsaving', 'error');
  }

// delete an existing IMAP mailbox
else if ($_action=='delete-folder')
  {
  if (strlen($_GET['_mboxes']))
    $deleted = $IMAP->delete_mailbox(array($_GET['_mboxes']));

  if ($REMOTE_REQUEST && $deleted)
    rcube_remote_response(sprintf("this.remove_folder_row('%s')", rep_specialchars_output($_GET['_mboxes'], 'js')));
  else if ($REMOTE_REQUEST)
    {
    $commands = show_message('errorsaving', 'error');
    rcube_remote_response($commands);
    }
  }



// build table with all folders listed by server
function rcube_subscription_form($attrib)
  {
  global $IMAP, $CONFIG, $OUTPUT, $JS_OBJECT_NAME;

  list($form_start, $form_end) = get_form_tags($attrib, 'folders');
  unset($attrib['form']);
  
  
  if (!$attrib['id'])
    $attrib['id'] = 'rcmSubscriptionlist';

  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));

  $out = "$form_start\n<table" . $attrib_str . ">\n";


  // add table header
  $out .= "<thead><tr>\n";
  $out .= sprintf('<td>%s</td><td>%s</td><td></td>', rcube_label('foldername'), rcube_label('subscribed'));
  $out .= "\n</tr></thead>\n<tbody>\n";


  // get folders from server
  $a_unsubscribed = $IMAP->list_unsubscribed();
  $a_subscribed = $IMAP->list_mailboxes();
  $a_js_folders = array();
 
  $checkbox_subscribe = new checkbox(array('name' => '_subscribed[]', 'onclick' => "$JS_OBJECT_NAME.command(this.checked?'subscribe':'unsubscribe',this.value)"));
  
  if ($attrib['deleteicon'])
    $button = sprintf('<img src="%s%s" alt="%s" border="0" />', $CONFIG['skin_path'], $attrib['deleteicon'], rcube_label('delete'));
  else
    $button = rcube_label('delete');


  // create list of available folders
  foreach ($a_unsubscribed as $i => $folder)
    {
    if ($CONFIG['protect_default_folders'] == TRUE && in_array($folder,$CONFIG['default_imap_folders']))
      continue;

    $zebra_class = $i%2 ? 'even' : 'odd';
    $folder_js = rep_specialchars_output($folder, 'js');
    $a_js_folders['rcmrow'.($i+1)] = $folder_js;

    $out .= sprintf('<tr id="rcmrow%d" class="%s"><td>%s</td><td>%s</td><td><a href="#delete" onclick="%s.command(\'delete-folder\',\'%s\')" title="%s">%s</a></td>',
                    $i+1,
                    $zebra_class,
                    rep_specialchars_output(rcube_charset_convert($folder, 'UTF-7', 'UTF-8'), 'html', 'all'),
                    $checkbox_subscribe->show(in_array($folder, $a_subscribed)?$folder:'', array('value' => $folder)),
                    $JS_OBJECT_NAME,
                    $folder_js,
                    rcube_label('deletefolder'),
                    $button);
    
    $out .= "</tr>\n";
    }

  $out .= "</tbody>\n</table>";
  $out .= "\n$form_end";


  $javascript = sprintf("%s.gui_object('subscriptionlist', '%s');\n", $JS_OBJECT_NAME, $attrib['id']);
  $javascript .= sprintf("%s.set_env('subscriptionrows', %s);", $JS_OBJECT_NAME, array2js($a_js_folders));
  $OUTPUT->add_script($javascript);

  return $out;  
  }


function rcube_create_folder_form($attrib)
  {
  global $JS_OBJECT_NAME;

  list($form_start, $form_end) = get_form_tags($attrib, 'create-folder');
  unset($attrib['form']);


  // return the complete edit form as table
  $out = "$form_start\n";

  $input = new textfield(array('name' => '_folder_name'));
  $out .= $input->show();
  
  if (get_boolean($attrib['button']))
    {
    $button = new input_field(array('type' => 'button',
                                    'value' => rcube_label('create'),
                                    'onclick' => "$JS_OBJECT_NAME.command('create-folder',this.form)"));
    $out .= $button->show();
    }

  $out .= "\n$form_end";

  return $out;
  }

function rcube_rename_folder_form($attrib)
  {
  global $CONFIG, $IMAP, $JS_OBJECT_NAME;

  list($form_start, $form_end) = get_form_tags($attrib, 'rename-folder');
  unset($attrib['form']);

  // return the complete edit form as table
  $out = "$form_start\n";

  $a_unsubscribed = $IMAP->list_unsubscribed();
  $select_folder = new select(array('name' => '_folder_oldname', 'id' => 'rcmfd_oldfolder'));

  foreach ($a_unsubscribed as $i => $folder)
    {
    if ($CONFIG['protect_default_folders'] == TRUE && in_array($folder,$CONFIG['default_imap_folders'])) 
      continue;

    $select_folder->add($folder);
    }

  $out .= $select_folder->show();

  $out .= " to ";
  $inputtwo = new textfield(array('name' => '_folder_newname'));
  $out .= $inputtwo->show();

  if (get_boolean($attrib['button']))
    {
    $button = new input_field(array('type' => 'button',
                                    'value' => rcube_label('rename'),
                                    'onclick' => "$JS_OBJECT_NAME.command('rename-folder',this.form)"));
    $out .= $button->show();
    }

  $out .= "\n$form_end";

  return $out;
  }


// add some labels to client
rcube_add_label('deletefolderconfirm');


parse_template('managefolders');
?>

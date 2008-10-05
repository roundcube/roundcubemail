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

function rcmail_user_prefs_form($attrib)
{
  global $RCMAIL;
  $config = $RCMAIL->config->all();

  $no_override = is_array($config['dont_override']) ? array_flip($config['dont_override']) : array();

  // add some labels to client
  $RCMAIL->output->add_label('nopagesizewarning');
  
  list($form_start, $form_end) = get_form_tags($attrib, 'save-prefs');
  unset($attrib['form']);

  $out = $form_start;
  $table = new html_table(array('cols' => 2));

  // show language selection
  if (!isset($no_override['language'])) {
    $a_lang = $RCMAIL->list_languages();
    asort($a_lang);
  
    $field_id = 'rcmfd_lang';
    $select_lang = new html_select(array('name' => '_language', 'id' => $field_id));
    $select_lang->add(array_values($a_lang), array_keys($a_lang));
  
    $table->add('title', html::label($field_id, Q(rcube_label('language'))));
    $table->add(null, $select_lang->show($RCMAIL->user->language));
  }


  // show page size selection
  if (!isset($no_override['timezone'])) {
    $field_id = 'rcmfd_timezone';
    $select_timezone = new html_select(array('name' => '_timezone', 'id' => $field_id, 'onchange' => "document.getElementById('rcmfd_dst').disabled=this.selectedIndex==0"));
    $select_timezone->add(rcube_label('autodetect'), 'auto');
    $select_timezone->add('(GMT -11:00) Midway Island, Samoa', '-11');
    $select_timezone->add('(GMT -10:00) Hawaii', '-10');
    $select_timezone->add('(GMT -9:30) Marquesas Islands', '-9.5');
    $select_timezone->add('(GMT -9:00) Alaska', '-9');
    $select_timezone->add('(GMT -8:00) Pacific Time (US/Canada)', '-8');
    $select_timezone->add('(GMT -7:00) Mountain Time (US/Canada)', '-7');
    $select_timezone->add('(GMT -6:00) Central Time (US/Canada), Mexico City', '-6');
    $select_timezone->add('(GMT -5:00) Eastern Time (US/Canada), Bogota, Lima', '-5');
    $select_timezone->add('(GMT -4:30) Caracas', '-4.5');
    $select_timezone->add('(GMT -4:00) Atlantic Time (Canada), La Paz', '-4');
    $select_timezone->add('(GMT -3:30) Nfld Time (Canada), Nfld, S. Labador', '-3.5');
    $select_timezone->add('(GMT -3:00) Brazil, Buenos Aires, Georgetown', '-3');
    $select_timezone->add('(GMT -2:00) Mid-Atlantic', '-2');
    $select_timezone->add('(GMT -1:00) Azores, Cape Verde Islands', '-1');
    $select_timezone->add('(GMT) Western Europe, London, Lisbon, Casablanca', '0');
    $select_timezone->add('(GMT +1:00) Central European Time', '1');
    $select_timezone->add('(GMT +2:00) EET: Kaliningrad, South Africa', '2');
    $select_timezone->add('(GMT +3:00) Baghdad, Kuwait, Riyadh, Moscow, Nairobi', '3');
    $select_timezone->add('(GMT +3:30) Tehran', '3.5');
    $select_timezone->add('(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi', '4');
    $select_timezone->add('(GMT +4:30) Kabul', '4.5');
    $select_timezone->add('(GMT +5:00) Ekaterinburg, Islamabad, Karachi', '5');
    $select_timezone->add('(GMT +5:30) Chennai, Kolkata, Mumbai, New Delhi', '5.5');
    $select_timezone->add('(GMT +5:45) Kathmandu', '5.75');
    $select_timezone->add('(GMT +6:00) Almaty, Dhaka, Colombo', '6');
    $select_timezone->add('(GMT +6:30) Cocos Islands, Myanmar', '6.5');
    $select_timezone->add('(GMT +7:00) Bangkok, Hanoi, Jakarta', '7');
    $select_timezone->add('(GMT +8:00) Beijing, Perth, Singapore, Taipei', '8');
    $select_timezone->add('(GMT +8:45) Caiguna, Eucla, Border Village', '8.75');
    $select_timezone->add('(GMT +9:00) Tokyo, Seoul, Yakutsk', '9');
    $select_timezone->add('(GMT +9:30) Adelaide, Darwin', '9.5');
    $select_timezone->add('(GMT +10:00) EAST/AEST: Sydney, Guam, Vladivostok', '10');
    $select_timezone->add('(GMT +10:30) New South Wales', '10.5');
    $select_timezone->add('(GMT +11:00) Magadan, Solomon Islands', '11');
    $select_timezone->add('(GMT +11:30) Norfolk Island', '11.5');
    $select_timezone->add('(GMT +12:00) Auckland, Wellington, Kamchatka', '12');
    $select_timezone->add('(GMT +12:45) Chatham Islands', '12.75');
    $select_timezone->add('(GMT +13:00) Tonga, Pheonix Islands', '13');
    $select_timezone->add('(GMT +14:00) Kiribati', '14');
  
    $table->add('title', html::label($field_id, Q(rcube_label('timezone'))));
    $table->add(null, $select_timezone->show((string)$config['timezone']));
  }

  // daylight savings
  if (!isset($no_override['dst_active'])) {
    $field_id = 'rcmfd_dst';
    $input_dst = new html_checkbox(array('name' => '_dst_active', 'id' => $field_id, 'value' => 1, 'disabled' => ($config['timezone'] === 'auto')));
    
    $table->add('title', html::label($field_id, Q(rcube_label('dstactive'))));
    $table->add(null, $input_dst->show($config['dst_active']));
  }

  // MM: Show checkbox for toggling 'pretty dates' 
  if (!isset($no_override['prettydate'])) {
    $field_id = 'rcmfd_prettydate';
    $input_prettydate = new html_checkbox(array('name' => '_pretty_date', 'id' => $field_id, 'value' => 1));

    $table->add('title', html::label($field_id, Q(rcube_label('prettydate'))));
    $table->add(null, $input_prettydate->show($config['prettydate']?1:0));
  }

  // show page size selection
  if (!isset($no_override['pagesize'])) {
    $field_id = 'rcmfd_pgsize';
    $input_pagesize = new html_inputfield(array('name' => '_pagesize', 'id' => $field_id, 'size' => 5));

    $table->add('title', html::label($field_id, Q(rcube_label('pagesize'))));
    $table->add(null, $input_pagesize->show($config['pagesize']));
  }

  // show drop-down for available skins
  if (!isset($no_override['skin'])) {
    $skins = rcmail_get_skins();

    if (count($skins) > 1) {
      $field_id = 'rcmfd_skin';
      $input_skin = new html_select(array('name'=>'_skin', 'id'=>$field_id));
      
      foreach($skins as $skin)
        $input_skin->add($skin, $skin);

      $table->add('title', html::label($field_id, Q(rcube_label('skin'))));
      $table->add(null, $input_skin->show($config['skin']));
    }
  }

  $out .= html::tag('fieldset', null, html::tag('legend', null, Q(rcube_label('uisettings'))) . $table->show($attrib));
  
  $table = new html_table(array('cols' => 2));

  // show config parameter for preview pane
  if (!isset($no_override['preview_pane'])) {
    $field_id = 'rcmfd_preview';
    $input_preview = new html_checkbox(array('name' => '_preview_pane', 'id' => $field_id, 'value' => 1));
    
    $table->add('title', html::label($field_id, Q(rcube_label('previewpane'))));
    $table->add(null, $input_preview->show($config['preview_pane']?1:0));
  }

  if (!isset($no_override['mdn_requests'])) {
    $field_id = 'rcmfd_mdn_requests';
    $select_mdn_requests = new html_select(array('name' => '_mdn_requests', 'id' => $field_id));
    $select_mdn_requests->add(rcube_label('askuser'), 0);
    $select_mdn_requests->add(rcube_label('autosend'), 1);
    $select_mdn_requests->add(rcube_label('ignore'), 2);

    $table->add('title', html::label($field_id, Q(rcube_label('mdnrequests'))));
    $table->add(null, $select_mdn_requests->show($config['mdn_requests']));
  }

  $out .= html::tag('fieldset', null, html::tag('legend', null, Q(rcube_label('mailboxview'))) . $table->show($attrib));

  $table = new html_table(array('cols' => 2));

  // show checkbox for HTML/plaintext messages
  if (!isset($no_override['prefer_html'])) {
    $field_id = 'rcmfd_htmlmsg';
    $input_preferhtml = new html_checkbox(array('name' => '_prefer_html', 'id' => $field_id, 'value' => 1,
      'onchange' => JS_OBJECT_NAME.'.toggle_prefer_html(this)'));

    $table->add('title', html::label($field_id, Q(rcube_label('preferhtml'))));
    $table->add(null, $input_preferhtml->show($config['prefer_html']?1:0));
  }

  // show checkbox for displaying images from people in the addressbook
  if (!isset($no_override['addrbook_show_images'])) {
    $field_id = 'rcmfd_addrbook_show_images';
    $input_addrbook_show_images = new html_checkbox(array('name' => '_addrbook_show_images', 'id' => $field_id,
      'value' => 1, 'disabled' => $config['prefer_html']?0:1));

    $table->add('title', html::label($field_id, Q(rcube_label('showknownimages'))));
    $table->add(null, $input_addrbook_show_images->show($config['addrbook_show_images']?1:0));
  }

  if (!isset($no_override['inline_images'])) {
    $field_id = 'rcmfd_inline_images';
    $input_inline_images = new html_checkbox(array('name' => '_inline_images', 'id' => $field_id, 'value' => 1));

    $table->add('title', html::label($field_id, Q(rcube_label('showinlineimages'))));
    $table->add(null, $input_inline_images->show($config['inline_images']?1:0));
  }

  $out .= html::tag('fieldset', null, html::tag('legend', null, Q(rcube_label('messagesdisplaying'))) . $table->show($attrib));
  
  $table = new html_table(array('cols' => 2));

  // Show checkbox for HTML Editor
  if (!isset($no_override['htmleditor'])) {
    $field_id = 'rcmfd_htmleditor';
    $input_htmleditor = new html_checkbox(array('name' => '_htmleditor', 'id' => $field_id, 'value' => 1));
    
    $table->add('title', html::label($field_id, Q(rcube_label('htmleditor'))));
    $table->add(null, $input_htmleditor->show($config['htmleditor']?1:0));
  }
                  
  if (!isset($no_override['draft_autosave'])) {
    $field_id = 'rcmfd_autosave';
    $select_autosave = new html_select(array('name' => '_draft_autosave', 'id' => $field_id, 'disabled' => empty($config['drafts_mbox'])));
    $select_autosave->add(rcube_label('never'), 0);
    foreach (array(3, 5, 10) as $i => $min)
      $select_autosave->add(rcube_label(array('name' => 'everynminutes', 'vars' => array('n' => $min))), $min*60);

    $table->add('title', html::label($field_id, Q(rcube_label('autosavedraft'))));
    $table->add(null, $select_autosave->show($config['draft_autosave']));
  }

  if (!isset($no_override['mime_param_folding'])) {
    $field_id = 'rcmfd_param_folding';
    $select_param_folding = new html_select(array('name' => '_mime_param_folding', 'id' => $field_id));
    $select_param_folding->add(rcube_label('2231folding'), 0);
    $select_param_folding->add(rcube_label('miscfolding'), 1);
    $select_param_folding->add(rcube_label('2047folding'), 2);

    $table->set_row_attribs('advanced');
    $table->add('title', html::label($field_id, Q(rcube_label('mimeparamfolding'))));
    $table->add(null, $select_param_folding->show($config['mime_param_folding']));
  }

  $out .= html::tag('fieldset', null, html::tag('legend', null, Q(rcube_label('messagescomposition'))) . $table->show($attrib));

  // Configure special folders
  if (!isset($no_override['default_imap_folders'])) {
    $RCMAIL->imap_init(true);
    $select = rcmail_mailbox_select(array('noselection' => '---', 'realnames' => true, 'maxlength' => 30));
    
    $table = new html_table(array('cols' => 2));

    if (!isset($no_override['drafts_mbox'])) {
      $table->add('title', Q(rcube_label('drafts')));
      $table->add(null, $select->show($config['drafts_mbox'], array('name' => "_drafts_mbox", 'onchange' => "document.getElementById('rcmfd_autosave').disabled=this.selectedIndex==0")));
    }

    if (!isset($no_override['sent_mbox'])) {
      $table->add('title', Q(rcube_label('sent')));
      $table->add(null, $select->show($config['sent_mbox'], array('name' => "_sent_mbox")));
    }

    if (!isset($no_override['junk_mbox'])) {
      $table->add('title', Q(rcube_label('junk')));
      $table->add(null, $select->show($config['junk_mbox'], array('name' => "_junk_mbox")));
    }

    if (!isset($no_override['trash_mbox'])) {
      $table->add('title', Q(rcube_label('trash')));
      $table->add(null, $select->show($config['trash_mbox'], array('name' => "_trash_mbox")));
    }

    $out .= html::tag('fieldset', null, html::tag('legend', null, Q(rcube_label('specialfolders'))) . $table->show($attrib));
  }

  $table = new html_table(array('cols' => 2));

  if (!isset($no_override['read_when_deleted'])) {
    $field_id = 'rcmfd_read_deleted';
    $input_readdeleted = new html_checkbox(array('name' => '_read_when_deleted', 'id' => $field_id, 'value' => 1));
    
    $table->add('title', html::label($field_id, Q(rcube_label('readwhendeleted'))));
    $table->add(null, $input_readdeleted->show($config['read_when_deleted']?1:0));
  }

  if (!isset($no_override['flag_for_deletion'])) {
    $field_id = 'rcmfd_flag_for_deletion';
    $input_flagfordeletion = new html_checkbox(array('name' => '_flag_for_deletion', 'id' => $field_id, 'value' => 1));
    
    $table->add('title', html::label($field_id, Q(rcube_label('flagfordeletion'))));
    $table->add(null, $input_flagfordeletion->show($config['flag_for_deletion']?1:0));
  }
  
  // don't show deleted messages
  if (!isset($no_override['skip_deleted'])) {
    $field_id = 'rcmfd_skip_deleted';
    $input_purge = new html_checkbox(array('name' => '_skip_deleted', 'id' => $field_id, 'value' => 1));
    
    $table->add('title', html::label($field_id, Q(rcube_label('skipdeleted'))));
    $table->add(null, $input_purge->show($config['skip_deleted']?1:0));
  }
  
  // Trash purging on logout
  if (!isset($no_override['logout_purge'])) {
    $field_id = 'rcmfd_logout_purge';
    $input_purge = new html_checkbox(array('name' => '_logout_purge', 'id' => $field_id, 'value' => 1));
    
    $table->add('title', html::label($field_id, Q(rcube_label('logoutclear'))));
    $table->add(null, $input_purge->show($config['logout_purge']?1:0));
  }
  
  // INBOX compacting on logout
  if (!isset($no_override['logout_expunge'])) {
    $field_id = 'rcmfd_logout_expunge';
    $input_expunge = new html_checkbox(array('name' => '_logout_expunge', 'id' => $field_id, 'value' => 1));

    $table->add('title', html::label($field_id, Q(rcube_label('logoutcompact'))));
    $table->add(null, $input_expunge->show($config['logout_expunge']?1:0));
  }

  $out .= html::tag('fieldset', null, html::tag('legend', null, Q(rcube_label('serversettings'))) . $table->show($attrib));

  return $out . $form_end;
  }



function rcmail_identities_list($attrib)
  {
  global $OUTPUT, $USER;

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmIdentitiesList';

  // define list of cols to be displayed
  $a_show_cols = array('name', 'email');

  // create XHTML table  
  $out = rcube_table_output($attrib, $USER->list_identities(), $a_show_cols, 'identity_id');
  
  // set client env
  $OUTPUT->add_gui_object('identitieslist', $attrib['id']);

  return $out;
  }



// similar function as in /steps/addressbook/edit.inc
function get_form_tags($attrib, $action, $add_hidden=array())
  {
  global $EDIT_FORM, $RCMAIL;

  $form_start = '';
  if (!strlen($EDIT_FORM))
    {
    $hiddenfields = new html_hiddenfield(array('name' => '_task', 'value' => $RCMAIL->task));
    $hiddenfields->add(array('name' => '_action', 'value' => $action));
    
    if ($add_hidden)
      $hiddenfields->add($add_hidden);
    
    $form_start = !strlen($attrib['form']) ? $RCMAIL->output->form_tag(array('name' => "form", 'method' => "post")) : '';
    $form_start .= $hiddenfields->show();
    }
    
  $form_end = (!strlen($EDIT_FORM) && !strlen($attrib['form'])) ? '</form>' : '';
  $form_name = strlen($attrib['form']) ? $attrib['form'] : 'form';

  if (!strlen($EDIT_FORM))
    $RCMAIL->output->add_gui_object('editform', $form_name);
  
  $EDIT_FORM = $form_name;

  return array($form_start, $form_end);
  }

function rcmail_get_skins()
  {
  $path = 'skins';
  $skins = array();

  $dir = opendir($path);
  
  if (!$dir)
	return false;
  
  while (($file = readdir($dir)) !== false)
    {
    $filename = $path.'/'.$file;
    if (is_dir($filename) && is_readable($filename) 
	&& !in_array($file, array('.', '..', '.svn')))
      $skins[] = $file;
    }

  closedir($dir);

  return $skins;
  }

$OUTPUT->set_env('multiple_identities', $RCMAIL->config->get('multiple_identities', true));

// register UI objects
$OUTPUT->add_handlers(array(
  'userprefs' => 'rcmail_user_prefs_form',
  'identitieslist' => 'rcmail_identities_list',
  'itentitieslist' => 'rcmail_identities_list'  // keep this for backward compatibility
));


?>

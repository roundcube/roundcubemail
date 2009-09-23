<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/edit_prefs.inc                                 |
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

 $Id: func.inc 2822 2009-07-31 09:07:54Z alec $

*/

if (!$OUTPUT->ajax_call)
  $OUTPUT->set_pagetitle(rcube_label('preferences'));


$CURR_SECTION = get_input_value('_section', RCUBE_INPUT_GPC);

function rcmail_user_prefs_form($attrib)
{
  global $RCMAIL, $CURR_SECTION;

  // add some labels to client
  $RCMAIL->output->add_label('nopagesizewarning');

  unset($attrib['form']);
  
  list($form_start, $form_end) = get_form_tags($attrib, 'save-prefs', null,
    array('name' => '_section', 'value' => $CURR_SECTION));

  $out = $form_start;
  
  $out .= rcmail_user_prefs_block($CURR_SECTION, $attrib);
  
  return $out . $form_end;
}

function rcmail_user_prefs_block($part, $attrib)
{
  global $RCMAIL;

  $config = $RCMAIL->config->all();
  $no_override = array_flip($RCMAIL->config->get('dont_override', array()));
  
  $blocks = array();
  
  switch ($part)
  {
  // General UI settings
  case 'general':
  
    $blocks = array(
      'main' => array('name' => Q(rcube_label('mainoptions'))),
      'list' => array('name' => Q(rcube_label('listoptions'))),
    );
    
    // language selection
    if (!isset($no_override['language'])) {
      $a_lang = $RCMAIL->list_languages();
      asort($a_lang);

      $field_id = 'rcmfd_lang';
      $select_lang = new html_select(array('name' => '_language', 'id' => $field_id));
      $select_lang->add(array_values($a_lang), array_keys($a_lang));

      $blocks['main']['options']['language'] = array(
        'title' => html::label($field_id, Q(rcube_label('language'))),
        'content' => $select_lang->show($RCMAIL->user->language),
      );
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
      $select_timezone->add('(GMT +2:00) EET: Tallinn, Helsinki, Kaliningrad, South Africa', '2');
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

      $blocks['main']['options']['timezone'] = array(
        'title' => html::label($field_id, Q(rcube_label('timezone'))),
        'content' => $select_timezone->show((string)$config['timezone']),
      );
    }

    // daylight savings
    if (!isset($no_override['dst_active'])) {
      $field_id = 'rcmfd_dst';
      $input_dst = new html_checkbox(array('name' => '_dst_active', 'id' => $field_id, 'value' => 1, 'disabled' => ($config['timezone'] === 'auto')));

      $blocks['main']['options']['dstactive'] = array(
        'title' => html::label($field_id, Q(rcube_label('dstactive'))),
        'content' => $input_dst->show($config['dst_active']),
      );
    }

    // MM: Show checkbox for toggling 'pretty dates' 
    if (!isset($no_override['prettydate'])) {
      $field_id = 'rcmfd_prettydate';
      $input_prettydate = new html_checkbox(array('name' => '_pretty_date', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['prettydate'] = array(
        'title' => html::label($field_id, Q(rcube_label('prettydate'))),
        'content' => $input_prettydate->show($config['prettydate']?1:0),
      );
    }

    // show page size selection
    if (!isset($no_override['pagesize'])) {
      $field_id = 'rcmfd_pgsize';
      $input_pagesize = new html_inputfield(array('name' => '_pagesize', 'id' => $field_id, 'size' => 5));

      $blocks['list']['options']['pagesize'] = array(
        'title' => html::label($field_id, Q(rcube_label('pagesize'))),
        'content' => $input_pagesize->show($config['pagesize']),
      );
    }

    // Show checkbox for toggling 'index_sort' 
    if (!isset($no_override['index_sort'])) {
      $field_id = 'rcmfd_indexsort';
      $input_indexsort = new html_checkbox(array('name' => '_index_sort', 'id' => $field_id, 'value' => 1));

      $blocks['list']['options']['index_sort'] = array(
        'title' => html::label($field_id, Q(rcube_label('indexsort'))),
        'content' => $input_indexsort->show($config['index_sort']?1:0),
      );
    }

    // show drop-down for available skins
    if (!isset($no_override['skin'])) {
      $skins = rcmail_get_skins();

      if (count($skins) > 1) {
        $field_id = 'rcmfd_skin';
        $input_skin = new html_select(array('name'=>'_skin', 'id'=>$field_id));

        foreach($skins as $skin)
          $input_skin->add($skin, $skin);

        $blocks['main']['options']['skin'] = array(
          'title' => html::label($field_id, Q(rcube_label('skin'))),
          'content' => $input_skin->show($config['skin']),
        );
      }
    }
    
    break;

  // Mailbox view (mail screen)
  case 'mailbox':

    $blocks = array(
      'main' => array('name' => Q(rcube_label('mainoptions'))),
      'new_message' => array('name' => Q(rcube_label('newmessage'))),
    );

    // show config parameter for preview pane
    if (!isset($no_override['preview_pane'])) {
      $field_id = 'rcmfd_preview';
      $input_preview = new html_checkbox(array('name' => '_preview_pane', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['preview_pane'] = array(
        'title' => html::label($field_id, Q(rcube_label('previewpane'))),
        'content' => $input_preview->show($config['preview_pane']?1:0),
      );
    }

    if (!isset($no_override['mdn_requests'])) {
      $field_id = 'rcmfd_mdn_requests';
      $select_mdn_requests = new html_select(array('name' => '_mdn_requests', 'id' => $field_id));
      $select_mdn_requests->add(rcube_label('askuser'), 0);
      $select_mdn_requests->add(rcube_label('autosend'), 1);
      $select_mdn_requests->add(rcube_label('ignore'), 2);

      $blocks['main']['options']['mdn_requests'] = array(
        'title' => html::label($field_id, Q(rcube_label('mdnrequests'))),
        'content' => $select_mdn_requests->show($config['mdn_requests']),
      );
    }

    if (!isset($no_override['focus_on_new_message'])) {
      $field_id = 'rcmfd_focus_on_new_message';
      $input_focus_on_new_message = new html_checkbox(array('name' => '_focus_on_new_message', 'id' => $field_id, 'value' => 1));

      $blocks['new_message']['options']['focus_on_new_message'] = array(
        'title' => html::label($field_id, Q(rcube_label('focusonnewmessage'))),
        'content' => $input_focus_on_new_message->show($config['focus_on_new_message']?1:0),
      );
    }

    if (!isset($no_override['keep_alive'])) {
      $field_id = 'rcmfd_keep_alive';
      $select_keep_alive = new html_select(array('name' => '_keep_alive', 'id' => $field_id));

      foreach(array(1, 3, 5, 10, 15, 30, 60) as $min)
        if((!$config['min_keep_alive'] || $config['min_keep_alive'] <= $min * 60)
            && (!$config['session_lifetime'] || $config['session_lifetime'] > $min)) {
          $select_keep_alive->add(rcube_label(array('name' => 'everynminutes', 'vars' => array('n' => $min))), $min);
        }

      $blocks['new_message']['options']['keep_alive'] = array(
        'title' => html::label($field_id, Q(rcube_label('keepalive'))),
        'content' => $select_keep_alive->show($config['keep_alive']/60),
      );
    }

    if (!isset($no_override['check_all_folders'])) {
      $field_id = 'rcmfd_check_all_folders';
      $input_check_all = new html_checkbox(array('name' => '_check_all_folders', 'id' => $field_id, 'value' => 1));

      $blocks['new_message']['options']['check_all_folders'] = array(
        'title' => html::label($field_id, Q(rcube_label('checkallfolders'))),
        'content' => $input_check_all->show($config['check_all_folders']?1:0),
      );
    }

    break;

  // Message viewing
  case 'mailview':

    $blocks = array(
      'main' => array('name' => Q(rcube_label('mainoptions'))),
    );

    // show checkbox for HTML/plaintext messages
    if (!isset($no_override['prefer_html'])) {
      $field_id = 'rcmfd_htmlmsg';
      $input_preferhtml = new html_checkbox(array('name' => '_prefer_html', 'id' => $field_id, 'value' => 1,
        'onchange' => JS_OBJECT_NAME.'.toggle_prefer_html(this)'));

      $blocks['main']['options']['prefer_html'] = array(
        'title' => html::label($field_id, Q(rcube_label('preferhtml'))),
        'content' => $input_preferhtml->show($config['prefer_html']?1:0),
      );
    }

    if (!isset($no_override['show_images'])) {
      $field_id = 'rcmfd_show_images';
      $input_show_images = new html_select(array('name' => '_show_images', 'id' => $field_id));
      $input_show_images->add(rcube_label('never'), 0);
      $input_show_images->add(rcube_label('fromknownsenders'), 1);
      $input_show_images->add(rcube_label('always'), 2);

      $blocks['main']['options']['show_images'] = array(
        'title' => html::label($field_id, Q(rcube_label('showremoteimages'))),
        'content' => $input_show_images->show($config['show_images']),
      );
    }

    if (!isset($no_override['inline_images'])) {
      $field_id = 'rcmfd_inline_images';
      $input_inline_images = new html_checkbox(array('name' => '_inline_images', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['inline_images'] = array(
        'title' => html::label($field_id, Q(rcube_label('showinlineimages'))),
        'content' => $input_inline_images->show($config['inline_images']?1:0),
      );
    }

    // "display after delete" checkbox
    if (!isset($no_override['display_next'])) {
      $field_id = 'rcmfd_displaynext';
      $input_displaynext = new html_checkbox(array('name' => '_display_next', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['display_next'] = array(
        'title' => html::label($field_id, Q(rcube_label('displaynext'))),
        'content' => $input_displaynext->show($config['display_next']?1:0),
      );
    }

    break;

  // Mail composition
  case 'compose':

    $blocks = array(
      'main' => array('name' => Q(rcube_label('mainoptions'))),
    );

    // Show checkbox for HTML Editor
    if (!isset($no_override['htmleditor'])) {
      $field_id = 'rcmfd_htmleditor';
      $input_htmleditor = new html_checkbox(array('name' => '_htmleditor', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['htmleditor'] = array(
        'title' => html::label($field_id, Q(rcube_label('htmleditor'))),
        'content' => $input_htmleditor->show($config['htmleditor']?1:0),
      );
    }

    if (!isset($no_override['draft_autosave'])) {
      $field_id = 'rcmfd_autosave';
      $select_autosave = new html_select(array('name' => '_draft_autosave', 'id' => $field_id, 'disabled' => empty($config['drafts_mbox'])));
      $select_autosave->add(rcube_label('never'), 0);
      foreach (array(1, 3, 5, 10) as $i => $min)
        $select_autosave->add(rcube_label(array('name' => 'everynminutes', 'vars' => array('n' => $min))), $min*60);

      $blocks['main']['options']['draft_autosave'] = array(
        'title' => html::label($field_id, Q(rcube_label('autosavedraft'))),
        'content' => $select_autosave->show($config['draft_autosave']),
      );
    }

    if (!isset($no_override['mime_param_folding'])) {
      $field_id = 'rcmfd_param_folding';
      $select_param_folding = new html_select(array('name' => '_mime_param_folding', 'id' => $field_id));
      $select_param_folding->add(rcube_label('2231folding'), 0);
      $select_param_folding->add(rcube_label('miscfolding'), 1);
      $select_param_folding->add(rcube_label('2047folding'), 2);

      $blocks['main']['options']['mime_param_folding'] = array(
	'advanced' => true,
        'title' => html::label($field_id, Q(rcube_label('mimeparamfolding'))),
        'content' => $select_param_folding->show($config['mime_param_folding']),
      );
    }

    break;

  // Special IMAP folders
  case 'folders':

    $blocks = array(
      'main' => array('name' => Q(rcube_label('mainoptions'))),
    );

    // Configure special folders
    if (!isset($no_override['default_imap_folders'])) {
      $RCMAIL->imap_init(true);
      $select = rcmail_mailbox_select(array('noselection' => '---', 'realnames' => true, 'maxlength' => 30));

      if (!isset($no_override['drafts_mbox']))
        $blocks['main']['options']['drafts_mbox'] = array(
          'title' => Q(rcube_label('drafts')),
          'content' => $select->show($config['drafts_mbox'], array('name' => "_drafts_mbox")),
        );

      if (!isset($no_override['sent_mbox']))
        $blocks['main']['options']['sent_mbox'] = array(
          'title' => Q(rcube_label('sent')),
          'content' => $select->show($config['sent_mbox'], array('name' => "_sent_mbox")),
	);

      if (!isset($no_override['junk_mbox']))
        $blocks['main']['options']['junk_mbox'] = array(
          'title' => Q(rcube_label('junk')),
          'content' => $select->show($config['junk_mbox'], array('name' => "_junk_mbox")),
	);

      if (!isset($no_override['trash_mbox']))
        $blocks['main']['options']['trash_mbox'] = array(
          'title' => Q(rcube_label('trash')),
          'content' => $select->show($config['trash_mbox'], array('name' => "_trash_mbox")),
        );
    }
    break;

  // Server settings
  case 'server':

    $blocks = array(
      'main' => array('name' => Q(rcube_label('mainoptions'))),
      'maintenance' => array('name' => Q(rcube_label('maintenance'))),
    );

    if (!isset($no_override['read_when_deleted'])) {
      $field_id = 'rcmfd_read_deleted';
      $input_readdeleted = new html_checkbox(array('name' => '_read_when_deleted', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['read_when_deleted'] = array(
        'title' => html::label($field_id, Q(rcube_label('readwhendeleted'))),
        'content' => $input_readdeleted->show($config['read_when_deleted']?1:0),
      );
    }

    if (!isset($no_override['flag_for_deletion'])) {
      $field_id = 'rcmfd_flag_for_deletion';
      $input_flagfordeletion = new html_checkbox(array('name' => '_flag_for_deletion', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['flag_for_deletion'] = array(
        'title' => html::label($field_id, Q(rcube_label('flagfordeletion'))),
        'content' => $input_flagfordeletion->show($config['flag_for_deletion']?1:0),
      );
    }

    // don't show deleted messages
    if (!isset($no_override['skip_deleted'])) {
      $field_id = 'rcmfd_skip_deleted';
      $input_purge = new html_checkbox(array('name' => '_skip_deleted', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['skip_deleted'] = array(
        'title' => html::label($field_id, Q(rcube_label('skipdeleted'))),
        'content' => $input_purge->show($config['skip_deleted']?1:0),
      );
    }

    // Trash purging on logout
    if (!isset($no_override['logout_purge'])) {
      $field_id = 'rcmfd_logout_purge';
      $input_purge = new html_checkbox(array('name' => '_logout_purge', 'id' => $field_id, 'value' => 1));

      $blocks['maintenance']['options']['logout_purge'] = array(
        'title' => html::label($field_id, Q(rcube_label('logoutclear'))),
        'content' => $input_purge->show($config['logout_purge']?1:0),
      );
    }

    // INBOX compacting on logout
    if (!isset($no_override['logout_expunge'])) {
      $field_id = 'rcmfd_logout_expunge';
      $input_expunge = new html_checkbox(array('name' => '_logout_expunge', 'id' => $field_id, 'value' => 1));

      $blocks['maintenance']['options']['logout_expunge'] = array(
        'title' => html::label($field_id, Q(rcube_label('logoutcompact'))),
        'content' => $input_expunge->show($config['logout_expunge']?1:0),
      );
    }

    break;
  }

  $data = $RCMAIL->plugins->exec_hook('user_preferences', array('section' => $part, 'blocks' => $blocks));

  // create output
  foreach ($data['blocks'] as $idx => $block) {
    if ($block['options']) {
      $table = new html_table(array('cols' => 2));

      foreach ($block['options'] as $option) {
	if ($option['advanced'])
          $table->set_row_attribs('advanced');
	
        $table->add('title', $option['title']);
	$table->add(null, $option['content']);
      }

      $out .= html::tag('fieldset', null, html::tag('legend', null, $block['name']) . $table->show($attrib));
    }
  }

  return $out;
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

function rcmail_prefs_section_name()
{
  global $SECTIONS, $CURR_SECTION;
  
  return $SECTIONS[$CURR_SECTION]['section'];
}


// register UI objects
$OUTPUT->add_handlers(array(
  'userprefs' => 'rcmail_user_prefs_form',
  'sectionname' => 'rcmail_prefs_section_name',
));

$OUTPUT->send('settingsedit');

?>

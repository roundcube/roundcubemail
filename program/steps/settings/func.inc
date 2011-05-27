<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/func.inc                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
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
  global $RCMAIL;

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmsectionslist';

  list($list, $cols) = rcmail_user_prefs();

  // create XHTML table
  $out = rcube_table_output($attrib, $list, $cols, 'id');

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

  // get identities list and define 'mail' column
  $list = $USER->list_identities();
  foreach ($list as $idx => $row)
    $list[$idx]['mail'] = trim($row['name'] . ' <' . rcube_idn_to_utf8($row['email']) .'>');

  // get all identites from DB and define list of cols to be displayed
  $plugin = $RCMAIL->plugins->exec_hook('identities_list', array(
    'list' => $list,
    'cols' => array('mail')));

  // @TODO: use <UL> instead of <TABLE> for identities list
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
	  'name'    => 'form',
	  'method'  => 'post',
	  'task'    => $RCMAIL->task,
	  'action'  => $action,
	  'request' => $request_key,
	  'noclose' => true
	) + $attrib);

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


function rcmail_user_prefs($current=null)
{
  global $RCMAIL;

  $sections['general'] = array('id' => 'general', 'section' => rcube_label('uisettings'));
  $sections['mailbox'] = array('id' => 'mailbox', 'section' => rcube_label('mailboxview'));
  $sections['compose'] = array('id' => 'compose', 'section' => rcube_label('messagescomposition'));
  $sections['mailview'] = array('id' => 'mailview','section' => rcube_label('messagesdisplaying'));
  $sections['addressbook'] = array('id' => 'addressbook','section' => rcube_label('addressbook'));
  $sections['folders'] = array('id' => 'folders', 'section' => rcube_label('specialfolders'));
  $sections['server'] = array('id' => 'server',  'section' => rcube_label('serversettings'));

  // hook + define list cols
  $plugin = $RCMAIL->plugins->exec_hook('preferences_sections_list',
        array('list' => $sections, 'cols' => array('section')));

  $sections = $plugin['list'];

  $config = $RCMAIL->config->all();
  $no_override = array_flip($RCMAIL->config->get('dont_override', array()));

  foreach ($sections as $idx => $sect) {

    if ($current && $sect['id'] != $current)
      continue;

    $blocks = array();

    switch ($sect['id']) {
    // general
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
      $input_preview = new html_checkbox(array('name' => '_preview_pane', 'id' => $field_id, 'value' => 1,
        'onchange' => JS_OBJECT_NAME.'.toggle_preview_pane(this)'));

      $blocks['main']['options']['preview_pane'] = array(
        'title' => html::label($field_id, Q(rcube_label('previewpane'))),
        'content' => $input_preview->show($config['preview_pane']?1:0),
      );
    }

    // show config parameter for preview pane auto mark as read delay
    if (!isset($no_override['preview_pane_mark_read'])) {
      // apply default if config option is not set at all
      $config['preview_pane_mark_read'] = $RCMAIL->config->get('preview_pane_mark_read', 0);

      $field_id = 'rcmfd_preview_pane_mark_read';
      $select_delay = new html_select(array('name' => '_preview_pane_mark_read', 'id' => $field_id,
        'disabled' => $config['preview_pane']?0:1));

      $select_delay->add(rcube_label('never'), '-1');
      $select_delay->add(rcube_label('immediately'), 0);
      foreach(array(5, 10, 20, 30) as $sec)
        $select_delay->add(rcube_label(array('name' => 'afternseconds', 'vars' => array('n' => $sec))), $sec);

      $blocks['main']['options']['preview_pane_mark_read'] = array(
        'title' => html::label($field_id, Q(rcube_label('previewpanemarkread'))),
        'content' => $select_delay->show(intval($config['preview_pane_mark_read'])),
      );
    }

    if (!isset($no_override['mdn_requests'])) {
      $field_id = 'rcmfd_mdn_requests';
      $select_mdn_requests = new html_select(array('name' => '_mdn_requests', 'id' => $field_id));
      $select_mdn_requests->add(rcube_label('askuser'), 0);
      $select_mdn_requests->add(rcube_label('autosend'), 1);
      $select_mdn_requests->add(rcube_label('autosendknown'), 3);
      $select_mdn_requests->add(rcube_label('autosendknownignore'), 4);
      $select_mdn_requests->add(rcube_label('ignore'), 2);

      $blocks['main']['options']['mdn_requests'] = array(
        'title' => html::label($field_id, Q(rcube_label('mdnrequests'))),
        'content' => $select_mdn_requests->show($config['mdn_requests']),
      );
    }

    $RCMAIL->imap_connect();
    $threading_supported = $RCMAIL->imap->get_capability('THREAD');

    if (!isset($no_override['autoexpand_threads']) && $threading_supported) {
      $field_id = 'rcmfd_autoexpand_threads';
      $select_autoexpand_threads = new html_select(array('name' => '_autoexpand_threads', 'id' => $field_id));
      $select_autoexpand_threads->add(rcube_label('never'), 0);
      $select_autoexpand_threads->add(rcube_label('do_expand'), 1);
      $select_autoexpand_threads->add(rcube_label('expand_only_unread'), 2);

      $blocks['main']['options']['autoexpand_threads'] = array(
        'title' => html::label($field_id, Q(rcube_label('autoexpand_threads'))),
        'content' => $select_autoexpand_threads->show($config['autoexpand_threads']),
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

    if (!isset($no_override['default_charset'])) {
      $field_id = 'rcmfd_default_charset';

      $blocks['main']['options']['default_charset'] = array(
        'title' => html::label($field_id, Q(rcube_label('defaultcharset'))),
        'content' => $RCMAIL->output->charset_selector(array(
	  'name' => '_default_charset', 'selected' => $config['default_charset']
	  ))
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
      'sig' => array('name' => Q(rcube_label('signatureoptions'))),
    );

    // Show checkbox for HTML Editor
    if (!isset($no_override['htmleditor'])) {
      $field_id = 'rcmfd_htmleditor';
      $select_htmleditor = new html_select(array('name' => '_htmleditor', 'id' => $field_id));
      $select_htmleditor->add(rcube_label('never'), 0);
      $select_htmleditor->add(rcube_label('always'), 1);
      $select_htmleditor->add(rcube_label('htmlonreply'), 2);

      $blocks['main']['options']['htmleditor'] = array(
        'title' => html::label($field_id, Q(rcube_label('htmleditor'))),
        'content' => $select_htmleditor->show(intval($config['htmleditor'])),
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

    if (!isset($no_override['force_7bit'])) {
      $field_id = 'rcmfd_force_7bit';
      $input_7bit = new html_checkbox(array('name' => '_force_7bit', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['force_7bit'] = array(
        'title' => html::label($field_id, Q(rcube_label('force7bit'))),
        'content' => $input_7bit->show($config['force_7bit']?1:0),
      );
    }

    if (!isset($no_override['mdn_default'])) {
      $field_id = 'rcmfd_mdn_default';
      $input_mdn = new html_checkbox(array('name' => '_mdn_default', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['mdn_default'] = array(
        'title' => html::label($field_id, Q(rcube_label('reqmdn'))),
        'content' => $input_mdn->show($config['mdn_default']?1:0),
      );
    }

    if (!isset($no_override['dsn_default'])) {
      $field_id = 'rcmfd_dsn_default';
      $input_dsn = new html_checkbox(array('name' => '_dsn_default', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['dsn_default'] = array(
        'title' => html::label($field_id, Q(rcube_label('reqdsn'))),
        'content' => $input_dsn->show($config['dsn_default']?1:0),
      );
    }

    if (!isset($no_override['reply_same_folder'])) {
      $field_id = 'rcmfd_reply_same_folder';
      $input_reply_same_folder = new html_checkbox(array('name' => '_reply_same_folder', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['reply_same_folder'] = array(
        'title' => html::label($field_id, Q(rcube_label('replysamefolder'))),
        'content' => $input_reply_same_folder->show($config['reply_same_folder']?1:0),
      );
    }

    if (!isset($no_override['top_posting'])) {
      $field_id = 'rcmfd_top_posting';
      $select_replymode = new html_select(array('name' => '_top_posting', 'id' => $field_id, 'onchange' => "\$('#rcmfd_sig_above').attr('disabled',this.selectedIndex==0)"));
      $select_replymode->add(rcube_label('replybottomposting'), 0);
      $select_replymode->add(rcube_label('replytopposting'), 1);

      $blocks['main']['options']['top_posting'] = array(
        'title' => html::label($field_id, Q(rcube_label('whenreplying'))),
        'content' => $select_replymode->show($config['top_posting']?1:0),
      );
    }

    if (!isset($no_override['show_sig'])) {
      $field_id = 'rcmfd_show_sig';
      $select_show_sig = new html_select(array('name' => '_show_sig', 'id' => $field_id));
      $select_show_sig->add(rcube_label('never'), 0);
      $select_show_sig->add(rcube_label('always'), 1);
      $select_show_sig->add(rcube_label('newmessageonly'), 2);
      $select_show_sig->add(rcube_label('replyandforwardonly'), 3);

      $blocks['sig']['options']['show_sig'] = array(
        'title' => html::label($field_id, Q(rcube_label('autoaddsignature'))),
        'content' => $select_show_sig->show($RCMAIL->config->get('show_sig', 1)),
      );
    }

    if (!isset($no_override['sig_above'])) {
      $field_id = 'rcmfd_sig_above';
      $select_sigabove = new html_select(array('name' => '_sig_above', 'id' => $field_id, 'disabled' => !$config['top_posting']));
      $select_sigabove->add(rcube_label('belowquote'), 0);
      $select_sigabove->add(rcube_label('abovequote'), 1);

      $blocks['sig']['options']['sig_above'] = array(
        'title' => html::label($field_id, Q(rcube_label('replysignaturepos'))),
        'content' => $select_sigabove->show($config['sig_above']?1:0),
      );
    }

    if (!isset($no_override['strip_existing_sig'])) {
      $field_id = 'rcmfd_strip_existing_sig';
      $input_stripexistingsig = new html_checkbox(array('name' => '_strip_existing_sig', 'id' => $field_id, 'value' => 1));

      $blocks['sig']['options']['strip_existing_sig'] = array(
        'title' => html::label($field_id, Q(rcube_label('replyremovesignature'))),
        'content' => $input_stripexistingsig->show($config['strip_existing_sig']?1:0),
      );
    }

    break;


    // Addressbook config
    case 'addressbook':

    $blocks = array(
      'main' => array('name' => Q(rcube_label('mainoptions'))),
    );

    if (!isset($no_override['default_addressbook'])
      && ($books = $RCMAIL->get_address_sources(true))
    ) {
      $field_id = 'rcmfd_default_addressbook';
      $select_abook = new html_select(array('name' => '_default_addressbook', 'id' => $field_id));

      foreach ($books as $book) {
        $select_abook->add($book['name'], $book['id']);
      }

      $blocks['main']['options']['default_addressbook'] = array(
        'title' => html::label($field_id, Q(rcube_label('defaultaddressbook'))),
        'content' => $select_abook->show($config['default_addressbook']),
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

      $RCMAIL->imap_connect();

      // load folders list only when needed
      if ($current) {
        $select = rcmail_mailbox_select(array('noselection' => '---', 'realnames' => true,
          'maxlength' => 30, 'exceptions' => array('INBOX')));
      }
      else // dummy select
        $select = new html_select();

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

    if (!isset($no_override['delete_always'])) {
      $field_id = 'rcmfd_delete_always';
      $input_delete_always = new html_checkbox(array('name' => '_delete_always', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['delete_always'] = array(
        'title' => html::label($field_id, Q(rcube_label('deletealways'))),
        'content' => $input_delete_always->show($config['delete_always']?1:0),
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

    $data = $RCMAIL->plugins->exec_hook('preferences_list', array('section' => $sect['id'], 'blocks' => $blocks));
    $found = false;

    // create output
    foreach ($data['blocks'] as $block) {
      if (!empty($block['content']) || !empty($block['options'])) {
        $found = true;
	    break;
      }
    }

    if (!$found)
      unset($sections[$idx]);
    else
      $sections[$idx]['blocks'] = $data['blocks'];
  }

  return array($sections, $plugin['cols']);
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
    if (!preg_match('/^\./', $file) && is_dir($filename) && is_readable($filename))
      $skins[] = $file;
  }

  closedir($dir);

  return $skins;
}


function rcmail_folder_options($mailbox)
{
    global $RCMAIL;

    $acl             = $RCMAIL->imap->get_capability('ACL');
    $default_folders = (array) $RCMAIL->config->get('default_imap_folders');
    $options         = array();

    $options['name']      = $mailbox;
    $options['options']   = $RCMAIL->imap->mailbox_options($mailbox, true);
    $options['namespace'] = $RCMAIL->imap->mailbox_namespace($mailbox);
    $options['rights']    = $acl ? (array)$RCMAIL->imap->my_rights($mailbox) : array();
    $options['special']   = in_array($mailbox, $default_folders);
    $options['protected'] = $options['special'] && $RCMAIL->config->get('protect_default_folders');

    if (is_array($options['options'])) {
        foreach ($options['options'] as $opt) {
            $opt = strtolower($opt);
            if ($opt == '\noselect' || $opt == '\nonexistent') {
                $options['noselect'] = true;
            }
        }
    }
    else {
        $options['noselect'] = true;
    }

    if (!empty($options['rights'])) {
        $options['norename'] = !in_array('x', $options['rights']) &&
            (!in_array('c', $options['rights']) || !in_array('d', $options['rights']));
        if (!$options['noselect']) {
            $options['noselect'] = !in_array('r', $options['rights']);
        }
    }

    return $options;    
}

// Updates (or creates) folder row in the subscriptions table
function rcmail_update_folder_row($name, $oldname=null)
{
    global $IMAP, $CONFIG, $OUTPUT;

    $delimiter    = $IMAP->get_hierarchy_delimiter();
    $name_utf8    = rcube_charset_convert($name, 'UTF7-IMAP');
    $protected    = ($CONFIG['protect_default_folders'] == true && in_array($name, $CONFIG['default_imap_folders']));

    $foldersplit  = explode($delimiter, $IMAP->mod_mailbox($name));
    $level        = count($foldersplit) - 1;
    $display_name = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level)
        . Q($protected ? rcmail_localize_foldername($name) : rcube_charset_convert($foldersplit[$level], 'UTF7-IMAP'));

    if ($oldname === null)
        $OUTPUT->command('add_folder_row', $name_utf8, $display_name, $protected, true);
    else
        $OUTPUT->command('replace_folder_row', rcube_charset_convert($oldname, 'UTF7-IMAP'),
            $name_utf8, $display_name, $protected);
}


// register UI objects
$OUTPUT->add_handlers(array(
  'prefsframe' => 'rcmail_preferences_frame',
  'sectionslist' => 'rcmail_sections_list',
  'identitieslist' => 'rcmail_identities_list',
));

// register action aliases
$RCMAIL->register_action_map(array(
    'folders'       => 'folders.inc',
    'rename-folder' => 'folders.inc',
    'delete-folder' => 'folders.inc',
    'subscribe'     => 'folders.inc',
    'unsubscribe'   => 'folders.inc',
    'purge'         => 'folders.inc',
    'folder-size'   => 'folders.inc',
    'add-identity'  => 'edit_identity.inc',
));

<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/func.inc                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality for user's settings & preferences             |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

if (!$OUTPUT->ajax_call)
  $OUTPUT->set_pagetitle(rcube_label('preferences'));


// similar function as /steps/settings/identities.inc::rcmail_identity_frame()
function rcmail_preferences_frame($attrib)
{
  global $OUTPUT;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmprefsframe';

  return $OUTPUT->frame($attrib, true);
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
  global $OUTPUT, $RCMAIL;

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmIdentitiesList';

  // get identities list and define 'mail' column
  $list = $RCMAIL->user->list_identities();
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
  $sections['mailview'] = array('id' => 'mailview','section' => rcube_label('messagesdisplaying'));
  $sections['compose'] = array('id' => 'compose', 'section' => rcube_label('messagescomposition'));
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
      'main'    => array('name' => Q(rcube_label('mainoptions'))),
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

    // timezone selection
    if (!isset($no_override['timezone'])) {
      $field_id = 'rcmfd_timezone';
      $select_timezone = new html_select(array('name' => '_timezone', 'id' => $field_id));
      $select_timezone->add(rcube_label('autodetect'), 'auto');

      $zones = array();
      foreach (DateTimeZone::listIdentifiers() as $i => $tzs) {
        try {
          $tz = new DateTimeZone($tzs);
          $date = new DateTime('2012-12-21', $tz);
          $offset = $date->format('Z') + 45000;
          $sortkey = sprintf('%06d.%s', $offset, $tzs);
          $zones[$sortkey] = array($tzs, $date->format('P'));
        }
        catch (Exception $e) {}
      }

      ksort($zones);
      foreach ($zones as $zone) {
        list($tzs, $offset) = $zone;
        $select_timezone->add('(GMT ' . $offset . ') ' . strtr($tzs, '_', ' '), $tzs);
      }

      $blocks['main']['options']['timezone'] = array(
        'title' => html::label($field_id, Q(rcube_label('timezone'))),
        'content' => $select_timezone->show((string)$config['timezone']),
      );
    }

    // date/time formatting
    if (!isset($no_override['time_format'])) {
      $reftime = mktime(7,30,0);
      $field_id = 'rcmfd_time_format';
      $select_time = new html_select(array('name' => '_time_format', 'id' => $field_id));
      foreach ((array)$RCMAIL->config->get('time_formats', array('G:i', 'H:i', 'g:i a', 'h:i A')) as $choice)
        $select_time->add(date($choice, $reftime), $choice);

      $blocks['main']['options']['time_format'] = array(
        'title' => html::label($field_id, Q(rcube_label('timeformat'))),
        'content' => $select_time->show($RCMAIL->config->get('time_format')),
      );
    }

    if (!isset($no_override['date_format'])) {
      $refdate = mktime(12,30,0,7,24);
      $field_id = 'rcmfd_date_format';
      $select_date = new html_select(array('name' => '_date_format', 'id' => $field_id));
      foreach ((array)$RCMAIL->config->get('date_formats', array('Y-m-d','d-m-Y','Y/m/d','m/d/Y','d/m/Y','d.m.Y','j.n.Y')) as $choice)
        $select_date->add(date($choice, $refdate), $choice);

      $blocks['main']['options']['date_format'] = array(
        'title' => html::label($field_id, Q(rcube_label('dateformat'))),
        'content' => $select_date->show($config['date_format']),
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

    if (!isset($no_override['refresh_interval'])) {
      $field_id = 'rcmfd_refresh_interval';
      $select_refresh_interval = new html_select(array('name' => '_refresh_interval', 'id' => $field_id));

      $select_refresh_interval->add(rcube_label('never'), 0);
      foreach (array(1, 3, 5, 10, 15, 30, 60) as $min) {
        if (!$config['min_refresh_interval'] || $config['min_refresh_interval'] <= $min * 60) {
          $label = rcube_label(array('name' => 'everynminutes', 'vars' => array('n' => $min)));
          $select_refresh_interval->add($label, $min);
        }
      }

      $blocks['main']['options']['refresh_interval'] = array(
        'title' => html::label($field_id, Q(rcube_label('refreshinterval'))),
        'content' => $select_refresh_interval->show($config['refresh_interval']/60),
      );
    }

    // show drop-down for available skins
    if (!isset($no_override['skin'])) {
      $skins = rcmail_get_skins();

      if (count($skins) > 1) {
        $field_id = 'rcmfd_skin';
        $input_skin = new html_radiobutton(array('name'=>'_skin'));

        $blocks['skin'] = array('name' => Q(rcube_label('skin')),);

        foreach($skins as $skin) {
          $thumbnail = "./skins/$skin/thumbnail.png";
          if (!is_file($thumbnail))
            $thumbnail = './program/resources/blank.gif';

          $skinname = ucfirst($skin);
          $author_link = $license_link = '';
          $meta = @json_decode(@file_get_contents("./skins/$skin/meta.json"), true);
          if (is_array($meta) && $meta['name']) {
            $skinname = $meta['name'];
            $author_link  = $meta['url'] ? html::a(array('href' => $meta['url'], 'target' => '_blank'), Q($meta['author'])) : Q($meta['author']);
            $license_link = $meta['license-url'] ? html::a(array('href' => $meta['license-url'], 'target' => '_blank'), Q($meta['license'])) : Q($meta['license']);
          }

          $blocks['skin']['options'][$skin]['content'] = html::label(array('class' => 'skinselection'),
            html::span('skinitem', $input_skin->show($config['skin'], array('value' => $skin, 'id' => $field_id.$skin))) .
            html::span('skinitem', html::img(array('src' => $thumbnail, 'class' => 'skinthumbnail', 'alt' => $skin, 'width' => 64, 'height' => 64))) .
            html::span('skinitem', html::span('skinname', Q($skinname)) . html::br() .
              html::span('skinauthor', $author_link ? 'by ' . $author_link : '') . html::br() .
              html::span('skinlicense', $license_link ? rcube_label('license').':&nbsp;' . $license_link : ''))
          );
        }
      }
    }

    $product_name = $RCMAIL->config->get('product_name', 'Roundcube Webmail');
    $RCMAIL->output->add_script(sprintf("%s.check_protocol_handler('%s', '#mailtoprotohandler');",
      JS_OBJECT_NAME, JQ($product_name)), 'foot');

    $blocks['browser'] = array(
      'name' => Q(rcube_label('browseroptions')),
      'options' => array('mailtoprotohandler' => array(
        'content' => html::a(array(
          'href' => '#',
          'id' => 'mailtoprotohandler'), Q(rcube_label('mailtoprotohandler'))),
        )),
    );

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
        'onchange' => "$('#rcmfd_preview_pane_mark_read').prop('disabled', !this.checked)"));

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

    $storage             = $RCMAIL->get_storage();
    $threading_supported = $storage->get_capability('THREAD');

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

    // show page size selection
    if (!isset($no_override['mail_pagesize'])) {
      $field_id = 'rcmfd_mail_pagesize';
      $input_pagesize = new html_inputfield(array('name' => '_mail_pagesize', 'id' => $field_id, 'size' => 5));

      $size = intval($config['mail_pagesize'] ? $config['mail_pagesize'] : $config['pagesize']);

      $blocks['main']['options']['pagesize'] = array(
        'title' => html::label($field_id, Q(rcube_label('pagesize'))),
        'content' => $input_pagesize->show($size ? $size : 50),
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

    // show checkbox to open message view in new window
    if (!isset($no_override['message_extwin'])) {
      $field_id = 'rcmfd_message_extwin';
      $input_msgextwin = new html_checkbox(array('name' => '_message_extwin', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['message_extwin'] = array(
        'title' => html::label($field_id, Q(rcube_label('showinextwin'))),
        'content' => $input_msgextwin->show($config['message_extwin']?1:0),
      );
    }

    // show checkbox for HTML/plaintext messages
    if (!isset($no_override['prefer_html'])) {
      $field_id = 'rcmfd_htmlmsg';
      $input_preferhtml = new html_checkbox(array('name' => '_prefer_html', 'id' => $field_id, 'value' => 1,
        'onchange' => "$('#rcmfd_show_images').prop('disabled', !this.checked).val(0)"));

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
      $input_show_images = new html_select(array('name' => '_show_images', 'id' => $field_id,
        'disabled' => !$config['prefer_html']));
      $input_show_images->add(rcube_label('never'), 0);
      $input_show_images->add(rcube_label('fromknownsenders'), 1);
      $input_show_images->add(rcube_label('always'), 2);

      $blocks['main']['options']['show_images'] = array(
        'title' => html::label($field_id, Q(rcube_label('showremoteimages'))),
        'content' => $input_show_images->show($config['prefer_html'] ? $config['show_images'] : 0),
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
      'main'       => array('name' => Q(rcube_label('mainoptions'))),
      'sig'        => array('name' => Q(rcube_label('signatureoptions'))),
      'spellcheck' => array('name' => Q(rcube_label('spellcheckoptions'))),
    );

    // show checkbox to compose messages in a new window
    if (!isset($no_override['compose_extwin'])) {
      $field_id = 'rcmfdcompose_extwin';
      $input_compextwin = new html_checkbox(array('name' => '_compose_extwin', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['compose_extwin'] = array(
        'title' => html::label($field_id, Q(rcube_label('composeextwin'))),
        'content' => $input_compextwin->show($config['compose_extwin']?1:0),
      );
    }

    if (!isset($no_override['htmleditor'])) {
      $field_id = 'rcmfd_htmleditor';
      $select_htmleditor = new html_select(array('name' => '_htmleditor', 'id' => $field_id));
      $select_htmleditor->add(rcube_label('never'), 0);
      $select_htmleditor->add(rcube_label('always'), 1);
      $select_htmleditor->add(rcube_label('htmlonreply'), 2);
      $select_htmleditor->add(rcube_label('htmlonreplyandforward'), 3);

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

    if (!isset($no_override['reply_mode'])) {
      $field_id = 'rcmfd_reply_mode';
      $select_replymode = new html_select(array('name' => '_reply_mode', 'id' => $field_id));
      $select_replymode->add(rcube_label('replyempty'), -1);
      $select_replymode->add(rcube_label('replybottomposting'), 0);
      $select_replymode->add(rcube_label('replytopposting'), 1);

      $blocks['main']['options']['reply_mode'] = array(
        'title' => html::label($field_id, Q(rcube_label('whenreplying'))),
        'content' => $select_replymode->show(intval($config['reply_mode'])),
      );
    }

    if (!isset($no_override['spellcheck_before_send']) && $config['enable_spellcheck']) {
      $field_id = 'rcmfd_spellcheck_before_send';
      $input_spellcheck = new html_checkbox(array('name' => '_spellcheck_before_send', 'id' => $field_id, 'value' => 1));

      $blocks['spellcheck']['options']['spellcheck_before_send'] = array(
        'title' => html::label($field_id, Q(rcube_label('spellcheckbeforesend'))),
        'content' => $input_spellcheck->show($config['spellcheck_before_send']?1:0),
      );
    }

    if ($config['enable_spellcheck']) {
      foreach (array('syms', 'nums', 'caps') as $key) {
        $key = 'spellcheck_ignore_'.$key;
        if (!isset($no_override[$key])) {
          $input_spellcheck = new html_checkbox(array('name' => '_'.$key, 'id' => 'rcmfd_'.$key, 'value' => 1));

          $blocks['spellcheck']['options'][$key] = array(
            'title' => html::label($field_id, Q(rcube_label(str_replace('_', '', $key)))),
            'content' => $input_spellcheck->show($config[$key]?1:0),
          );
        }
      }
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

    if (!isset($no_override['strip_existing_sig'])) {
      $field_id = 'rcmfd_strip_existing_sig';
      $input_stripexistingsig = new html_checkbox(array('name' => '_strip_existing_sig', 'id' => $field_id, 'value' => 1));

      $blocks['sig']['options']['strip_existing_sig'] = array(
        'title' => html::label($field_id, Q(rcube_label('replyremovesignature'))),
        'content' => $input_stripexistingsig->show($config['strip_existing_sig']?1:0),
      );
    }

    if (!isset($no_override['forward_attachment'])) {
      $field_id = 'rcmfd_forward_attachment';
      $select = new html_select(array('name' => '_forward_attachment', 'id' => $field_id));
      $select->add(rcube_label('inline'), 0);
      $select->add(rcube_label('asattachment'), 1);

      $blocks['main']['options']['forward_attachment'] = array(
        'title' => html::label($field_id, Q(rcube_label('forwardmode'))),
        'content' => $select->show(intval($config['forward_attachment'])),
      );
    }

    if (!isset($no_override['default_font'])) {
      $field_id = 'rcmfd_default_font';
      $fonts    = rcube_fontdefs();
      $selected = $config['default_font'];

      $select = '<select name="_default_font" id="'.$field_id.'">';
      $select .= '<option value=""' . (!$selected ? ' selected="selected"' : '') . '>---</option>';
      foreach ($fonts as $fname => $font)
        $select .= '<option value="'.$fname.'"'
          . ($fname == $selected ? ' selected="selected"' : '')
          . ' style=\'font-family: ' . $font . '\'>'
          . Q($fname) . '</option>';
      $select .= '</select>';

      $blocks['main']['options']['default_font'] = array(
        'title' => html::label($field_id, Q(rcube_label('defaultfont'))),
        'content' => $select
      );
    }

    break;


    // Addressbook config
    case 'addressbook':

    $blocks = array(
      'main' => array('name' => Q(rcube_label('mainoptions'))),
    );

    if (!isset($no_override['default_addressbook'])
      && ($books = $RCMAIL->get_address_sources(true, true))
    ) {
      $field_id = 'rcmfd_default_addressbook';
      $select_abook = new html_select(array('name' => '_default_addressbook', 'id' => $field_id));

      foreach ($books as $book) {
        $select_abook->add(html_entity_decode($book['name'], ENT_COMPAT, 'UTF-8'), $book['id']);
      }

      $blocks['main']['options']['default_addressbook'] = array(
        'title' => html::label($field_id, Q(rcube_label('defaultabook'))),
        'content' => $select_abook->show($config['default_addressbook']),
      );
    }

    // show addressbook listing mode selection
    if (!isset($no_override['addressbook_name_listing'])) {
      $field_id = 'rcmfd_addressbook_name_listing';
      $select_listing = new html_select(array('name' => '_addressbook_name_listing', 'id' => $field_id));
      $select_listing->add(rcube_label('name'), 0);
      $select_listing->add(rcube_label('firstname') . ' '  . rcube_label('surname'), 1);
      $select_listing->add(rcube_label('surname')   . ' '  . rcube_label('firstname'), 2);
      $select_listing->add(rcube_label('surname')   . ', ' . rcube_label('firstname'), 3);

      $blocks['main']['options']['list_name_listing'] = array(
        'title' => html::label($field_id, Q(rcube_label('listnamedisplay'))),
        'content' => $select_listing->show($config['addressbook_name_listing']),
      );
    }

    // show addressbook sort column
    if (!isset($no_override['addressbook_sort_col'])) {
      $field_id = 'rcmfd_addressbook_sort_col';
      $select_sort = new html_select(array('name' => '_addressbook_sort_col', 'id' => $field_id));
      $select_sort->add(rcube_label('name'), 'name');
      $select_sort->add(rcube_label('firstname'), 'firstname');
      $select_sort->add(rcube_label('surname'), 'surname');

      $blocks['main']['options']['sort_col'] = array(
        'title' => html::label($field_id, Q(rcube_label('listsorting'))),
        'content' => $select_sort->show($config['addressbook_sort_col']),
      );
    }

    // show addressbook page size selection
    if (!isset($no_override['addressbook_pagesize'])) {
      $field_id = 'rcmfd_addressbook_pagesize';
      $input_pagesize = new html_inputfield(array('name' => '_addressbook_pagesize', 'id' => $field_id, 'size' => 5));

      $size = intval($config['addressbook_pagesize'] ? $config['addressbook_pagesize'] : $config['pagesize']);

      $blocks['main']['options']['pagesize'] = array(
        'title' => html::label($field_id, Q(rcube_label('pagesize'))),
        'content' => $input_pagesize->show($size ? $size : 50),
      );
    }

    if (!isset($no_override['autocomplete_single'])) {
      $field_id = 'rcmfd_autocomplete_single';
      $checkbox = new html_checkbox(array('name' => '_autocomplete_single', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['autocomplete_single'] = array(
        'title' => html::label($field_id, Q(rcube_label('autocompletesingle'))),
        'content' => $checkbox->show($config['autocomplete_single']?1:0),
      );
    }

    break;

    // Special IMAP folders
    case 'folders':

    $blocks = array(
      'main' => array('name' => Q(rcube_label('mainoptions'))),
    );

    // Configure special folders
    if (!isset($no_override['default_folders'])) {
      // load folders list only when needed
      if ($current) {
        $select = rcmail_mailbox_select(array(
          'noselection'   => '---',
          'realnames'     => true,
          'maxlength'     => 30,
          'folder_filter' => 'mail',
          'folder_rights' => 'w',
          // #1486114, #1488279
          'onchange'      => "if ($(this).val() == 'INBOX') $(this).val('')",
        ));
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

    if (!isset($no_override['delete_junk'])) {
      $field_id = 'rcmfd_delete_junk';
      $input_delete_junk = new html_checkbox(array('name' => '_delete_junk', 'id' => $field_id, 'value' => 1));

      $blocks['main']['options']['delete_junk'] = array(
        'title' => html::label($field_id, Q(rcube_label('deletejunk'))),
        'content' => $input_delete_junk->show($config['delete_junk']?1:0),
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

    $options = $RCMAIL->get_storage()->folder_info($mailbox);
    $options['protected'] = $options['is_root'] || ($options['special'] && $RCMAIL->config->get('protect_default_folders'));

    return $options;
}

/**
 * Updates (or creates) folder row in the subscriptions table
 *
 * @param string $name      Folder name
 * @param string $oldname   Old folder name (for update)
 * @param bool   $subscribe Checks subscription checkbox
 * @param string $class     CSS class name for folder row
 */
function rcmail_update_folder_row($name, $oldname=null, $subscribe=false, $class_name=null)
{
    global $RCMAIL, $OUTPUT;

    $default_folders = (array) $RCMAIL->config->get('default_folders');
    $protect_folders = $RCMAIL->config->get('protect_default_folders');

    $storage      = $RCMAIL->get_storage();
    $delimiter    = $storage->get_hierarchy_delimiter();
    $name_utf8    = rcube_charset_convert($name, 'UTF7-IMAP');
    $protected    = $protect_folders && in_array($name, $default_folders);

    $foldersplit  = explode($delimiter, $storage->mod_folder($name));
    $level        = count($foldersplit) - 1;
    $display_name = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level)
        . Q($protected ? rcmail_localize_foldername($name) : rcube_charset_convert($foldersplit[$level], 'UTF7-IMAP'));

    if ($oldname === null)
        $OUTPUT->command('add_folder_row', $name_utf8, $display_name, $protected, $subscribe,
            false, $class_name);
    else
        $OUTPUT->command('replace_folder_row', rcube_charset_convert($oldname, 'UTF7-IMAP'),
            $name_utf8, $display_name, $protected, $class_name);
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

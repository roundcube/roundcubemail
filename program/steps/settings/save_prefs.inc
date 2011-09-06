<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/save_prefs.inc                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Save user preferences to DB and to the current session              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$CURR_SECTION = get_input_value('_section', RCUBE_INPUT_POST);

$a_user_prefs = array();

// set options for specified section
switch ($CURR_SECTION)
{
  case 'general':
    $a_user_prefs = array(
      'language'     => isset($_POST['_language']) ? get_input_value('_language', RCUBE_INPUT_POST) : $CONFIG['language'],
      'timezone'     => isset($_POST['_timezone']) ? (is_numeric($_POST['_timezone']) ? floatval($_POST['_timezone']) : get_input_value('_timezone', RCUBE_INPUT_POST)) : $CONFIG['timezone'],
      'dst_active'   => isset($_POST['_dst_active']) ? TRUE : FALSE,
      'pagesize'     => is_numeric($_POST['_pagesize']) ? max(2, intval($_POST['_pagesize'])) : $CONFIG['pagesize'],
      'prettydate'   => isset($_POST['_pretty_date']) ? TRUE : FALSE,
      'skin' 	     => isset($_POST['_skin']) ? get_input_value('_skin', RCUBE_INPUT_POST) : $CONFIG['skin'],
    );

  break;

  case 'mailbox':
    $a_user_prefs = array(
      'preview_pane'         => isset($_POST['_preview_pane']) ? TRUE : FALSE,
      'preview_pane_mark_read' => isset($_POST['_preview_pane_mark_read']) ? intval($_POST['_preview_pane_mark_read']) : $CONFIG['preview_pane_mark_read'],
      'autoexpand_threads'   => isset($_POST['_autoexpand_threads']) ? intval($_POST['_autoexpand_threads']) : 0,
      'mdn_requests'         => isset($_POST['_mdn_requests']) ? intval($_POST['_mdn_requests']) : 0,
      'keep_alive'           => isset($_POST['_keep_alive']) ? intval($_POST['_keep_alive'])*60 : $CONFIG['keep_alive'],
      'check_all_folders'    => isset($_POST['_check_all_folders']) ? TRUE : FALSE,
    );

  break;

  case 'mailview':
    $a_user_prefs = array(
      'prefer_html'     => isset($_POST['_prefer_html']) ? TRUE : FALSE,
      'inline_images'   => isset($_POST['_inline_images']) ? TRUE : FALSE,
      'show_images'     => isset($_POST['_show_images']) ? intval($_POST['_show_images']) : 0,
      'display_next'    => isset($_POST['_display_next']) ? TRUE : FALSE,
      'default_charset' => get_input_value('_default_charset', RCUBE_INPUT_POST),
    );

  break;

  case 'compose':
    $a_user_prefs = array(
      'htmleditor'         => intval($_POST['_htmleditor']),
      'draft_autosave'     => isset($_POST['_draft_autosave']) ? intval($_POST['_draft_autosave']) : 0,
      'mime_param_folding' => isset($_POST['_mime_param_folding']) ? intval($_POST['_mime_param_folding']) : 0,
      'force_7bit'         => isset($_POST['_force_7bit']) ? TRUE : FALSE,
      'mdn_default'        => isset($_POST['_mdn_default']) ? TRUE : FALSE,
      'dsn_default'        => isset($_POST['_dsn_default']) ? TRUE : FALSE,
      'reply_same_folder'  => isset($_POST['_reply_same_folder']) ? TRUE : FALSE,
      'spellcheck_before_send' => isset($_POST['_spellcheck_before_send']) ? TRUE : FALSE,
      'spellcheck_ignore_syms' => isset($_POST['_spellcheck_ignore_syms']) ? TRUE : FALSE,
      'spellcheck_ignore_nums' => isset($_POST['_spellcheck_ignore_nums']) ? TRUE : FALSE,
      'spellcheck_ignore_caps' => isset($_POST['_spellcheck_ignore_caps']) ? TRUE : FALSE,
      'show_sig'           => isset($_POST['_show_sig']) ? intval($_POST['_show_sig']) : 1,
      'top_posting'        => !empty($_POST['_top_posting']),
      'strip_existing_sig' => isset($_POST['_strip_existing_sig']),
      'sig_above'          => !empty($_POST['_sig_above']) && !empty($_POST['_top_posting']),
    );

  break;

  case 'addressbook':
    $a_user_prefs = array(
      'default_addressbook' => get_input_value('_default_addressbook', RCUBE_INPUT_POST, true),
    );

  break;

  case 'server':
    $a_user_prefs = array(
      'read_when_deleted' => isset($_POST['_read_when_deleted']) ? TRUE : FALSE,
      'skip_deleted'      => isset($_POST['_skip_deleted']) ? TRUE : FALSE,
      'flag_for_deletion' => isset($_POST['_flag_for_deletion']) ? TRUE : FALSE,
      'delete_always' 	  => isset($_POST['_delete_always']) ? TRUE : FALSE,
      'logout_purge'      => isset($_POST['_logout_purge']) ? TRUE : FALSE,
      'logout_expunge'    => isset($_POST['_logout_expunge']) ? TRUE : FALSE,
    );

  break;

  case 'folders':
    $a_user_prefs = array(
      'drafts_mbox' => get_input_value('_drafts_mbox', RCUBE_INPUT_POST, true),
      'sent_mbox'   => get_input_value('_sent_mbox', RCUBE_INPUT_POST, true),
      'junk_mbox'   => get_input_value('_junk_mbox', RCUBE_INPUT_POST, true),
      'trash_mbox'  => get_input_value('_trash_mbox', RCUBE_INPUT_POST, true),
    );

  break;
}

$plugin = rcmail::get_instance()->plugins->exec_hook('preferences_save',
  array('prefs' => $a_user_prefs, 'section' => $CURR_SECTION));

$a_user_prefs = $plugin['prefs'];

// don't override these parameters
foreach ((array)$CONFIG['dont_override'] as $p)
  $a_user_prefs[$p] = $CONFIG[$p];


// verify some options
switch ($CURR_SECTION)
{
  case 'general':

    // switch UI language
    if (isset($_POST['_language']) && $a_user_prefs['language'] != $_SESSION['language']) {
      $RCMAIL->load_language($a_user_prefs['language']);
      $OUTPUT->command('reload', 500);
    }

    // switch skin (if valid, otherwise unset the pref and fall back to default)
    if (!$OUTPUT->set_skin($a_user_prefs['skin']))
      unset($a_user_prefs['skin']);

    // force min size
    if ($a_user_prefs['pagesize'] < 1)
      $a_user_prefs['pagesize'] = 10;

    if (isset($CONFIG['max_pagesize']) && ($a_user_prefs['pagesize'] > $CONFIG['max_pagesize']))
      $a_user_prefs['pagesize'] = (int) $CONFIG['max_pagesize'];

    $a_user_prefs['timezone'] = (string) $a_user_prefs['timezone'];

  break;
  case 'mailbox':

    // force keep_alive
    if (isset($a_user_prefs['keep_alive'])) {
      $a_user_prefs['keep_alive'] = max(60, $CONFIG['min_keep_alive'], $a_user_prefs['keep_alive']);
      if (!empty($CONFIG['session_lifetime']))
        $a_user_prefs['keep_alive'] = min($CONFIG['session_lifetime']*60, $a_user_prefs['keep_alive']);
    }

  break;
  case 'folders':

    // special handling for 'default_imap_folders'
    if (in_array('default_imap_folders', (array)$CONFIG['dont_override'])) {
      foreach (array('drafts_mbox','sent_mbox','junk_mbox','trash_mbox') as $p)
        $a_user_prefs[$p] = $CONFIG[$p];
    } else {
      $a_user_prefs['default_imap_folders'] = array('INBOX');
      foreach (array('drafts_mbox','sent_mbox','junk_mbox','trash_mbox') as $p) {
        if ($a_user_prefs[$p])
          $a_user_prefs['default_imap_folders'][] = $a_user_prefs[$p];
      }
    }

  break;
}

// Save preferences
if (!$plugin['abort'])
  $saved = $USER->save_prefs($a_user_prefs);
else
  $saved = $plugin['result'];

if ($saved)
  $OUTPUT->show_message('successfullysaved', 'confirmation');
else
  $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsaving', 'error');

// display the form again
rcmail_overwrite_action('edit-prefs');


<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/show.inc                                           |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Display a mail message similar as a usual mail application does     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

$PRINT_MODE = $RCMAIL->action=='print' ? TRUE : FALSE;

// Read browser capabilities and store them in session
if ($caps = get_input_value('_caps', RCUBE_INPUT_GET)) {
  $browser_caps = array();
  foreach (explode(',', $caps) as $cap) {
    $cap = explode('=', $cap);
    $browser_caps[$cap[0]] = $cap[1];
  }
  $_SESSION['browser_caps'] = $browser_caps;
}

// similar code as in program/steps/mail/get.inc
if ($uid = get_input_value('_uid', RCUBE_INPUT_GET)) {
  $MESSAGE = new rcube_message($uid);

  // if message not found (wrong UID)...
  if (empty($MESSAGE->headers)) {
    rcmail_message_error($uid);
  }

  $mbox_name = $RCMAIL->storage->get_folder();

  // show images?
  rcmail_check_safe($MESSAGE);

  // set message charset as default
  if (!empty($MESSAGE->headers->charset))
    $RCMAIL->storage->set_charset($MESSAGE->headers->charset);

  $OUTPUT->set_pagetitle(abbreviate_string($MESSAGE->subject, 128, '...', true));

  // give message uid to the client
  $OUTPUT->set_env('uid', $MESSAGE->uid);
  // set environement
  $OUTPUT->set_env('safemode', $MESSAGE->is_safe);
  $OUTPUT->set_env('sender', $MESSAGE->sender['string']);
  $OUTPUT->set_env('permaurl', rcmail_url('show', array('_uid' => $MESSAGE->uid, '_mbox' => $mbox_name)));
  $OUTPUT->set_env('delimiter', $RCMAIL->storage->get_hierarchy_delimiter());
  $OUTPUT->set_env('mailbox', $mbox_name);
  $OUTPUT->set_env('compose_extwin', $RCMAIL->config->get('compose_extwin',false));

  // mimetypes supported by the browser (default settings)
  $mimetypes = (array)$RCMAIL->config->get('client_mimetypes');

  // Remove unsupported types, which makes that attachment which cannot be
  // displayed in a browser will be downloaded directly without displaying an overlay page
  if (empty($_SESSION['browser_caps']['pdf']) && ($key = array_search('application/pdf', $mimetypes)) !== false) {
    unset($mimetypes[$key]);
  }
  if (empty($_SESSION['browser_caps']['flash']) && ($key = array_search('application/x-shockwave-flash', $mimetypes)) !== false) {
    unset($mimetypes[$key]);
  }
  if (empty($_SESSION['browser_caps']['tif']) && ($key = array_search('image/tiff', $mimetypes)) !== false) {
    // we can convert tiff to jpeg
    if (!$RCMAIL->config->get('im_convert_path')) {
      unset($mimetypes[$key]);
    }
  }

  $OUTPUT->set_env('mimetypes', array_values($mimetypes));

  if ($CONFIG['drafts_mbox'])
    $OUTPUT->set_env('drafts_mailbox', $CONFIG['drafts_mbox']);
  if ($CONFIG['trash_mbox'])
    $OUTPUT->set_env('trash_mailbox', $CONFIG['trash_mbox']);
  if ($CONFIG['junk_mbox'])
    $OUTPUT->set_env('junk_mailbox', $CONFIG['junk_mbox']);
  if ($CONFIG['delete_junk'])
    $OUTPUT->set_env('delete_junk', true);
  if ($CONFIG['flag_for_deletion'])
    $OUTPUT->set_env('flag_for_deletion', true);
  if ($CONFIG['read_when_deleted'])
    $OUTPUT->set_env('read_when_deleted', true);
  if ($CONFIG['skip_deleted'])
    $OUTPUT->set_env('skip_deleted', true);
  if ($CONFIG['display_next'])
    $OUTPUT->set_env('display_next', true);
  if ($MESSAGE->headers->get('list-post', false))
    $OUTPUT->set_env('list_post', true);
  if ($CONFIG['forward_attachment'])
    $OUTPUT->set_env('forward_attachment', true);

  if (!$OUTPUT->ajax_call)
    $OUTPUT->add_label('checkingmail', 'deletemessage', 'movemessagetotrash',
      'movingmessage', 'deletingmessage', 'markingmessage');

  // check for unset disposition notification
  if ($MESSAGE->headers->mdn_to
      && empty($MESSAGE->headers->flags['MDNSENT'])
      && empty($MESSAGE->headers->flags['SEEN'])
      && ($RCMAIL->storage->check_permflag('MDNSENT') || $RCMAIL->storage->check_permflag('*'))
      && $mbox_name != $CONFIG['drafts_mbox']
      && $mbox_name != $CONFIG['sent_mbox']
  ) {
    $mdn_cfg = intval($CONFIG['mdn_requests']);

    if ($mdn_cfg == 1 || (($mdn_cfg == 3 || $mdn_cfg ==  4) && rcmail_contact_exists($MESSAGE->sender['mailto']))) {
      // Send MDN
      if (rcmail_send_mdn($MESSAGE, $smtp_error))
        $OUTPUT->show_message('receiptsent', 'confirmation');
      else if ($smtp_error)
        $OUTPUT->show_message($smtp_error['label'], 'error', $smtp_error['vars']);
      else
        $OUTPUT->show_message('errorsendingreceipt', 'error');
    }
    else if ($mdn_cfg != 2 && $mdn_cfg != 4) {
      // Ask user
      $OUTPUT->add_label('mdnrequest');
      $OUTPUT->set_env('mdn_request', true);
    }
  }

  if (empty($MESSAGE->headers->flags['SEEN'])
    && ($RCMAIL->action == 'show' || ($RCMAIL->action == 'preview' && intval($CONFIG['preview_pane_mark_read']) == 0))
  ) {
    $RCMAIL->plugins->exec_hook('message_read', array('uid' => $MESSAGE->uid,
      'mailbox' => $mbox_name, 'message' => $MESSAGE));
  }
}



function rcmail_message_attachments($attrib)
{
  global $PRINT_MODE, $MESSAGE, $RCMAIL;

  $out = $ol = '';

  if (sizeof($MESSAGE->attachments)) {
    foreach ($MESSAGE->attachments as $attach_prop) {
      $filename = rcmail_attachment_name($attach_prop, true);

      if ($PRINT_MODE) {
        $size = $RCMAIL->message_part_size($attach_prop);
        $ol .= html::tag('li', null, Q(sprintf("%s (%s)", $filename, $size)));
      }
      else {
        if ($attrib['maxlength'] && mb_strlen($filename) > $attrib['maxlength']) {
          $title    = $filename;
          $filename = abbreviate_string($filename, $attrib['maxlength']);
        }
        else {
          $title = '';
        }

        $ol .= html::tag('li', rcmail_filetype2classname($attach_prop->mimetype, $filename),
          html::a(array(
            'href' => $MESSAGE->get_part_url($attach_prop->mime_id, false),
            'onclick' => sprintf(
              'return %s.command(\'load-attachment\',{part:\'%s\', mimetype:\'%s\'},this)',
              JS_OBJECT_NAME,
              $attach_prop->mime_id,
              rcmail_fix_mimetype($attach_prop->mimetype)),
              'onmouseover' => $title ? '' : 'rcube_webmail.long_subject_title_ex(this, 0)',
              'title' => Q($title),
            ),
            Q($filename)));
      }
    }

    $out = html::tag('ul', $attrib, $ol, html::$common_attrib);
  }

  return $out;
}

function rcmail_remote_objects_msg()
{
  global $MESSAGE, $RCMAIL;

  $attrib['id']    = 'remote-objects-message';
  $attrib['class'] = 'notice';
  $attrib['style'] = 'display: none';

  $msg = Q(rcube_label('blockedimages')) . '&nbsp;';
  $msg .= html::a(array('href' => "#loadimages", 'onclick' => JS_OBJECT_NAME.".command('load-images')"), Q(rcube_label('showimages')));

  // add link to save sender in addressbook and reload message
  if ($MESSAGE->sender['mailto'] && $RCMAIL->config->get('show_images') == 1) {
    $msg .= ' ' . html::a(array('href' => "#alwaysload", 'onclick' => JS_OBJECT_NAME.".command('always-load')", 'style' => "white-space:nowrap"),
      Q(rcube_label(array('name' => 'alwaysshow', 'vars' => array('sender' => $MESSAGE->sender['mailto'])))));
  }

  $RCMAIL->output->add_gui_object('remoteobjectsmsg', $attrib['id']);
  return html::div($attrib, $msg);
}

function rcmail_message_buttons()
{
  global $MESSAGE, $RCMAIL, $CONFIG;

  $mbox  = $RCMAIL->storage->get_folder();
  $delim = $RCMAIL->storage->get_hierarchy_delimiter();
  $dbox  = $CONFIG['drafts_mbox'];

  // the message is not a draft
  if ($mbox != $dbox && strpos($mbox, $dbox.$delim) !== 0) {
    return '';
  }

  $attrib['id']    = 'message-buttons';
  $attrib['class'] = 'notice';

  $msg = Q(rcube_label('isdraft')) . '&nbsp;';
  $msg .= html::a(array('href' => "#edit", 'onclick' => JS_OBJECT_NAME.".command('edit')"), Q(rcube_label('edit')));

  return html::div($attrib, $msg);
}

function rcmail_message_objects($attrib)
{
  global $RCMAIL, $MESSAGE;

  if (!$attrib['id'])
    $attrib['id'] = 'message-objects';

  $content = array(
    rcmail_message_buttons(),
    rcmail_remote_objects_msg(),
  );

  $plugin = $RCMAIL->plugins->exec_hook('message_objects',
    array('content' => $content, 'message' => $MESSAGE));

  $content = implode("\n", $plugin['content']);

  return html::div($attrib, $content);
}

function rcmail_contact_exists($email)
{
  global $RCMAIL;

  if ($email) {
    // @TODO: search in all address books?
    $CONTACTS = $RCMAIL->get_address_book(-1, true);

    if (is_object($CONTACTS)) {
      $existing = $CONTACTS->search('email', $email, true, false);
      if ($existing->count) {
        return true;
      }
    }
  }

  return false;
}

function rcmail_message_contactphoto($attrib)
{
  global $RCMAIL, $MESSAGE;

  $placeholder = $attrib['placeholder'] ? $RCMAIL->config->get('skin_path') . $attrib['placeholder'] : null;
  if ($MESSAGE->sender)
    $photo_img = $RCMAIL->url(array('_task' => 'addressbook', '_action' => 'photo', '_email' => $MESSAGE->sender['mailto'], '_alt' => $placeholder));
  else
    $photo_img = $placeholder ? $placeholder : 'program/resources/blank.gif';

  return html::img(array('src' => $photo_img) + $attrib);
}


$OUTPUT->add_handlers(array(
  'messageattachments' => 'rcmail_message_attachments',
  'mailboxname' => 'rcmail_mailbox_name_display',
  'messageobjects' => 'rcmail_message_objects',
  'contactphoto' => 'rcmail_message_contactphoto',
));


if ($RCMAIL->action=='print' && $OUTPUT->template_exists('messageprint'))
  $OUTPUT->send('messageprint', false);
else if ($RCMAIL->action=='preview' && $OUTPUT->template_exists('messagepreview'))
  $OUTPUT->send('messagepreview', false);
else
  $OUTPUT->send('message', false);


// mark message as read
if ($MESSAGE && $MESSAGE->headers && empty($MESSAGE->headers->flags['SEEN']) &&
  ($RCMAIL->action == 'show' || ($RCMAIL->action == 'preview' && intval($CONFIG['preview_pane_mark_read']) == 0)))
{
  if ($RCMAIL->storage->set_flag($MESSAGE->uid, 'SEEN')) {
    if ($count = rcmail_get_unseen_count($mbox_name)) {
      rcmail_set_unseen_count($mbox_name, $count - 1);
    }
  }
}

exit;


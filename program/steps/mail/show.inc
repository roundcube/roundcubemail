<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/show.inc                                           |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Display a mail message similar as a usual mail application does     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$PRINT_MODE = $RCMAIL->action=='print' ? TRUE : FALSE;

// similar code as in program/steps/mail/get.inc
if ($uid = get_input_value('_uid', RCUBE_INPUT_GET)) {
  $MESSAGE = new rcube_message($uid);

  // if message not found (wrong UID)...
  if (empty($MESSAGE->headers)) {
    rcmail_message_error($uid);
  }

  $mbox_name = $RCMAIL->imap->get_mailbox_name();

  // show images?
  rcmail_check_safe($MESSAGE);

  // set message charset as default
  if (!empty($MESSAGE->headers->charset))
    $RCMAIL->imap->set_charset($MESSAGE->headers->charset);

  $OUTPUT->set_pagetitle(abbreviate_string($MESSAGE->subject, 128, '...', true));

  // give message uid to the client
  $OUTPUT->set_env('uid', $MESSAGE->uid);
  // set environement
  $OUTPUT->set_env('safemode', $MESSAGE->is_safe);
  $OUTPUT->set_env('sender', $MESSAGE->sender['string']);
  $OUTPUT->set_env('permaurl', rcmail_url('show', array('_uid' => $MESSAGE->uid, '_mbox' => $mbox_name)));
  $OUTPUT->set_env('delimiter', $RCMAIL->imap->get_hierarchy_delimiter());
  $OUTPUT->set_env('mailbox', $mbox_name);

  // mimetypes supported by the browser (default settings)
  $mimetypes = $RCMAIL->config->get('client_mimetypes', 'text/plain,text/html,text/xml,image/jpeg,image/gif,image/png,application/x-javascript,application/pdf,application/x-shockwave-flash');
  $OUTPUT->set_env('mimetypes', is_string($mimetypes) ? explode(',', $mimetypes) : (array)$mimetypes);

  if ($CONFIG['trash_mbox'])
    $OUTPUT->set_env('trash_mailbox', $CONFIG['trash_mbox']);
  if ($CONFIG['flag_for_deletion'])
    $OUTPUT->set_env('flag_for_deletion', true);
  if ($CONFIG['read_when_deleted'])
    $OUTPUT->set_env('read_when_deleted', true);
  if ($CONFIG['skip_deleted'])
    $OUTPUT->set_env('skip_deleted', true);
  if ($CONFIG['display_next'])
    $OUTPUT->set_env('display_next', true);
  if ($MESSAGE->headers->others['list-post'])
    $OUTPUT->set_env('list_post', true);
  if ($CONFIG['forward_attachment'])
    $OUTPUT->set_env('forward_attachment', true);

  if (!$OUTPUT->ajax_call)
    $OUTPUT->add_label('checkingmail', 'deletemessage', 'movemessagetotrash',
      'movingmessage', 'deletingmessage');

  // check for unset disposition notification
  if ($MESSAGE->headers->mdn_to
      && empty($MESSAGE->headers->flags['MDNSENT'])
      && empty($MESSAGE->headers->flags['SEEN'])
      && ($RCMAIL->imap->check_permflag('MDNSENT') || $RCMAIL->imap->check_permflag('*'))
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
  global $PRINT_MODE, $MESSAGE;

  $out = $ol = '';

  if (sizeof($MESSAGE->attachments)) {
    foreach ($MESSAGE->attachments as $attach_prop) {
      if ($PRINT_MODE) {
        $ol .= html::tag('li', null, sprintf("%s (%s)", Q($attach_prop->filename), Q(show_bytes($attach_prop->size))));
      }
      else {
        if (mb_strlen($attach_prop->filename) > 50) {
          $filename = abbreviate_string($attach_prop->filename, 50);
          $title = $attach_prop->filename;
      }
      else {
        $filename = $attach_prop->filename;
        $title = '';
      }

        $ol .= html::tag('li', null,
          html::a(array(
            'href' => $MESSAGE->get_part_url($attach_prop->mime_id, false),
            'onclick' => sprintf(
              'return %s.command(\'load-attachment\',{part:\'%s\', mimetype:\'%s\'},this)',
              JS_OBJECT_NAME,
              $attach_prop->mime_id,
              rcmail_fix_mimetype($attach_prop->mimetype)),
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

  $mbox  = $RCMAIL->imap->get_mailbox_name();
  $delim = $RCMAIL->imap->get_hierarchy_delimiter();
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
    $CONTACTS = $RCMAIL->get_address_book(null, true);
    $existing = $CONTACTS->search('email', $email, true, false);
    if ($existing->count)
      return true;
  }

  return false;
}


$OUTPUT->add_handlers(array(
  'messageattachments' => 'rcmail_message_attachments',
  'mailboxname' => 'rcmail_mailbox_name_display',
  'messageobjects' => 'rcmail_message_objects',
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
  if ($RCMAIL->imap->set_flag($MESSAGE->uid, 'SEEN')) {
    if ($count = rcmail_get_unseen_count($mbox_name)) {
      rcmail_set_unseen_count($mbox_name, $count - 1);
    }
  }
}

exit;


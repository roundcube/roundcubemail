<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/compose.inc                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Compose a new mail message with all headers and attachments         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// define constants for message compose mode
define('RCUBE_COMPOSE_REPLY', 0x0106);
define('RCUBE_COMPOSE_FORWARD', 0x0107);
define('RCUBE_COMPOSE_DRAFT', 0x0108);
define('RCUBE_COMPOSE_EDIT', 0x0109);

$MESSAGE_FORM = NULL;
$MESSAGE = NULL;

// Nothing below is called during message composition, only at "new/forward/reply/draft" initialization or
// if a compose-ID is given (i.e. when the compose step is opened in a new window/tab).
// Since there are many ways to leave the compose page improperly, it seems necessary to clean-up an old
// compose when a "new/forward/reply/draft" is called - otherwise the old session attachments will appear

$MESSAGE_ID = get_input_value('_id', RCUBE_INPUT_GET);
if (!is_array($_SESSION['compose']) || $_SESSION['compose']['id'] != $MESSAGE_ID)
{
  rcmail_compose_cleanup();

  // Infinite redirect prevention in case of broken session (#1487028)
  if ($MESSAGE_ID)
    raise_error(array('code' => 500, 'type' => 'php',
      'file' => __FILE__, 'line' => __LINE__,
      'message' => "Invalid session"), true, true);

  $_SESSION['compose'] = array(
    'id' => uniqid(mt_rand()),
    'param' => request2param(RCUBE_INPUT_GET),
    'mailbox' => $IMAP->get_mailbox_name(),
  );
  
  // process values like "mailto:foo@bar.com?subject=new+message&cc=another"
  if ($_SESSION['compose']['param']['to']) {
    // #1486037: remove "mailto:" prefix
    $_SESSION['compose']['param']['to'] = preg_replace('/^mailto:/i', '', $_SESSION['compose']['param']['to']);
    $mailto = explode('?', $_SESSION['compose']['param']['to']);
    if (count($mailto) > 1) {
      $_SESSION['compose']['param']['to'] = $mailto[0];
      parse_str($mailto[1], $query);
      foreach ($query as $f => $val)
        $_SESSION['compose']['param'][$f] = $val;
    }
  }
  
  // select folder where to save the sent message
  $_SESSION['compose']['param']['sent_mbox'] = $RCMAIL->config->get('sent_mbox');
  
  // pipe compose parameters thru plugins
  $plugin = $RCMAIL->plugins->exec_hook('message_compose', $_SESSION['compose']);
  $_SESSION['compose']['param'] = array_merge($_SESSION['compose']['param'], $plugin['param']);

  // add attachments listed by message_compose hook
  if (is_array($plugin['attachments'])) {
    foreach ($plugin['attachments'] as $attach) {
      // we have structured data
      if (is_array($attach)) {
        $attachment = $attach;
      }
      // only a file path is given
      else {
        $filename = basename($attach);
        $attachment = array(
          'name' => $filename,
          'mimetype' => rc_mime_content_type($attach, $filename),
          'path' => $attach
        );
      }
      
      // save attachment if valid
      if (($attachment['data'] && $attachment['name']) || ($attachment['path'] && file_exists($attachment['path']))) {
        $attachment = rcmail::get_instance()->plugins->exec_hook('attachment_save', $attachment);
      }
      
      if ($attachment['status'] && !$attachment['abort']) {
        unset($attachment['data'], $attachment['status'], $attachment['abort']);
        $_SESSION['compose']['attachments'][$attachment['id']] = $attachment;
      }
    }
  }

  // check if folder for saving sent messages exists and is subscribed (#1486802)
  if (($sent_folder = $_SESSION['compose']['param']['sent_mbox']) && !$IMAP->mailbox_exists($sent_folder, true)) {
    // folder may exist but isn't subscribed (#1485241)
    if (!$IMAP->mailbox_exists($sent_folder))
      $IMAP->create_mailbox($sent_folder, true);
    else
      $IMAP->subscribe($sent_folder);
  }

  // redirect to a unique URL with all parameters stored in session
  $OUTPUT->redirect(array('_action' => 'compose', '_id' => $_SESSION['compose']['id']));
}


// add some labels to client
$OUTPUT->add_label('nosubject', 'nosenderwarning', 'norecipientwarning', 'nosubjectwarning', 'cancel',
    'nobodywarning', 'notsentwarning', 'notuploadedwarning', 'savingmessage', 'sendingmessage', 
    'messagesaved', 'converting', 'editorwarning', 'searching', 'uploading', 'fileuploaderror');

// add config parameters to client script
if (!empty($CONFIG['drafts_mbox'])) {
  $OUTPUT->set_env('drafts_mailbox', $CONFIG['drafts_mbox']);
  $OUTPUT->set_env('draft_autosave', $CONFIG['draft_autosave']);
}
// set current mailbox in client environment
$OUTPUT->set_env('mailbox', $IMAP->get_mailbox_name());
$OUTPUT->set_env('sig_above', $CONFIG['sig_above']);
$OUTPUT->set_env('top_posting', $CONFIG['top_posting']);

// get reference message and set compose mode
if ($msg_uid = $_SESSION['compose']['param']['reply_uid'])
  $compose_mode = RCUBE_COMPOSE_REPLY;
else if ($msg_uid = $_SESSION['compose']['param']['forward_uid'])
  $compose_mode = RCUBE_COMPOSE_FORWARD;
else if ($msg_uid = $_SESSION['compose']['param']['uid'])
  $compose_mode = RCUBE_COMPOSE_EDIT;
else if ($msg_uid = $_SESSION['compose']['param']['draft_uid']) {
  $RCMAIL->imap->set_mailbox($CONFIG['drafts_mbox']);
  $compose_mode = RCUBE_COMPOSE_DRAFT;
}

$config_show_sig = $RCMAIL->config->get('show_sig', 1);
if ($config_show_sig == 1)
  $OUTPUT->set_env('show_sig', true);
else if ($config_show_sig == 2 && (empty($compose_mode) || $compose_mode == RCUBE_COMPOSE_EDIT || $compose_mode == RCUBE_COMPOSE_DRAFT))
  $OUTPUT->set_env('show_sig', true);
else if ($config_show_sig == 3 && ($compose_mode == RCUBE_COMPOSE_REPLY || $compose_mode == RCUBE_COMPOSE_FORWARD))
  $OUTPUT->set_env('show_sig', true);
else
  $OUTPUT->set_env('show_sig', false);

// set line length for body wrapping
$LINE_LENGTH = $RCMAIL->config->get('line_length', 72);

if (!empty($msg_uid))
{
  // similar as in program/steps/mail/show.inc
  // re-set 'prefer_html' to have possibility to use html part for compose
  $CONFIG['prefer_html'] = $CONFIG['prefer_html'] || $CONFIG['htmleditor'] || $compose_mode == RCUBE_COMPOSE_DRAFT || $compose_mode == RCUBE_COMPOSE_EDIT;
  $MESSAGE = new rcube_message($msg_uid);
  
  // make sure message is marked as read
  if ($MESSAGE && $MESSAGE->headers && !$MESSAGE->headers->seen)
    $IMAP->set_flag($msg_uid, 'SEEN');

  if (!empty($MESSAGE->headers->charset))
    $IMAP->set_charset($MESSAGE->headers->charset);
    
  if ($compose_mode == RCUBE_COMPOSE_REPLY)
  {
    $_SESSION['compose']['reply_uid'] = $msg_uid;
    $_SESSION['compose']['reply_msgid'] = $MESSAGE->headers->messageID;
    $_SESSION['compose']['references']  = trim($MESSAGE->headers->references . " " . $MESSAGE->headers->messageID);

    if (!empty($_SESSION['compose']['param']['all']))
      $MESSAGE->reply_all = $_SESSION['compose']['param']['all'];

    $OUTPUT->set_env('compose_mode', 'reply');
  }
  else if ($compose_mode == RCUBE_COMPOSE_DRAFT)
  {
    if ($MESSAGE->headers->others['x-draft-info'])
    {
      // get reply_uid/forward_uid to flag the original message when sending
      $info = rcmail_draftinfo_decode($MESSAGE->headers->others['x-draft-info']);

      if ($info['type'] == 'reply')
        $_SESSION['compose']['reply_uid'] = $info['uid'];
      else if ($info['type'] == 'forward')
        $_SESSION['compose']['forward_uid'] = $info['uid'];

      $_SESSION['compose']['mailbox'] = $info['folder'];
    }
    
    if ($MESSAGE->headers->in_reply_to)
      $_SESSION['compose']['reply_msgid'] = '<'.$MESSAGE->headers->in_reply_to.'>';

    $_SESSION['compose']['references']  = $MESSAGE->headers->references;
  }
  else if ($compose_mode == RCUBE_COMPOSE_FORWARD)
  {
    $_SESSION['compose']['forward_uid'] = $msg_uid;
    $OUTPUT->set_env('compose_mode', 'forward');
  }
}

// process $MESSAGE body/attachments, set $MESSAGE_BODY/$HTML_MODE vars and some session data
$MESSAGE_BODY = rcmail_prepare_message_body();


/****** compose mode functions ********/

function rcmail_compose_headers($attrib)
{
  global $IMAP, $MESSAGE, $DB, $compose_mode;
  static $sa_recipients = array();

  list($form_start, $form_end) = get_form_tags($attrib);

  $out = '';
  $part = strtolower($attrib['part']);

  switch ($part)
  {
    case 'from':
      return $form_start . rcmail_compose_header_from($attrib);

    case 'to':
      $fname = '_to';
      $header = $param = 'to';

      // we have a set of recipients stored is session
      if (($mailto_id = $_SESSION['compose']['param']['mailto']) && $_SESSION['mailto'][$mailto_id])
        $fvalue = urldecode($_SESSION['mailto'][$mailto_id]);

    case 'cc':
      if (!$fname) {
        $fname = '_cc';
        $header = $param = 'cc';
      }
    case 'bcc':
      if (!$fname) {
        $fname = '_bcc';
        $header = $param = 'bcc';
      }

      $allow_attrib = array('id', 'class', 'style', 'cols', 'rows', 'tabindex');
      $field_type = 'html_textarea';
      break;

    case 'replyto':
    case 'reply-to':
      $fname = '_replyto';
      $param = 'replyto';
      $header = 'reply-to';

    case 'mailreplyto':
    case 'mailreply-to':
      if (!$fname) {
        $fname = '_mailreplyto';
        $param = 'mailreplyto';
        $header = 'mailreply-to';
      }

    case 'mailfollowupto':
    case 'mailfollowup-to':
      if (!$fname) {
        $fname = '_mailfollowupto';
        $param = 'mailfollowupto';
        $header = 'mailfollowup-to';
      }

      $allow_attrib = array('id', 'class', 'style', 'size', 'tabindex');
      $field_type = 'html_inputfield';
      break;
  }

  if ($fname && !empty($_POST[$fname])) {
    $fvalue = get_input_value($fname, RCUBE_INPUT_POST, TRUE);
  }
  else if ($fname && !$fvalue && !empty($_SESSION['compose']['param'][$param])) {
    $fvalue = $_SESSION['compose']['param'][$param];
  }
  else if ($header && $compose_mode == RCUBE_COMPOSE_REPLY) {
    // get recipent address(es) out of the message headers
    if ($header == 'to') {
      $mailfollowup = $MESSAGE->headers->others['mail-followup-to'];
      $mailreplyto  = $MESSAGE->headers->others['mail-reply-to'];

      if ($MESSAGE->reply_all == 'list' && $mailfollowup)
        $fvalue = $mailfollowup;
      else if ($MESSAGE->reply_all == 'list'
        && preg_match('/<mailto:([^>]+)>/i', $MESSAGE->headers->others['list-post'], $m))
        $fvalue = $m[1];
      else if ($mailreplyto)
        $fvalue = $mailreplyto;
      else if (!empty($MESSAGE->headers->replyto))
        $fvalue = $MESSAGE->headers->replyto;
      else if (!empty($MESSAGE->headers->from))
        $fvalue = $MESSAGE->headers->from;
    }
    // add recipent of original message if reply to all
    else if ($header == 'cc' && !empty($MESSAGE->reply_all) && $MESSAGE->reply_all != 'list') {
      if ($v = $MESSAGE->headers->to)
        $fvalue .= $v;
      if ($v = $MESSAGE->headers->cc)
        $fvalue .= (!empty($fvalue) ? ', ' : '') . $v;
    }

    // split recipients and put them back together in a unique way
    if (!empty($fvalue)) {
      $to_addresses = $IMAP->decode_address_list($fvalue);
      $fvalue = '';

      foreach ($to_addresses as $addr_part) {
        if (empty($addr_part['mailto']))
          continue;

        $mailto = idn_to_utf8($addr_part['mailto']);

        if (!in_array($mailto, $sa_recipients)
            && (!$MESSAGE->compose_from
                || !in_array_nocase($mailto, $MESSAGE->compose_from)
                || (count($to_addresses)==1 && $header=='to')) // allow reply to yourself
        ) {
          if ($addr_part['name'] && $addr_part['mailto'] != $addr_part['name'])
            $string = format_email_recipient($mailto, $addr_part['name']);
          else
            $string = $mailto;
          $fvalue .= (strlen($fvalue) ? ', ':'') . $string;
          $sa_recipients[] = $addr_part['mailto'];
        }
      }
    }
  }
  else if ($header && in_array($compose_mode, array(RCUBE_COMPOSE_DRAFT, RCUBE_COMPOSE_EDIT))) {
    // get drafted headers
    if ($header=='to' && !empty($MESSAGE->headers->to))
      $fvalue = $MESSAGE->get_header('to');
    else if ($header=='cc' && !empty($MESSAGE->headers->cc))
      $fvalue = $MESSAGE->get_header('cc');
    else if ($header=='bcc' && !empty($MESSAGE->headers->bcc))
      $fvalue = $MESSAGE->get_header('bcc');
    else if ($header=='reply-to' && !empty($MESSAGE->headers->replyto))
      $fvalue = $MESSAGE->get_header('reply-to');
    else if ($header=='mail-reply-to' && !empty($MESSAGE->headers->others['mail-reply-to']))
      $fvalue = $MESSAGE->get_header('followup-to');
    else if ($header=='mail-followup-to' && !empty($MESSAGE->headers->others['mail-followup-to']))
      $fvalue = $MESSAGE->get_header('mail-followup-to');

    $addresses = $IMAP->decode_address_list($fvalue);
    $fvalue = '';

    foreach ($addresses as $addr_part) {
      if (empty($addr_part['mailto']))
        continue;

      $mailto = idn_to_utf8($addr_part['mailto']);

      if ($addr_part['name'] && $addr_part['mailto'] != $addr_part['name'])
        $string = format_email_recipient($mailto, $addr_part['name']);
      else
        $string = $mailto;
      $fvalue .= (strlen($fvalue) ? ', ':'') . $string;
    }
  }

  if ($fname && $field_type)
  {
    // pass the following attributes to the form class
    $field_attrib = array('name' => $fname, 'spellcheck' => 'false');
    foreach ($attrib as $attr => $value)
      if (in_array($attr, $allow_attrib))
        $field_attrib[$attr] = $value;

    // create teaxtarea object
    $input = new $field_type($field_attrib);
    $out = $input->show($fvalue);
  }
  
  if ($form_start)
    $out = $form_start.$out;

  return $out;
}


function rcmail_compose_header_from($attrib)
{
  global $IMAP, $MESSAGE, $DB, $USER, $OUTPUT, $compose_mode;
    
  // pass the following attributes to the form class
  $field_attrib = array('name' => '_from');
  foreach ($attrib as $attr => $value)
    if (in_array($attr, array('id', 'class', 'style', 'size', 'tabindex')))
      $field_attrib[$attr] = $value;

  // extract all recipients of the reply-message
  $a_recipients = array();
  if ($compose_mode == RCUBE_COMPOSE_REPLY && is_object($MESSAGE->headers))
  {
    $MESSAGE->compose_from = array();

    $a_to = $IMAP->decode_address_list($MESSAGE->headers->to);
    foreach ($a_to as $addr)
    {
      if (!empty($addr['mailto']))
        $a_recipients[] = strtolower($addr['mailto']);
    }

    if (!empty($MESSAGE->headers->cc))
    {
      $a_cc = $IMAP->decode_address_list($MESSAGE->headers->cc);
      foreach ($a_cc as $addr)
      {
        if (!empty($addr['mailto']))
          $a_recipients[] = strtolower($addr['mailto']);
      }
    }
  }

  // get this user's identities
  $user_identities = $USER->list_identities();

  if (count($user_identities))
  {
    $a_signatures = array();

    $field_attrib['onchange'] = JS_OBJECT_NAME.".change_identity(this)";
    $select_from = new html_select($field_attrib);

    // create SELECT element
    foreach ($user_identities as $sql_arr)
    {
      $email = mb_strtolower(idn_to_utf8($sql_arr['email']));
      $identity_id = $sql_arr['identity_id'];
      $select_from->add(format_email_recipient($email, $sql_arr['name']), $identity_id);

      // add signature to array
      if (!empty($sql_arr['signature']) && empty($_SESSION['compose']['param']['nosig']))
      {
        $a_signatures[$identity_id]['text'] = $sql_arr['signature'];
        $a_signatures[$identity_id]['is_html'] = ($sql_arr['html_signature'] == 1) ? true : false;
        if ($a_signatures[$identity_id]['is_html'])
        {
            $h2t = new html2text($a_signatures[$identity_id]['text'], false, false);
            $a_signatures[$identity_id]['plain_text'] = trim($h2t->get_text());
        }
      }

      if ($compose_mode == RCUBE_COMPOSE_REPLY && is_array($MESSAGE->compose_from))
        $MESSAGE->compose_from[] = $email;
    }

    $from_id = 0;

    // overwrite identity selection with post parameter
    if (!empty($_POST['_from']))
      $from_id = get_input_value('_from', RCUBE_INPUT_POST);
    else if (!empty($_SESSION['compose']['param']['from']))
      $from_id = $_SESSION['compose']['param']['from'];
    else if (count($user_identities) > 1) {
      $return_path = $MESSAGE->headers->others['return-path'];

      // Set identity
      foreach ($user_identities as $sql_arr) {
        // set draft's identity
        if ($compose_mode == RCUBE_COMPOSE_DRAFT || $compose_mode == RCUBE_COMPOSE_EDIT) {
          if ($MESSAGE->headers->from == format_email_recipient($sql_arr['email'], $sql_arr['name'])) {
            $from_id = $sql_arr['identity_id'];
            break;
          }
        }
        // set identity if it's one of the reply-message recipients (with prio for default identity)
        else if (in_array($sql_arr['email'], $a_recipients) && (empty($from_id) || $sql_arr['standard']))
          $from_id = $sql_arr['identity_id'];
        // set identity when replying to mailing list
        else if (strpos($return_path, str_replace('@', '=', $sql_arr['email']).'@') !== false)
          $from_id = $sql_arr['identity_id'];

        if ($from_id)
          break;
      }
    }

    $out = $select_from->show($from_id);

    // add signatures to client
    $OUTPUT->set_env('signatures', $a_signatures);
  }
  else
  {
    $input_from = new html_inputfield($field_attrib);
    $out = $input_from->show($_POST['_from']);
  }

  return $out;
}


function rcmail_compose_editor_mode()
{
  global $RCMAIL, $MESSAGE, $compose_mode;
  static $useHtml;

  if ($useHtml !== null)
    return $useHtml;

  $html_editor = intval($RCMAIL->config->get('htmleditor'));

  if ($compose_mode == RCUBE_COMPOSE_DRAFT || $compose_mode == RCUBE_COMPOSE_EDIT) {
    $useHtml = $MESSAGE->has_html_part();
  }
  else if ($compose_mode == RCUBE_COMPOSE_REPLY) {
    $useHtml = ($html_editor == 1 || ($html_editor == 2 && $MESSAGE->has_html_part()));
  }
  else { // RCUBE_COMPOSE_FORWARD or NEW
    $useHtml = ($html_editor == 1);
  }

  return $useHtml;
}


function rcmail_prepare_message_body()
{
  global $RCMAIL, $MESSAGE, $compose_mode, $LINE_LENGTH, $HTML_MODE;

  // use posted message body
  if (!empty($_POST['_message'])) {
    $body = get_input_value('_message', RCUBE_INPUT_POST, true);
    $isHtml = (bool) get_input_value('_is_html', RCUBE_INPUT_POST);
  }
  else if ($_SESSION['compose']['param']['body']) {
    $body = $_SESSION['compose']['param']['body'];
    $isHtml = false;
  }
  // reply/edit/draft/forward
  else if ($compose_mode) {
    $has_html_part = $MESSAGE->has_html_part();
    $isHtml = rcmail_compose_editor_mode();

    if ($isHtml) {
      if ($has_html_part) {
        $body = $MESSAGE->first_html_part();
      }
      else {
        $body = $MESSAGE->first_text_part();
        // try to remove the signature
        if ($RCMAIL->config->get('strip_existing_sig', true))
          $body = rcmail_remove_signature($body);
        // add HTML formatting
        $body = rcmail_plain_body($body);
        if ($body)
          $body = '<pre>' . $body . '</pre>';
      }
    }
    else {
      if ($has_html_part) {
        // use html part if it has been used for message (pre)viewing
        // decrease line length for quoting
        $len = $compose_mode == RCUBE_COMPOSE_REPLY ? $LINE_LENGTH-2 : $LINE_LENGTH;
        $txt = new html2text($MESSAGE->first_html_part(), false, true, $len);
        $body = $txt->get_text();
      }
      else {
        $body = $MESSAGE->first_text_part($part);
        if ($body && $part && $part->ctype_secondary == 'plain'
            && $part->ctype_parameters['format'] == 'flowed'
        ) {
          $body = rcube_message::unfold_flowed($body);
        }
      }
    }

    // compose reply-body
    if ($compose_mode == RCUBE_COMPOSE_REPLY)
      $body = rcmail_create_reply_body($body, $isHtml);
    // forward message body inline
    else if ($compose_mode == RCUBE_COMPOSE_FORWARD)
      $body = rcmail_create_forward_body($body, $isHtml);
    // load draft message body
    else if ($compose_mode == RCUBE_COMPOSE_DRAFT || $compose_mode == RCUBE_COMPOSE_EDIT)
      $body = rcmail_create_draft_body($body, $isHtml);
  }
  else { // new message
    $isHtml = rcmail_compose_editor_mode();
  }

  $plugin = $RCMAIL->plugins->exec_hook('message_compose_body',
    array('body' => $body, 'html' => $isHtml, 'mode' => $compose_mode));
  $body = $plugin['body'];
  unset($plugin);

  // add blocked.gif attachment (#1486516)
  if ($isHtml && preg_match('#<img src="\./program/blocked\.gif"#', $body)) {
    if ($attachment = rcmail_save_image('program/blocked.gif', 'image/gif')) {
      $_SESSION['compose']['attachments'][$attachment['id']] = $attachment;
      $body = preg_replace('#\./program/blocked\.gif#',
        $RCMAIL->comm_path.'&_action=display-attachment&_file=rcmfile'.$attachment['id'],
        $body);
    }
  }
  
  $HTML_MODE = $isHtml;
  
  return $body;
}

function rcmail_compose_body($attrib)
{
  global $RCMAIL, $CONFIG, $OUTPUT, $MESSAGE, $compose_mode, $LINE_LENGTH, $HTML_MODE, $MESSAGE_BODY;
  
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  if (empty($attrib['id']))
    $attrib['id'] = 'rcmComposeBody';

  $attrib['name'] = '_message';

  $isHtml = $HTML_MODE;
  
  $out = $form_start ? "$form_start\n" : '';

  $saveid = new html_hiddenfield(array('name' => '_draft_saveid', 'value' => $compose_mode==RCUBE_COMPOSE_DRAFT ? str_replace(array('<','>'), "", $MESSAGE->headers->messageID) : ''));
  $out .= $saveid->show();

  $drafttoggle = new html_hiddenfield(array('name' => '_draft', 'value' => 'yes'));
  $out .= $drafttoggle->show();

  $msgtype = new html_hiddenfield(array('name' => '_is_html', 'value' => ($isHtml?"1":"0")));
  $out .= $msgtype->show();

  // If desired, set this textarea to be editable by TinyMCE
  if ($isHtml) $attrib['class'] = 'mce_editor';
  $textarea = new html_textarea($attrib);
  $out .= $textarea->show($MESSAGE_BODY);
  $out .= $form_end ? "\n$form_end" : '';

  $OUTPUT->set_env('composebody', $attrib['id']);

  // include HTML editor
  rcube_html_editor();
  
  // include GoogieSpell
  if (!empty($CONFIG['enable_spellcheck'])) {

    $engine = $RCMAIL->config->get('spellcheck_engine','googie');
    $spellcheck_langs = (array) $RCMAIL->config->get('spellcheck_languages',
      array('da'=>'Dansk', 'de'=>'Deutsch', 'en' => 'English', 'es'=>'Español',
            'fr'=>'Français', 'it'=>'Italiano', 'nl'=>'Nederlands', 'pl'=>'Polski',
            'pt'=>'Português', 'fi'=>'Suomi', 'sv'=>'Svenska'));

    // googie works only with two-letter codes
    if ($engine == 'googie') {
      $lang = strtolower(substr($_SESSION['language'], 0, 2));

      $spellcheck_langs_googie = array();
      foreach ($spellcheck_langs as $key => $name)
        $spellcheck_langs_googie[strtolower(substr($key,0,2))] = $name;
        $spellcheck_langs = $spellcheck_langs_googie;
    }
    else {
      $lang = $_SESSION['language'];

      // if not found in the list, try with two-letter code
      if (!$spellcheck_langs[$lang])
        $lang = strtolower(substr($lang, 0, 2));
    }

    if (!$spellcheck_langs[$lang])
      $lang = 'en';

    $editor_lang_set = array();
    foreach ($spellcheck_langs as $key => $name) {
      $editor_lang_set[] = ($key == $lang ? '+' : '') . JQ($name).'='.JQ($key);
    }
    
    $OUTPUT->include_script('googiespell.js');
    $OUTPUT->add_script(sprintf(
      "var googie = new GoogieSpell('\$__skin_path/images/googiespell/','?_task=utils&_action=spell&lang=');\n".
      "googie.lang_chck_spell = \"%s\";\n".
      "googie.lang_rsm_edt = \"%s\";\n".
      "googie.lang_close = \"%s\";\n".
      "googie.lang_revert = \"%s\";\n".
      "googie.lang_no_error_found = \"%s\";\n".
      "googie.setLanguages(%s);\n".
      "googie.setCurrentLanguage('%s');\n".
      "googie.setSpellContainer('spellcheck-control');\n".
      "googie.decorateTextarea('%s');\n".
      "%s.set_env('spellcheck', googie);",
      JQ(Q(rcube_label('checkspelling'))),
      JQ(Q(rcube_label('resumeediting'))),
      JQ(Q(rcube_label('close'))),
      JQ(Q(rcube_label('revertto'))),
      JQ(Q(rcube_label('nospellerrors'))),
      json_serialize($spellcheck_langs),
      $lang,
      $attrib['id'],
      JS_OBJECT_NAME), 'foot');

    $OUTPUT->add_label('checking');
    $OUTPUT->set_env('spellcheck_langs', join(',', $editor_lang_set));
  }
 
  $out .= "\n".'<iframe name="savetarget" src="program/blank.gif" style="width:0;height:0;border:none;visibility:hidden;"></iframe>';

  return $out;
}


function rcmail_create_reply_body($body, $bodyIsHtml)
{
  global $RCMAIL, $MESSAGE, $LINE_LENGTH;

  // build reply prefix
  $from = array_pop($RCMAIL->imap->decode_address_list($MESSAGE->get_header('from')));
  $prefix = sprintf("On %s, %s wrote:",
    $MESSAGE->headers->date, $from['name'] ? $from['name'] : idn_to_utf8($from['email']));

  if (!$bodyIsHtml) {
    $body = preg_replace('/\r?\n/', "\n", $body);

    // try to remove the signature
    if ($RCMAIL->config->get('strip_existing_sig', true))
      $body = rcmail_remove_signature($body);

    // soft-wrap and quote message text
    $body = rcmail_wrap_and_quote(rtrim($body, "\n"), $LINE_LENGTH);

    $prefix .= "\n";
    $suffix = '';

    if ($RCMAIL->config->get('top_posting'))
      $prefix = "\n\n\n" . $prefix;
  }
  else {
    // save inline images to files
    $cid_map = rcmail_write_inline_attachments($MESSAGE);
    // set is_safe flag (we need this for html body washing)
    rcmail_check_safe($MESSAGE);
    // clean up html tags
    $body = rcmail_wash_html($body, array('safe' => $MESSAGE->is_safe), $cid_map);

    // build reply (quote content)
    $prefix = '<p>' . Q($prefix) . "</p>\n";
    $prefix .= '<blockquote>';

    if ($RCMAIL->config->get('top_posting')) {
      $prefix = '<br>' . $prefix;
      $suffix = '</blockquote>';
    }
    else {
      $suffix = '</blockquote><p></p>';
    }
  }

  return $prefix.$body.$suffix;
}


function rcmail_create_forward_body($body, $bodyIsHtml)
{
  global $IMAP, $MESSAGE, $OUTPUT;

  // add attachments
  if (!isset($_SESSION['compose']['forward_attachments']) && is_array($MESSAGE->mime_parts))
    $cid_map = rcmail_write_compose_attachments($MESSAGE, $bodyIsHtml);

  if (!$bodyIsHtml)
  {
    $prefix = "\n\n\n-------- Original Message --------\n";
    $prefix .= 'Subject: ' . $MESSAGE->subject . "\n";
    $prefix .= 'Date: ' . $MESSAGE->headers->date . "\n";
    $prefix .= 'From: ' . $MESSAGE->get_header('from') . "\n";
    $prefix .= 'To: ' . $MESSAGE->get_header('to') . "\n";

    if ($MESSAGE->headers->cc)
      $prefix .= 'Cc: ' . $MESSAGE->get_header('cc') . "\n";
    if ($MESSAGE->headers->replyto && $MESSAGE->headers->replyto != $MESSAGE->headers->from)
      $prefix .= 'Reply-To: ' . $MESSAGE->get_header('replyto') . "\n";

    $prefix .= "\n";
  }
  else
  {
    // set is_safe flag (we need this for html body washing)
    rcmail_check_safe($MESSAGE);
    // clean up html tags
    $body = rcmail_wash_html($body, array('safe' => $MESSAGE->is_safe), $cid_map);

    $prefix = sprintf(
      "<br /><p>-------- Original Message --------</p>" .
        "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">Subject: </th><td>%s</td></tr>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">Date: </th><td>%s</td></tr>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">From: </th><td>%s</td></tr>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">To: </th><td>%s</td></tr>",
      Q($MESSAGE->subject),
      Q($MESSAGE->headers->date),
      htmlspecialchars(Q($MESSAGE->get_header('from'), 'replace'), ENT_COMPAT, $OUTPUT->get_charset()),
      htmlspecialchars(Q($MESSAGE->get_header('to'), 'replace'), ENT_COMPAT, $OUTPUT->get_charset()));

    if ($MESSAGE->headers->cc)
      $prefix .= sprintf("<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">Cc: </th><td>%s</td></tr>",
        htmlspecialchars(Q($MESSAGE->get_header('cc'), 'replace'), ENT_COMPAT, $OUTPUT->get_charset()));

    if ($MESSAGE->headers->replyto && $MESSAGE->headers->replyto != $MESSAGE->headers->from)
      $prefix .= sprintf("<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">Reply-To: </th><td>%s</td></tr>",
        htmlspecialchars(Q($MESSAGE->get_header('replyto'), 'replace'), ENT_COMPAT, $OUTPUT->get_charset()));

    $prefix .= "</tbody></table><br>";
  }
    
  return $prefix.$body;
}


function rcmail_create_draft_body($body, $bodyIsHtml)
{
  global $MESSAGE, $OUTPUT;
  
  /**
   * add attachments
   * sizeof($MESSAGE->mime_parts can be 1 - e.g. attachment, but no text!
   */
  if (empty($_SESSION['compose']['forward_attachments'])
      && is_array($MESSAGE->mime_parts)
      && count($MESSAGE->mime_parts) > 0)
  {
    $cid_map = rcmail_write_compose_attachments($MESSAGE, $bodyIsHtml);

    // replace cid with href in inline images links
    if ($cid_map)
      $body = str_replace(array_keys($cid_map), array_values($cid_map), $body);
  }
  
  return $body;
}


function rcmail_remove_signature($body)
{
  global $RCMAIL;

  $len = strlen($body);
  $sig_max_lines = $RCMAIL->config->get('sig_max_lines', 15);

  while (($sp = strrpos($body, "-- \n", $sp ? -$len+$sp-1 : 0)) !== false) {
    if ($sp == 0 || $body[$sp-1] == "\n") {
      // do not touch blocks with more that X lines
      if (substr_count($body, "\n", $sp) < $sig_max_lines) {
        $body = substr($body, 0, max(0, $sp-1));
      }
      break;
    }
  }

  return $body;
}


function rcmail_write_compose_attachments(&$message, $bodyIsHtml)
{
  global $OUTPUT;

  $cid_map = $messages = array();
  foreach ((array)$message->mime_parts as $pid => $part)
  {
    if (($part->ctype_primary != 'message' || !$bodyIsHtml) && $part->ctype_primary != 'multipart' && 
        ($part->disposition == 'attachment' || ($part->disposition == 'inline' && $bodyIsHtml) || $part->filename)
        && $part->mimetype != 'application/ms-tnef'
    ) {
      $skip = false;
      if ($part->mimetype == 'message/rfc822') {
        $messages[] = $part->mime_id;
      } else if ($messages) {
        // skip attachments included in message/rfc822 attachment (#1486487)
        foreach ($messages as $mimeid)
          if (strpos($part->mime_id, $mimeid.'.') === 0) {
            $skip = true;
            break;
          }
      }

      if (!$skip && ($attachment = rcmail_save_attachment($message, $pid))) {
        $_SESSION['compose']['attachments'][$attachment['id']] = $attachment;
        if ($bodyIsHtml && ($part->content_id || $part->content_location)) {
          $url = $OUTPUT->app->comm_path.'&_action=display-attachment&_file=rcmfile'.$attachment['id'];
          if ($part->content_id)
            $cid_map['cid:'.$part->content_id] = $url;
          else
            $cid_map[$part->content_location] = $url;
        }
      }
    }
  }

  $_SESSION['compose']['forward_attachments'] = true;

  return $cid_map;
}


function rcmail_write_inline_attachments(&$message)
{
  global $OUTPUT;

  $cid_map = array();
  foreach ((array)$message->mime_parts as $pid => $part) {
    if (($part->content_id || $part->content_location) && $part->filename) {
      if ($attachment = rcmail_save_attachment($message, $pid)) {
        $_SESSION['compose']['attachments'][$attachment['id']] = $attachment;
        $url = $OUTPUT->app->comm_path.'&_action=display-attachment&_file=rcmfile'.$attachment['id'];
        if ($part->content_id)
          $cid_map['cid:'.$part->content_id] = $url;
        else
          $cid_map[$part->content_location] = $url;
      }
    }
  }

  return $cid_map;
}

function rcmail_save_attachment(&$message, $pid)
{
  $part = $message->mime_parts[$pid];
  $mem_limit = parse_bytes(ini_get('memory_limit'));
  $curr_mem = function_exists('memory_get_usage') ? memory_get_usage() : 16*1024*1024; // safe value: 16MB
  $data = $path = null;

  // don't load too big attachments into memory
  if ($mem_limit > 0 && $part->size > $mem_limit - $curr_mem) {
    $rcmail = rcmail::get_instance();
    $temp_dir = unslashify($rcmail->config->get('temp_dir'));
    $path = tempnam($temp_dir, 'rcmAttmnt');
    if ($fp = fopen($path, 'w')) {
      $message->get_part_content($pid, $fp);
      fclose($fp);
    } else
      return false;
  } else {
    $data = $message->get_part_content($pid);
  }

  $attachment = array(
    'name' => $part->filename ? $part->filename : 'Part_'.$pid.'.'.$part->ctype_secondary,
    'mimetype' => $part->ctype_primary . '/' . $part->ctype_secondary,
    'content_id' => $part->content_id,
    'data' => $data,
    'path' => $path,
    'size' => $path ? filesize($path) : strlen($data),
  );

  $attachment = rcmail::get_instance()->plugins->exec_hook('attachment_save', $attachment);

  if ($attachment['status']) {
    unset($attachment['data'], $attachment['status'], $attachment['content_id'], $attachment['abort']);
    return $attachment;
  } else if ($path) {
    @unlink($path);
  }
  
  return false;
}

function rcmail_save_image($path, $mimetype='')
{
  // handle attachments in memory
  $data = file_get_contents($path);

  $attachment = array(
    'name' => rcmail_basename($path),
    'mimetype' => $mimetype ? $mimetype : rc_mime_content_type($path, $name),
    'data' => $data,
    'size' => strlen($data),
  );

  $attachment = rcmail::get_instance()->plugins->exec_hook('attachment_save', $attachment);

  if ($attachment['status']) {
    unset($attachment['data'], $attachment['status'], $attachment['content_id'], $attachment['abort']);
    return $attachment;
  }
  
  return false;
}

function rcmail_basename($filename)
{
  // basename() is not unicode safe and locale dependent
  if (stristr(PHP_OS, 'win') || stristr(PHP_OS, 'netware')) {
    return preg_replace('/^.*[\\\\\\/]/', '', $filename);
  } else {
    return preg_replace('/^.*[\/]/', '', $filename);
  }
}

function rcmail_compose_subject($attrib)
{
  global $MESSAGE, $compose_mode;
  
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  $attrib['name'] = '_subject';
  $attrib['spellcheck'] = 'true';
  $textfield = new html_inputfield($attrib);

  $subject = '';

  // use subject from post
  if (isset($_POST['_subject'])) {
    $subject = get_input_value('_subject', RCUBE_INPUT_POST, TRUE);
  }
  // create a reply-subject
  else if ($compose_mode == RCUBE_COMPOSE_REPLY) {
    if (preg_match('/^re:/i', $MESSAGE->subject))
      $subject = $MESSAGE->subject;
    else
      $subject = 'Re: '.$MESSAGE->subject;
  }
  // create a forward-subject
  else if ($compose_mode == RCUBE_COMPOSE_FORWARD) {
    if (preg_match('/^fwd:/i', $MESSAGE->subject))
      $subject = $MESSAGE->subject;
    else
      $subject = 'Fwd: '.$MESSAGE->subject;
  }
  // creeate a draft-subject
  else if ($compose_mode == RCUBE_COMPOSE_DRAFT || $compose_mode == RCUBE_COMPOSE_EDIT) {
    $subject = $MESSAGE->subject;
  }
  else if (!empty($_SESSION['compose']['param']['subject'])) {
    $subject = $_SESSION['compose']['param']['subject'];
  }
  
  $out = $form_start ? "$form_start\n" : '';
  $out .= $textfield->show($subject);
  $out .= $form_end ? "\n$form_end" : '';

  return $out;
}


function rcmail_compose_attachment_list($attrib)
{
  global $OUTPUT, $CONFIG;
  
  // add ID if not given
  if (!$attrib['id'])
    $attrib['id'] = 'rcmAttachmentList';
  
  $out = "\n";
  $jslist = array();

  if (is_array($_SESSION['compose']['attachments']))
  {
    if ($attrib['deleteicon']) {
      $button = html::img(array(
        'src' => $CONFIG['skin_path'] . $attrib['deleteicon'],
        'alt' => rcube_label('delete')
      ));
    }
    else
      $button = Q(rcube_label('delete'));

    foreach ($_SESSION['compose']['attachments'] as $id => $a_prop)
    {
      if (empty($a_prop))
        continue;
      
      $out .= html::tag('li', array('id' => 'rcmfile'.$id),
        html::a(array(
            'href' => "#delete",
            'title' => rcube_label('delete'),
            'onclick' => sprintf("return %s.command('remove-attachment','rcmfile%s', this)", JS_OBJECT_NAME, $id)),
          $button) . Q($a_prop['name']));
        
        $jslist['rcmfile'.$id] = array('name' => $a_prop['name'], 'complete' => true, 'mimetype' => $a_prop['mimetype']);
    }
  }

  if ($attrib['deleteicon'])
    $_SESSION['compose']['deleteicon'] = $CONFIG['skin_path'] . $attrib['deleteicon'];
  if ($attrib['cancelicon'])
    $OUTPUT->set_env('cancelicon', $CONFIG['skin_path'] . $attrib['cancelicon']);
  if ($attrib['loadingicon'])
    $OUTPUT->set_env('loadingicon', $CONFIG['skin_path'] . $attrib['loadingicon']);

  $OUTPUT->set_env('attachments', $jslist);
  $OUTPUT->add_gui_object('attachmentlist', $attrib['id']);
    
  return html::tag('ul', $attrib, $out, html::$common_attrib);
}


function rcmail_compose_attachment_form($attrib)
{
  global $OUTPUT;

  // add ID if not given
  if (!$attrib['id'])
    $attrib['id'] = 'rcmUploadbox';

  // find max filesize value
  $max_filesize = parse_bytes(ini_get('upload_max_filesize'));
  $max_postsize = parse_bytes(ini_get('post_max_size'));
  if ($max_postsize && $max_postsize < $max_filesize)
    $max_filesize = $max_postsize;
  $max_filesize = show_bytes($max_filesize);
  
  $button = new html_inputfield(array('type' => 'button'));
  
  $out = html::div($attrib,
    $OUTPUT->form_tag(array('name' => 'uploadform', 'method' => 'post', 'enctype' => 'multipart/form-data'),
      html::div(null, rcmail_compose_attachment_field(array('size' => $attrib[attachmentfieldsize]))) .
      html::div('hint', rcube_label(array('name' => 'maxuploadsize', 'vars' => array('size' => $max_filesize)))) .
      html::div('buttons',
        $button->show(rcube_label('close'), array('class' => 'button', 'onclick' => "$('#$attrib[id]').hide()")) . ' ' .
        $button->show(rcube_label('upload'), array('class' => 'button mainaction', 'onclick' => JS_OBJECT_NAME . ".command('send-attachment', this.form)"))
      )
    )
  );
  
  $OUTPUT->add_gui_object('uploadbox', $attrib['id']);
  return $out;
}


function rcmail_compose_attachment_field($attrib)
{
  $attrib['type'] = 'file';
  $attrib['name'] = '_attachments[]';
  $field = new html_inputfield($attrib);
  return $field->show();
}


function rcmail_priority_selector($attrib)
{
  global $MESSAGE;
  
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);

  $attrib['name'] = '_priority';
  $selector = new html_select($attrib);

  $selector->add(array(rcube_label('lowest'),
                       rcube_label('low'),
                       rcube_label('normal'),
                       rcube_label('high'),
                       rcube_label('highest')),
                 array(5, 4, 0, 2, 1));

  if (isset($_POST['_priority']))
    $sel = $_POST['_priority'];
  else if (intval($MESSAGE->headers->priority) != 3)
    $sel = intval($MESSAGE->headers->priority);
  else
    $sel = 0;

  $out = $form_start ? "$form_start\n" : '';
  $out .= $selector->show($sel);
  $out .= $form_end ? "\n$form_end" : '';

  return $out;
}


function rcmail_receipt_checkbox($attrib)
{
  global $RCMAIL, $MESSAGE, $compose_mode;

  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);

  if (!isset($attrib['id']))
    $attrib['id'] = 'receipt';  

  $attrib['name'] = '_receipt';
  $attrib['value'] = '1';
  $checkbox = new html_checkbox($attrib);

  if ($MESSAGE && in_array($compose_mode, array(RCUBE_COMPOSE_DRAFT, RCUBE_COMPOSE_EDIT)))
    $mdn_default = (bool) $MESSAGE->headers->mdn_to;
  else
    $mdn_default = $RCMAIL->config->get('mdn_default');

  $out = $form_start ? "$form_start\n" : '';
  $out .= $checkbox->show($mdn_default);
  $out .= $form_end ? "\n$form_end" : '';

  return $out;
}


function rcmail_dsn_checkbox($attrib)
{
  global $RCMAIL;

  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);

  if (!isset($attrib['id']))
    $attrib['id'] = 'dsn';

  $attrib['name'] = '_dsn';
  $attrib['value'] = '1';
  $checkbox = new html_checkbox($attrib);

  $out = $form_start ? "$form_start\n" : '';
  $out .= $checkbox->show($RCMAIL->config->get('dsn_default'));
  $out .= $form_end ? "\n$form_end" : '';

  return $out;
}


function rcmail_editor_selector($attrib)
{
  global $CONFIG, $MESSAGE, $compose_mode;

  // determine whether HTML or plain text should be checked
  $useHtml = rcmail_compose_editor_mode();

  if (empty($attrib['editorid']))
    $attrib['editorid'] = 'rcmComposeBody';

  if (empty($attrib['name']))
    $attrib['name'] = 'editorSelect';

  $attrib['onchange'] = "return rcmail_toggle_editor(this, '".$attrib['editorid']."', '_is_html')";

  $select = new html_select($attrib);

  $select->add(Q(rcube_label('htmltoggle')), 'html');
  $select->add(Q(rcube_label('plaintoggle')), 'plain');

  return $select->show($useHtml ? 'html' : 'plain');

  foreach ($choices as $value => $text) {
    $attrib['id'] = '_' . $value;
    $attrib['value'] = $value;
    $selector .= $radio->show($chosenvalue, $attrib) . html::label($attrib['id'], Q(rcube_label($text)));
  }

  return $selector;
}


function rcmail_store_target_selection($attrib)
{
  $attrib['name'] = '_store_target';
  $select = rcmail_mailbox_select(array_merge($attrib, array('noselection' => '- '.rcube_label('dontsave').' -')));
  return $select->show($_SESSION['compose']['param']['sent_mbox'], $attrib);
}


function get_form_tags($attrib)
{
  global $RCMAIL, $MESSAGE_FORM;

  $form_start = '';
  if (!$MESSAGE_FORM)
  {
    $hiddenfields = new html_hiddenfield(array('name' => '_task', 'value' => $RCMAIL->task));
    $hiddenfields->add(array('name' => '_action', 'value' => 'send'));

    $form_start = empty($attrib['form']) ? $RCMAIL->output->form_tag(array('name' => "form", 'method' => "post")) : '';
    $form_start .= $hiddenfields->show();
  }
    
  $form_end = ($MESSAGE_FORM && !strlen($attrib['form'])) ? '</form>' : '';
  $form_name = !empty($attrib['form']) ? $attrib['form'] : 'form';
  
  if (!$MESSAGE_FORM)
    $RCMAIL->output->add_gui_object('messageform', $form_name);
  
  $MESSAGE_FORM = $form_name;

  return array($form_start, $form_end);
}


// register UI objects
$OUTPUT->add_handlers(array(
  'composeheaders' => 'rcmail_compose_headers',
  'composesubject' => 'rcmail_compose_subject',
  'composebody' => 'rcmail_compose_body',
  'composeattachmentlist' => 'rcmail_compose_attachment_list',
  'composeattachmentform' => 'rcmail_compose_attachment_form',
  'composeattachment' => 'rcmail_compose_attachment_field',
  'priorityselector' => 'rcmail_priority_selector',
  'editorselector' => 'rcmail_editor_selector',
  'receiptcheckbox' => 'rcmail_receipt_checkbox',
  'dsncheckbox' => 'rcmail_dsn_checkbox',
  'storetarget' => 'rcmail_store_target_selection',
));

$OUTPUT->send('compose');



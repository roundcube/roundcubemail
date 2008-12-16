<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/compose.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
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

$MESSAGE_FORM = NULL;
$MESSAGE = NULL;

// Nothing below is called during message composition, only at "new/forward/reply/draft" initialization or
// if a compose-ID is given (i.e. when the compose step is opened in a new window/tab).
// Since there are many ways to leave the compose page improperly, it seems necessary to clean-up an old
// compose when a "new/forward/reply/draft" is called - otherwise the old session attachments will appear

if (!is_array($_SESSION['compose']) || $_SESSION['compose']['id'] != get_input_value('_id', RCUBE_INPUT_GET))
{
  rcmail_compose_cleanup();
  $_SESSION['compose'] = array('id' => uniqid(rand()), 'param' => array_map('strip_tags', $_GET));
  
  // process values like "mailto:foo@bar.com?subject=new+message&cc=another"
  if ($_SESSION['compose']['param']['_to']) {
    $mailto = explode('?', $_SESSION['compose']['param']['_to']);
    if (count($mailto) > 1) {
      $_SESSION['compose']['param']['_to'] = $mailto[0];
      parse_str($mailto[1], $query);
      foreach ($query as $f => $val)
        $_SESSION['compose']['param']["_$f"] = $val;
    }
  }

  // redirect to a unique URL with all parameters stored in session
  $OUTPUT->redirect(array('_action' => 'compose', '_id' => $_SESSION['compose']['id']));
}

// add some labels to client
$OUTPUT->add_label('nosubject', 'nosenderwarning', 'norecipientwarning', 'nosubjectwarning',
    'nobodywarning', 'notsentwarning', 'savingmessage', 'sendingmessage', 'messagesaved',
    'converting', 'editorwarning', 'searching');

// add config parameters to client script
if (!empty($CONFIG['drafts_mbox'])) {
  $OUTPUT->set_env('drafts_mailbox', $CONFIG['drafts_mbox']);
  $OUTPUT->set_env('draft_autosave', $CONFIG['draft_autosave']);
}
// set current mailbox in client environment
$OUTPUT->set_env('mailbox', $IMAP->get_mailbox_name());

// get reference message and set compose mode
if ($msg_uid = $_SESSION['compose']['param']['_reply_uid'])
  $compose_mode = RCUBE_COMPOSE_REPLY;
else if ($msg_uid = $_SESSION['compose']['param']['_forward_uid'])
  $compose_mode = RCUBE_COMPOSE_FORWARD;
else if ($msg_uid = $_SESSION['compose']['param']['_draft_uid'])
  $compose_mode = RCUBE_COMPOSE_DRAFT;

if (!empty($msg_uid))
{
  // similar as in program/steps/mail/show.inc
  $MESSAGE = new rcube_message($msg_uid);
  
  if (!empty($MESSAGE->headers->charset))
    $IMAP->set_charset($MESSAGE->headers->charset);
    
  if ($compose_mode == RCUBE_COMPOSE_REPLY)
  {
    $_SESSION['compose']['reply_uid'] = $msg_uid;
    $_SESSION['compose']['reply_msgid'] = $MESSAGE->headers->messageID;
    $_SESSION['compose']['references']  = trim($MESSAGE->headers->references . " " . $MESSAGE->headers->messageID);

    if (!empty($_SESSION['compose']['param']['_all']))
      $MESSAGE->reply_all = 1;
  }
  else if ($compose_mode == RCUBE_COMPOSE_DRAFT)
  {
    if($MESSAGE->headers->in_reply_to)
    {
      // TODO: how to get reply_uid/forward_uid value, maybe we must set X-Reply-UID/X-Forward-UID
      // $_SESSION['compose']['reply_uid'] = ?
      // $_SESSION['compose']['forward_uid'] = ?
      $_SESSION['compose']['reply_msgid'] = '<'.$MESSAGE->headers->in_reply_to.'>';
    }
    $_SESSION['compose']['references']  = $MESSAGE->headers->references;
  }
  else if ($compose_mode == RCUBE_COMPOSE_FORWARD)
  {
    $_SESSION['compose']['forward_uid'] = $msg_uid;
  }
}

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
      return rcmail_compose_header_from($attrib);

    case 'to':
      $fname = '_to';
      $header = 'to';
      
      // we have a set of recipients stored is session
      if (($mailto_id = $_SESSION['compose']['param']['_mailto']) && $_SESSION['mailto'][$mailto_id])
        $fvalue = urldecode($_SESSION['mailto'][$mailto_id]);
      
    case 'cc':
      if (!$fname)
      {
        $fname = '_cc';
        $header = 'cc';
      }
    case 'bcc':
      if (!$fname)
      {
        $fname = '_bcc';
        $header = 'bcc';
      }
        
      $allow_attrib = array('id', 'class', 'style', 'cols', 'rows', 'tabindex');
      $field_type = 'html_textarea';
      break;

    case 'replyto':
    case 'reply-to':
      $fname = '_replyto';
      $allow_attrib = array('id', 'class', 'style', 'size', 'tabindex');
      $field_type = 'html_inputfield';
      break;
  }
 
  if ($fname && !empty($_POST[$fname]))
    $fvalue = get_input_value($fname, RCUBE_INPUT_POST, TRUE);
  else if ($fname && !$fvalue && !empty($_SESSION['compose']['param'][$fname]))
    $fvalue = $_SESSION['compose']['param'][$fname];

  else if ($header && $compose_mode == RCUBE_COMPOSE_REPLY)
  {
    // get recipent address(es) out of the message headers
    if ($header=='to' && !empty($MESSAGE->headers->replyto))
      $fvalue = $MESSAGE->headers->replyto;

    else if ($header=='to' && !empty($MESSAGE->headers->from))
      $fvalue = $MESSAGE->headers->from;

    // add recipent of original message if reply to all
    else if ($header=='cc' && !empty($MESSAGE->reply_all))
    {
      if ($v = $MESSAGE->headers->to)
        $fvalue .= $v;

      if ($v = $MESSAGE->headers->cc)
        $fvalue .= (!empty($fvalue) ? ', ' : '') . $v;
    }

    // split recipients and put them back together in a unique way
    if (!empty($fvalue))
    {
      $to_addresses = $IMAP->decode_address_list($fvalue);
      $fvalue = '';

      foreach ($to_addresses as $addr_part)
      {
        if (!empty($addr_part['mailto'])
            && !in_array($addr_part['mailto'], $sa_recipients)
            && (!$MESSAGE->compose_from
                || !in_array_nocase($addr_part['mailto'], $MESSAGE->compose_from)
                || (count($to_addresses)==1 && $header=='to'))) // allow reply to yourself
        {
          $fvalue .= (strlen($fvalue) ? ', ':'').$addr_part['string'];
          $sa_recipients[] = $addr_part['mailto'];
        }
      }
    }
  }
  else if ($header && $compose_mode == RCUBE_COMPOSE_DRAFT)
  {
    // get drafted headers
    if ($header=='to' && !empty($MESSAGE->headers->to))
      $fvalue = $MESSAGE->get_header('to');

    if ($header=='cc' && !empty($MESSAGE->headers->cc))
      $fvalue = $MESSAGE->get_header('cc');

    if ($header=='bcc' && !empty($MESSAGE->headers->bcc))
      $fvalue = $MESSAGE->get_header('bcc');
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
        $a_recipients[] = rc_strtolower($addr['mailto']);
    }

    if (!empty($MESSAGE->headers->cc))
    {
      $a_cc = $IMAP->decode_address_list($MESSAGE->headers->cc);
      foreach ($a_cc as $addr)
      {
        if (!empty($addr['mailto']))
          $a_recipients[] = rc_strtolower($addr['mailto']);
      }
    }
  }

  // get this user's identities
  $sql_result = $USER->list_identities();

  if ($DB->num_rows($sql_result))
  {
    $from_id = 0;
    $a_signatures = array();

    $field_attrib['onchange'] = JS_OBJECT_NAME.".change_identity(this)";
    $select_from = new html_select($field_attrib);

    while ($sql_arr = $DB->fetch_assoc($sql_result))
    {
      $identity_id = $sql_arr['identity_id'];
      $select_from->add(format_email_recipient($sql_arr['email'], $sql_arr['name']), $identity_id);

      // add signature to array
      if (!empty($sql_arr['signature']))
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
        $MESSAGE->compose_from[] = $sql_arr['email'];

      if (empty($_POST['_from']))
      {
        // set draft's identity
        if ($compose_mode == RCUBE_COMPOSE_DRAFT && strstr($MESSAGE->headers->from, $sql_arr['email']))
          $from_id = $sql_arr['identity_id'];
        // set identity if it's one of the reply-message recipients (with prio for default identity)
        else if (in_array(rc_strtolower($sql_arr['email']), $a_recipients) && (empty($from_id) || $sql_arr['standard']))
          $from_id = $sql_arr['identity_id'];
      }
    }

    // overwrite identity selection with post parameter
    if (!empty($_POST['_from']))
      $from_id = get_input_value('_from', RCUBE_INPUT_POST);

    $out = $select_from->show($from_id);

    // add signatures to client
    $OUTPUT->set_env('signatures', $a_signatures);
  }
  else
  {
    $input_from = new html_inputfield($field_attrib);
    $out = $input_from->show($_POST['_from']);
  }
  
  if ($form_start)
    $out = $form_start.$out;

  return $out;
}


function rcmail_compose_body($attrib)
{
  global $RCMAIL, $CONFIG, $OUTPUT, $MESSAGE, $compose_mode;
  
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  if (empty($attrib['id']))
    $attrib['id'] = 'rcmComposeMessage';

  $attrib['name'] = '_message';

  if ($CONFIG['htmleditor'])
    $isHtml = true;
  else
    $isHtml = false;

  $body = '';

  // use posted message body
  if (!empty($_POST['_message']))
  {
    $body = get_input_value('_message', RCUBE_INPUT_POST, true);
  }
  else if ($compose_mode)
  {
    if ($isHtml && $MESSAGE->has_html_part())
    {
      $body = $MESSAGE->first_html_part();
      $isHtml = true;
    }
    else
    {
      $body = $MESSAGE->first_text_part();
      $isHtml = false;
    }
    
    // compose reply-body
    if ($compose_mode == RCUBE_COMPOSE_REPLY)
      $body = rcmail_create_reply_body($body, $isHtml);
    // forward message body inline
    else if ($compose_mode == RCUBE_COMPOSE_FORWARD)
      $body = rcmail_create_forward_body($body, $isHtml);
    // load draft message body
    else if ($compose_mode == RCUBE_COMPOSE_DRAFT)
      $body = rcmail_create_draft_body($body, $isHtml);

    if ($isHtml) {
      // replace cid with href in inline images links
      foreach ((array)$_SESSION['compose']['attachments'] as $pid => $attachment) {
        if ($attachment['content_id']) {
          $body = str_replace('cid:'. $attachment['content_id'], 
            $OUTPUT->app->comm_path.'&_action=display-attachment&_file=rcmfile'.$pid, $body);
        }
      }
    }
  }
  else if (!empty($_SESSION['compose']['param']['_body']))
  {
    $body = $_SESSION['compose']['param']['_body'];
  }

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
  $out .= $textarea->show($body);
  $out .= $form_end ? "\n$form_end" : '';

  // include HTML editor
  rcube_html_editor();
  
  // include GoogieSpell
  if (!empty($CONFIG['enable_spellcheck'])) {

    $lang = strtolower(substr($_SESSION['language'], 0, 2));
  
    $spellcheck_langs = (array)$RCMAIL->config->get('spellcheck_languages', array('da'=>'Dansk', 'de'=>'Deutsch', 'en' => 'English', 'es'=>'Español', 'fr'=>'Français', 'it'=>'Italiano', 'nl'=>'Nederlands', 'pl'=>'Polski', 'pt'=>'Português', 'fi'=>'Suomi', 'sv'=>'Svenska'));
    if (!$spellcheck_langs[$lang])
      $lang = 'en';
    
    $editor_lang_set = array();
    foreach ($spellcheck_langs as $key => $name) {
      $editor_lang_set[] = ($key == $lang ? '+' : '') . JQ($name).'='.JQ($key);
      }
    
    $OUTPUT->include_script('googiespell.js');
    $OUTPUT->add_script(sprintf(
      "var googie = new GoogieSpell('\$__skin_path/images/googiespell/','%s&_action=spell&lang=');\n".
      "googie.lang_chck_spell = \"%s\";\n".
      "googie.lang_rsm_edt = \"%s\";\n".
      "googie.lang_close = \"%s\";\n".
      "googie.lang_revert = \"%s\";\n".
      "googie.lang_no_error_found = \"%s\";\n".
      "googie.setLanguages(%s);\n".
      "googie.setCurrentLanguage('%s');\n".
      "googie.decorateTextarea('%s');\n".
      "%s.set_env('spellcheck', googie);",
      $RCMAIL->comm_path,
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
  global $IMAP, $MESSAGE, $OUTPUT;

  if (! $bodyIsHtml)
  {
    // try to remove the signature
    if (($sp = strrpos($body, '-- ')) !== false && ($sp == 0 || $body{$sp-1} == "\n"))
      {
      if ($body{$sp+3}==' ' || $body{$sp+3}=="\n" || $body{$sp+3}=="\r")
        $body = substr($body, 0, max(0, $sp-1));
      }

    // soft-wrap message first
    $body = rcmail_wrap_quoted($body, 75);

    $body = rtrim($body, "\r\n");

    if ($body) {
      // split body into single lines
      $a_lines = preg_split('/\r?\n/', $body);

      // add > to each line
      for($n=0; $n<sizeof($a_lines); $n++) {
        if (strpos($a_lines[$n], '>')===0)
          $a_lines[$n] = '>'.$a_lines[$n];
        else
          $a_lines[$n] = '> '.$a_lines[$n];
        }
 
      $body = join("\n", $a_lines);
      }

    // add title line(s)
    $prefix = wordwrap(sprintf("On %s, %s wrote:\n",
      $MESSAGE->headers->date,
      $MESSAGE->get_header('from')), 76);

    $suffix = '';
  }
  else
  {
    $prefix = sprintf("On %s, %s wrote:<br />\n",
      $MESSAGE->headers->date,
      htmlspecialchars(Q($MESSAGE->get_header('from'), 'replace'), ENT_COMPAT, $OUTPUT->get_charset()));
    $prefix .= '<blockquote type="cite" style="padding-left:5px; border-left:#1010ff 2px solid; margin-left:5px; width:100%">';
    $suffix = "</blockquote><p></p>";

    rcmail_write_inline_attachments($MESSAGE);
  }

  return $prefix.$body.$suffix;
}


function rcmail_create_forward_body($body, $bodyIsHtml)
{
  global $IMAP, $MESSAGE, $OUTPUT;

  if (!$bodyIsHtml)
  {
    $prefix = "\n\n\n-------- Original Message --------\n";
    $prefix .= 'Subject: ' . $MESSAGE->subject . "\n";
    $prefix .= 'Date: ' . $MESSAGE->headers->date . "\n";
    $prefix .= 'From: ' . $MESSAGE->get_header('from') . "\n";
    $prefix .= 'To: ' . $MESSAGE->get_header('to') . "\n";
    if ($MESSAGE->headers->replyto && $MESSAGE->headers->replyto != $MESSAGE->headers->from)
      $prefix .= 'Reply-To: ' . $MESSAGE->get_header('replyto') . "\n";
    $prefix .= "\n";
  }
  else
  {
    $prefix = sprintf(
      "<br><br>-------- Original Message --------" .
        "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">Subject: </th><td>%s</td></tr>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">Date: </th><td>%s</td></tr>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">From: </th><td>%s</td></tr>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">To: </th><td>%s</td></tr>",
      Q($MESSAGE->subject),
      Q($MESSAGE->headers->date),
      htmlspecialchars(Q($MESSAGE->get_header('from'), 'replace'), ENT_COMPAT, $OUTPUT->get_charset(), true),
	htmlspecialchars(Q($MESSAGE->get_header('to'), 'replace'), ENT_COMPAT, $OUTPUT->get_charset(), true));

    if ($MESSAGE->headers->replyto && $MESSAGE->headers->replyto != $MESSAGE->headers->from)
      $prefix .= sprintf("<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">Reply-To: </th><td>%s</td></tr>",
	htmlspecialchars(Q($MESSAGE->get_header('replyto'), 'replace'), ENT_COMPAT, $OUTPUT->get_charset(), true));

    $prefix .= "</tbody></table><br>";
  }

  // add attachments
  if (!isset($_SESSION['compose']['forward_attachments']) && is_array($MESSAGE->mime_parts))
    rcmail_write_compose_attachments($MESSAGE, $bodyIsHtml);
    
  return $prefix.$body;
}


function rcmail_create_draft_body($body, $bodyIsHtml)
{
  global $MESSAGE;
  
  /**
   * add attachments
   * sizeof($MESSAGE->mime_parts can be 1 - e.g. attachment, but no text!
   */
  if (!isset($_SESSION['compose']['forward_attachments'])
      && is_array($MESSAGE->mime_parts)
      && count($MESSAGE->mime_parts) > 0)
    rcmail_write_compose_attachments($MESSAGE, $bodyIsHtml);

  return $body;
}
  
  
function rcmail_write_compose_attachments(&$message, $bodyIsHtml)
{
  foreach ((array)$message->mime_parts as $pid => $part)
  {
    if (($part->ctype_primary != 'message' || !$bodyIsHtml) &&
        ($part->disposition=='attachment' || $part->disposition=='inline' || $part->headers['content-id']
         || (empty($part->disposition) && $part->filename)))
    {
      if ($attachment = rcmail_save_attachment($message, $pid))
        $_SESSION['compose']['attachments'][] = $attachment;
    }
  }
	
  $_SESSION['compose']['forward_attachments'] = true;
}


function rcmail_write_inline_attachments(&$message)
{
  foreach ((array)$message->mime_parts as $pid => $part)
  {
    if ($part->content_id && $part->filename)
    {
      if ($attachment = rcmail_save_attachment($message, $pid))
        $_SESSION['compose']['attachments'][] = $attachment;
    }
  }
}

function rcmail_save_attachment(&$message, $pid)
{
  global $RCMAIL;

  $temp_dir = unslashify($RCMAIL->config->get('temp_dir'));
  $tmp_path = tempnam($temp_dir, 'rcmAttmnt');
  $part = $message->mime_parts[$pid];
  
  if ($fp = fopen($tmp_path, 'w'))
  {
    $message->get_part_content($pid, $fp);
    fclose($fp);

    return array(
        'mimetype' => $part->ctype_primary . '/' . $part->ctype_secondary,
        'name' => $part->filename,
        'path' => $tmp_path,
	'content_id' => $part->content_id
    );
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
    if (eregi('^re:', $MESSAGE->subject))
      $subject = $MESSAGE->subject;
    else
      $subject = 'Re: '.$MESSAGE->subject;
  }
  // create a forward-subject
  else if ($compose_mode == RCUBE_COMPOSE_FORWARD) {
    if (eregi('^fwd:', $MESSAGE->subject))
      $subject = $MESSAGE->subject;
    else
      $subject = 'Fwd: '.$MESSAGE->subject;
  }
  // creeate a draft-subject
  else if ($compose_mode == RCUBE_COMPOSE_DRAFT) {
    $subject = $MESSAGE->subject;
  }
  else if (!empty($_SESSION['compose']['param']['_subject'])) {
    $subject = $_SESSION['compose']['param']['_subject'];
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
  
  if (is_array($_SESSION['compose']['attachments']))
  {
    if ($attrib['deleteicon'])
      $button = html::img(array(
        'src' => $CONFIG['skin_path'] . $attrib['deleteicon'],
        'alt' => rcube_label('delete'),
        'style' => "padding-right:2px;vertical-align:middle"));
    else
      $button = Q(rcube_label('delete'));

    foreach ($_SESSION['compose']['attachments'] as $id => $a_prop)
    {
      if (empty($a_prop))
        continue;
      
      $out .= html::tag('li', array('id' => "rcmfile".$id),
        html::a(array(
            'href' => "#delete",
            'title' => rcube_label('delete'),
            'onclick' => sprintf("return %s.command('remove-attachment','rcmfile%d', this)", JS_OBJECT_NAME, $id)),
          $button) . Q($a_prop['name']));
    }
  }

  $OUTPUT->add_gui_object('attachmentlist', $attrib['id']);
    
  return html::tag('ul', $attrib, $out, html::$common_attrib);
}


function rcmail_compose_attachment_form($attrib)
{
  global $OUTPUT;

  // add ID if not given
  if (!$attrib['id'])
    $attrib['id'] = 'rcmUploadbox';
  
  $button = new html_inputfield(array('type' => 'button', 'class' => 'button'));
  
  $out = html::div($attrib,
    $OUTPUT->form_tag(array('name' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'),
      html::div(null, rcmail_compose_attachment_field(array())) .
      html::div('hint', rcube_label(array('name' => 'maxuploadsize', 'vars' => array('size' => show_bytes(parse_bytes(ini_get('upload_max_filesize'))))))) .
      html::div('buttons',
        $button->show(rcube_label('close'), array('onclick' => "document.getElementById('$attrib[id]').style.visibility='hidden'")) . ' ' .
        $button->show(rcube_label('upload'), array('onclick' => JS_OBJECT_NAME . ".command('send-attachment', this.form)"))
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
                 
  $sel = isset($_POST['_priority']) ? $_POST['_priority'] : intval($MESSAGE->headers->priority);

  $out = $form_start ? "$form_start\n" : '';
  $out .= $selector->show($sel);
  $out .= $form_end ? "\n$form_end" : '';
         
  return $out;
}


function rcmail_receipt_checkbox($attrib)
{
  global $MESSAGE, $compose_mode;
  
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  if (!isset($attrib['id']))
    $attrib['id'] = 'receipt';  

  $attrib['name'] = '_receipt';
  $attrib['value'] = '1';
  $checkbox = new html_checkbox($attrib);

  $out = $form_start ? "$form_start\n" : '';
  $out .= $checkbox->show(
    $compose_mode == RCUBE_COMPOSE_DRAFT && $MESSAGE->headers->mdn_to ? 1 : 0);
  $out .= $form_end ? "\n$form_end" : '';

  return $out;
}


function rcmail_editor_selector($attrib)
{
  global $CONFIG, $MESSAGE, $compose_mode;

  $choices = array(
    'html'  => 'htmltoggle',
    'plain' => 'plaintoggle'
  );

  // determine whether HTML or plain text should be checked
  $useHtml = $CONFIG['htmleditor'] ? true : false;

  if ($compose_mode)
    $useHtml = ($useHtml && $MESSAGE->has_html_part());

  $editorid = empty($attrib['editorid']) ? 'rcmComposeMessage' : $attrib['editorid'];

  $selector = '';
  $chosenvalue = $useHtml ? 'html' : 'plain';
  $radio = new html_radiobutton(array('name' => '_editorSelect',
    'onclick' => "return rcmail_toggle_editor(this.value=='html', '$editorid', '_is_html')"));

  foreach ($choices as $value => $text)
  {
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
  return $select->show(rcmail::get_instance()->config->get('sent_mbox'), $attrib);
}


function get_form_tags($attrib)
{
  global $RCMAIL, $MESSAGE_FORM;

  $form_start = '';
  if (!strlen($MESSAGE_FORM))
  {
    $hiddenfields = new html_hiddenfield(array('name' => '_task', 'value' => $RCMAIL->task));
    $hiddenfields->add(array('name' => '_action', 'value' => 'send'));

    $form_start = empty($attrib['form']) ? $RCMAIL->output->form_tag(array('name' => "form", 'method' => "post")) : '';
    $form_start .= $hiddenfields->show();
  }
    
  $form_end = (strlen($MESSAGE_FORM) && !strlen($attrib['form'])) ? '</form>' : '';
  $form_name = !empty($attrib['form']) ? $attrib['form'] : 'form';
  
  if (!strlen($MESSAGE_FORM))
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
  'storetarget' => 'rcmail_store_target_selection',
));

$OUTPUT->send('compose');

?>

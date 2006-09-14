<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/compose.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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

require_once('Mail/mimeDecode.php');

// define constants for message compose mode
define('RCUBE_COMPOSE_REPLY', 0x0106);
define('RCUBE_COMPOSE_FORWARD', 0x0107);
define('RCUBE_COMPOSE_DRAFT', 0x0108);


// remove an attachment
if ($_action=='remove-attachment' && preg_match('/^rcmfile([0-9]+)$/', $_GET['_file'], $regs))
  {
  $id = $regs[1];
  if (is_array($_SESSION['compose']['attachments'][$id]))
    {
    @unlink($_SESSION['compose']['attachments'][$id]['path']);
    $_SESSION['compose']['attachments'][$id] = NULL;
    $commands = sprintf("parent.%s.remove_from_attachment_list('rcmfile%d');\n", $JS_OBJECT_NAME, $id);
    rcube_remote_response($commands);  
    exit;
    }
  }


$MESSAGE_FORM = NULL;
$MESSAGE = NULL;

// nothing below is called during message composition, only at "new/forward/reply/draft" initialization
// since there are many ways to leave the compose page improperly, it seems necessary to clean-up an old
// compose when a "new/forward/reply/draft" is called - otherwise the old session attachments will appear

rcmail_compose_cleanup();
$_SESSION['compose'] = array('id' => uniqid(rand()));

// add some labels to client
rcube_add_label('nosubject', 'norecipientwarning', 'nosubjectwarning', 'nobodywarning', 'notsentwarning', 'savingmessage', 'sendingmessage', 'messagesaved', 'converting');

// add config parameter to client script
$OUTPUT->add_script(sprintf("%s.set_env('draft_autosave', %d);", $JS_OBJECT_NAME, !empty($CONFIG['drafts_mbox']) ? $CONFIG['draft_autosave'] : 0));


// get reference message and set compose mode
if ($msg_uid = get_input_value('_reply_uid', RCUBE_INPUT_GET))
  $compose_mode = RCUBE_COMPOSE_REPLY;
else if ($msg_uid = get_input_value('_forward_uid', RCUBE_INPUT_GET))
  $compose_mode = RCUBE_COMPOSE_FORWARD;
else if ($msg_uid = get_input_value('_draft_uid', RCUBE_INPUT_GET))
  $compose_mode = RCUBE_COMPOSE_DRAFT;


if (!empty($msg_uid))
  {
  // similar as in program/steps/mail/show.inc
  $MESSAGE = array('UID' => $msg_uid);
  $MESSAGE['headers'] = &$IMAP->get_headers($msg_uid);
  $MESSAGE['structure'] = &$IMAP->get_structure($msg_uid);  
  $MESSAGE['subject'] = $IMAP->decode_header($MESSAGE['headers']->subject);
  $MESSAGE['parts'] = $IMAP->get_mime_numbers($MESSAGE['structure']);

  if ($compose_mode == RCUBE_COMPOSE_REPLY)
    {
    $_SESSION['compose']['reply_uid'] = $msg_uid;
    $_SESSION['compose']['reply_msgid'] = $MESSAGE['headers']->messageID;
    $_SESSION['compose']['references']  = $MESSAGE['headers']->reference;
    $_SESSION['compose']['references'] .= !empty($MESSAGE['headers']->reference) ? ' ' : '';
    $_SESSION['compose']['references'] .= $MESSAGE['headers']->messageID;

    if (!empty($_GET['_all']))
      $MESSAGE['reply_all'] = 1;
    }
  else if ($compose_mode == RCUBE_COMPOSE_FORWARD)
    {
    $_SESSION['compose']['forward_uid'] = $msg_uid;
    }
  else if ($compose_mode == RCUBE_COMPOSE_DRAFT)
    {
    $_SESSION['compose']['draft_uid'] = $msg_uid;
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

      // we have contact id's as get parameters
      if (!empty($_GET['_to']) && preg_match('/^[0-9]+(,[0-9]+)*$/', $_GET['_to']))
        {
        $a_recipients = array();
        $sql_result = $DB->query("SELECT name, email
                                  FROM ".get_table_name('contacts')."
                                  WHERE user_id=?
                                  AND    del<>1
                                  AND    contact_id IN (".$_GET['_to'].")",
                                  $_SESSION['user_id']);
                                         
        while ($sql_arr = $DB->fetch_assoc($sql_result))
          $a_recipients[] = format_email_recipient($sql_arr['email'], $sql_arr['name']);
          
        if (sizeof($a_recipients))
          $fvalue = join(', ', $a_recipients);
        }
      else if (!empty($_GET['_to']))
        $fvalue = get_input_value('_to', RCUBE_INPUT_GET);
        
    case 'cc':
      if (!$fname)
        {
        $fname = '_cc';
        $header = 'cc';
        }
    case 'bcc':
      if (!$fname)
        $fname = '_bcc';
        
      $allow_attrib = array('id', 'class', 'style', 'cols', 'rows', 'wrap', 'tabindex');
      $field_type = 'textarea';            
      break;

    case 'replyto':
    case 'reply-to':
      $fname = '_replyto';
      $allow_attrib = array('id', 'class', 'style', 'size', 'tabindex');
      $field_type = 'textfield';
      break;
    
    }
 
  if ($fname && !empty($_POST[$fname]))
    $fvalue = get_input_value($fname, RCUBE_INPUT_POST, TRUE);

  else if ($header && $compose_mode == RCUBE_COMPOSE_REPLY)
    {
    // get recipent address(es) out of the message headers
    if ($header=='to' && !empty($MESSAGE['headers']->replyto))
      $fvalue = $IMAP->decode_header($MESSAGE['headers']->replyto);

    else if ($header=='to' && !empty($MESSAGE['headers']->from))
      $fvalue = $IMAP->decode_header($MESSAGE['headers']->from);

    // add recipent of original message if reply to all
    else if ($header=='cc' && !empty($MESSAGE['reply_all']))
      {
      if ($v = $IMAP->decode_header($MESSAGE['headers']->to))
        $fvalue .= $v;

      if ($v = $IMAP->decode_header($MESSAGE['headers']->cc))
        $fvalue .= (!empty($fvalue) ? ', ' : '') . $v;
      }

    // split recipients and put them back together in a unique way
    if (!empty($fvalue))
      {
      $to_addresses = $IMAP->decode_address_list($fvalue);
      $fvalue = '';
      foreach ($to_addresses as $addr_part)
        {
        if (!in_array($addr_part['mailto'], $sa_recipients) && (!$MESSAGE['FROM'] || !in_array($addr_part['mailto'], $MESSAGE['FROM'])))
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
    if ($header=='to' && !empty($MESSAGE['headers']->to))
      $fvalue = $IMAP->decode_header($MESSAGE['headers']->to);

    if ($header=='cc' && !empty($MESSAGE['headers']->cc))
      $fvalue = $IMAP->decode_header($MESSAGE['headers']->cc);

    if ($header=='bcc' && !empty($MESSAGE['headers']->bcc))
      $fvalue = $IMAP->decode_header($MESSAGE['headers']->bcc);

    }

        
  if ($fname && $field_type)
    {
    // pass the following attributes to the form class
    $field_attrib = array('name' => $fname);
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
  global $IMAP, $MESSAGE, $DB, $OUTPUT, $JS_OBJECT_NAME, $compose_mode;
    
  // pass the following attributes to the form class
  $field_attrib = array('name' => '_from');
  foreach ($attrib as $attr => $value)
    if (in_array($attr, array('id', 'class', 'style', 'size', 'tabindex')))
      $field_attrib[$attr] = $value;

  // extract all recipients of the reply-message
  $a_recipients = array();
  if ($compose_mode == RCUBE_COMPOSE_REPLY && is_object($MESSAGE['headers']))
    {
    $MESSAGE['FROM'] = array();

    $a_to = $IMAP->decode_address_list($MESSAGE['headers']->to);        
    foreach ($a_to as $addr)
      {
      if (!empty($addr['mailto']))
        $a_recipients[] = $addr['mailto'];
      }

    if (!empty($MESSAGE['headers']->cc))
      {
      $a_cc = $IMAP->decode_address_list($MESSAGE['headers']->cc);
      foreach ($a_cc as $addr)
        {
        if (!empty($addr['mailto']))
          $a_recipients[] = $addr['mailto'];
        }
      }
    }

  // get this user's identities
  $sql_result = $DB->query("SELECT identity_id, name, email, signature, html_signature
                            FROM   ".get_table_name('identities')."
                            WHERE user_id=?
                            AND    del<>1
                            ORDER BY ".$DB->quoteIdentifier('standard')." DESC, name ASC",
                           $_SESSION['user_id']);

  if ($DB->num_rows($sql_result))
    {
    $from_id = 0;
    $a_signatures = array();

    $field_attrib['onchange'] = "$JS_OBJECT_NAME.change_identity(this)";
    $select_from = new select($field_attrib);

    while ($sql_arr = $DB->fetch_assoc($sql_result))
      {
      $identity_id = $sql_arr['identity_id'];
      $select_from->add(format_email_recipient($sql_arr['email'], $sql_arr['name']), $identity_id);

      // add signature to array
      if (!empty($sql_arr['signature']))
        {
        $a_signatures[$identity_id]['text'] = $sql_arr['signature'];
        $a_signatures[$identity_id]['is_html'] = ($sql_arr['html_signature'] == 1) ? true : false;
        }

      // set identity if it's one of the reply-message recipients
      if (in_array($sql_arr['email'], $a_recipients))
        $from_id = $sql_arr['identity_id'];

      if ($compose_mode == RCUBE_COMPOSE_REPLY && is_array($MESSAGE['FROM']))
        $MESSAGE['FROM'][] = $sql_arr['email'];

      if ($compose_mode == RCUBE_COMPOSE_DRAFT && strstr($MESSAGE['headers']->from, $sql_arr['email']))
        $from_id = $sql_arr['identity_id'];
      }

    // overwrite identity selection with post parameter
    if (isset($_POST['_from']))
      $from_id = get_input_value('_from', RCUBE_INPUT_POST);

    $out = $select_from->show($from_id);

    // add signatures to client
    $OUTPUT->add_script(sprintf("%s.set_env('signatures', %s);", $JS_OBJECT_NAME, array2js($a_signatures)));  
    }
  else
    {
    $input_from = new textfield($field_attrib);
    $out = $input_from->show($_POST['_from']);
    }
  
  if ($form_start)
    $out = $form_start.$out;

  return $out;
  }

  

function rcmail_compose_body($attrib)
  {
  global $CONFIG, $OUTPUT, $MESSAGE, $JS_OBJECT_NAME, $compose_mode;
  
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
    $body = get_input_value('_message', RCUBE_INPUT_POST, TRUE);
    }
  // compose reply-body
  else if ($compose_mode == RCUBE_COMPOSE_REPLY)
    {
    $hasHtml = rcmail_has_html_part($MESSAGE['parts']); 
    if ($hasHtml && $CONFIG['htmleditor'])
      {
      $body = rcmail_first_html_part($MESSAGE);
      $isHtml = true;
      }
    else
      {
      $body = rcmail_first_text_part($MESSAGE);
      $isHtml = false;
      }
    if (strlen($body))
      $body = rcmail_create_reply_body($body, $isHtml);
    }
  // forward message body inline
  else if ($compose_mode == RCUBE_COMPOSE_FORWARD)
    {
    $hasHtml = rcmail_has_html_part($MESSAGE['parts']);
    if ($hasHtml && $CONFIG['htmleditor'])
      {
      $body = rcmail_first_html_part($MESSAGE);
      $isHtml = true;
      }
    else
      {
      $body = rcmail_first_text_part($MESSAGE);
      $isHtml = false;
      }
    if (strlen($body))
      $body = rcmail_create_forward_body($body, $isHtml);
    }
  else if ($compose_mode == RCUBE_COMPOSE_DRAFT)
    {
    $hasHtml = rcmail_has_html_part($MESSAGE['parts']);
    if ($hasHtml && $CONFIG['htmleditor'])
      {
      $body = rcmail_first_html_part($MESSAGE);
      $isHtml = true;
      }
    else
      {
      $body = rcmail_first_text_part($MESSAGE);
      $isHtml = false;
      }
    if (strlen($body))
      $body = rcmail_create_draft_body($body, $isHtml);
    }

  $OUTPUT->include_script('tiny_mce/tiny_mce.js');
  $OUTPUT->include_script("editor.js");
  $OUTPUT->add_script('rcmail_editor_init($__skin_path);');

  $out = $form_start ? "$form_start\n" : '';

  $saveid = new hiddenfield(array('name' => '_draft_saveid', 'value' => str_replace(array('<','>'),"",$MESSAGE['headers']->messageID) ));
  $out .= $saveid->show();

  $drafttoggle = new hiddenfield(array('name' => '_draft', 'value' => 'yes'));
  $out .= $drafttoggle->show();

  $msgtype = new hiddenfield(array('name' => '_is_html', 'value' => ($isHtml?"1":"0")));
  $out .= $msgtype->show();

  // If desired, set this text area to be editable by TinyMCE
  if ($isHtml)
    $attrib['mce_editable'] = "true";
  $textarea = new textarea($attrib);
  $out .= $textarea->show($body);
  $out .= $form_end ? "\n$form_end" : '';

  // include GoogieSpell
  if (!empty($CONFIG['enable_spellcheck']) && !$isHtml)
    {
    $lang_set = '';
    if (!empty($CONFIG['spellcheck_languages']) && is_array($CONFIG['spellcheck_languages']))
      $lang_set = "googie.setLanguages(".array2js($CONFIG['spellcheck_languages']).");\n";
    
    $OUTPUT->include_script('googiespell.js');
    $OUTPUT->add_script(sprintf("var googie = new GoogieSpell('\$__skin_path/images/googiespell/','%s&_action=spell&lang=');\n".
                                "googie.lang_chck_spell = \"%s\";\n".
                                "googie.lang_rsm_edt = \"%s\";\n".
                                "googie.lang_close = \"%s\";\n".
                                "googie.lang_revert = \"%s\";\n".
                                "googie.lang_no_error_found = \"%s\";\n%s".
                                "googie.setCurrentLanguage('%s');\n".
                                "googie.decorateTextarea('%s');\n".
                                "%s.set_env('spellcheck', googie);",
                                $GLOBALS['COMM_PATH'],
                                rep_specialchars_output(rcube_label('checkspelling')),
                                rep_specialchars_output(rcube_label('resumeediting')),
                                rep_specialchars_output(rcube_label('close')),
                                rep_specialchars_output(rcube_label('revertto')),
                                rep_specialchars_output(rcube_label('nospellerrors')),
                                $lang_set,
                                substr($_SESSION['user_lang'], 0, 2),
                                $attrib['id'],
                                $JS_OBJECT_NAME), 'foot');

    rcube_add_label('checking');
    }
 
  $out .= "\n".'<iframe name="savetarget" src="program/blank.gif" style="width:0;height:0;visibility:hidden;"></iframe>';

  return $out;
  }


function rcmail_create_reply_body($body, $bodyIsHtml)
  {
  global $IMAP, $MESSAGE;

  if (! $bodyIsHtml)
  {
    // soft-wrap message first
    $body = wordwrap($body, 75);
  
    // split body into single lines
    $a_lines = preg_split('/\r?\n/', $body);
  
    // add > to each line
    for($n=0; $n<sizeof($a_lines); $n++)
      {
      if (strpos($a_lines[$n], '>')===0)
        $a_lines[$n] = '>'.$a_lines[$n];
      else
        $a_lines[$n] = '> '.$a_lines[$n];
      }
 
    $body = join("\n", $a_lines);

    // add title line
    $prefix = sprintf("\n\n\nOn %s, %s wrote:\n",
             $MESSAGE['headers']->date,
             $IMAP->decode_header($MESSAGE['headers']->from));

    // try to remove the signature
    if ($sp = strrstr($body, '-- '))
      {
      if ($body{$sp+3}==' ' || $body{$sp+3}=="\n" || $body{$sp+3}=="\r")
        $body = substr($body, 0, $sp-1);
      }
    $suffix = '';
  }
  else
  {
     $prefix = sprintf("<br><br>On %s, %s wrote:<br><blockquote type=\"cite\" " .
                       "style=\"padding-left: 5px; border-left: #1010ff 2px solid; " .
                       "margin-left: 5px; width: 100%%\">",
                       $MESSAGE['headers']->date,
                       $IMAP->decode_header($MESSAGE['headers']->from));

     $suffix = "</blockquote>";
  }

  return $prefix.$body.$suffix;
  }


function rcmail_create_forward_body($body, $bodyIsHtml)
  {
  global $IMAP, $MESSAGE;

  if (! $bodyIsHtml)
  {
    // soft-wrap message first
    $body = wordwrap($body, 80);
  
    $prefix = sprintf("\n\n\n-------- Original Message --------\nSubject: %s\nDate: %s\nFrom: %s\nTo: %s\n\n",
                     $MESSAGE['subject'],
                     $MESSAGE['headers']->date,
                     $IMAP->decode_header($MESSAGE['headers']->from),
                     $IMAP->decode_header($MESSAGE['headers']->to));
  }
  else
  {
      $prefix = sprintf(
        "<br><br>-------- Original Message --------" .
        "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">Subject: </th><td>%s</td></tr>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">Date: </th><td>%s</td></tr>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">From: </th><td>%s</td></tr>" .
        "<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">To: </th><td>%s</td></tr>" .
        "</tbody></table><br>",
                     $MESSAGE['subject'],
                     $MESSAGE['headers']->date,
                     $IMAP->decode_header($MESSAGE['headers']->from),
                     $IMAP->decode_header($MESSAGE['headers']->to));
  }

  // add attachments
  if (!isset($_SESSION['compose']['forward_attachments']) &&
      is_array($MESSAGE['parts']) && sizeof($MESSAGE['parts'])>1)
    rcmail_write_compose_attachments($MESSAGE);

  return $prefix.$body;
  }


function rcmail_create_draft_body($body, $bodyIsHtml)
  {
  global $IMAP, $MESSAGE;
    
  // add attachments
  if (!isset($_SESSION['compose']['forward_attachments']) &&
      is_array($MESSAGE['parts']) && sizeof($MESSAGE['parts'])>1)
    rcmail_write_compose_attachments($MESSAGE);

  return $body;
  }
  
  
function rcmail_write_compose_attachments(&$message)
  {
  global $IMAP, $CONFIG;

  $temp_dir = unslashify($CONFIG['temp_dir']);

  if (!is_array($_SESSION['compose']['attachments']))
    $_SESSION['compose']['attachments'] = array();
  
  foreach ($message['parts'] as $pid => $part)
    {
    if ($part->ctype_primary != 'message' && $part->ctype_primary != 'text' &&
        ($part->disposition=='attachment' || $part->disposition=='inline' || $part->headers['content-id'] ||
         (empty($part->disposition) && ($part->d_parameters['filename'] || $part->ctype_parameters['name']))))
      {
      $tmp_path = tempnam($temp_dir, 'rcmAttmnt');
      if ($fp = fopen($tmp_path, 'w'))
        {
        fwrite($fp, $IMAP->get_message_part($message['UID'], $pid, $part->encoding));
        fclose($fp);
        
        $filename = !empty($part->d_parameters['filename']) ? $part->d_parameters['filename'] :
                     (!empty($part->ctype_parameters['name']) ? $part->ctype_parameters['name'] :
                      (!empty($part->headers['content-description']) ? $part->headers['content-description'] : 'file'));

        $_SESSION['compose']['attachments'][] = array(
          'name' => rcube_imap::decode_mime_string($filename),
          'mimetype' => $part->ctype_primary . '/' . $part->ctype_secondary,
          'path' => $tmp_path
          );
        }
      }
    }
	
  $_SESSION['compose']['forward_attachments'] = TRUE;
  }


function rcmail_compose_subject($attrib)
  {
  global $CONFIG, $MESSAGE, $compose_mode;
  
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  $attrib['name'] = '_subject';
  $textfield = new textfield($attrib);

  $subject = '';

  // use subject from post
  if (isset($_POST['_subject']))
    $subject = get_input_value('_subject', RCUBE_INPUT_POST, TRUE);
    
  // create a reply-subject
  else if ($compose_mode == RCUBE_COMPOSE_REPLY)
    {
    if (eregi('^re:', $MESSAGE['subject']))
      $subject = $MESSAGE['subject'];
    else
      $subject = 'Re: '.$MESSAGE['subject'];
    }

  // create a forward-subject
  else if ($compose_mode == RCUBE_COMPOSE_FORWARD)
    {
    if (eregi('^fwd:', $MESSAGE['subject']))
      $subject = $MESSAGE['subject'];
    else
      $subject = 'Fwd: '.$MESSAGE['subject'];
    }

  // creeate a draft-subject
  else if ($compose_mode == RCUBE_COMPOSE_DRAFT)
    $subject = $MESSAGE['subject'];
  
  $out = $form_start ? "$form_start\n" : '';
  $out .= $textfield->show($subject);
  $out .= $form_end ? "\n$form_end" : '';
         
  return $out;
  }


function rcmail_compose_attachment_list($attrib)
  {
  global $OUTPUT, $JS_OBJECT_NAME, $CONFIG;
  
  // add ID if not given
  if (!$attrib['id'])
    $attrib['id'] = 'rcmAttachmentList';
  
  // allow the following attributes to be added to the <ul> tag
  $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style'));
 
  $out = '<ul'. $attrib_str . ">\n";
  
  if (is_array($_SESSION['compose']['attachments']))
    {
    if ($attrib['deleteicon'])
      $button = sprintf('<img src="%s%s" alt="%s" border="0" style="padding-right:2px;vertical-align:middle" />',
                        $CONFIG['skin_path'],
                        $attrib['deleteicon'],
                        rcube_label('delete'));
    else
      $button = rcube_label('delete');

    foreach ($_SESSION['compose']['attachments'] as $id => $a_prop)
      $out .= sprintf('<li id="rcmfile%d"><a href="#delete" onclick="return %s.command(\'remove-attachment\',\'rcmfile%d\', this)" title="%s">%s</a>%s</li>',
                      $id,
                      $JS_OBJECT_NAME,
                      $id,
                      rcube_label('delete'), 
                      $button,
                      rep_specialchars_output($a_prop['name']));
    }

  $OUTPUT->add_script(sprintf("%s.gui_object('attachmentlist', '%s');", $JS_OBJECT_NAME, $attrib['id']));  
    
  $out .= '</ul>';
  return $out;
  }



function rcmail_compose_attachment_form($attrib)
  {
  global $OUTPUT, $JS_OBJECT_NAME, $SESS_HIDDEN_FIELD;

  // add ID if not given
  if (!$attrib['id'])
    $attrib['id'] = 'rcmUploadbox';
  
  // allow the following attributes to be added to the <div> tag
  $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style'));
  $input_field = rcmail_compose_attachment_field(array());
  $label_send = rcube_label('upload');
  $label_close = rcube_label('close');
  
  $out = <<<EOF
<div$attrib_str>
<form action="./" method="post" enctype="multipart/form-data">
$SESS_HIDDEN_FIELD
$input_field<br />
<input type="button" value="$label_close" class="button" onclick="document.getElementById('$attrib[id]').style.visibility='hidden'" />
<input type="button" value="$label_send" class="button" onclick="$JS_OBJECT_NAME.command('send-attachment', this.form)" />
</form>
</div>
EOF;

  
  $OUTPUT->add_script(sprintf("%s.gui_object('uploadbox', '%s');", $JS_OBJECT_NAME, $attrib['id']));  
  return $out;
  }


function rcmail_compose_attachment_field($attrib)
  {
  // allow the following attributes to be added to the <input> tag
  $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style', 'size'));
 
  $out = '<input type="file" name="_attachments[]"'. $attrib_str . " />";
  return $out;
  }


function rcmail_priority_selector($attrib)
  {
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  $attrib['name'] = '_priority';
  $selector = new select($attrib);

  $selector->add(array(rcube_label('lowest'),
                       rcube_label('low'),
                       rcube_label('normal'),
                       rcube_label('high'),
                       rcube_label('highest')),
                 array(5, 4, 0, 2, 1));
                 
  $sel = isset($_POST['_priority']) ? $_POST['_priority'] : 0;

  $out = $form_start ? "$form_start\n" : '';
  $out .= $selector->show($sel);
  $out .= $form_end ? "\n$form_end" : '';
         
  return $out;
  }


function rcmail_receipt_checkbox($attrib)
  {
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  if (!isset($attrib['id']))
    $attrib['id'] = 'receipt';  

  $attrib['name'] = '_receipt';
  $attrib['value'] = '1';
  $checkbox = new checkbox($attrib);

  $out = $form_start ? "$form_start\n" : '';
  $out .= $checkbox->show(0);
  $out .= $form_end ? "\n$form_end" : '';

  return $out;
  }


function rcmail_editor_selector($attrib)
{
  global $CONFIG, $MESSAGE, $compose_mode;

  $choices = array(
    'html'  => 'HTML',
    'plain' => 'Plain text'
  );

  // determine whether HTML or plain text should be checked 
  if ($CONFIG['htmleditor'])
    $useHtml = true;
  else
    $useHtml = false;

  if ($compose_mode == RCUBE_COMPOSE_REPLY ||
      $compose_mode == RCUBE_COMPOSE_FORWARD ||
      $compose_mode == RCUBE_COMPOSE_DRAFT)
    {
    $hasHtml = rcmail_has_html_part($MESSAGE['parts']);
    $useHtml = ($hasHtml && $CONFIG['htmleditor']);
    }

  $selector = '';
  foreach ($choices as $value => $text)
    {
    $checked = '';
    if ((($text == 'HTML') && $useHtml) ||
        (($text != 'HTML') && !$useHtml))
      $checked = 'checked';

    $selector .= sprintf("<input type='radio' name='_editorSelect' value='%s' %s onclick='return rcmail_toggle_editor(this)'>%s</input>\n",
                          $value, $checked, $text);
    }

  return $selector;
}


function get_form_tags($attrib)
  {
  global $CONFIG, $OUTPUT, $JS_OBJECT_NAME, $MESSAGE_FORM, $SESS_HIDDEN_FIELD;  

  $form_start = '';
  if (!strlen($MESSAGE_FORM))
    {
    $hiddenfields = new hiddenfield(array('name' => '_task', 'value' => $GLOBALS['_task']));
    $hiddenfields->add(array('name' => '_action', 'value' => 'send'));

    $form_start = empty($attrib['form']) ? '<form name="form" action="./" method="post">' : '';
    $form_start .= "\n$SESS_HIDDEN_FIELD\n";
    $form_start .= $hiddenfields->show();
    }
    
  $form_end = (strlen($MESSAGE_FORM) && !strlen($attrib['form'])) ? '</form>' : '';
  $form_name = !empty($attrib['form']) ? $attrib['form'] : 'form';
  
  if (!strlen($MESSAGE_FORM))
    $OUTPUT->add_script("$JS_OBJECT_NAME.gui_object('messageform', '$form_name');");
  
  $MESSAGE_FORM = $form_name;

  return array($form_start, $form_end);  
  }


function format_email_recipient($email, $name='')
  {
  if ($name && $name != $email)
    return sprintf('%s <%s>', strpos($name, ",") ? '"'.$name.'"' : $name, $email);
  else
    return $email;
  }


function rcmail_charset_pulldown($selected='ISO-8859-1')
  {
  $select = new select();
  
  
  return $select->show($selected);
  }


/****** get contacts for this user and add them to client scripts ********/

$sql_result = $DB->query("SELECT name, email
                          FROM ".get_table_name('contacts')." WHERE  user_id=?
                          AND  del<>1",$_SESSION['user_id']);
                                   
if ($DB->num_rows($sql_result))
  {        
  $a_contacts = array();
  while ($sql_arr = $DB->fetch_assoc($sql_result))
    if ($sql_arr['email'])
      $a_contacts[] = format_email_recipient($sql_arr['email'], rep_specialchars_output($sql_arr['name'], 'js'));
  
  $OUTPUT->add_script(sprintf("$JS_OBJECT_NAME.set_env('contacts', %s);", array2js($a_contacts)));
  }


parse_template('compose');
?>

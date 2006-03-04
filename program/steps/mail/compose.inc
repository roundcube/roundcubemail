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


$MESSAGE_FORM = NULL;
$REPLY_MESSAGE = NULL;
$FORWARD_MESSAGE = NULL;


if (!is_array($_SESSION['compose']))
  $_SESSION['compose'] = array('id' => uniqid(rand()));


// add some labels to client
rcube_add_label('nosubject', 'norecipientwarning', 'nosubjectwarning', 'nobodywarning', 'sendingmessage', 'notsentwarning');


if ($_GET['_reply_uid'] || $_GET['_forward_uid'])
  {
  $msg_uid = $_GET['_reply_uid'] ? $_GET['_reply_uid'] : $_GET['_forward_uid'];

  // similar as in program/steps/mail/show.inc
  $MESSAGE = array();
  $MESSAGE['headers'] = $IMAP->get_headers($msg_uid);
  
  $MESSAGE['source'] = rcmail_message_source($msg_uid);
  
  $mmd = new Mail_mimeDecode($MESSAGE['source']);
  $MESSAGE['structure'] = $mmd->decode(array('include_bodies' => TRUE,
                                             'decode_headers' => TRUE,
                                             'decode_bodies' => FALSE));

  $MESSAGE['subject'] = $IMAP->decode_header($MESSAGE['headers']->subject);
  $MESSAGE['parts'] = $mmd->getMimeNumbers($MESSAGE['structure']);
  
  if ($_GET['_reply_uid'])
    {
    $REPLY_MESSAGE = &$MESSAGE;
    $_SESSION['compose']['reply_uid'] = $_GET['_reply_uid'];
    $_SESSION['compose']['reply_msgid'] = $REPLY_MESSAGE['headers']->messageID;
    $_SESSION['compose']['references']  = $REPLY_MESSAGE['headers']->reference;
    $_SESSION['compose']['references'] .= !empty($REPLY_MESSAGE['headers']->reference) ? ' ' : '';
    $_SESSION['compose']['references'] .= $REPLY_MESSAGE['headers']->messageID;

    if ($_GET['_all'])
      $REPLY_MESSAGE['reply_all'] = 1;
    }
  else
    {
    $FORWARD_MESSAGE = $MESSAGE;
    $_SESSION['compose']['forward_uid'] = $_GET['_forward_uid'];
    }
  }



/****** compose mode functions ********/


function rcmail_compose_headers($attrib)
  {
  global $IMAP, $REPLY_MESSAGE, $DB;
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
      if (!empty($_GET['_to']) && preg_match('/^([0-9]+,?)+$/', $_GET['_to']))
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
        $fvalue = $_GET['_to'];
        
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
  else if ($header && is_object($REPLY_MESSAGE['headers']))
    {
    // get recipent address(es) out of the message headers
    if ($header=='to' && $REPLY_MESSAGE['headers']->replyto)
      $fvalue = $IMAP->decode_header($REPLY_MESSAGE['headers']->replyto);

    else if ($header=='to' && $REPLY_MESSAGE['headers']->from)
      $fvalue = $IMAP->decode_header($REPLY_MESSAGE['headers']->from);

    // add recipent of original message if reply to all
    else if ($header=='cc' && $REPLY_MESSAGE['reply_all'])
      {
      if ($IMAP->decode_header($REPLY_MESSAGE['headers']->to))
        $fvalue .= $IMAP->decode_header($REPLY_MESSAGE['headers']->to);

      if ($IMAP->decode_header($REPLY_MESSAGE['headers']->cc))
        {
        if($fvalue)
          $fvalue .= ', ';

        $fvalue .= $IMAP->decode_header($REPLY_MESSAGE['headers']->cc);
        }
      }

    // split recipients and put them back together in a unique way
    if (!empty($fvalue))
      {
      $to_addresses = $IMAP->decode_address_list($fvalue);
      $fvalue = '';
      foreach ($to_addresses as $addr_part)
        {
        if (!in_array($addr_part['mailto'], $sa_recipients) && (!$REPLY_MESSAGE['FROM'] || !in_array($addr_part['mailto'], $REPLY_MESSAGE['FROM'])))
          {
          $fvalue .= (strlen($fvalue) ? ', ':'').$addr_part['string'];
          $sa_recipients[] = $addr_part['mailto'];
          }
        }
      }
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
  global $IMAP, $REPLY_MESSAGE, $DB, $OUTPUT, $JS_OBJECT_NAME;
    
  // pass the following attributes to the form class
  $field_attrib = array('name' => '_from');
  foreach ($attrib as $attr => $value)
    if (in_array($attr, array('id', 'class', 'style', 'size', 'tabindex')))
      $field_attrib[$attr] = $value;

  // extract all recipients of the reply-message
  $a_recipients = array();
  if ($REPLY_MESSAGE && is_object($REPLY_MESSAGE['headers']))
    {
    $REPLY_MESSAGE['FROM'] = array();

    $a_to = $IMAP->decode_address_list($REPLY_MESSAGE['headers']->to);        
    foreach ($a_to as $addr)
      {
      if (!empty($addr['mailto']))
        $a_recipients[] = $addr['mailto'];
      }

    if (!empty($REPLY_MESSAGE['headers']->cc))
      {
      $a_cc = $IMAP->decode_address_list($REPLY_MESSAGE['headers']->cc);
      foreach ($a_cc as $addr)
        {
        if (!empty($addr['mailto']))
          $a_recipients[] = $addr['mailto'];
        }
      }
    }

  // get this user's identities
  $sql_result = $DB->query("SELECT identity_id, name, email, signature
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
      $select_from->add(format_email_recipient($sql_arr['email'], $sql_arr['name']), $sql_arr['identity_id']);

      // add signature to array
      if (!empty($sql_arr['signature']))
        $a_signatures[$sql_arr['identity_id']] = $sql_arr['signature'];
      
      // set identity if it's one of the reply-message recipients
      if (in_array($sql_arr['email'], $a_recipients))
        $from_id = $sql_arr['identity_id'];
        
      if ($REPLY_MESSAGE && is_array($REPLY_MESSAGE['FROM']))
        $REPLY_MESSAGE['FROM'][] = $sql_arr['email'];
      }

    // overwrite identity selection with post parameter
    if (isset($_POST['_from']))
      $from_id = $_POST['_from'];

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
  global $CONFIG, $REPLY_MESSAGE, $FORWARD_MESSAGE;
  
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  $attrib['name'] = '_message';
  $textarea = new textarea($attrib);

  $body = '';
  
  // use posted message body
  if (!empty($_POST['_message']))
    $body = get_input_value('_message', RCUBE_INPUT_POST, TRUE);
    
  // compose reply-body
  else if (is_array($REPLY_MESSAGE['parts']))
    {
    $body = rcmail_first_text_part($REPLY_MESSAGE['parts']);
    if (strlen($body))
      $body = rcmail_create_reply_body($body);
    }

  // forward message body inline
  else if (is_array($FORWARD_MESSAGE['parts']))
    {
    $body = rcmail_first_text_part($FORWARD_MESSAGE['parts']);
    if (strlen($body))
      $body = rcmail_create_forward_body($body);
    }
  
  $out = $form_start ? "$form_start\n" : '';
  $out .= $textarea->show($body);
  $out .= $form_end ? "\n$form_end" : '';
         
  return $out;
  }


function rcmail_create_reply_body($body)
  {
  global $IMAP, $REPLY_MESSAGE;

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
  $pefix = sprintf("\n\n\nOn %s, %s wrote:\n",
           $REPLY_MESSAGE['headers']->date,
           $IMAP->decode_header($REPLY_MESSAGE['headers']->from));
           

  // try to remove the signature
  if ($sp = strrpos($body, '-- '))
    {
    if ($body{$sp+3}==' ' || $body{$sp+3}=="\n" || $body{$sp+3}=="\r")
      $body = substr($body, 0, $sp-1);
    }

  return $pefix.$body;
  }


function rcmail_create_forward_body($body)
  {
  global $IMAP, $FORWARD_MESSAGE;

  // soft-wrap message first
  $body = wordwrap($body, 80);
  
  $prefix = sprintf("\n\n\n-------- Original Message --------\nSubject: %s\nDate: %s\nFrom: %s\nTo: %s\n\n",
                   $FORWARD_MESSAGE['subject'],
                   $FORWARD_MESSAGE['headers']->date,
                   $IMAP->decode_header($FORWARD_MESSAGE['headers']->from),
                   $IMAP->decode_header($FORWARD_MESSAGE['headers']->to));

  // add attachments
  if (!isset($_SESSION['compose']['forward_attachments']) && is_array($FORWARD_MESSAGE['parts']) && sizeof($FORWARD_MESSAGE['parts'])>1)
    {
    $temp_dir = rcmail_create_compose_tempdir();

    if (!is_array($_SESSION['compose']['attachments']))
      $_SESSION['compose']['attachments'] = array();
  
    foreach ($FORWARD_MESSAGE['parts'] as $part)
      {
      if ($part->disposition != 'attachment')
        continue;

      $tmp_path = tempnam($temp_dir, 'rcmAttmnt');
      if ($fp = fopen($tmp_path, 'w'))
        {
        fwrite($fp, $IMAP->mime_decode($part->body, $part->headers['content-transfer-encoding']));
        fclose($fp);

        $_SESSION['compose']['attachments'][] = array('name' => $part->d_parameters['filename'],
                                                      'mimetype' => $part->ctype_primary . '/' . $part->ctype_secondary,
                                                      'path' => $tmp_path);
        }
      }

    $_SESSION['compose']['forward_attachments'] = TRUE;
    }

  return $prefix.$body;
  }



function rcmail_compose_subject($attrib)
  {
  global $CONFIG, $REPLY_MESSAGE, $FORWARD_MESSAGE;
  
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  $attrib['name'] = '_subject';
  $textfield = new textfield($attrib);

  $subject = '';

  // use subject from post
  if (isset($_POST['_subject']))
    $subject = get_input_value('_subject', RCUBE_INPUT_POST, TRUE);
    
  // create a reply-subject
  else if (isset($REPLY_MESSAGE['subject']))
    {
    if (eregi('^re:', $REPLY_MESSAGE['subject']))
      $subject = $REPLY_MESSAGE['subject'];
    else
      $subject = 'Re: '.$REPLY_MESSAGE['subject'];
    }

  // create a forward-subject
  else if (isset($FORWARD_MESSAGE['subject']))
    {
    if (eregi('^fwd:', $REPLY_MESSAGE['subject']))
      $subject = $FORWARD_MESSAGE['subject'];
    else
      $subject = 'Fwd: '.$FORWARD_MESSAGE['subject'];
    }

  
  $out = $form_start ? "$form_start\n" : '';
  $out .= $textfield->show($subject);
  $out .= $form_end ? "\n$form_end" : '';
         
  return $out;
  }


function rcmail_compose_attachment_list($attrib)
  {
  global $OUTPUT, $JS_OBJECT_NAME;
  
  // add ID if not given
  if (!$attrib['id'])
    $attrib['id'] = 'rcmAttachmentList';
  
  // allow the following attributes to be added to the <ul> tag
  $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style'));
 
  $out = '<ul'. $attrib_str . ">\n";
  
  if (is_array($_SESSION['compose']['attachments']))
    {
    foreach ($_SESSION['compose']['attachments'] as $i => $a_prop)
      $out .= sprintf("<li>%s</li>\n", $a_prop['name']);
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
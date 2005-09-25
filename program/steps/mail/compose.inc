<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/compose.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
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
    $REPLY_MESSAGE = $MESSAGE;
    $_SESSION['compose']['reply_uid'] = $_GET['_reply_uid'];
    $_SESSION['compose']['reply_msgid'] = $REPLY_MESSAGE['headers']->messageID;
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

  list($form_start, $form_end) = get_form_tags($attrib);
  
  $out = '';
  $part = strtolower($attrib['part']);
  
  switch ($part)
    {
    case 'from':
      // pass the following attributes to the form class
      $field_attrib = array('name' => '_from');
      foreach ($attrib as $attr => $value)
        if (in_array($attr, array('id', 'class', 'style', 'size')))
          $field_attrib[$attr] = $value;
    
      // get this user's identities
      $sql_result = $DB->query(sprintf("SELECT identity_id, name, email
                                        FROM   %s
                                        WHERE  user_id=%d
                                        AND    del!='1'
                                        ORDER BY `default` DESC, name ASC",
                                       get_table_name('identities'),
                                       $_SESSION['user_id']));
                                   
      if ($DB->num_rows($sql_result))
        {        
        $select_from = new select($field_attrib);
        while ($sql_arr = $DB->fetch_assoc($sql_result))
          $select_from->add(format_email_recipient($sql_arr['email'], $sql_arr['name']), $sql_arr['identity_id']);
        
        $out = $select_from->show($_POST['_from']);
        }
      else
        {
        $input_from = new textfield($field_attrib);
        $out = $input_from->show($_POST['_from']);
        }
        
      if ($form_start)
        $out = $form_start.$out;

      return $out;
    

    case 'to':
      $fname = '_to';
      $header = 'to';
      
      // we have contact id's as get parameters
      if (strlen($_GET['_to']) && preg_match('/[0-9]+,?/', $_GET['_to']))
        {
        $a_recipients = array();
        $sql_result = $DB->query(sprintf("SELECT name, email
                                          FROM   %s
                                          WHERE  user_id=%d
                                          AND    del!='1'
                                          AND    contact_id IN (%s)",
                                         get_table_name('contacts'),
                                         $_SESSION['user_id'],
                                         $_GET['_to']));
                                         
        while ($sql_arr = $DB->fetch_assoc($sql_result))
          $a_recipients[] = format_email_recipient($sql_arr['email'], $sql_arr['name']);
          
        if (sizeof($a_recipients))
          $fvalue = join(', ', $a_recipients);
        }
      else if (strlen($_GET['_to']))
        $fvalue = $_GET['_to'];
        
    case 'cc':
      if (!$fname)
        {
        $fname = '_cc';
        //$header = 'cc';
        }
    case 'bcc':
      if (!$fname)
        $fname = '_bcc';
        
      $allow_attrib = array('id', 'class', 'style', 'cols', 'rows', 'wrap');
      $field_type = 'textarea';            
      break;

    case 'replyto':
    case 'reply-to':
      $fname = '_replyto';
      $allow_attrib = array('id', 'class', 'style', 'size');
      $field_type = 'textfield';
      break;
    
    }
    
    
  if ($fname && $_POST[$fname])
    $fvalue = $_POST[$fname];
  else if ($header && is_object($REPLY_MESSAGE['headers']))
    {
    // get recipent address(es) out of the message headers
    if ($header=='to' && $REPLY_MESSAGE['headers']->replyto)
      $fvalue = $IMAP->decode_header($REPLY_MESSAGE['headers']->replyto);
    else if ($header=='to' && $REPLY_MESSAGE['headers']->from)
      $fvalue = $IMAP->decode_header($REPLY_MESSAGE['headers']->from);
    
    // split recipients and put them back together in a unique way
    $to_addresses = $IMAP->decode_address_list($fvalue);
    $fvalue = '';
    foreach ($to_addresses as $addr_part)
      $fvalue .= (strlen($fvalue) ? ', ':'').$addr_part['string'];
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


/*function rcube_compose_headers($attrib)
  {
  global $CONFIG, $OUTPUT;

  list($form_start, $form_end) = get_form_tags($attrib);

  // allow the following attributes to be added to the headers table
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border'));
  
  $labels = array();
  $labels['from'] = rcube_label('from');
  $labels['to'] = rcube_label('to');
  $labels['cc'] = rcube_label('cc');
  $labels['bcc'] = rcube_label('bcc');
  $labels['replyto'] = rcube_label('replyto');

  $input_from = new textfield(array('name' => '_from', 'size' => 30));
  $input_to = new textfield(array('name' => '_to', 'size' => 30));
  $input_cc = new textfield(array('name' => '_cc', 'size' => 30));
  $input_bcc = new textfield(array('name' => '_bcc', 'size' => 30));
  $input_replyto = new textfield(array('name' => '_replyto', 'size' => 30));

  $fields = array();
  $fields['from'] = $input_from->show($_POST['_from']);
  $fields['to'] = $input_to->show($_POST['_to']);
  $fields['cc'] = $input_cc->show($_POST['_cc']);
  $fields['bcc'] = $input_bcc->show($_POST['_bcc']);
  $fields['replyto'] = $input_replyto->show($_POST['_replyto']);


  $out = <<<EOF
$form_start
<table$attrib_str><tr>

<td class="title">$labels[from]</td>
<td>$fields[from]</td>

</tr><tr>

<td class="title">$labels[to]</td>
<td>$fields[to]</td>

</tr><tr>

<td class="title">$labels[cc]</td>
<td>$fields[cc]</td>

</tr><tr>

<td class="title">$labels[bcc]</td>
<td>$fields[bcc]</td>

</tr><tr>

<td class="title">$labels[replyto]</td>
<td>$fields[replyto]</td>

</tr></table>
$form_end
EOF;

  return $out;  
  }
*/


function rcmail_compose_body($attrib)
  {
  global $CONFIG, $REPLY_MESSAGE, $FORWARD_MESSAGE;
  
  list($form_start, $form_end) = get_form_tags($attrib);
  unset($attrib['form']);
  
  $attrib['name'] = '_message';
  $textarea = new textarea($attrib);

  $body = '';
  
  // use posted message body
  if ($_POST['_message'])
    $body = $_POST['_message'];
    
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
  if ($_POST['_subject'])
    $subject = $_POST['_subject'];
    
  // create a reply-subject
  else if (isset($REPLY_MESSAGE['subject']))
    $subject = 'Re: '.$REPLY_MESSAGE['subject'];

  // create a forward-subject
  else if (isset($FORWARD_MESSAGE['subject']))
    $subject = 'Fwd: '.$FORWARD_MESSAGE['subject'];

  
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
                 array(1, 2, 0, 4, 5));
                 
  $sel = $_POST['_priority'] ? $_POST['_priority'] : 0;

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
    
    $form_start = !strlen($attrib['form']) ? '<form name="form" action="./" method="post">' : '';
    $form_start .= "\n$SESS_HIDDEN_FIELD\n";
    $form_start .= $hiddenfields->show();
    }
    
  $form_end = (strlen($MESSAGE_FORM) && !strlen($attrib['form'])) ? '</form>' : '';
  $form_name = strlen($attrib['form']) ? $attrib['form'] : 'form';
  
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


/****** get contacts for this user and add them to client scripts ********/

$sql_result = $DB->query(sprintf("SELECT name, email
                                  FROM   %s
                                  WHERE  user_id=%d
                                  AND    del!='1'",
                                 get_table_name('contacts'),
                                 $_SESSION['user_id']));
                                   
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
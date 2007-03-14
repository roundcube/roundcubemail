<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/sendmail.inc                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Compose a new mail message with all headers and attachments         |
 |   and send it using IlohaMail's SMTP methods or with PHP mail()       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


//require_once('lib/smtp.inc');
require_once('include/rcube_smtp.inc');
require_once('lib/html2text.inc');
require_once('Mail/mime.php');


if (!isset($_SESSION['compose']['id']))
  {
  rcmail_overwrite_action('list');
  return;
  }


/****** message sending functions ********/


function rcmail_get_identity($id)
  {
  global $DB, $CHARSET, $OUTPUT;
  
  // get identity record
  $sql_result = $DB->query("SELECT *, email AS mailto
                            FROM ".get_table_name('identities')."
                            WHERE  identity_id=?
                            AND    user_id=?
                            AND    del<>1",
                            $id,$_SESSION['user_id']);
                                   
  if ($DB->num_rows($sql_result))
    {
    $sql_arr = $DB->fetch_assoc($sql_result);
    $out = $sql_arr;
    $name = strpos($sql_arr['name'], ",") ? '"'.$sql_arr['name'].'"' : $sql_arr['name'];
    $out['string'] = sprintf('%s <%s>',
                             rcube_charset_convert($name, $CHARSET, $OUTPUT->get_charset()),
                             $sql_arr['mailto']);
    return $out;
    }

  return FALSE;  
  }

/**
 * go from this:
 * <img src=".../tiny_mce/plugins/emotions/images/smiley-cool.gif" border="0" alt="Cool" title="Cool" />
 *
 * to this:
 *
 * <IMG src="cid:smiley-cool.gif"/>
 * ...
 * ------part...
 * Content-Type: image/gif
 * Content-Transfer-Encoding: base64
 * Content-ID: <smiley-cool.gif>
 */
function rcmail_attach_emoticons(&$mime_message)
{
  global $CONFIG, $INSTALL_PATH;

  $htmlContents = $mime_message->getHtmlBody();

  // remove any null-byte characters before parsing
  $body = preg_replace('/\x00/', '', $htmlContents);
  
  $last_img_pos = 0;

  $searchstr = 'program/js/tiny_mce/plugins/emotions/images/';

  // keep track of added images, so they're only added once
  $included_images = array();

  // find emoticon image tags
  while ($pos = strpos($body, $searchstr, $last_img_pos))
    {
    $pos2 = strpos($body, '"', $pos);
    $body_pre = substr($body, 0, $pos);
    $image_name = substr($body,
                         $pos + strlen($searchstr),
                         $pos2 - ($pos + strlen($searchstr)));
    // sanitize image name so resulting attachment doesn't leave images dir
    $image_name = preg_replace('/[^a-zA-Z0-9_\.\-]/i','',$image_name);

    $body_post = substr($body, $pos2);

    if (! in_array($image_name, $included_images))
      {
      // add the image to the MIME message
      $img_file = $INSTALL_PATH . '/' . $searchstr . $image_name;
      if(! $mime_message->addHTMLImage($img_file, 'image/gif', '', true, '_' . $image_name))
        {
        show_message("emoticonerror", 'error');
        }
      array_push($included_images, $image_name);
      }

    $body = $body_pre . 'cid:_' . $image_name . $body_post;

    $last_img_pos = $pos2;
    }
   
  $mime_message->setHTMLBody($body);
}

if (strlen($_POST['_draft_saveid']) > 3)
  $olddraftmessageid = get_input_value('_draft_saveid', RCUBE_INPUT_POST);

$message_id = sprintf('<%s@%s>', md5(uniqid('rcmail'.rand(),true)), rcmail_mail_domain($_SESSION['imap_host']));
$savedraft = !empty($_POST['_draft']) ? TRUE : FALSE;

// remove all scripts and act as called in frame
$OUTPUT->reset();
$_framed = TRUE;


/****** check submission and compose message ********/


if (empty($_POST['_to']) && empty($_POST['_subject']) && $_POST['_message'])
  {
  show_message("sendingfailed", 'error'); 
  //rcmail_overwrite_action('compose');
  rcube_iframe_response();
  return;
  }


// set default charset
$input_charset = $OUTPUT->get_charset();
$message_charset = isset($_POST['_charset']) ? $_POST['_charset'] : $input_charset;

$mailto_regexp = array('/[,;]\s*[\r\n]+/', '/[\r\n]+/', '/[,;]\s*$/m', '/;/');
$mailto_replace = array(', ', ', ', '', ',');

// replace new lines and strip ending ', '
$mailto = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_to', RCUBE_INPUT_POST, TRUE, $message_charset));

// decode address strings
$to_address_arr = $IMAP->decode_address_list($mailto);
$identity_arr = rcmail_get_identity(get_input_value('_from', RCUBE_INPUT_POST));

$from = $identity_arr['mailto'];
$first_to = is_array($to_address_arr[0]) ? $to_address_arr[0]['mailto'] : $mailto;

if (empty($identity_arr['string']))
  $identity_arr['string'] = $from;

// compose headers array
$headers = array('Date' => date('D, j M Y H:i:s O'),
                 'From' => rcube_charset_convert($identity_arr['string'], $CHARSET, $message_charset),
                 'To'   => $mailto);

// additional recipients
if (!empty($_POST['_cc']))
  $headers['Cc'] = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_cc', RCUBE_INPUT_POST, TRUE, $message_charset));

if (!empty($_POST['_bcc']))
  $headers['Bcc'] = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_bcc', RCUBE_INPUT_POST, TRUE, $message_charset));
  
if (!empty($identity_arr['bcc']))
  $headers['Bcc'] = ($headers['Bcc'] ? $headers['Bcc'].', ' : '') . $identity_arr['bcc'];

// add subject
$headers['Subject'] = trim(get_input_value('_subject', RCUBE_INPUT_POST, FALSE, $message_charset));

if (!empty($identity_arr['organization']))
  $headers['Organization'] = $identity_arr['organization'];

if (!empty($identity_arr['reply-to']))
  $headers['Reply-To'] = $identity_arr['reply-to'];

if (!empty($_SESSION['compose']['reply_msgid']))
  $headers['In-Reply-To'] = $_SESSION['compose']['reply_msgid'];

if (!empty($_SESSION['compose']['references']))
  $headers['References'] = $_SESSION['compose']['references'];

if (!empty($_POST['_priority']))
  {
  $priority = (int)$_POST['_priority'];
  $a_priorities = array(1=>'highest', 2=>'high', 4=>'low', 5=>'lowest');
  if ($str_priority = $a_priorities[$priority])
    $headers['X-Priority'] = sprintf("%d (%s)", $priority, ucfirst($str_priority));
  }

if (!empty($_POST['_receipt']))
  {
  $headers['Return-Receipt-To'] = $identity_arr['string'];
  $headers['Disposition-Notification-To'] = $identity_arr['string'];
  }

// additional headers
$headers['Message-ID'] = $message_id;
$headers['X-Sender'] = $from;

if (!empty($CONFIG['useragent']))
  $headers['User-Agent'] = $CONFIG['useragent'];

// fetch message body
$message_body = get_input_value('_message', RCUBE_INPUT_POST, TRUE, $message_charset);

// append generic footer to all messages
if (!empty($CONFIG['generic_message_footer']))
  {
  $file = realpath($CONFIG['generic_message_footer']);
  if($fp = fopen($file, 'r'))
    {
    $content = fread($fp, filesize($file));
    fclose($fp);
    $message_body .= "\r\n" . rcube_charset_convert($content, 'UTF-8', $message_charset);
    }
  }

// try to autodetect operating system and use the correct line endings
// use the configured delimiter for headers
if (!empty($CONFIG['mail_header_delimiter']))
  $header_delm = $CONFIG['mail_header_delimiter'];
else if (strtolower(substr(PHP_OS, 0, 3)=='win')) 
  $header_delm = "\r\n";
else if (strtolower(substr(PHP_OS, 0, 3)=='mac'))
  $header_delm = "\r\n";
else    
  $header_delm = "\n";

// create PEAR::Mail_mime instance

$isHtmlVal = strtolower(get_input_value('_is_html', RCUBE_INPUT_POST));
$isHtml = ($isHtmlVal == "1");

$MAIL_MIME = new Mail_mime($header_delm);

// For HTML-formatted messages, construct the MIME message with both
// the HTML part and the plain-text part

if ($isHtml)
  {
  $MAIL_MIME->setHTMLBody($message_body);

  // add a plain text version of the e-mail as an alternative part.
  $h2t = new html2text($message_body);
  $plainTextPart = $h2t->get_text();
  $MAIL_MIME->setTXTBody($plainTextPart);

  // look for "emoticon" images from TinyMCE and copy into message as attachments
  rcmail_attach_emoticons($MAIL_MIME);
  }
else
  {
  $MAIL_MIME->setTXTBody($message_body, FALSE, TRUE);
  }


// add stored attachments, if any
if (is_array($_SESSION['compose']['attachments']))
  foreach ($_SESSION['compose']['attachments'] as $attachment)
    $MAIL_MIME->addAttachment($attachment['path'], $attachment['mimetype'], $attachment['name'], TRUE);

  
// add submitted attachments
if (is_array($_FILES['_attachments']['tmp_name']))
  foreach ($_FILES['_attachments']['tmp_name'] as $i => $filepath)
    $MAIL_MIME->addAttachment($filepath, $files['type'][$i], $files['name'][$i], TRUE);


// chose transfer encoding
$charset_7bit = array('ASCII', 'ISO-2022-JP', 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-15');
$transfer_encoding = in_array(strtoupper($message_charset), $charset_7bit) ? '7bit' : '8bit';

// encoding settings for mail composing
$message_param = array('text_encoding' => $transfer_encoding,
                       'html_encoding' => 'quoted-printable',
                       'head_encoding' => 'quoted-printable',
                       'head_charset'  => $message_charset,
                       'html_charset'  => $message_charset,
                       'text_charset'  => $message_charset);

// compose message body and get headers
$msg_body = $MAIL_MIME->get($message_param);
// unset to save memory.
unset($MAIL_MIME->_parts);

// encoding subject header with mb_encode provides better results with asian characters
if ($MBSTRING && function_exists("mb_encode_mimeheader"))
{
  mb_internal_encoding($message_charset);
  $mb_subject = mb_encode_mimeheader($headers['Subject'], $message_charset, 'Q');
  mb_internal_encoding($CHARSET);
}

// Begin SMTP Delivery Block 
if (!$savedraft) {

  // send thru SMTP server using custom SMTP library
  if ($CONFIG['smtp_server'])
    {
    // generate list of recipients
    $a_recipients = array($mailto);
  
    if (strlen($headers['Cc']))
      $a_recipients[] = $headers['Cc'];
    if (strlen($headers['Bcc']))
      $a_recipients[] = $headers['Bcc'];
  
    // clean Bcc from header for recipients
    $send_headers = $headers;
    unset($send_headers['Bcc']);
  
    if (!empty($mb_subject))
      $send_headers['Subject'] = $mb_subject;
  
    // send message
    $smtp_response = array();
    $sent = smtp_mail($from, $a_recipients, $MAIL_MIME->txtHeaders($send_headers), $msg_body, $smtp_response);
  
    // log error
    if (!$sent)
      {
      raise_error(array('code' => 800,
                        'type' => 'smtp',
                        'line' => __LINE__,
                        'file' => __FILE__,
                        'message' => "SMTP error: ".join("\n", $smtp_response)), TRUE, FALSE);
      }
    }
  
  // send mail using PHP's mail() function
  else
    {
    // unset some headers because they will be added by the mail() function
    $headers_enc = $MAIL_MIME->headers($headers);
    $headers_php = $MAIL_MIME->_headers;
    unset($headers_php['To'], $headers_php['Subject']);
    
    if (!empty($mb_subject))
      $headers_enc['Subject'] = $mb_subject;
    
    // reset stored headers and overwrite
    $MAIL_MIME->_headers = array();
    $header_str = $MAIL_MIME->txtHeaders($headers_php);
  
    if (ini_get('safe_mode'))
      $sent = mail($headers_enc['To'], $headers_enc['Subject'], $msg_body, $header_str);
    else
      $sent = mail($headers_enc['To'], $headers_enc['Subject'], $msg_body, $header_str, "-f$from");
    }
  
  
  // return to compose page if sending failed
  if (!$sent)
    {
    show_message("sendingfailed", 'error'); 
    rcube_iframe_response();
    return;
    }
  
  
  // set repliead flag
  if ($_SESSION['compose']['reply_uid'])
    $IMAP->set_flag($_SESSION['compose']['reply_uid'], 'ANSWERED');

  } // End of SMTP Delivery Block



// Determine which folder to save message
if ($savedraft)
  $store_target = 'drafts_mbox';
else
  $store_target = 'sent_mbox';

if ($CONFIG[$store_target])
  {
  // create string of complete message headers
  $header_str = $MAIL_MIME->txtHeaders($headers);

  // check if mailbox exists
  if (!in_array_nocase($CONFIG[$store_target], $IMAP->list_mailboxes()))
    $store_folder = $IMAP->create_mailbox($CONFIG[$store_target], TRUE);
  else
    $store_folder = TRUE;
  
  // add headers to message body
  $msg_body = $header_str."\r\n".$msg_body;

  // append message to sent box
  if ($store_folder)
    $saved = $IMAP->save_message($CONFIG[$store_target], $msg_body);

  // raise error if saving failed
  if (!$saved)
    {
    raise_error(array('code' => 800,
                      'type' => 'imap',
                      'file' => __FILE__,
                      'message' => "Could not save message in $CONFIG[$store_target]"), TRUE, FALSE);
    
    show_message('errorsaving', 'error');
    rcube_iframe_response($errorout);
    }

  if ($olddraftmessageid)
    {
    // delete previous saved draft
    $a_deleteid = $IMAP->search($CONFIG['drafts_mbox'],'HEADER Message-ID',$olddraftmessageid);
    $deleted = $IMAP->delete_message($IMAP->get_uid($a_deleteid[0],$CONFIG['drafts_mbox']),$CONFIG['drafts_mbox']);

    // raise error if deletion of old draft failed
    if (!$deleted)
      raise_error(array('code' => 800,
                        'type' => 'imap',
                        'file' => __FILE__,
                        'message' => "Could not delete message from ".$CONFIG['drafts_mbox']), TRUE, FALSE);
    }
  }

if ($savedraft)
  {
  // clear the "saving message" busy status, and display success
  show_message('messagesaved', 'confirmation');

  // update "_draft_saveid" on the page, which is used to delete a previous draft
  $frameout = "var foundid = parent.rcube_find_object('_draft_saveid', parent.document);\n";
  $frameout .= sprintf("foundid.value = '%s';\n", str_replace(array('<','>'), "", $message_id));

  // update the "cmp_hash" to prevent "Unsaved changes" warning
  $frameout .= sprintf("parent.%s.cmp_hash = parent.%s.compose_field_hash();\n", $JS_OBJECT_NAME, $JS_OBJECT_NAME);

  // start the auto-save timer again
  $frameout .= sprintf("parent.%s.auto_save_start();", $JS_OBJECT_NAME);

  // send html page with JS calls as response
  rcube_iframe_response($frameout);
  }
else
  {
  if ($CONFIG['smtp_log'])
    {
    $log_entry = sprintf("[%s] User: %d on %s; Message for %s; %s\n",
                 date("d-M-Y H:i:s O", mktime()),
                 $_SESSION['user_id'],
                 $_SERVER['REMOTE_ADDR'],
                 $mailto,
                 !empty($smtp_response) ? join('; ', $smtp_response) : '');

    if ($fp = @fopen($CONFIG['log_dir'].'/sendmail', 'a'))
      {
      fwrite($fp, $log_entry);
      fclose($fp);
      }
    }

  rcmail_compose_cleanup();
  rcube_iframe_response(sprintf("parent.$JS_OBJECT_NAME.sent_successfully('%s');",
                                JQ(rcube_label('messagesent'))));
  }


?>

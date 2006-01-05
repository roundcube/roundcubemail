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
require_once('Mail/mime.php');


if (!isset($_SESSION['compose']['id']))
  {
  rcmail_overwrite_action('list');
  return;
  }


/****** message sending functions ********/



function rcmail_get_identity($id)
  {
  global $DB;
  
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
    $out['string'] = sprintf('%s <%s>', $sql_arr['name'], $sql_arr['mailto']);
    return $out;
    }

  return FALSE;  
  }



/****** check submission and compose message ********/


if (empty($_POST['_to']) && empty($_POST['_subject']) && $_POST['_message'])
  {
  show_message("sendingfailed", 'error'); 
  rcmail_overwrite_action('compose');
  return;
  }


// set default charset
if (empty($CHARSET))
  $CHARSET = 'ISO-8859-1';


$mailto_regexp = array('/[,;]\s*[\r\n]+/', '/[\r\n]+/', '/[,;]\s*$/m');
$mailto_replace = array(', ', ', ', '');

// repalce new lines and strip ending ', '
$mailto = preg_replace($mailto_regexp, $mailto_replace, stripslashes($_POST['_to']));

// decode address strings
$to_address_arr = $IMAP->decode_address_list($mailto);
$identity_arr = rcmail_get_identity($_POST['_from']);


$from = $identity_arr['mailto'];
$first_to = is_array($to_address_arr[0]) ? $to_address_arr[0]['mailto'] : $mailto;


// create unique message-id
$message_id = sprintf('<%s@%s>', md5(uniqid('rcmail')), $_SESSION['imap_host']);


// compose headers array
$headers = array('Date' => date('D, j M Y G:i:s O'),
                 'From' => $identity_arr['string'],
                 'To'   => $mailto);

// additional recipients
if ($_POST['_cc'])
  $headers['Cc'] = preg_replace($mailto_regexp, $mailto_replace, stripslashes($_POST['_cc']));

if ($_POST['_bcc'])
  $headers['Bcc'] = preg_replace($mailto_regexp, $mailto_replace, stripslashes($_POST['_bcc']));
  
if (strlen($identity_arr['bcc']))
  $headers['Bcc'] = ($headers['Bcc'] ? $headers['Bcc'].', ' : '') . $identity_arr['bcc'];

// add subject
$headers['Subject'] = trim(stripslashes($_POST['_subject']));

if (strlen($identity_arr['organization']))
  $headers['Organization'] = $identity_arr['organization'];

if (strlen($identity_arr['reply-to']))
  $headers['Reply-To'] = $identity_arr['reply-to'];

if ($_SESSION['compose']['reply_msgid'])
  $headers['In-Reply-To'] = $_SESSION['compose']['reply_msgid'];


if ($_POST['_priority'])
  {
  $priority = (int)$_POST['_priority'];
  $a_priorities = array(1=>'lowest', 2=>'low', 4=>'high', 5=>'highest');
  if ($str_priority = $a_priorities[$priority])
    $headers['X-Priority'] = sprintf("%d (%s)", $priority, ucfirst($str_priority));
  }


// additional headers
$headers['Message-ID'] = $message_id;
$headers['X-Sender'] = $from;

if ($CONFIG['useragent'])
  $headers['User-Agent'] = $CONFIG['useragent'];


// append generic footer to all messages
if (!empty($CONFIG['generic_message_footer']))
  {
  $file = realpath($CONFIG['generic_message_footer']);
  if($fp = fopen($file, 'r'))
    {
    $content = fread($fp, filesize($file));
    fclose($fp);
    $_POST['_message'] .= "\r\n" . $content;
    }
  }


// use the configured delimiter for headers
$header_delm = $rcmail_config['mail_header_delimiter'] ? $rcmail_config['mail_header_delimiter'] : "\r\n";

// create PEAR::Mail_mime instance
$MAIL_MIME = new Mail_mime($header_delm);
$MAIL_MIME->setTXTBody(stripslashes($_POST['_message']), FALSE, TRUE);
//$MAIL_MIME->setTXTBody(wordwrap(stripslashes($_POST['_message'])), FALSE, TRUE);


// add stored attachments, if any
if (is_array($_SESSION['compose']['attachments']))
  foreach ($_SESSION['compose']['attachments'] as $attachment)
    $MAIL_MIME->addAttachment($attachment['path'], $attachment['mimetype'], $attachment['name'], TRUE);

  
// add submitted attachments
if (is_array($_FILES['_attachments']['tmp_name']))
  foreach ($_FILES['_attachments']['tmp_name'] as $i => $filepath)
    $MAIL_MIME->addAttachment($filepath, $files['type'][$i], $files['name'][$i], TRUE);


$message_charset = isset($_POST['_charset']) ? $_POST['_charset'] : $CHARSET;

// encoding settings for mail composing
$message_param = array('text_encoding' => '7bit',
                       'html_encoding' => 'quoted-printable',
                       'head_encoding' => 'quoted-printable',
                       'head_charset'  => $message_charset,
                       'html_charset'  => $message_charset,
                       'text_charset'  => $message_charset);

// compose message body and get headers
$msg_body = $MAIL_MIME->get($message_param);
$msg_subject = $headers['Subject'];


// send thru SMTP server using cusotm SMTP library
if ($CONFIG['smtp_server'])
  {
  // generate list of recipients
  $a_recipients = array($mailto);

  if (strlen($headers['Cc']))
    $a_recipients[] = $headers['Cc'];
  if (strlen($headers['Bcc']))
    $a_recipients[] = $headers['Bcc'];

  // generate message headers
  $header_str = $MAIL_MIME->txtHeaders($headers);

  // send message
  $sent = smtp_mail($from, $a_recipients, $header_str, $msg_body);

  // log error
  if (!$sent)
    {
    raise_error(array('code' => 800,
                      'type' => 'smtp',
                      'line' => __LINE__,
                      'file' => __FILE__,
                      'message' => "SMTP error: $SMTP_ERROR"), TRUE, FALSE);
    }
  }

// send mail using PHP's mail() function
else
  {
  // unset some headers because they will be added by the mail() function
  $headers_php = $headers;
  $headers_enc = $MAIL_MIME->headers($headers);
  unset($headers_php['To'], $headers_php['Subject']);

  // reset stored headers and overwrite
  $MAIL_MIME->_headers = array();
  $header_str = $MAIL_MIME->txtHeaders($headers_php);
  
  if(ini_get('safe_mode'))
    $sent = mail($headers_enc['To'], $headers_enc['Subject'], $msg_body, $header_str);
  else
    $sent = mail($headers_enc['To'], $headers_enc['Subject'], $msg_body, $header_str, "-f$from");
  }


// return to compose page if sending failed
if (!$sent)
  {
  show_message("sendingfailed", 'error'); 
  rcmail_overwrite_action('compose');
  return;
  }


// set repliead flag
if ($_SESSION['compose']['reply_uid'])
  $IMAP->set_flag($_SESSION['compose']['reply_uid'], 'ANSWERED');


// copy message to sent folder
if ($CONFIG['sent_mbox'])
  {
  // create string of complete message headers
  $header_str = $MAIL_MIME->txtHeaders($headers);

  // check if mailbox exists
  if (!in_array_nocase($CONFIG['sent_mbox'], $IMAP->list_mailboxes()))
    $mbox = $IMAP->create_mailbox($CONFIG['sent_mbox'], TRUE);
  else
    $mbox = TRUE;

  // append message to sent box
  if ($mbox)
    $saved = $IMAP->save_message($CONFIG['sent_mbox'], $header_str."\r\n".$msg_body);

  // raise error if saving failed
  if (!$saved)
    raise_error(array('code' => 800,
                      'type' => 'imap',
                      'file' => __FILE__,
                      'message' => "Could not save message in $CONFIG[sent_mbox]"), TRUE, FALSE);
  }


// log mail sending
if ($CONFIG['smtp_log'])
  {
  $log_entry = sprintf("[%s] User: %d on %s; Message for %s; Subject: %s\n",
               date("d-M-Y H:i:s O", mktime()),
               $_SESSION['user_id'],
               $_SERVER['REMOTE_ADDR'],
               $mailto,
               $msg_subject);

  if ($fp = @fopen($CONFIG['log_dir'].'/sendmail', 'a'))
    {
    fwrite($fp, $log_entry);
    fclose($fp);
    }
  }


// show confirmation
show_message('messagesent', 'confirmation');


// kill compose entry from session
rcmail_compose_cleanup();

?>
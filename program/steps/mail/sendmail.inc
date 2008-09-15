<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/sendmail.inc                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Compose a new mail message with all headers and attachments         |
 |   and send it using the PEAR::Net_SMTP class or with PHP mail()       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


// remove all scripts and act as called in frame
$OUTPUT->reset();
$OUTPUT->framed = TRUE;

$savedraft = !empty($_POST['_draft']) ? TRUE : FALSE;

/****** checks ********/

if (!isset($_SESSION['compose']['id'])) {
  raise_error(array('code' => 500, 'file' => __FILE__, 'message' => "Invalid compose ID"), true, false);
  console("Sendmail error", $_SESSION['compose']);
  $OUTPUT->show_message("An internal error occured. Please try again.", 'error');
  $OUTPUT->send('iframe');
}

if (!$savedraft && empty($_POST['_to']) && empty($_POST['_cc']) && empty($_POST['_bcc']) && empty($_POST['_subject']) && $_POST['_message']) {
  $OUTPUT->show_message('sendingfailed', 'error');
  $OUTPUT->send('iframe');
}

if(!$savedraft && !empty($CONFIG['sendmail_delay'])) {
  $wait_sec = time() - intval($CONFIG['sendmail_delay']) - intval($_SESSION['last_message_time']);
  if($wait_sec < 0)
    {
    $OUTPUT->show_message('senttooquickly', 'error', array('sec' => $wait_sec * -1));
    $OUTPUT->send('iframe');
    }
}


/****** message sending functions ********/

// get identity record
function rcmail_get_identity($id)
  {
  global $USER, $OUTPUT;
  
  if ($sql_arr = $USER->get_identity($id))
    {
    $out = $sql_arr;
    $out['mailto'] = $sql_arr['email'];
    $name = strpos($sql_arr['name'], ",") ? '"'.$sql_arr['name'].'"' : $sql_arr['name'];
    $out['string'] = sprintf('%s <%s>',
                             rcube_charset_convert($name, RCMAIL_CHARSET, $OUTPUT->get_charset()),
                             $sql_arr['email']);
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
  global $CONFIG;

  $htmlContents = $mime_message->getHtmlBody();

  // remove any null-byte characters before parsing
  $body = preg_replace('/\x00/', '', $htmlContents);
  
  $last_img_pos = 0;

  $searchstr = 'program/js/tiny_mce/plugins/emotions/img/';

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
      $img_file = INSTALL_PATH . '/' . $searchstr . $image_name;
      if(! $mime_message->addHTMLImage($img_file, 'image/gif', '', true, $image_name))
        $OUTPUT->show_message("emoticonerror", 'error');

      array_push($included_images, $image_name);
      }

    $body = $body_pre . $img_file . $body_post;

    $last_img_pos = $pos2;
    }
   
  $mime_message->setHTMLBody($body);
}


/****** compose message ********/

if (strlen($_POST['_draft_saveid']) > 3)
  $olddraftmessageid = get_input_value('_draft_saveid', RCUBE_INPUT_POST);

$message_id = sprintf('<%s@%s>', md5(uniqid('rcmail'.rand(),true)), $RCMAIL->config->mail_domain($_SESSION['imap_host']));

// set default charset
$input_charset = $OUTPUT->get_charset();
$message_charset = isset($_POST['_charset']) ? $_POST['_charset'] : $input_charset;

$mailto_regexp = array('/[,;]\s*[\r\n]+/', '/[\r\n]+/', '/[,;]\s*$/m', '/;/');
$mailto_replace = array(', ', ', ', '', ',');

// replace new lines and strip ending ', '
$mailto = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_to', RCUBE_INPUT_POST, TRUE, $message_charset));
$mailcc = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_cc', RCUBE_INPUT_POST, TRUE, $message_charset));
$mailbcc = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_bcc', RCUBE_INPUT_POST, TRUE, $message_charset));

if (empty($mailto) && !empty($mailcc)) {
  $mailto = $mailcc;
  $mailcc = null;
}
else if (empty($mailto))
  $mailto = 'undisclosed-recipients:;';

// get sender name and address
$identity_arr = rcmail_get_identity(get_input_value('_from', RCUBE_INPUT_POST));
$from = $identity_arr['mailto'];

if (empty($identity_arr['string']))
  $identity_arr['string'] = $from;

// compose headers array
$headers = array('Date' => date('r'),
                 'From' => rcube_charset_convert($identity_arr['string'], RCMAIL_CHARSET, $message_charset),
                 'To'   => $mailto);

// additional recipients
if (!empty($mailcc))
  $headers['Cc'] = $mailcc;

if (!empty($mailbcc))
  $headers['Bcc'] = $mailbcc;
  
if (!empty($identity_arr['bcc']))
  $headers['Bcc'] = ($headers['Bcc'] ? $headers['Bcc'].', ' : '') . $identity_arr['bcc'];

// add subject
$headers['Subject'] = trim(get_input_value('_subject', RCUBE_INPUT_POST, FALSE, $message_charset));

if (!empty($identity_arr['organization']))
  $headers['Organization'] = $identity_arr['organization'];

if (!empty($_POST['_replyto']))
  $headers['Reply-To'] = preg_replace($mailto_regexp, $mailto_replace, get_input_value('_replyto', RCUBE_INPUT_POST, TRUE, $message_charset));
else if (!empty($identity_arr['reply-to']))
  $headers['Reply-To'] = $identity_arr['reply-to'];

if (!empty($_SESSION['compose']['reply_msgid']))
  $headers['In-Reply-To'] = $_SESSION['compose']['reply_msgid'];

if (!empty($_SESSION['compose']['references']))
  $headers['References'] = $_SESSION['compose']['references'];

if (!empty($_POST['_priority']))
  {
  $priority = intval($_POST['_priority']);
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
if ($CONFIG['http_received_header'])
{
  $nldlm = $RCMAIL->config->header_delimiter() . "\t";
  $headers['Received'] =  wordwrap('from ' . (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?
      gethostbyaddr($_SERVER['HTTP_X_FORWARDED_FOR']).' ['.$_SERVER['HTTP_X_FORWARDED_FOR'].']'.$nldlm.' via ' : '') .
    gethostbyaddr($_SERVER['REMOTE_ADDR']).' ['.$_SERVER['REMOTE_ADDR'].']'.$nldlm.'with ' .
    $_SERVER['SERVER_PROTOCOL'].' ('.$_SERVER['REQUEST_METHOD'].'); ' . date('r'),
    69, $nldlm);
}

$headers['Message-ID'] = $message_id;
$headers['X-Sender'] = $from;

if (!empty($CONFIG['useragent']))
  $headers['User-Agent'] = $CONFIG['useragent'];

$isHtmlVal = strtolower(get_input_value('_is_html', RCUBE_INPUT_POST));
$isHtml = ($isHtmlVal == "1");

// fetch message body
$message_body = get_input_value('_message', RCUBE_INPUT_POST, TRUE, $message_charset);

// remove signature's div ID
if (!$savedraft && $isHtml)
  $message_body = preg_replace('/\s*id="_rc_sig"/', '', $message_body);

// append generic footer to all messages
if (!$savedraft && !empty($CONFIG['generic_message_footer']) && ($footer = file_get_contents(realpath($CONFIG['generic_message_footer']))))
  $message_body .= "\r\n" . rcube_charset_convert($footer, 'UTF-8', $message_charset);

// create extended PEAR::Mail_mime instance
$MAIL_MIME = new rcube_mail_mime($RCMAIL->config->header_delimiter());

// For HTML-formatted messages, construct the MIME message with both
// the HTML part and the plain-text part

if ($isHtml)
  {
  $MAIL_MIME->setHTMLBody($message_body);

  // add a plain text version of the e-mail as an alternative part.
  $h2t = new html2text($message_body);
  $plainTextPart = wordwrap($h2t->get_text(), 998, "\r\n", true);
  if (!strlen($plainTextPart)) 
    { 
    // empty message body breaks attachment handling in drafts 
    $plainTextPart = "\r\n"; 
    }
  $MAIL_MIME->setTXTBody(html_entity_decode($plainTextPart, ENT_COMPAT, 'utf-8'));

  // look for "emoticon" images from TinyMCE and copy into message as attachments
  rcmail_attach_emoticons($MAIL_MIME);
  }
else
  {
  $message_body = wordwrap($message_body, 75, "\r\n");
  $message_body = wordwrap($message_body, 998, "\r\n", true);
  if (!strlen($message_body))  
    { 
    // empty message body breaks attachment handling in drafts 
    $message_body = "\r\n"; 
    } 
  $MAIL_MIME->setTXTBody($message_body, FALSE, TRUE);
  }

// chose transfer encoding
$charset_7bit = array('ASCII', 'ISO-2022-JP', 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-15');
$transfer_encoding = in_array(strtoupper($message_charset), $charset_7bit) ? '7bit' : '8bit';

// add stored attachments, if any
if (is_array($_SESSION['compose']['attachments']))
  foreach ($_SESSION['compose']['attachments'] as $id => $attachment)
  {
    $dispurl = '/\ssrc\s*=\s*[\'"]?\S+display-attachment\S+file=rcmfile' . $id . '[\'"]?/';
    $match = preg_match($dispurl, $message_body);
    if ($isHtml && ($match > 0))
    {
      $message_body = preg_replace($dispurl, ' src="'.$attachment['name'].'"', $message_body);
      $MAIL_MIME->setHTMLBody($message_body);
      $MAIL_MIME->addHTMLImage($attachment['path'], $attachment['mimetype'], $attachment['name']);
    }
    else
    {
      $ctype = str_replace('image/pjpeg', 'image/jpeg', $attachment['mimetype']); // #1484914

      // .eml attachments send inline
      $MAIL_MIME->addAttachment($attachment['path'],
        $ctype, 
        $attachment['name'], true, 
        ($ctype == 'message/rfc822' ? $transfer_encoding : 'base64'),
        ($ctype == 'message/rfc822' ? 'inline' : 'attachment'),
        $message_charset);
    }
  }

// add submitted attachments
if (is_array($_FILES['_attachments']['tmp_name']))
  foreach ($_FILES['_attachments']['tmp_name'] as $i => $filepath)
    {
    $ctype = $files['type'][$i];
    $ctype = str_replace('image/pjpeg', 'image/jpeg', $ctype); // #1484914
    
    $MAIL_MIME->addAttachment($filepath, $ctype, $files['name'][$i], true,
	($ctype == 'message/rfc822' ? $transfer_encoding : 'base64'),
	'attachment', $message_charset);
    }


// encoding settings for mail composing
$MAIL_MIME->setParam(array(
  'text_encoding' => $transfer_encoding,
  'html_encoding' => 'quoted-printable',
  'head_encoding' => 'quoted-printable',
  'head_charset'  => $message_charset,
  'html_charset'  => $message_charset,
  'text_charset'  => $message_charset,
));

// encoding subject header with mb_encode provides better results with asian characters
if (function_exists("mb_encode_mimeheader"))
{
  mb_internal_encoding($message_charset);
  $headers['Subject'] = mb_encode_mimeheader($headers['Subject'], $message_charset, 'Q');
  mb_internal_encoding(RCMAIL_CHARSET);
}

// pass headers to message object
$MAIL_MIME->headers($headers);

// Begin SMTP Delivery Block 
if (!$savedraft)
{
  $sent = rcmail_deliver_message($MAIL_MIME, $from, $mailto);
  
  // return to compose page if sending failed
  if (!$sent)
    {
    $OUTPUT->show_message("sendingfailed", 'error'); 
    $OUTPUT->send('iframe');
    }

  // save message sent time
  if (!empty($CONFIG['sendmail_delay']))
    $_SESSION['last_message_time'] = time();
  
  // set replied/forwarded flag
  if ($_SESSION['compose']['reply_uid'])
    $IMAP->set_flag($_SESSION['compose']['reply_uid'], 'ANSWERED');
  else if ($_SESSION['compose']['forward_uid'])
    $IMAP->set_flag($_SESSION['compose']['forward_uid'], 'FORWARDED');

} // End of SMTP Delivery Block



// Determine which folder to save message
if ($savedraft)
  $store_target = $CONFIG['drafts_mbox'];
else	
  $store_target = isset($_POST['_store_target']) ? get_input_value('_store_target', RCUBE_INPUT_POST) : $CONFIG['sent_mbox'];

if ($store_target)
  {
  // check if mailbox exists
  if (!in_array_nocase($store_target, $IMAP->list_mailboxes()))
    {
      // folder may be existing but not subscribed (#1485241)
      if (!in_array_nocase($store_target, $IMAP->list_unsubscribed()))
        $store_folder = $IMAP->create_mailbox($store_target, TRUE);
      else if ($IMAP->subscribe($store_target))
        $store_folder = TRUE;
    }
  else
    $store_folder = TRUE;
  
  // append message to sent box
  if ($store_folder)
    $saved = $IMAP->save_message($store_target, $MAIL_MIME->getMessage());

  // raise error if saving failed
  if (!$saved)
    {
    raise_error(array('code' => 800, 'type' => 'imap', 'file' => __FILE__,
                      'message' => "Could not save message in $store_target"), TRUE, FALSE);
    
    if ($savedraft) {
      $OUTPUT->show_message('errorsaving', 'error');
      $OUTPUT->send('iframe');
      }
    }

  if ($olddraftmessageid)
    {
    // delete previous saved draft
    $a_deleteid = $IMAP->search($CONFIG['drafts_mbox'], 'HEADER Message-ID', $olddraftmessageid);
    $deleted = $IMAP->delete_message($IMAP->get_uid($a_deleteid[0], $CONFIG['drafts_mbox']), $CONFIG['drafts_mbox']);

    // raise error if deletion of old draft failed
    if (!$deleted)
      raise_error(array('code' => 800, 'type' => 'imap', 'file' => __FILE__,
                        'message' => "Could not delete message from ".$CONFIG['drafts_mbox']), TRUE, FALSE);
    }
  }

if ($savedraft)
  {
  $msgid = strtr($message_id, array('>' => '', '<' => ''));
  
  // remember new draft-uid
  $draftids = $IMAP->search($CONFIG['drafts_mbox'], 'HEADER Message-ID', $msgid);
  $_SESSION['compose']['param']['_draft_uid'] = $IMAP->get_uid($draftids[0], $CONFIG['drafts_mbox']);

  // display success
  $OUTPUT->show_message('messagesaved', 'confirmation');

  // update "_draft_saveid" and the "cmp_hash" to prevent "Unsaved changes" warning
  $OUTPUT->command('set_draft_id', $msgid);
  $OUTPUT->command('compose_field_hash', true);

  // start the auto-save timer again
  $OUTPUT->command('auto_save_start');

  $OUTPUT->send('iframe');
  }
else
  {
  rcmail_compose_cleanup();

  if ($store_folder && !$saved)
    $OUTPUT->command('sent_successfully', 'error', rcube_label('errorsavingsent'));
  else
    $OUTPUT->command('sent_successfully', 'confirmation', rcube_label('messagesent'));
  $OUTPUT->send('iframe');
  }

?>

<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/sendmail.inc                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
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
  raise_error(array('code' => 500, 'type' => 'smtp',
    'file' => __FILE__, 'line' => __LINE__,
    'message' => "Invalid compose ID"), true, false);

  $OUTPUT->show_message("An internal error occured. Please try again.", 'error');
  $OUTPUT->send('iframe');
}

if (!$savedraft) {
  if (empty($_POST['_to']) && empty($_POST['_cc']) && empty($_POST['_bcc'])
    && empty($_POST['_subject']) && $_POST['_message']) {
    $OUTPUT->show_message('sendingfailed', 'error');
    $OUTPUT->send('iframe');
  }

  if(!empty($CONFIG['sendmail_delay'])) {
    $wait_sec = time() - intval($CONFIG['sendmail_delay']) - intval($CONFIG['last_message_time']);
    if($wait_sec < 0) {
      $OUTPUT->show_message('senttooquickly', 'error', array('sec' => $wait_sec * -1));
      $OUTPUT->send('iframe');
    }
  }
}


/****** message sending functions ********/

// encrypt parts of the header
function rcmail_encrypt_header($what)
{
  global $CONFIG, $RCMAIL;
  if (!$CONFIG['http_received_header_encrypt'])
  {
    return $what;
  }
  return $RCMAIL->encrypt($what);
}

// get identity record
function rcmail_get_identity($id)
  {
  global $USER, $OUTPUT;
  
  if ($sql_arr = $USER->get_identity($id))
    {
    $out = $sql_arr;
    $out['mailto'] = $sql_arr['email'];
    
    // Special chars as defined by RFC 822 need to in quoted string (or escaped).
    if (preg_match('/[\(\)\<\>\\\.\[\]@,;:"]/', $sql_arr['name']))
      $name = '"' . addcslashes($sql_arr['name'], '"') . '"';
    else
      $name = $sql_arr['name'];

    $out['string'] = rcube_charset_convert($name, RCMAIL_CHARSET, $OUTPUT->get_charset());
    if ($sql_arr['email'])
      $out['string'] .= ' <' . $sql_arr['email'] . '>';

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

  $body = $mime_message->getHTMLBody();

  // remove any null-byte characters before parsing
  $body = preg_replace('/\x00/', '', $body);
  
  $searchstr = 'program/js/tiny_mce/plugins/emotions/img/';
  $offset = 0;

  // keep track of added images, so they're only added once
  $included_images = array();

  if (preg_match_all('# src=[\'"]([^\'"]+)#', $body, $matches, PREG_OFFSET_CAPTURE)) {
    foreach ($matches[1] as $m) {
      // find emoticon image tags
      if (preg_match('#'.$searchstr.'(.*)$#', $m[0], $imatches)) {
        $image_name = $imatches[1];

        // sanitize image name so resulting attachment doesn't leave images dir
        $image_name = preg_replace('/[^a-zA-Z0-9_\.\-]/i', '', $image_name);
        $img_file = INSTALL_PATH . '/' . $searchstr . $image_name;

        if (! in_array($image_name, $included_images)) {
          // add the image to the MIME message
          if (! $mime_message->addHTMLImage($img_file, 'image/gif', '', true, $image_name))
            $OUTPUT->show_message("emoticonerror", 'error');
          array_push($included_images, $image_name);
        }

        $body = substr_replace($body, $img_file, $m[1] + $offset, strlen($m[0]));
        $offset += strlen($img_file) - strlen($m[0]);
      }
    }
  }

  $mime_message->setHTMLBody($body);

  return $body;
}

// parse email address input
function rcmail_email_input_format($mailto)
{
  global $EMAIL_FORMAT_ERROR;

  $regexp = array('/[,;]\s*[\r\n]+/', '/[\r\n]+/', '/[,;]\s*$/m', '/;/', '/(\S{1})(<\S+@\S+>)/U');
  $replace = array(', ', ', ', '', ',', '\\1 \\2');

  // replace new lines and strip ending ', ', make address input more valid
  $mailto = trim(preg_replace($regexp, $replace, $mailto));

  $result = array();
  $items = rcube_explode_quoted_string(',', $mailto);

  foreach($items as $item) {
    $item = trim($item);
    // address in brackets without name (do nothing)
    if (preg_match('/^<\S+@\S+>$/', $item)) {
      $result[] = $item;
    // address without brackets and without name (add brackets)
    } else if (preg_match('/^\S+@\S+$/', $item)) {
      $result[] = '<'.$item.'>';
    // address with name (handle name)
    } else if (preg_match('/\S+@\S+>*$/', $item, $matches)) {
      $address = $matches[0];
      $name = str_replace($address, '', $item);
      $name = trim($name);
      if ($name && ($name[0] != '"' || $name[strlen($name)-1] != '"')
          && preg_match('/[\(\)\<\>\\\.\[\]@,;:"]/', $name)) {
	  $name = '"'.addcslashes($name, '"').'"';
      }
      if (!preg_match('/^<\S+@\S+>$/', $address))
        $address = '<'.$address.'>';

      $result[] = $name.' '.$address;
      $item = $address;
    } else if (trim($item)) {
      continue;
    }

    // check address format
    $item = trim($item, '<>');
    if ($item && !check_email($item)) {
      $EMAIL_FORMAT_ERROR = $item;
      return;
    }
  }

  return implode(', ', $result);
}

/****** compose message ********/

if (strlen($_POST['_draft_saveid']) > 3)
  $olddraftmessageid = get_input_value('_draft_saveid', RCUBE_INPUT_POST);

$message_id = sprintf('<%s@%s>', md5(uniqid('rcmail'.mt_rand(),true)), $RCMAIL->config->mail_domain($_SESSION['imap_host']));

// set default charset
$input_charset = $OUTPUT->get_charset();
$message_charset = isset($_POST['_charset']) ? $_POST['_charset'] : $input_charset;

$EMAIL_FORMAT_ERROR = NULL;

$mailto = rcmail_email_input_format(get_input_value('_to', RCUBE_INPUT_POST, TRUE, $message_charset));
$mailcc = rcmail_email_input_format(get_input_value('_cc', RCUBE_INPUT_POST, TRUE, $message_charset));
$mailbcc = rcmail_email_input_format(get_input_value('_bcc', RCUBE_INPUT_POST, TRUE, $message_charset));

if ($EMAIL_FORMAT_ERROR) {
  $OUTPUT->show_message('emailformaterror', 'error', array('email' => $EMAIL_FORMAT_ERROR)); 
  $OUTPUT->send('iframe');
}

if (empty($mailto) && !empty($mailcc)) {
  $mailto = $mailcc;
  $mailcc = null;
}
else if (empty($mailto))
  $mailto = 'undisclosed-recipients:;';

// get sender name and address
$from = get_input_value('_from', RCUBE_INPUT_POST, true, $message_charset);
$identity_arr = rcmail_get_identity($from);

if (!$identity_arr && ($from = rcmail_email_input_format($from))) {
  if (preg_match('/(\S+@\S+)/', $from, $m))
    $identity_arr['mailto'] = $m[1];
} else
  $from = $identity_arr['mailto'];

if (empty($identity_arr['string']))
  $identity_arr['string'] = $from;

// compose headers array
$headers = array();

// if configured, the Received headers goes to top, for good measure
if ($CONFIG['http_received_header'])
{
  $nldlm = $RCMAIL->config->header_delimiter() . "\t";
  // FROM/VIA
  $http_header = 'from ';
  if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $host = $_SERVER['HTTP_X_FORWARDED_FOR'];
    $hostname = gethostbyaddr($host);
    if ($CONFIG['http_received_header_encrypt']) {
      $http_header .= rcmail_encrypt_header($hostname);
      if ($host != $hostname)
        $http_header .= ' ('. rcmail_encrypt_header($host) . ')';
    } else {
      $http_header .= (($host != $hostname) ? $hostname : '[' . $host . ']');
      if ($host != $hostname)
        $http_header .= ' (['. $host .'])';
    }
    $http_header .= $nldlm . ' via ';
  }
  $host = $_SERVER['REMOTE_ADDR'];
  $hostname = gethostbyaddr($host);
  if ($CONFIG['http_received_header_encrypt']) {
    $http_header .= rcmail_encrypt_header($hostname);
    if ($host != $hostname)
      $http_header .= ' ('. rcmail_encrypt_header($host) . ')';
  } else {
    $http_header .= (($host != $hostname) ? $hostname : '[' . $host . ']');
    if ($host != $hostname)
      $http_header .= ' (['. $host .'])';
  }
  // BY
  $http_header .= $nldlm . 'by ' . $_SERVER['HTTP_HOST'];
  // WITH
  $http_header .= $nldlm . 'with HTTP (' . $_SERVER['SERVER_PROTOCOL'] .
      ' '.$_SERVER['REQUEST_METHOD'] . '); ' . date('r');
  $http_header = wordwrap($http_header, 69, $nldlm);

  $headers['Received'] = $http_header;
}

$headers['Date'] = date('r');
$headers['From'] = rcube_charset_convert($identity_arr['string'], RCMAIL_CHARSET, $message_charset);
$headers['To'] = $mailto;

// additional recipients
if (!empty($mailcc))
  $headers['Cc'] = $mailcc;

if (!empty($mailbcc))
  $headers['Bcc'] = $mailbcc;
  
if (!empty($identity_arr['bcc']))
  $headers['Bcc'] = ($headers['Bcc'] ? $headers['Bcc'].', ' : '') . $identity_arr['bcc'];

// add subject
$headers['Subject'] = trim(get_input_value('_subject', RCUBE_INPUT_POST, TRUE, $message_charset));

if (!empty($identity_arr['organization']))
  $headers['Organization'] = $identity_arr['organization'];

if (!empty($_POST['_replyto']))
  $headers['Reply-To'] = rcmail_email_input_format(get_input_value('_replyto', RCUBE_INPUT_POST, TRUE, $message_charset));
else if (!empty($identity_arr['reply-to']))
  $headers['Reply-To'] = $identity_arr['reply-to'];

if (!empty($_SESSION['compose']['reply_msgid']))
  $headers['In-Reply-To'] = $_SESSION['compose']['reply_msgid'];
  
// remember reply/forward UIDs in special headers
if (!empty($_SESSION['compose']['reply_uid']) && $savedraft)
  $headers['X-Draft-Info'] = array('type' => 'reply', 'uid' => $_SESSION['compose']['reply_uid']);
else if (!empty($_SESSION['compose']['forward_uid']) && $savedraft)
  $headers['X-Draft-Info'] = array('type' => 'forward', 'uid' => $_SESSION['compose']['forward_uid']);

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
$headers['Message-ID'] = $message_id;
$headers['X-Sender'] = $from;

if (is_array($headers['X-Draft-Info']))
  $headers['X-Draft-Info'] = rcmail_draftinfo_encode($headers['X-Draft-Info'] + array('folder' => $_SESSION['compose']['mailbox']));

if (!empty($CONFIG['useragent']))
  $headers['User-Agent'] = $CONFIG['useragent'];

// exec hook for header checking and manipulation
$data = $RCMAIL->plugins->exec_hook('outgoing_message_headers', array('headers' => $headers));

// sending aborted by plugin
if ($data['abort'] && !$savedraft) {
  $OUTPUT->show_message($data['message'] ? $data['message'] : 'sendingfailed');
  $OUTPUT->send('iframe');
}
else
  $headers = $data['headers'];


$isHtmlVal = strtolower(get_input_value('_is_html', RCUBE_INPUT_POST));
$isHtml = ($isHtmlVal == "1");

// fetch message body
$message_body = get_input_value('_message', RCUBE_INPUT_POST, TRUE, $message_charset);

if (!$savedraft) {
  // remove signature's div ID
  if ($isHtml)
    $message_body = preg_replace('/\s*id="_rc_sig"/', '', $message_body);

  // generic footer for all messages
  if (!empty($CONFIG['generic_message_footer'])) {
    $footer = file_get_contents(realpath($CONFIG['generic_message_footer']));
    $footer = rcube_charset_convert($footer, RCMAIL_CHARSET, $message_charset);
  }
}

// set line length for body wrapping
$LINE_LENGTH = $RCMAIL->config->get('line_length', 75);

// Since we can handle big messages with disk usage, we need more time to work
@set_time_limit(0);

// create PEAR::Mail_mime instance
$MAIL_MIME = new Mail_mime($RCMAIL->config->header_delimiter());

// Check if we have enough memory to handle the message in it
// It's faster than using files, so we'll do this if we only can
if (is_array($_SESSION['compose']['attachments']) && $CONFIG['smtp_server']
  && ($mem_limit = parse_bytes(ini_get('memory_limit'))))
{
  $memory = function_exists('memory_get_usage') ? memory_get_usage() : 16*1024*1024; // safe value: 16MB

  foreach ($_SESSION['compose']['attachments'] as $id => $attachment)
    $memory += $attachment['size'];

  // Yeah, Net_SMTP needs up to 12x more memory, 1.33 is for base64
  if ($memory * 1.33 * 12 > $mem_limit)
    $MAIL_MIME->setParam('delay_file_io', true);
}

// For HTML-formatted messages, construct the MIME message with both
// the HTML part and the plain-text part

if ($isHtml) {
  $plugin = $RCMAIL->plugins->exec_hook('outgoing_message_body', array('body' => $message_body, 'type' => 'html', 'message' => $MAIL_MIME));
  $MAIL_MIME->setHTMLBody($plugin['body'] . ($footer ? "\r\n<pre>".$footer.'</pre>' : ''));

  // add a plain text version of the e-mail as an alternative part.
  $h2t = new html2text($plugin['body'], false, true, 0);
  $plainTextPart = rc_wordwrap($h2t->get_text(), $LINE_LENGTH, "\r\n") . ($footer ? "\r\n".$footer : '');
  $plainTextPart = wordwrap($plainTextPart, 998, "\r\n", true);
  if (!strlen($plainTextPart)) {
    // empty message body breaks attachment handling in drafts 
    $plainTextPart = "\r\n"; 
  }
  $plugin = $RCMAIL->plugins->exec_hook('outgoing_message_body', array('body' => $plainTextPart, 'type' => 'alternative', 'message' => $MAIL_MIME));
  $MAIL_MIME->setTXTBody($plugin['body']);

  // look for "emoticon" images from TinyMCE and copy into message as attachments
  $message_body = rcmail_attach_emoticons($MAIL_MIME);
}
else
  {
  $message_body = rc_wordwrap($message_body, $LINE_LENGTH, "\r\n");
  if ($footer)
    $message_body .= "\r\n" . $footer;
  $message_body = wordwrap($message_body, 998, "\r\n", true);
  if (!strlen($message_body)) { 
    // empty message body breaks attachment handling in drafts 
    $message_body = "\r\n"; 
  }
  $plugin = $RCMAIL->plugins->exec_hook('outgoing_message_body', array('body' => $message_body, 'type' => 'plain', 'message' => $MAIL_MIME));
  $MAIL_MIME->setTXTBody($plugin['body'], false, true);
}

// add stored attachments, if any
if (is_array($_SESSION['compose']['attachments']))
{
  foreach ($_SESSION['compose']['attachments'] as $id => $attachment) {
    // This hook retrieves the attachment contents from the file storage backend
    $attachment = $RCMAIL->plugins->exec_hook('get_attachment', $attachment);

    $dispurl = '/\ssrc\s*=\s*[\'"]*\S+display-attachment\S+file=rcmfile' . preg_quote($attachment['id']) . '[\s\'"]\s*/';
    $message_body = $MAIL_MIME->getHTMLBody();
    if ($isHtml && (preg_match($dispurl, $message_body) > 0)) {
      $message_body = preg_replace($dispurl, ' src="'.$attachment['name'].'" ', $message_body);
      $MAIL_MIME->setHTMLBody($message_body);
      
      if ($attachment['data'])
        $MAIL_MIME->addHTMLImage($attachment['data'], $attachment['mimetype'], $attachment['name'], false);
      else
        $MAIL_MIME->addHTMLImage($attachment['path'], $attachment['mimetype'], $attachment['name'], true);
    }
    else {
      $ctype = str_replace('image/pjpeg', 'image/jpeg', $attachment['mimetype']); // #1484914
      $file = $attachment['data'] ? $attachment['data'] : $attachment['path'];

      // .eml attachments send inline
      $MAIL_MIME->addAttachment($file,
        $ctype, 
        $attachment['name'],
        ($attachment['data'] ? false : true),
        // @TODO: quoted-printable for message/rfc822 is safe,
	// but we should check that 7bit or 8bit is possible here
        ($ctype == 'message/rfc822' ? 'quoted-printable' : 'base64'),
        ($ctype == 'message/rfc822' ? 'inline' : 'attachment'),
        $message_charset, '', '', 
        $CONFIG['mime_param_folding'] ? 'quoted-printable' : NULL,
        $CONFIG['mime_param_folding'] == 2 ? 'quoted-printable' : NULL
      );
    }
  }
}

// choose transfer encoding for plain/text body
if (preg_match('/[^\x00-\x7F]/', $MAIL_MIME->getTXTBody()))
  $transfer_encoding = $RCMAIL->config->get('force_7bit') ? 'quoted-printable' : '8bit';
else
  $transfer_encoding = '7bit';

// encoding settings for mail composing
$MAIL_MIME->setParam('text_encoding', $transfer_encoding);
$MAIL_MIME->setParam('html_encoding', 'quoted-printable');
$MAIL_MIME->setParam('head_encoding', 'quoted-printable');
$MAIL_MIME->setParam('head_charset', $message_charset);
$MAIL_MIME->setParam('html_charset', $message_charset);
$MAIL_MIME->setParam('text_charset', $message_charset);

// encoding subject header with mb_encode provides better results with asian characters
if (function_exists('mb_encode_mimeheader'))
{
  mb_internal_encoding($message_charset);
  $headers['Subject'] = mb_encode_mimeheader($headers['Subject'],
    $message_charset, 'Q', $RCMAIL->config->header_delimiter(), 8);
  mb_internal_encoding(RCMAIL_CHARSET);
}

// pass headers to message object
$MAIL_MIME->headers($headers);

// Begin SMTP Delivery Block 
if (!$savedraft)
{
  // check for 'From' address (identity may be incomplete)
  if ($identity_arr && !$identity_arr['mailto']) {
    $OUTPUT->show_message('nofromaddress', 'error');
    $OUTPUT->send('iframe'); 
  }

  $sent = rcmail_deliver_message($MAIL_MIME, $from, $mailto, $smtp_error, $mailbody_file);

  // return to compose page if sending failed
  if (!$sent)
    {
    // remove temp file
    if ($mailbody_file) {
      unlink($mailbody_file);
      }

    if ($smtp_error)
      $OUTPUT->show_message($smtp_error['label'], 'error', $smtp_error['vars']); 
    else
      $OUTPUT->show_message('sendingfailed', 'error'); 
    $OUTPUT->send('iframe');
    }

  // save message sent time
  if (!empty($CONFIG['sendmail_delay']))
    $RCMAIL->user->save_prefs(array('last_message_time' => time()));
  
  // set replied/forwarded flag
  if ($_SESSION['compose']['reply_uid'])
    $IMAP->set_flag($_SESSION['compose']['reply_uid'], 'ANSWERED', $_SESSION['compose']['mailbox']);
  else if ($_SESSION['compose']['forward_uid'])
    $IMAP->set_flag($_SESSION['compose']['forward_uid'], 'FORWARDED', $_SESSION['compose']['mailbox']);

} // End of SMTP Delivery Block


// Determine which folder to save message
if ($savedraft)
  $store_target = $CONFIG['drafts_mbox'];
else	
  $store_target = isset($_POST['_store_target']) ? get_input_value('_store_target', RCUBE_INPUT_POST) : $CONFIG['sent_mbox'];

if ($store_target)
  {
  // check if mailbox exists
  if (!in_array($store_target, $IMAP->list_mailboxes()))
    {
      // folder may be existing but not subscribed (#1485241)
      if (!in_array($store_target, $IMAP->list_unsubscribed()))
        $store_folder = $IMAP->create_mailbox($store_target, TRUE);
      else if ($IMAP->subscribe($store_target))
        $store_folder = TRUE;
    }
  else
    $store_folder = TRUE;

  // append message to sent box
  if ($store_folder) {

    // message body in file
    if ($mailbody_file || $MAIL_MIME->getParam('delay_file_io')) {
      $headers = $MAIL_MIME->txtHeaders();
      
      // file already created
      if ($mailbody_file)
        $msg = $mailbody_file;
      else {
        $temp_dir = $RCMAIL->config->get('temp_dir');
        $mailbody_file = tempnam($temp_dir, 'rcmMsg');
        if (!PEAR::isError($msg = $MAIL_MIME->saveMessageBody($mailbody_file)))
          $msg = $mailbody_file;
        }
      }
    else {
      $msg = $MAIL_MIME->getMessage();
      $headers = '';
      }

    if (PEAR::isError($msg))
      raise_error(array('code' => 600, 'type' => 'php',
	    'file' => __FILE__, 'line' => __LINE__,
            'message' => "Could not create message: ".$msg->getMessage()),
            TRUE, FALSE);
    else {
      $saved = $IMAP->save_message($store_target, $msg, $headers, $mailbody_file ? true : false);
      }

    if ($mailbody_file) {
      unlink($mailbody_file);
      $mailbody_file = null;
      }

    // raise error if saving failed
    if (!$saved) {
      raise_error(array('code' => 800, 'type' => 'imap',
	    'file' => __FILE__, 'line' => __LINE__,
            'message' => "Could not save message in $store_target"), TRUE, FALSE);
    
      if ($savedraft) {
        $OUTPUT->show_message('errorsaving', 'error');
        $OUTPUT->send('iframe');
        }
      }
    }

  if ($olddraftmessageid)
    {
    // delete previous saved draft
    $a_deleteid = $IMAP->search($CONFIG['drafts_mbox'], 'HEADER Message-ID '.$olddraftmessageid);

    $deleted = $IMAP->delete_message($IMAP->get_uid($a_deleteid[0], $CONFIG['drafts_mbox']), $CONFIG['drafts_mbox']);

    // raise error if deletion of old draft failed
    if (!$deleted)
      raise_error(array('code' => 800, 'type' => 'imap',
    		'file' => __FILE__, 'line' => __LINE__,
                'message' => "Could not delete message from ".$CONFIG['drafts_mbox']), TRUE, FALSE);
    }
  }
// remove temp file
else if ($mailbody_file) {
  unlink($mailbody_file);
  }


if ($savedraft)
  {
  $msgid = strtr($message_id, array('>' => '', '<' => ''));
  
  // remember new draft-uid
  $draftids = $IMAP->search($CONFIG['drafts_mbox'], 'HEADER Message-ID '.$msgid);
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

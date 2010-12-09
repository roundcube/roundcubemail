<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/sendmail.inc                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
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

$savedraft = !empty($_POST['_draft']) ? true : false;

/****** checks ********/

if (!isset($_SESSION['compose']['id'])) {
  raise_error(array('code' => 500, 'type' => 'php',
    'file' => __FILE__, 'line' => __LINE__,
    'message' => "Invalid compose ID"), true, false);

  $OUTPUT->show_message('internalerror', 'error');
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
  if (!$CONFIG['http_received_header_encrypt']) {
    return $what;
  }
  return $RCMAIL->encrypt($what);
}

// get identity record
function rcmail_get_identity($id)
{
  global $USER, $OUTPUT;

  if ($sql_arr = $USER->get_identity($id)) {
    $out = $sql_arr;
    $out['mailto'] = $sql_arr['email'];
    $out['string'] = format_email_recipient($sql_arr['email'],
      rcube_charset_convert($sql_arr['name'], RCMAIL_CHARSET, $OUTPUT->get_charset()));

    return $out;
  }

  return FALSE;
}

/**
 * go from this:
 * <img src="http[s]://.../tiny_mce/plugins/emotions/images/smiley-cool.gif" border="0" alt="Cool" title="Cool" />
 *
 * to this:
 *
 * <img src="/path/on/server/.../tiny_mce/plugins/emotions/images/smiley-cool.gif" border="0" alt="Cool" title="Cool" />
 * ...
 */
function rcmail_fix_emoticon_paths(&$mime_message)
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

// parse email address input (and count addresses)
function rcmail_email_input_format($mailto, $count=false, $check=true)
{
  global $EMAIL_FORMAT_ERROR, $RECIPIENT_COUNT;

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
      $item = idn_to_ascii($item);
      $result[] = $item;
    // address without brackets and without name (add brackets)
    } else if (preg_match('/^\S+@\S+$/', $item)) {
      $item = idn_to_ascii($item);
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
      $address = idn_to_ascii($address);
      if (!preg_match('/^<\S+@\S+>$/', $address))
        $address = '<'.$address.'>';

      $result[] = $name.' '.$address;
      $item = $address;
    } else if (trim($item)) {
      continue;
    }

    // check address format
    $item = trim($item, '<>');
    if ($item && $check && !check_email($item)) {
      $EMAIL_FORMAT_ERROR = $item;
      return;
    }
  }

  if ($count) {
    $RECIPIENT_COUNT += count($result);
  }

  return implode(', ', $result);
}


/****** compose message ********/

if (strlen($_POST['_draft_saveid']) > 3)
  $olddraftmessageid = get_input_value('_draft_saveid', RCUBE_INPUT_POST);

$message_id = rcmail_gen_message_id();

// set default charset
$input_charset = $OUTPUT->get_charset();
$message_charset = isset($_POST['_charset']) ? $_POST['_charset'] : $input_charset;

$EMAIL_FORMAT_ERROR = NULL;
$RECIPIENT_COUNT = 0;

$mailto = rcmail_email_input_format(get_input_value('_to', RCUBE_INPUT_POST, TRUE, $message_charset), true);
$mailcc = rcmail_email_input_format(get_input_value('_cc', RCUBE_INPUT_POST, TRUE, $message_charset), true);
$mailbcc = rcmail_email_input_format(get_input_value('_bcc', RCUBE_INPUT_POST, TRUE, $message_charset), true);

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

// Get sender name and address...
$from = get_input_value('_from', RCUBE_INPUT_POST, true, $message_charset);
// ... from identity...
if (is_numeric($from)) {
  if (is_array($identity_arr = rcmail_get_identity($from))) {
    if ($identity_arr['mailto'])
      $from = $identity_arr['mailto'];
    if ($identity_arr['string'])
      $from_string = $identity_arr['string'];
  }
  else {
    $from = null;
  }
}
// ... if there is no identity record, this might be a custom from
else if ($from_string = rcmail_email_input_format($from)) {
  if (preg_match('/(\S+@\S+)/', $from_string, $m))
    $from = trim($m[1], '<>');
  else
    $from = null;
}

if (!$from_string && $from)
  $from_string = $from;

// compose headers array
$headers = array();

// if configured, the Received headers goes to top, for good measure
if ($CONFIG['http_received_header'])
{
  $nldlm = "\r\n\t";
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

$headers['Date'] = rcmail_user_date();
$headers['From'] = rcube_charset_convert($from_string, RCMAIL_CHARSET, $message_charset);
$headers['To'] = $mailto;

// additional recipients
if (!empty($mailcc))
  $headers['Cc'] = $mailcc;

if (!empty($mailbcc))
  $headers['Bcc'] = $mailbcc;

if (!empty($identity_arr['bcc'])) {
  $headers['Bcc'] = ($headers['Bcc'] ? $headers['Bcc'].', ' : '') . $identity_arr['bcc'];
  $RECIPIENT_COUNT ++;
}

if (($max_recipients = (int) $RCMAIL->config->get('max_recipients')) > 0) {
  if ($RECIPIENT_COUNT > $max_recipients) {
    $OUTPUT->show_message('toomanyrecipients', 'error', array('max' => $max_recipients));
    $OUTPUT->send('iframe');
  }
}

// add subject
$headers['Subject'] = trim(get_input_value('_subject', RCUBE_INPUT_POST, TRUE, $message_charset));

if (!empty($identity_arr['organization']))
  $headers['Organization'] = $identity_arr['organization'];

if (!empty($_POST['_replyto'])) {
  $reply_to = rcmail_email_input_format(get_input_value('_replyto', RCUBE_INPUT_POST, TRUE, $message_charset));
  $headers['Reply-To'] = $reply_to;
  $headers['Mail-Reply-To'] = $reply_to;
}
else if (!empty($identity_arr['reply-to']))
  $headers['Reply-To'] = rcmail_email_input_format($identity_arr['reply-to'], false, true);

if (!empty($_POST['_followupto']))
  $headers['Mail-Followup-To'] = rcmail_email_input_format(get_input_value('_followupto', RCUBE_INPUT_POST, TRUE, $message_charset));

if (!empty($_SESSION['compose']['reply_msgid']))
  $headers['In-Reply-To'] = $_SESSION['compose']['reply_msgid'];

// remember reply/forward UIDs in special headers
if (!empty($_SESSION['compose']['reply_uid']) && $savedraft)
  $headers['X-Draft-Info'] = array('type' => 'reply', 'uid' => $_SESSION['compose']['reply_uid']);
else if (!empty($_SESSION['compose']['forward_uid']) && $savedraft)
  $headers['X-Draft-Info'] = array('type' => 'forward', 'uid' => $_SESSION['compose']['forward_uid']);

if (!empty($_SESSION['compose']['references']))
  $headers['References'] = $_SESSION['compose']['references'];

if (!empty($_POST['_priority'])) {
  $priority = intval($_POST['_priority']);
  $a_priorities = array(1=>'highest', 2=>'high', 4=>'low', 5=>'lowest');
  if ($str_priority = $a_priorities[$priority])
    $headers['X-Priority'] = sprintf("%d (%s)", $priority, ucfirst($str_priority));
}

if (!empty($_POST['_receipt'])) {
  $headers['Return-Receipt-To'] = $from_string;
  $headers['Disposition-Notification-To'] = $from_string;
}

// additional headers
$headers['Message-ID'] = $message_id;
$headers['X-Sender'] = $from;

if (is_array($headers['X-Draft-Info']))
  $headers['X-Draft-Info'] = rcmail_draftinfo_encode($headers['X-Draft-Info'] + array('folder' => $_SESSION['compose']['mailbox']));

if (!empty($CONFIG['useragent']))
  $headers['User-Agent'] = $CONFIG['useragent'];

// exec hook for header checking and manipulation
$data = $RCMAIL->plugins->exec_hook('message_outgoing_headers', array('headers' => $headers));

// sending aborted by plugin
if ($data['abort'] && !$savedraft) {
  $OUTPUT->show_message($data['message'] ? $data['message'] : 'sendingfailed');
  $OUTPUT->send('iframe');
}
else
  $headers = $data['headers'];


$isHtml = (bool) get_input_value('_is_html', RCUBE_INPUT_POST);

// fetch message body
$message_body = get_input_value('_message', RCUBE_INPUT_POST, TRUE, $message_charset);

if (!$savedraft) {
  if ($isHtml) {
    // remove signature's div ID
    $message_body = preg_replace('/\s*id="_rc_sig"/', '', $message_body);

    // add inline css for blockquotes
    $bstyle = 'padding-left:5px; border-left:#1010ff 2px solid; margin-left:5px; width:100%';
    $message_body = preg_replace('/<blockquote>/',
	'<blockquote type="cite" style="'.$bstyle.'">', $message_body);
  }

  // generic footer for all messages
  if ($isHtml && !empty($CONFIG['generic_message_footer_html'])) {
      $footer = file_get_contents(realpath($CONFIG['generic_message_footer_html']));
      $footer = rcube_charset_convert($footer, RCMAIL_CHARSET, $message_charset);
  }
  else if (!empty($CONFIG['generic_message_footer'])) {
    $footer = file_get_contents(realpath($CONFIG['generic_message_footer']));
    $footer = rcube_charset_convert($footer, RCMAIL_CHARSET, $message_charset);
    if ($isHtml)
      $footer = '<pre>'.$footer.'</pre>';
  }
  if ($footer)
    $message_body .= "\r\n" . $footer;
}

// set line length for body wrapping
$LINE_LENGTH = $RCMAIL->config->get('line_length', 72);

// Since we can handle big messages with disk usage, we need more time to work
@set_time_limit(0);

// create PEAR::Mail_mime instance
$MAIL_MIME = new Mail_mime("\r\n");

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
  $plugin = $RCMAIL->plugins->exec_hook('message_outgoing_body',
    array('body' => $message_body, 'type' => 'html', 'message' => $MAIL_MIME));

  $MAIL_MIME->setHTMLBody($plugin['body']);

  // replace emoticons
  $plugin['body'] = rcmail_replace_emoticons($plugin['body']);

  // add a plain text version of the e-mail as an alternative part.
  $h2t = new html2text($plugin['body'], false, true, 0);
  $plainTextPart = rc_wordwrap($h2t->get_text(), $LINE_LENGTH, "\r\n");
  $plainTextPart = wordwrap($plainTextPart, 998, "\r\n", true);
  if (!$plainTextPart) {
    // empty message body breaks attachment handling in drafts
    $plainTextPart = "\r\n";
  }
  else {
    // make sure all line endings are CRLF (#1486712)
    $plainTextPart = preg_replace('/\r?\n/', "\r\n", $plainTextPart);
  }

  $plugin = $RCMAIL->plugins->exec_hook('message_outgoing_body',
    array('body' => $plainTextPart, 'type' => 'alternative', 'message' => $MAIL_MIME));

  $MAIL_MIME->setTXTBody($plugin['body']);

  // look for "emoticon" images from TinyMCE and change their src paths to
  // be file paths on the server instead of URL paths.
  $message_body = rcmail_fix_emoticon_paths($MAIL_MIME);
}
else {
  $plugin = $RCMAIL->plugins->exec_hook('message_outgoing_body',
    array('body' => $message_body, 'type' => 'plain', 'message' => $MAIL_MIME));

  $message_body = $plugin['body'];

  // compose format=flowed content if enabled
  if ($flowed = $RCMAIL->config->get('send_format_flowed', true))
    $message_body = rcube_message::format_flowed($message_body, min($LINE_LENGTH+2, 79));
  else
    $message_body = rc_wordwrap($message_body, $LINE_LENGTH, "\r\n");

  $message_body = wordwrap($message_body, 998, "\r\n", true);
  if (!strlen($message_body)) { 
    // empty message body breaks attachment handling in drafts 
    $message_body = "\r\n"; 
  }

  $MAIL_MIME->setTXTBody($message_body, false, true);
}

// add stored attachments, if any
if (is_array($_SESSION['compose']['attachments']))
{
  foreach ($_SESSION['compose']['attachments'] as $id => $attachment) {
    // This hook retrieves the attachment contents from the file storage backend
    $attachment = $RCMAIL->plugins->exec_hook('attachment_get', $attachment);

    $dispurl = '/\ssrc\s*=\s*[\'"]*\S+display-attachment\S+file=rcmfile' . preg_quote($attachment['id']) . '[\s\'"]*/';
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
        ($ctype == 'message/rfc822' ? '8bit' : 'base64'),
        ($ctype == 'message/rfc822' ? 'inline' : 'attachment'),
        '', '', '',
        $CONFIG['mime_param_folding'] ? 'quoted-printable' : NULL,
        $CONFIG['mime_param_folding'] == 2 ? 'quoted-printable' : NULL,
        '', RCMAIL_CHARSET
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
$MAIL_MIME->setParam('text_charset', $message_charset . ($flowed ? ";\r\n format=flowed" : ''));

// encoding subject header with mb_encode provides better results with asian characters
if (function_exists('mb_encode_mimeheader')) {
  mb_internal_encoding($message_charset);
  $headers['Subject'] = mb_encode_mimeheader($headers['Subject'],
    $message_charset, 'Q', "\r\n", 8);
  mb_internal_encoding(RCMAIL_CHARSET);
}

// pass headers to message object
$MAIL_MIME->headers($headers);

// Begin SMTP Delivery Block
if (!$savedraft)
{
  // check 'From' address (identity may be incomplete)
  if (empty($from)) {
    $OUTPUT->show_message('nofromaddress', 'error');
    $OUTPUT->send('iframe');
  }

  // Handle Delivery Status Notification request
  if (!empty($_POST['_dsn'])) {
    $smtp_opts['dsn'] = true;
  }

  $sent = rcmail_deliver_message($MAIL_MIME, $from, $mailto,
    $smtp_error, $mailbody_file, $smtp_opts);

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
  // check if folder is subscribed
  if ($IMAP->mailbox_exists($store_target, true))
    $store_folder = true;
  // folder may be existing but not subscribed (#1485241)
  else if (!$IMAP->mailbox_exists($store_target))
    $store_folder = $IMAP->create_mailbox($store_target, true);
  else if ($IMAP->subscribe($store_target))
    $store_folder = true;

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
    $a_deleteid = $IMAP->search_once($CONFIG['drafts_mbox'],
        'HEADER Message-ID '.$olddraftmessageid, true);
    $deleted = $IMAP->delete_message($a_deleteid, $CONFIG['drafts_mbox']);

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
  $draftuids = $IMAP->search_once($CONFIG['drafts_mbox'], 'HEADER Message-ID '.$msgid, true);
  $_SESSION['compose']['param']['_draft_uid'] = $draftuids[0];

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



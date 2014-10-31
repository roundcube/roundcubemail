<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/sendmail.inc                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Compose a new mail message with all headers and attachments         |
 |   and send it using the PEAR::Net_SMTP class or with PHP mail()       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

// remove all scripts and act as called in frame
$OUTPUT->reset();
$OUTPUT->framed = TRUE;

$savedraft      = !empty($_POST['_draft']) ? true : false;
$sendmail_delay = (int) $RCMAIL->config->get('sendmail_delay');
$drafts_mbox    = $RCMAIL->config->get('drafts_mbox');

$COMPOSE_ID = rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC);
$COMPOSE    =& $_SESSION['compose_data_'.$COMPOSE_ID];

/****** checks ********/

if (!isset($COMPOSE['id'])) {
    rcube::raise_error(array('code' => 500, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Invalid compose ID"), true, false);

    $OUTPUT->show_message('internalerror', 'error');
    $OUTPUT->send('iframe');
}

if (!$savedraft) {
    if (empty($_POST['_to']) && empty($_POST['_cc']) && empty($_POST['_bcc'])
        && empty($_POST['_subject']) && $_POST['_message']
    ) {
        $OUTPUT->show_message('sendingfailed', 'error');
        $OUTPUT->send('iframe');
    }

    if ($sendmail_delay) {
        $wait_sec = time() - $sendmail_delay - intval($RCMAIL->config->get('last_message_time'));
        if ($wait_sec < 0) {
            $OUTPUT->show_message('senttooquickly', 'error', array('sec' => $wait_sec * -1));
            $OUTPUT->send('iframe');
        }
    }
}


/****** compose message ********/

if (empty($COMPOSE['param']['message-id'])) {
    $COMPOSE['param']['message-id'] = $RCMAIL->gen_message_id();
}
$message_id = $COMPOSE['param']['message-id'];

// set default charset
$message_charset = isset($_POST['_charset']) ? $_POST['_charset'] : $OUTPUT->get_charset();

$EMAIL_FORMAT_ERROR = NULL;
$RECIPIENT_COUNT    = 0;

$mailto  = rcmail_email_input_format(rcube_utils::get_input_value('_to', rcube_utils::INPUT_POST, TRUE, $message_charset), true);
$mailcc  = rcmail_email_input_format(rcube_utils::get_input_value('_cc', rcube_utils::INPUT_POST, TRUE, $message_charset), true);
$mailbcc = rcmail_email_input_format(rcube_utils::get_input_value('_bcc', rcube_utils::INPUT_POST, TRUE, $message_charset), true);

if ($EMAIL_FORMAT_ERROR && !$savedraft) {
    $OUTPUT->show_message('emailformaterror', 'error', array('email' => $EMAIL_FORMAT_ERROR));
    $OUTPUT->send('iframe');
}

if (empty($mailto) && !empty($mailcc)) {
    $mailto = $mailcc;
    $mailcc = null;
}
else if (empty($mailto)) {
    $mailto = 'undisclosed-recipients:;';
}

// Get sender name and address...
$from = rcube_utils::get_input_value('_from', rcube_utils::INPUT_POST, true, $message_charset);
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

if (!$from_string && $from) {
    $from_string = $from;
}

// compose headers array
$headers = array();

// if configured, the Received headers goes to top, for good measure
if ($RCMAIL->config->get('http_received_header')) {
    $nldlm   = "\r\n\t";
    $encrypt = $RCMAIL->config->get('http_received_header_encrypt');

    // FROM/VIA
    $http_header = 'from ';

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $hosts    = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'], 2);
        $hostname = gethostbyaddr($hosts[0]);

        if ($encrypt) {
            $http_header .= rcmail_encrypt_header($hostname);
            if ($host != $hostname)
                $http_header .= ' ('. rcmail_encrypt_header($host) . ')';
        }
        else {
            $http_header .= (($host != $hostname) ? $hostname : '[' . $host . ']');
            if ($host != $hostname)
                $http_header .= ' (['. $host .'])';
        }
        $http_header .= $nldlm . ' via ';
    }

    $host = $_SERVER['REMOTE_ADDR'];
    $hostname = gethostbyaddr($host);

    if ($encrypt) {
        $http_header .= rcmail_encrypt_header($hostname);
        if ($host != $hostname)
            $http_header .= ' ('. rcmail_encrypt_header($host) . ')';
    }
    else {
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

$headers['Date'] = $RCMAIL->user_date();
$headers['From'] = rcube_charset::convert($from_string, RCUBE_CHARSET, $message_charset);
$headers['To'] = $mailto;

// additional recipients
if (!empty($mailcc)) {
    $headers['Cc'] = $mailcc;
}
if (!empty($mailbcc)) {
    $headers['Bcc'] = $mailbcc;
}

if (($max_recipients = (int) $RCMAIL->config->get('max_recipients')) > 0) {
    if ($RECIPIENT_COUNT > $max_recipients) {
        $OUTPUT->show_message('toomanyrecipients', 'error', array('max' => $max_recipients));
        $OUTPUT->send('iframe');
    }
}

// add subject
$headers['Subject'] = trim(rcube_utils::get_input_value('_subject', rcube_utils::INPUT_POST, TRUE, $message_charset));

if (!empty($identity_arr['organization'])) {
    $headers['Organization'] = $identity_arr['organization'];
}
if ($hdr = rcube_utils::get_input_value('_replyto', rcube_utils::INPUT_POST, TRUE, $message_charset)) {
    $headers['Reply-To'] = rcmail_email_input_format($hdr);
}
if (!empty($headers['Reply-To'])) {
    $headers['Mail-Reply-To'] = $headers['Reply-To'];
}
if ($hdr = rcube_utils::get_input_value('_followupto', rcube_utils::INPUT_POST, TRUE, $message_charset)) {
    $headers['Mail-Followup-To'] = rcmail_email_input_format($hdr);
}

// remember reply/forward UIDs in special headers
if (!empty($COMPOSE['reply_uid']) && $savedraft) {
    $headers['X-Draft-Info'] = array('type' => 'reply', 'uid' => $COMPOSE['reply_uid']);
}
else if (!empty($COMPOSE['forward_uid']) && $savedraft) {
    $headers['X-Draft-Info'] = array('type' => 'forward', 'uid' => rcube_imap_generic::compressMessageSet($COMPOSE['forward_uid']));
}

if (!empty($COMPOSE['reply_msgid'])) {
    $headers['In-Reply-To'] = $COMPOSE['reply_msgid'];
}
if (!empty($COMPOSE['references'])) {
    $headers['References'] = $COMPOSE['references'];
}

if (!empty($_POST['_priority'])) {
    $priority     = intval($_POST['_priority']);
    $a_priorities = array(1 => 'highest', 2 => 'high', 4 => 'low', 5 => 'lowest');

    if ($str_priority = $a_priorities[$priority]) {
        $headers['X-Priority'] = sprintf("%d (%s)", $priority, ucfirst($str_priority));
    }
}

if (!empty($_POST['_receipt'])) {
    $headers['Return-Receipt-To']           = $from_string;
    $headers['Disposition-Notification-To'] = $from_string;
}

// additional headers
$headers['Message-ID'] = $message_id;
$headers['X-Sender']   = $from;

if (is_array($headers['X-Draft-Info'])) {
    $headers['X-Draft-Info'] = rcmail_draftinfo_encode($headers['X-Draft-Info'] + array('folder' => $COMPOSE['mailbox']));
}
if ($hdr = $RCMAIL->config->get('useragent')) {
    $headers['User-Agent'] = $hdr;
}

// exec hook for header checking and manipulation
// Depracated: use message_before_send hook instead
$data = $RCMAIL->plugins->exec_hook('message_outgoing_headers', array('headers' => $headers));

// sending aborted by plugin
if ($data['abort'] && !$savedraft) {
    $OUTPUT->show_message($data['message'] ? $data['message'] : 'sendingfailed');
    $OUTPUT->send('iframe');
}
else {
    $headers = $data['headers'];
}

$isHtml = (bool) rcube_utils::get_input_value('_is_html', rcube_utils::INPUT_POST);

// fetch message body
$message_body = rcube_utils::get_input_value('_message', rcube_utils::INPUT_POST, TRUE, $message_charset);

if ($isHtml) {
    $bstyle = array();

    if ($font_size = $RCMAIL->config->get('default_font_size')) {
        $bstyle[] = 'font-size: ' . $font_size;
    }
    if ($font_family = $RCMAIL->config->get('default_font')) {
        $bstyle[] = 'font-family: ' . rcmail::font_defs($font_family);
    }

    // append doctype and html/body wrappers
    $message_body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">'
        . "\r\n<html><body" . (!empty($bstyle) ? " style='" . implode($bstyle, '; ') . "'" : '') . ">\r\n"
        . $message_body;
}

if (!$savedraft) {
    if ($isHtml) {
        // remove signature's div ID
        $message_body = preg_replace('/\s*id="_rc_sig"/', '', $message_body);

        // add inline css for blockquotes
        $bstyle       = 'padding-left:5px; border-left:#1010ff 2px solid; margin-left:5px';
        $message_body = preg_replace('/<blockquote>/',
            '<blockquote type="cite" style="'.$bstyle.'">', $message_body);
    }

    // Check spelling before send
    if ($RCMAIL->config->get('spellcheck_before_send') && $RCMAIL->config->get('enable_spellcheck')
        && empty($COMPOSE['spell_checked']) && !empty($message_body)
    ) {
        $message_body = str_replace("\r\n", "\n", $message_body);
        $spellchecker = new rcube_spellchecker(rcube_utils::get_input_value('_lang', rcube_utils::INPUT_GPC));
        $spell_result = $spellchecker->check($message_body, $isHtml);

        $COMPOSE['spell_checked'] = true;

        if (!$spell_result) {
            $result = $isHtml ? $spellchecker->get_words() : $spellchecker->get_xml();

            $OUTPUT->show_message('mispellingsfound', 'error');
            $OUTPUT->command('spellcheck_resume', $isHtml, $result);
            $OUTPUT->send('iframe');
        }
    }

    // generic footer for all messages
    if ($footer = rcmail_generic_message_footer($isHtml)) {
        $footer = rcube_charset::convert($footer, RCUBE_CHARSET, $message_charset);
        $message_body .= "\r\n" . $footer;
    }
}

if ($isHtml) {
    $message_body .= "\r\n</body></html>\r\n";
}

// sort attachments to make sure the order is the same as in the UI (#1488423)
if ($files = rcube_utils::get_input_value('_attachments', rcube_utils::INPUT_POST)) {
    $files = explode(',', $files);
    $files = array_flip($files);
    foreach ($files as $idx => $val) {
        $files[$idx] = $COMPOSE['attachments'][$idx];
        unset($COMPOSE['attachments'][$idx]);
    }

    $COMPOSE['attachments'] = array_merge(array_filter($files), $COMPOSE['attachments']);
}

// set line length for body wrapping
$LINE_LENGTH = $RCMAIL->config->get('line_length', 72);

// Since we can handle big messages with disk usage, we need more time to work
@set_time_limit(0);

// create PEAR::Mail_mime instance
$MAIL_MIME = new Mail_mime("\r\n");

// Check if we have enough memory to handle the message in it
// It's faster than using files, so we'll do this if we only can
if (is_array($COMPOSE['attachments']) && $RCMAIL->config->get('smtp_server')
  && ($mem_limit = parse_bytes(ini_get('memory_limit')))
) {
    $memory = 0;
    foreach ($COMPOSE['attachments'] as $id => $attachment) {
        $memory += $attachment['size'];
    }

    // Yeah, Net_SMTP needs up to 12x more memory, 1.33 is for base64
    if (!rcube_utils::mem_check($memory * 1.33 * 12)) {
        $MAIL_MIME->setParam('delay_file_io', true);
    }
}

// For HTML-formatted messages, construct the MIME message with both
// the HTML part and the plain-text part
if ($isHtml) {
    $plugin = $RCMAIL->plugins->exec_hook('message_outgoing_body',
        array('body' => $message_body, 'type' => 'html', 'message' => $MAIL_MIME));

    $MAIL_MIME->setHTMLBody($plugin['body']);

    // replace emoticons
    $plugin['body'] = $RCMAIL->replace_emoticons($plugin['body']);

    // add a plain text version of the e-mail as an alternative part.
    $h2t = new rcube_html2text($plugin['body'], false, true, 0, $message_charset);
    $plainTextPart = rcube_mime::wordwrap($h2t->get_text(), $LINE_LENGTH, "\r\n", false, $message_charset);
    $plainTextPart = wordwrap($plainTextPart, 998, "\r\n", true);

    // make sure all line endings are CRLF (#1486712)
    $plainTextPart = preg_replace('/\r?\n/', "\r\n", $plainTextPart);

    $plugin = $RCMAIL->plugins->exec_hook('message_outgoing_body',
        array('body' => $plainTextPart, 'type' => 'alternative', 'message' => $MAIL_MIME));

    $MAIL_MIME->setTXTBody($plugin['body']);

    // look for "emoticon" images from TinyMCE and change their src paths to
    // be file paths on the server instead of URL paths.
    rcmail_fix_emoticon_paths($MAIL_MIME);

    // Extract image Data URIs into message attachments (#1488502)
    rcmail_extract_inline_images($MAIL_MIME, $from);
}
else {
    $plugin = $RCMAIL->plugins->exec_hook('message_outgoing_body',
        array('body' => $message_body, 'type' => 'plain', 'message' => $MAIL_MIME));

    $message_body = $plugin['body'];

    // compose format=flowed content if enabled
    if ($flowed = ($savedraft || $RCMAIL->config->get('send_format_flowed', true)))
        $message_body = rcube_mime::format_flowed($message_body, min($LINE_LENGTH+2, 79), $message_charset);
    else
        $message_body = rcube_mime::wordwrap($message_body, $LINE_LENGTH, "\r\n", false, $message_charset);

    $message_body = wordwrap($message_body, 998, "\r\n", true);

    $MAIL_MIME->setTXTBody($message_body, false, true);
}

// add stored attachments, if any
if (is_array($COMPOSE['attachments'])) {
    foreach ($COMPOSE['attachments'] as $id => $attachment) {
        // This hook retrieves the attachment contents from the file storage backend
        $attachment = $RCMAIL->plugins->exec_hook('attachment_get', $attachment);

        if ($isHtml) {
            $dispurl      = '/\ssrc\s*=\s*[\'"]*\S+display-attachment\S+file=rcmfile'
                . preg_quote($attachment['id']) . '[\s\'"]*/';
            $message_body = $MAIL_MIME->getHTMLBody();
            $is_inline    = preg_match($dispurl, $message_body);
        }
        else {
            $is_inline = false;
        }

        // inline image
        if ($is_inline) {
            // Mail_Mime does not support many inline attachments with the same name (#1489406)
            // we'll generate cid: urls here to workaround this
            $cid = preg_replace('/[^0-9a-zA-Z]/', '', uniqid(time(), true));
            if (preg_match('#(@[0-9a-zA-Z\-\.]+)#', $from, $matches)) {
                $cid .= $matches[1];
            }
            else {
                $cid .= '@localhost';
            }

            $message_body = preg_replace($dispurl, ' src="cid:' . $cid . '" ', $message_body);

            $MAIL_MIME->setHTMLBody($message_body);

            if ($attachment['data'])
                $MAIL_MIME->addHTMLImage($attachment['data'], $attachment['mimetype'], $attachment['name'], false, $cid);
            else
                $MAIL_MIME->addHTMLImage($attachment['path'], $attachment['mimetype'], $attachment['name'], true, $cid);
        }
        else {
            $ctype   = str_replace('image/pjpeg', 'image/jpeg', $attachment['mimetype']); // #1484914
            $file    = $attachment['data'] ? $attachment['data'] : $attachment['path'];
            $folding = (int) $RCMAIL->config->get('mime_param_folding');

            $MAIL_MIME->addAttachment($file,
                $ctype,
                $attachment['name'],
                $attachment['data'] ? false : true,
                $ctype == 'message/rfc822' ? '8bit' : 'base64',
                'attachment',
                '', '', '',
                $folding ? 'quoted-printable' : NULL,
                $folding == 2 ? 'quoted-printable' : NULL,
                '', RCUBE_CHARSET
            );
        }
    }
}

// choose transfer encoding for plain/text body
if (preg_match('/[^\x00-\x7F]/', $MAIL_MIME->getTXTBody())) {
    $text_charset      = $message_charset;
    $transfer_encoding = $RCMAIL->config->get('force_7bit') ? 'quoted-printable' : '8bit';
}
else {
    $text_charset      = 'US-ASCII';
    $transfer_encoding = '7bit';
}

if ($flowed) {
    $text_charset .= ";\r\n format=flowed";
}

// encoding settings for mail composing
$MAIL_MIME->setParam('text_encoding', $transfer_encoding);
$MAIL_MIME->setParam('html_encoding', 'quoted-printable');
$MAIL_MIME->setParam('head_encoding', 'quoted-printable');
$MAIL_MIME->setParam('head_charset', $message_charset);
$MAIL_MIME->setParam('html_charset', $message_charset);
$MAIL_MIME->setParam('text_charset', $text_charset);

// encoding subject header with mb_encode provides better results with asian characters
if (function_exists('mb_encode_mimeheader')) {
    mb_internal_encoding($message_charset);
    $headers['Subject'] = mb_encode_mimeheader($headers['Subject'],
        $message_charset, 'Q', "\r\n", 8);
    mb_internal_encoding(RCUBE_CHARSET);
}

// pass headers to message object
$MAIL_MIME->headers($headers);

// Begin SMTP Delivery Block
if (!$savedraft) {
    // check 'From' address (identity may be incomplete)
    if (empty($from)) {
        $OUTPUT->show_message('nofromaddress', 'error');
        $OUTPUT->send('iframe');
    }

    // Handle Delivery Status Notification request
    if (!empty($_POST['_dsn'])) {
        $smtp_opts['dsn'] = true;
    }

    $sent = $RCMAIL->deliver_message($MAIL_MIME, $from, $mailto,
        $smtp_error, $mailbody_file, $smtp_opts);

    // return to compose page if sending failed
    if (!$sent) {
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
    if ($sendmail_delay) {
        $RCMAIL->user->save_prefs(array('last_message_time' => time()));
    }

    // set replied/forwarded flag
    if ($COMPOSE['reply_uid'])
        $RCMAIL->storage->set_flag($COMPOSE['reply_uid'], 'ANSWERED', $COMPOSE['mailbox']);
    else if ($COMPOSE['forward_uid'])
        $RCMAIL->storage->set_flag($COMPOSE['forward_uid'], 'FORWARDED', $COMPOSE['mailbox']);
}

// Determine which folder to save message
if ($savedraft) {
    $store_target = $drafts_mbox;
}
else if (!$RCMAIL->config->get('no_save_sent_messages')) {
    $store_target = rcube_utils::get_input_value('_store_target', rcube_utils::INPUT_POST);
    if (!strlen($store_target)) {
        $store_target = $RCMAIL->config->get('sent_mbox');
    }
}

if ($store_target) {
    // check if folder is subscribed
    if ($RCMAIL->storage->folder_exists($store_target, true)) {
        $store_folder = true;
    }
    // folder may be existing but not subscribed (#1485241)
    else if (!$RCMAIL->storage->folder_exists($store_target)) {
        $store_folder = $RCMAIL->storage->create_folder($store_target, true);
    }
    else if ($RCMAIL->storage->subscribe($store_target)) {
        $store_folder = true;
    }

    // append message to sent box
    if ($store_folder) {
        // message body in file
        if ($mailbody_file || $MAIL_MIME->getParam('delay_file_io')) {
            $headers = $MAIL_MIME->txtHeaders();

            // file already created
            if ($mailbody_file) {
                $msg = $mailbody_file;
            }
            else {
                $temp_dir      = $RCMAIL->config->get('temp_dir');
                $mailbody_file = tempnam($temp_dir, 'rcmMsg');

                if (!PEAR::isError($msg = $MAIL_MIME->saveMessageBody($mailbody_file))) {
                    $msg = $mailbody_file;
                }
            }
        }
        else {
            $msg     = $MAIL_MIME->getMessage();
            $headers = '';
        }

        if (PEAR::isError($msg)) {
            rcube::raise_error(array('code' => 650, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Could not create message: ".$msg->getMessage()),
                true, false);
        }
        else {
            $saved = $RCMAIL->storage->save_message($store_target, $msg, $headers,
                $mailbody_file ? true : false, array('SEEN'));
        }

        if ($mailbody_file) {
            unlink($mailbody_file);
            $mailbody_file = null;
        }
    }

    // raise error if saving failed
    if (!$saved) {
        rcube::raise_error(array('code' => 800, 'type' => 'imap',
            'file' => __FILE__, 'line' => __LINE__,
            'message' => "Could not save message in $store_target"), true, false);

        if ($savedraft) {
            $OUTPUT->show_message('errorsaving', 'error');
            // start the auto-save timer again
            $OUTPUT->command('auto_save_start');
            $OUTPUT->send('iframe');
        }
    }

    // delete previous saved draft
    if ($saved && ($old_id = rcube_utils::get_input_value('_draft_saveid', rcube_utils::INPUT_POST))) {
        $deleted = $RCMAIL->storage->delete_message($old_id, $drafts_mbox);

        // raise error if deletion of old draft failed
        if (!$deleted) {
            rcube::raise_error(array('code' => 800, 'type' => 'imap',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Could not delete message from $drafts_mbox"), true, false);
        }
    }
}
// remove temp file
else if ($mailbody_file) {
    unlink($mailbody_file);
}


if ($savedraft) {
    // remember new draft-uid ($saved could be an UID or true/false here)
    if ($saved && is_bool($saved)) {
        $index = $RCMAIL->storage->search_once($drafts_mbox, 'HEADER Message-ID ' . $message_id);
        $saved = @max($index->get());
    }

    if ($saved) {
        $plugin = $RCMAIL->plugins->exec_hook('message_draftsaved',
            array('msgid' => $message_id, 'uid' => $saved, 'folder' => $store_target));

        // display success
        $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'messagesaved', 'confirmation');

        // update "_draft_saveid" and the "cmp_hash" to prevent "Unsaved changes" warning
        $COMPOSE['param']['draft_uid'] = $plugin['uid'];
        $OUTPUT->command('set_draft_id', $plugin['uid']);
        $OUTPUT->command('compose_field_hash', true);
    }

    // start the auto-save timer again
    $OUTPUT->command('auto_save_start');
}
else {
    $folders = array();

    if ($COMPOSE['mode'] == 'reply' || $COMPOSE['mode'] == 'forward') {
        $folders[] = $COMPOSE['mailbox'];
    }

    rcmail_compose_cleanup($COMPOSE_ID);
    $OUTPUT->command('remove_compose_data', $COMPOSE_ID);

    if ($store_folder && !$saved) {
        $OUTPUT->command('sent_successfully', 'error', $RCMAIL->gettext('errorsavingsent'), $folders);
    }
    else if ($store_folder) {
        $folders[] = $store_target;
    }

    $OUTPUT->command('sent_successfully', 'confirmation', $RCMAIL->gettext('messagesent'), $folders);
}

$OUTPUT->send('iframe');


/****** message sending functions ********/

// encrypt parts of the header
function rcmail_encrypt_header($what)
{
    global $RCMAIL;

    if (!$RCMAIL->config->get('http_received_header_encrypt')) {
        return $what;
    }

    return $RCMAIL->encrypt($what);
}

// get identity record
function rcmail_get_identity($id)
{
    global $RCMAIL, $message_charset;

    if ($sql_arr = $RCMAIL->user->get_identity($id)) {
        $out = $sql_arr;

        if ($message_charset != RCUBE_CHARSET) {
            foreach ($out as $k => $v) {
                $out[$k] = rcube_charset::convert($v, RCUBE_CHARSET, $message_charset);
            }
        }

        $out['mailto'] = $sql_arr['email'];
        $out['string'] = format_email_recipient($sql_arr['email'], $sql_arr['name']);

        return $out;
    }

    return false;
}

/**
 * go from this:
 * <img src="http[s]://.../tiny_mce/plugins/emotions/images/smiley-cool.gif" border="0" alt="Cool" title="Cool" />
 *
 * to this:
 *
 * <img src="/path/on/server/.../tiny_mce/plugins/emotions/images/smiley-cool.gif" border="0" alt="Cool" title="Cool" />
 */
function rcmail_fix_emoticon_paths($mime_message)
{
    global $RCMAIL;

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
                $img_file   = INSTALL_PATH . '/' . $searchstr . $image_name;

                if (! in_array($image_name, $included_images)) {
                    // add the image to the MIME message
                    if (!$mime_message->addHTMLImage($img_file, 'image/gif', '', true, $image_name)) {
                        $RCMAIL->output->show_message("emoticonerror", 'error');
                    }

                    array_push($included_images, $image_name);
                }

                $body    = substr_replace($body, $img_file, $m[1] + $offset, strlen($m[0]));
                $offset += strlen($img_file) - strlen($m[0]);
            }
        }
    }

    $mime_message->setHTMLBody($body);
}

/**
 * Extract image attachments from HTML content (data URIs)
 */
function rcmail_extract_inline_images($mime_message, $from)
{
    $body   = $mime_message->getHTMLBody();
    $offset = 0;
    $list   = array();
    $domain = 'localhost';
    $regexp = '#img[^>]+src=[\'"](data:([^;]*);base64,([a-z0-9+/=\r\n]+))([\'"])#i';

    if (preg_match_all($regexp, $body, $matches, PREG_OFFSET_CAPTURE)) {
        // get domain for the Content-ID, must be the same as in Mail_Mime::get()
        if (preg_match('#@([0-9a-zA-Z\-\.]+)#', $from, $m)) {
            $domain = $m[1];
        }

        foreach ($matches[1] as $idx => $m) {
            $data = preg_replace('/\r\n/', '', $matches[3][$idx][0]);
            $data = base64_decode($data);

            if (empty($data)) {
                continue;
            }

            $hash      = md5($data) . '@' . $domain;
            $mime_type = $matches[2][$idx][0];
            $name      = $list[$hash];

            if (empty($mime_type)) {
                $mime_type = rcube_mime::image_content_type($data);
            }

            // add the image to the MIME message
            if (!$name) {
                $ext         = preg_replace('#^[^/]+/#', '', $mime_type);
                $name        = substr($hash, 0, 8) . '.' . $ext;
                $list[$hash] = $name;

                $mime_message->addHTMLImage($data, $mime_type, $name, false, $hash);
            }

            $body = substr_replace($body, $name, $m[1] + $offset, strlen($m[0]));
            $offset += strlen($name) - strlen($m[0]);
        }
    }

    $mime_message->setHTMLBody($body);
}

/**
 * Parse and cleanup email address input (and count addresses)
 *
 * @param string  Address input
 * @param boolean Do count recipients (saved in global $RECIPIENT_COUNT)
 * @param boolean Validate addresses (errors saved in global $EMAIL_FORMAT_ERROR)
 * @return string Canonical recipients string separated by comma
 */
function rcmail_email_input_format($mailto, $count=false, $check=true)
{
    global $RCMAIL, $EMAIL_FORMAT_ERROR, $RECIPIENT_COUNT;

    // simplified email regexp, supporting quoted local part
    $email_regexp = '(\S+|("[^"]+"))@\S+';

    $delim   = trim($RCMAIL->config->get('recipients_separator', ','));
    $regexp  = array("/[,;$delim]\s*[\r\n]+/", '/[\r\n]+/', "/[,;$delim]\s*\$/m", '/;/', '/(\S{1})(<'.$email_regexp.'>)/U');
    $replace = array($delim.' ', ', ', '', $delim, '\\1 \\2');

    // replace new lines and strip ending ', ', make address input more valid
    $mailto = trim(preg_replace($regexp, $replace, $mailto));
    $items  = rcube_utils::explode_quoted_string($delim, $mailto);
    $result = array();

    foreach ($items as $item) {
        $item = trim($item);
        // address in brackets without name (do nothing)
        if (preg_match('/^<'.$email_regexp.'>$/', $item)) {
            $item     = rcube_utils::idn_to_ascii(trim($item, '<>'));
            $result[] = $item;
        }
        // address without brackets and without name (add brackets)
        else if (preg_match('/^'.$email_regexp.'$/', $item)) {
            $item     = rcube_utils::idn_to_ascii($item);
            $result[] = $item;
        }
        // address with name (handle name)
        else if (preg_match('/<*'.$email_regexp.'>*$/', $item, $matches)) {
            $address = $matches[0];
            $name    = trim(str_replace($address, '', $item));
            if ($name[0] == '"' && $name[count($name)-1] == '"') {
                $name = substr($name, 1, -1);
            }
            $name     = stripcslashes($name);
            $address  = rcube_utils::idn_to_ascii(trim($address, '<>'));
            $result[] = format_email_recipient($address, $name);
            $item     = $address;
        }
        else if (trim($item)) {
            continue;
        }

        // check address format
        $item = trim($item, '<>');
        if ($item && $check && !rcube_utils::check_email($item)) {
            $EMAIL_FORMAT_ERROR = $item;
            return;
        }
    }

    if ($count) {
        $RECIPIENT_COUNT += count($result);
    }

    return implode(', ', $result);
}


function rcmail_generic_message_footer($isHtml)
{
    global $RCMAIL;

    if ($isHtml && ($file = $RCMAIL->config->get('generic_message_footer_html'))) {
        $html_footer = true;
    }
    else {
        $file = $RCMAIL->config->get('generic_message_footer');
        $html_footer = false;
    }

    if ($file && realpath($file)) {
        // sanity check
        if (!preg_match('/\.(php|ini|conf)$/', $file) && strpos($file, '/etc/') === false) {
            $footer = file_get_contents($file);
            if ($isHtml && !$html_footer) {
                $footer = '<pre>' . $footer . '</pre>';
            }
            return $footer;
        }
    }

    return false;
}

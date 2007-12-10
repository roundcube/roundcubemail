<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/sendmdn.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2007, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send a message disposition notification for a specific mail         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: sendmail.inc 930 2007-11-25 17:34:19Z thomasb $

*/

require_once('lib/rc_mail_mime.inc');


if (!empty($_POST['_uid']))
 {
  $MESSAGE = array('UID' => get_input_value('_uid', RCUBE_INPUT_POST));
  $MESSAGE['headers'] = $IMAP->get_headers($MESSAGE['UID']);
  $MESSAGE['subject'] = rcube_imap::decode_mime_string($MESSAGE['headers']->subject, $MESSAGE['headers']->charset);
  
  if ($MESSAGE['headers']->mdn_to && !$MESSAGE['headers']->mdn_sent)
  {
    $identity = $USER->get_identity();
    $sender = format_email_recipient($identity['email'], $identity['name']);
    $recipient = array_shift($IMAP->decode_address_list($MESSAGE['headers']->mdn_to));
    $mailto = $recipient['mailto'];

    $COMPOSE = new rc_mail_mime(rcmail_header_delm());
    $COMPOSE->setParam(array(
      'text_encoding' => 'quoted-printable',
      'html_encoding' => 'quoted-printable',
      'head_encoding' => 'quoted-printable',
      'head_charset'  => RCMAIL_CHARSET,
      'html_charset'  => RCMAIL_CHARSET,
      'text_charset'  => RCMAIL_CHARSET,
    ));
    
    // compose headers array
    $headers = array(
      'Date' => date('r'),
      'From' => $sender,
      'To'   => $MESSAGE['headers']->mdn_to,
      'Subject' => rcube_label('receiptread') . ': ' . $MESSAGE['subject'],
      'Message-ID' => sprintf('<%s@%s>', md5(uniqid('rcmail'.rand(),true)), rcmail_mail_domain($_SESSION['imap_host'])),
      'X-Sender' => $identity['email'],
      'Content-Type' => 'multipart/report; report-type=disposition-notification',
    );
    
    if (!empty($CONFIG['useragent']))
      $headers['User-Agent'] = $CONFIG['useragent'];

    $body = rcube_label("yourmessage") . "\r\n\r\n" .
      "\t" . rcube_label("to") . ': ' . rcube_imap::decode_mime_string($MESSAGE['headers']->to, $MESSAGE['headers']->charset) . "\r\n" .
      "\t" . rcube_label("subject") . ': ' . $MESSAGE['subject'] . "\r\n" .
      "\t" . rcube_label("sent") . ': ' . format_date(strtotime($MESSAGE['headers']->date), $CONFIG['date_long']) . "\r\n" .
      "\r\n" . rcube_label("receiptnote") . "\r\n";
    
    $report = "Reporting-UA: RoundCube Webmail (Version ".RCMAIL_VERSION.")\r\n";
    
    if ($MESSAGE['headers']->to)
        $report .= "Original-Recipient: {$MESSAGE['headers']->to}\r\n";
    
    $report .= "Final-Recipient: rfc822; {$identity['email']}\r\n" .
               "Original-Message-ID: {$MESSAGE['headers']->messageID}\r\n" .
               "Disposition: manual-action/MDN-sent-manually; displayed\r\n";
    
    $COMPOSE->headers($headers, true);
    $COMPOSE->setTXTBody($body);
    $COMPOSE->addAttachment($report, 'message/disposition-notification', 'MDNPart2.txt', false, '7bit', 'inline');

    $sent = rcmail_deliver_message($COMPOSE, $identity['email'], $mailto);

    if ($sent)
    {
      $IMAP->set_flag($MESSAGE['UID'], 'MDNSENT');
      $OUTPUT->set_env('mdn_request', false);
      $OUTPUT->show_message('receiptsent', 'confirmation');
      $OUTPUT->send();
    }
  }
}

// Error if arrive here
$OUTPUT->show_message('errorsendingreceipt', 'error');
$OUTPUT->send();

?>
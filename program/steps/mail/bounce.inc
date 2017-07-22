<?php

/**
 +-----------------------------------------------------------------------+
 | program/steps/mail/bounce.inc                                         |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2017, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Bounce/resend an email message                                      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

$msg_uid    = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_GP);
$msg_folder = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_GP, true);
$MESSAGE    = new rcube_message($msg_uid, $msg_folder);

if (!$MESSAGE->headers) {
    $OUTPUT->show_message('messageopenerror', 'error');
    $OUTPUT->send('iframe');
}

// Display Bounce form
if (empty($_POST)) {
    if (!empty($MESSAGE->headers->charset)) {
        $RCMAIL->storage->set_charset($MESSAGE->headers->charset);
    }

    // Initialize helper class to build the UI
    $SENDMAIL = new rcmail_sendmail(
        array('mode' => rcmail_sendmail::MODE_FORWARD),
        array('message' => $MESSAGE)
    );

    $OUTPUT->set_env('mailbox', $msg_folder);
    $OUTPUT->set_env('uid', $msg_uid);

    $OUTPUT->send('bounce');
}

// Initialize helper class to send the message
$SENDMAIL = new rcmail_sendmail(array('mode' => rcmail_sendmail::MODE_FORWARD), array(
        'sendmail'      => true,
        'error_handler' => function() use ($OUTPUT) {
            call_user_func_array(array($OUTPUT, 'show_message'), func_get_args());
            $OUTPUT->send('iframe');
        }
));

// Handle the form input
$input_headers = $SENDMAIL->headers_input();

// Set Resent-* headers, these will be added on top of the bounced message
$headers = array_filter(array(
//        'Received'          => $input_headers['Received'],
        'Resent-From'       => $input_headers['From'],
        'Resent-To'         => $input_headers['To'],
        'Resent-Cc'         => $input_headers['Cc'],
        'Resent-Bcc'        => $input_headers['Bcc'],
        'Resent-Date'       => $input_headers['Date'],
        'Resent-Message-ID' => $input_headers['Message-ID'],
));

// Create the bounce message
$BOUNCE = new rcmail_resend_mail(array(
        'bounce_message' => $MESSAGE,
        'bounce_headers' => $headers,
));

// Send the bounce message
$SENDMAIL->deliver_message($BOUNCE);

// Save in Sent (if requested)
$saved = $SENDMAIL->save_message($BOUNCE);

if (!$saved && strlen($SENDMAIL->options['store_target'])) {
    $RCMAIL->display_server_error('errorsaving');
}

$OUTPUT->show_message('messagesent', 'confirmation', null, false);

$OUTPUT->send('iframe');

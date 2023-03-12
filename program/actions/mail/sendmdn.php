<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send a message disposition notification for a specific mail         |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_sendmdn extends rcmail_action
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        if ($uid = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_POST)) {
            $sent = self::send_mdn($uid, $smtp_error);
        }

        // show either confirm or error message
        if (!empty($sent)) {
            $rcmail->output->set_env('mdn_request', false);
            $rcmail->output->show_message('receiptsent', 'confirmation');
        }
        else if (!empty($smtp_error) && is_string($smtp_error)) {
            $rcmail->output->show_message($smtp_error, 'error');
        }
        else if (!empty($smtp_error) && !empty($smtp_error['label'])) {
            $rcmail->output->show_message($smtp_error['label'], 'error', $smtp_error['vars']);
        }
        else {
            $rcmail->output->show_message('errorsendingreceipt', 'error');
        }

        // Redirect to 'addcontact' action to save the sender address
        if (!empty($_POST['_save'])) {
            if ($_POST['_save'] == 5) {
                $_POST['_source'] = rcube_addressbook::TYPE_TRUSTED_SENDER;
            }

            $rcmail->action = 'addcontact';
            return;
        }

        $rcmail->output->send();
    }

    /**
     * Send the MDN response
     *
     * @param mixed        $message    Original message object (rcube_message) or UID
     * @param array|string $smtp_error SMTP error array or (deprecated) string
     *
     * @return boolean Send status
     */
    public static function send_mdn($message, &$smtp_error)
    {
        $rcmail = rcmail::get_instance();

        if (!is_object($message) || !is_a($message, 'rcube_message')) {
            $message = new rcube_message($message);
        }

        if ($message->headers->mdn_to && empty($message->headers->flags['MDNSENT']) &&
            ($rcmail->storage->check_permflag('MDNSENT') || $rcmail->storage->check_permflag('*'))
        ) {
            $charset   = $message->headers->charset;
            $identity  = rcmail_sendmail::identity_select($message);
            $sender    = format_email_recipient($identity['email'], $identity['name']);
            $recipient = array_first(rcube_mime::decode_address_list($message->headers->mdn_to, 1, true, $charset));
            $mailto    = $recipient['mailto'];

            $compose = new Mail_mime("\r\n");

            $compose->setParam('text_encoding', 'quoted-printable');
            $compose->setParam('html_encoding', 'quoted-printable');
            $compose->setParam('head_encoding', 'quoted-printable');
            $compose->setParam('head_charset', RCUBE_CHARSET);
            $compose->setParam('html_charset', RCUBE_CHARSET);
            $compose->setParam('text_charset', RCUBE_CHARSET);

            // compose headers array
            $headers = [
                'Date'       => $rcmail->user_date(),
                'From'       => $sender,
                'To'         => $message->headers->mdn_to,
                'Subject'    => $rcmail->gettext('receiptread') . ': ' . $message->subject,
                'Message-ID' => $rcmail->gen_message_id($identity['email']),
                'X-Sender'   => $identity['email'],
                'References' => trim($message->headers->references . ' ' . $message->headers->messageID),
                'In-Reply-To' => $message->headers->messageID,
            ];

            $report = "Final-Recipient: rfc822; {$identity['email']}\r\n"
                . "Original-Message-ID: {$message->headers->messageID}\r\n"
                . "Disposition: manual-action/MDN-sent-manually; displayed\r\n";

            if ($message->headers->to) {
                $report .= "Original-Recipient: {$message->headers->to}\r\n";
            }

            if ($agent = $rcmail->config->get('useragent')) {
                $headers['User-Agent'] = $agent;
                $report .= "Reporting-UA: $agent\r\n";
            }

            $to   = rcube_mime::decode_mime_string($message->headers->to, $charset);
            $date = $rcmail->format_date($message->headers->date, $rcmail->config->get('date_long'));
            $body = $rcmail->gettext("yourmessage") . "\r\n\r\n" .
                "\t" . $rcmail->gettext("to") . ": {$to}\r\n" .
                "\t" . $rcmail->gettext("subject") . ": {$message->subject}\r\n" .
                "\t" . $rcmail->gettext("date") . ": {$date}\r\n" .
                "\r\n" . $rcmail->gettext("receiptnote");

            $compose->headers(array_filter($headers));
            $compose->setContentType('multipart/report', ['report-type'=> 'disposition-notification']);
            $compose->setTXTBody(rcube_mime::wordwrap($body, 75, "\r\n"));
            $compose->addAttachment($report, 'message/disposition-notification', 'MDNPart2.txt', false, '7bit', 'inline');

            // SMTP options
            $options = ['mdn_use_from' => (bool) $rcmail->config->get('mdn_use_from')];

            $sent = $rcmail->deliver_message($compose, $identity['email'], $mailto, $smtp_error, $body_file, $options, true);

            if ($sent) {
                $rcmail->storage->set_flag($message->uid, 'MDNSENT');
                return true;
            }
        }

        return false;
    }
}

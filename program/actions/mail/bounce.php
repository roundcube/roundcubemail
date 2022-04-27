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
 |   Bounce/resend an email message                                      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_bounce extends rcmail_action
{
    protected static $MESSAGE;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail     = rcmail::get_instance();
        $msg_uid    = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GP);
        $msg_folder = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_GP, true);
        $MESSAGE    = new rcube_message($msg_uid, $msg_folder);

        self::$MESSAGE = $MESSAGE;

        if (!$MESSAGE->headers) {
            $rcmail->output->show_message('messageopenerror', 'error');
            $rcmail->output->send('iframe');
        }

        // Display Bounce form
        if (empty($_POST)) {
            if (!empty($MESSAGE->headers->charset)) {
                $rcmail->storage->set_charset($MESSAGE->headers->charset);
            }

            // Initialize helper class to build the UI
            $SENDMAIL = new rcmail_sendmail(
                ['mode' => rcmail_sendmail::MODE_FORWARD],
                ['message' => $MESSAGE]
            );

            $rcmail->output->set_env('mailbox', $msg_folder);
            $rcmail->output->set_env('uid', $msg_uid);
            $rcmail->output->add_handler('bounceobjects', [$this, 'bounce_objects']);
            $rcmail->output->send('bounce');
        }

        // Initialize helper class to send the message
        $SENDMAIL = new rcmail_sendmail(
            ['mode' => rcmail_sendmail::MODE_FORWARD],
            [
                'sendmail'      => true,
                'error_handler' => function(...$args) use ($rcmail) {
                    call_user_func_array([$rcmail->output, 'show_message'], $args);
                    $rcmail->output->send('iframe');
                }
            ]
        );

        // Handle the form input
        $input_headers = $SENDMAIL->headers_input();

        // Set Resent-* headers, these will be added on top of the bounced message
        $headers = [];
        foreach (['From', 'To', 'Cc', 'Bcc', 'Date', 'Message-ID'] as $name) {
            if (!empty($input_headers[$name])) {
                $headers['Resent-' . $name] = $input_headers[$name];
            }
        }

        // Create the bounce message
        $BOUNCE = new rcmail_resend_mail([
                'bounce_message' => $MESSAGE,
                'bounce_headers' => $headers,
        ]);

        // Send the bounce message
        $SENDMAIL->deliver_message($BOUNCE);

        // Save in Sent (if requested)
        $saved = $SENDMAIL->save_message($BOUNCE);

        if (!$saved && strlen($SENDMAIL->options['store_target'])) {
            self::display_server_error('errorsaving');
        }

        $rcmail->output->show_message('messagesent', 'confirmation', null, false);
        $rcmail->output->send('iframe');
    }

    /**
     * Handler for template object 'bounceObjects'
     *
     * @param array $attrib HTML attributes
     *
     * @return string HTML content
     */
    public static function bounce_objects($attrib)
    {
        if (empty($attrib['id'])) {
            $attrib['id'] = 'bounce-objects';
        }

        $rcmail  = rcmail::get_instance();
        $content = [];

        // Always display a hint about the bounce feature behavior
        $msg        = html::span(null, rcube::Q($rcmail->gettext('bouncehint')));
        $msg_attrib = ['id' => 'bounce-hint', 'class' => 'boxinformation'];
        $content[]  = html::div($msg_attrib, $msg);

        // Add a warning about Bcc recipients
        if (self::$MESSAGE->headers->get('bcc', false) || self::$MESSAGE->headers->get('resent-bcc', false)) {
            $msg        = html::span(null, rcube::Q($rcmail->gettext('bccemail')));
            $msg_attrib = ['id' => 'bcc-warning', 'class' => 'boxwarning'];
            $content[]  = html::div($msg_attrib, $msg);
        }

        $plugin = $rcmail->plugins->exec_hook('bounce_objects',
            ['content' => $content, 'message' => self::$MESSAGE]);

        $content = implode("\n", $plugin['content']);

        return $content ? html::div($attrib, $content) : '';
    }
}

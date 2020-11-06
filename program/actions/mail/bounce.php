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
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail     = rcmail::get_instance();
        $msg_uids   = rcube_utils::get_input_value('_uids', rcube_utils::INPUT_GP);
        $msg_folder = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_GP, true);
        $MESSAGES   = [];

        foreach(explode(',', $msg_uids) as $uid) {
            array_push($MESSAGES, new rcube_message($uid, $msg_folder));
        }

        if (count($MESSAGES) == 0 || !$MESSAGES[0]->headers) {
            $rcmail->output->show_message('messageopenerror', 'error');
            $rcmail->output->send('iframe');
        }

        // Display Bounce form
        if (empty($_POST)) {
            if (!empty($MESSAGES[0]->headers->charset)) {
                $rcmail->storage->set_charset($MESSAGES[0]->headers->charset);
            }

            // Initialize helper class to build the UI
            $SENDMAIL = new rcmail_sendmail(
                [
                    'mode'  => rcmail_sendmail::MODE_FORWARD,
                    'param' => ['sent_mbox' => $rcmail->config->get('bounce_save_mbox')]
                ],
                ['message' => $MESSAGES[0]]
            );

            $rcmail->output->set_env('mailbox', $msg_folder);
            $rcmail->output->set_env('uids', $msg_uids);

            $rcmail->output->send('bounce');
        }

        // Initialize helper class to send the message
        $SENDMAIL = new rcmail_sendmail(
            ['mode' => rcmail_sendmail::MODE_FORWARD],
            [
                'sendmail'      => true,
                'error_handler' => function() use ($rcmail) {
                    call_user_func_array([$rcmail->output, 'show_message'], func_get_args());
                    $rcmail->output->send('iframe');
                }
            ]
        );

        // Handle the form input
        $input_headers = $SENDMAIL->headers_input();

        // Set Resent-* headers, these will be added on top of the bounced message
        $headers = array_filter([
        //        'Received'          => $input_headers['Received'],
                'Resent-From'       => $input_headers['From'],
                'Resent-To'         => $input_headers['To'],
                'Resent-Cc'         => $input_headers['Cc'],
                'Resent-Bcc'        => $input_headers['Bcc'],
                'Resent-Date'       => $input_headers['Date'],
        ]);

        $save_error = false;

        foreach ($MESSAGES as $MESSAGE) {
            $headers['Resent-Message-ID'] = $rcmail->gen_message_id($headers['Resent-From']);

            // Create the bounce message
            $BOUNCE = new rcmail_resend_mail([
                    'bounce_message' => $MESSAGE,
                    'bounce_headers' => $headers,
            ]);

            // Send the bounce message
            //
            // Do not disconnect from the server after sending, otherwise any
            // following bounced messages will fail to be sent.
            $SENDMAIL->deliver_message($BOUNCE, false);

            // Save in Sent (if requested)
            $saved = $SENDMAIL->save_message($BOUNCE);

            if (!$saved && strlen($SENDMAIL->options['store_target'])) {
                self::display_server_error('errorsaving');
                $save_error = true;
            }
        }

        if (!$save_error) {
            $rcmail->user->save_prefs(['bounce_save_mbox' => $SENDMAIL->options['store_target']]);
        }

        $rcmail->output->show_message('messagesent', 'confirmation', null, false);
        $rcmail->output->send('iframe');
    }
}

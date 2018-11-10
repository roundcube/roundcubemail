<?php

/**
 * Email learn driver
 *
 * @version 3.0
 *
 * @author Philip Weir
 *
 * Copyright (C) 2009-2017 Philip Weir
 *
 * This driver is part of the MarkASJunk plugin for Roundcube.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Roundcube. If not, see https://www.gnu.org/licenses/.
 */
class markasjunk_email_learn
{
    private $rcube;

    public function spam($uids, $src_mbox, $dst_mbox)
    {
        $this->_do_emaillearn($uids, true);
    }

    public function ham($uids, $src_mbox, $dst_mbox)
    {
        $this->_do_emaillearn($uids, false);
    }

    private function _do_emaillearn($uids, $spam)
    {
        $this->rcube  = rcube::get_instance();
        $identity_arr = $this->rcube->user->get_identity();
        $from         = $identity_arr['email'];
        $from_string  = format_email_recipient($identity_arr['email'], $identity_arr['name']);
        $attach       = $this->rcube->config->get('markasjunk_email_attach', false);
        $temp_dir     = unslashify($this->rcube->config->get('temp_dir'));

        $mailto = $this->rcube->config->get($spam ? 'markasjunk_email_spam' : 'markasjunk_email_ham');
        $mailto = $this->_parse_vars($mailto, $spam, $from);

        // no address to send to, exit
        if (!$mailto) {
            return;
        }

        $subject = $this->rcube->config->get('markasjunk_email_subject');
        $subject = $this->_parse_vars($subject, $spam, $from);

        foreach ($uids as $uid) {
            $MESSAGE = new rcube_message($uid);
            $message_file = null;

            // set message charset as default
            if (!empty($MESSAGE->headers->charset)) {
                $this->rcube->storage->set_charset($MESSAGE->headers->charset);
            }

            $OUTPUT   = $this->rcube->output;
            $SENDMAIL = new rcmail_sendmail(null, array(
                    'sendmail' => true,
                    'from' => $from,
                    'mailto' => $mailto,
                    'dsn_enabled' => false,
                    'charset' => 'UTF-8',
                    'error_handler' => function() use ($OUTPUT) {
                        call_user_func_array(array($OUTPUT, 'show_message'), func_get_args());
                        $OUTPUT->send();
                    }
                ));

            if ($attach) {
                $headers = array(
                    'Date' => $this->rcube->user_date(),
                    'From' => $from_string,
                    'To' => $mailto,
                    'Subject' => $subject,
                    'User-Agent' => $this->rcube->config->get('useragent'),
                    'Message-ID' => $this->rcube->gen_message_id($from),
                    'X-Sender' => $from
                );

                $message_text = ($spam ? 'Spam' : 'Ham') . ' report from ' . $this->rcube->config->get('product_name');

                // create attachment
                $orig_subject = $MESSAGE->get_header('subject');
                $disp_name    = (!empty($orig_subject) ? $orig_subject : 'message_rfc822') . '.eml';
                $message_file = tempnam($temp_dir, 'rcm');
                $attachment   = array();

                if ($fp = fopen($message_file, 'w')) {
                    $this->rcube->storage->get_raw_body($uid, $fp);
                    fclose($fp);

                    $attachment = array(
                        'name'     => $disp_name,
                        'mimetype' => 'message/rfc822',
                        'path'     => $message_file,
                        'size'     => filesize($message_file),
                        'charset'  => $MESSAGE->headers->charset
                    );
                }

                // create message
                $MAIL_MIME = $SENDMAIL->create_message($headers, $message_text, false, array($attachment));

                if (count($attachment) > 0) { // sanity check incase creating the attachment failed
                    $folding = (int) $this->rcube->config->get('mime_param_folding');

                    $MAIL_MIME->addAttachment($attachment['path'],
                        $attachment['mimetype'], $attachment['name'], true,
                        '8bit', 'attachment', $attachment['charset'], '', '',
                        $folding ? 'quoted-printable' : null,
                        $folding == 2 ? 'quoted-printable' : null,
                        '', RCUBE_CHARSET
                    );
                }
            }
            else {
                $headers = array(
                    'Resent-From'       => $from_string,
                    'Resent-To'         => $mailto,
                    'Resent-Date'       => $this->rcube->user_date(),
                    'Resent-Message-ID' => $this->rcube->gen_message_id($from)
                );

                // create the bounce message
                $MAIL_MIME = new rcmail_resend_mail(array(
                    'bounce_message' => $MESSAGE,
                    'bounce_headers' => $headers,
                ));
            }

            $SENDMAIL->deliver_message($MAIL_MIME);
            $message_file = $message_file ?: $MAIL_MIME->mailbody_file;

            // clean up
            if ($message_file) {
                unlink($message_file);
            }

            if ($this->rcube->config->get('markasjunk_debug')) {
                rcube::write_log('', $uid . ($spam ? ' SPAM ' : ' HAM ') . $mailto . ' (' . $subject . ')');

                if ($smtp_error['vars']) {
                    rcube::write_log('', $smtp_error['vars']);
                }
            }
        }
    }

    private function _parse_vars($data, $spam, $from)
    {
        $data = str_replace('%u', $_SESSION['username'], $data);
        $data = str_replace('%t', $spam ? 'spam' : 'ham', $data);
        $data = str_replace('%l', $this->rcube->user->get_username('local'), $data);
        $data = str_replace('%d', $this->rcube->user->get_username('domain'), $data);
        $data = str_replace('%i', $from, $data);

        return $data;
    }
}

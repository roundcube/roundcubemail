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
 |   Display a mail message similar as a usual mail application does     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_viewsource extends rcmail_action
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        if (!empty($_GET['_save'])) {
            $rcmail->request_security_check(rcube_utils::INPUT_GET);
        }

        ob_end_clean();

        // similar code as in program/steps/mail/get.inc
        if ($uid = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GET)) {
            if ($pos = strpos($uid, '.')) {
                $message = new rcube_message($uid);
                $headers = $message->headers;
                $part_id = substr($uid, $pos + 1);
            }
            else {
                $headers = $rcmail->storage->get_message_headers($uid);
            }

            $charset = $headers->charset ?: $rcmail->config->get('default_charset');

            if (!empty($_GET['_save'])) {
                $subject  = rcube_mime::decode_header($headers->subject, $headers->charset);
                $filename = self::filename_from_subject(mb_substr($subject, 0, 128));
                $filename = ($filename ?: $uid)  . '.eml';

                $rcmail->output->download_headers($filename, [
                        'length'       => $headers->size,
                        'type'         => 'text/plain',
                        'type_charset' => $charset,
                ]);
            }
            else {
                header("Content-Type: text/plain; charset={$charset}");
            }

            if (isset($part_id) && isset($message)) {
                $message->get_part_body($part_id, empty($_GET['_save']), 0, -1);
            }
            else {
                $rcmail->storage->print_raw_body($uid, empty($_GET['_save']));
            }
        }
        else {
            rcube::raise_error([
                    'code'    => 500,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => "Message UID $uid not found"
                ],
                true, true
            );
        }

        exit;
    }

    /**
     * Helper function to convert message subject into filename
     */
    public static function filename_from_subject($str)
    {
        $str = preg_replace('/[:\t\n\r\0\x0B\/]+\s*/', ' ', $str);

        return trim($str, " \t\n\r\0\x0B./_");
    }
}

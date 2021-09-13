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
 |   Fetch message headers in raw format for display                     |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_headers extends rcmail_action_mail_index
{
    protected static $source;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();
        $uid    = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GP);
        $inline = $rcmail->output instanceof rcmail_output_html;

        if (!$uid) {
            exit;
        }

        if ($pos = strpos($uid, '.')) {
            $message = new rcube_message($uid);
            $source  = $message->get_part_body(substr($uid, $pos + 1));
            $source  = substr($source, 0, strpos($source, "\r\n\r\n"));
        }
        else {
            $source = $rcmail->storage->get_raw_headers($uid);
        }

        if ($source !== false) {
            $source = trim(rcube_charset::clean($source));
            $source = htmlspecialchars($source, ENT_COMPAT | ENT_HTML401, RCUBE_CHARSET);
            $source = preg_replace(
                [
                    '/\n[\t\s]+/',
                    '/^([a-z0-9_:-]+)/im',
                    '/\r?\n/'
                ],
                [
                    "\n&nbsp;&nbsp;&nbsp;&nbsp;",
                    '<font class="bold">\1</font>',
                    '<br />'
                ],
                $source
            );

            self::$source = $source;

            $rcmail->output->add_handlers(['dialogcontent' => [$this, 'headers_output']]);

            if ($inline) {
                $rcmail->output->set_env('dialog_class', 'text-nowrap');
            }
            else {
                $rcmail->output->command('set_headers', $source);
            }
        }
        else if (!$inline) {
            $rcmail->output->show_message('messageopenerror', 'error');
        }

        $rcmail->output->send($inline ? 'dialog' : null);
    }

    public static function headers_output()
    {
        return self::$source;
    }
}

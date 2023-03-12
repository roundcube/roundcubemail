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
 |   Convert plain text to HTML                                          |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_utils_text2html extends rcmail_action
{
    public static $source = 'php://input';

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $text = file_get_contents(self::$source);

        $converter = new rcube_text2html($text, false, ['wrap' => true]);

        $rcmail = rcmail::get_instance();

        $html = $converter->get_html();

        $rcmail->output->sendExit($html, ['Content-Type: text/html; charset=' . RCUBE_CHARSET]);
    }
}

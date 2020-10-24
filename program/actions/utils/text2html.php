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
    // only process ajax requests
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $text = stream_get_contents(fopen('php://input', 'r'));

        $converter = new rcube_text2html($text, false, ['wrap' => true]);

        header('Content-Type: text/html; charset=' . RCUBE_CHARSET);
        print $converter->get_html();
        exit;
    }
}

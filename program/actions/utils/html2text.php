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
 |   Convert HTML message to plain text                                  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_utils_html2text extends rcmail_action
{
    public static $source = 'php://input';

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $html = file_get_contents(self::$source);

        $params['links'] = (bool) rcube_utils::get_input_value('_do_links', rcube_utils::INPUT_GET);
        $params['width'] = (int) rcube_utils::get_input_value('_width', rcube_utils::INPUT_GET);

        $rcmail = rcmail::get_instance();
        $text   = $rcmail->html2text($html, $params);

        $rcmail->output->sendExit($text, ['Content-Type: text/plain; charset=' . RCUBE_CHARSET]);
    }
}

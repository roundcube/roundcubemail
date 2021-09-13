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
 |   Displaying uploaded images                                          |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_upload_display extends rcmail_action
{
    protected static $mode = self::MODE_HTTP;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $from = rcube_utils::get_input_string('_from', rcube_utils::INPUT_GET);
        $type = preg_replace('/(add|edit)-/', '', $from);

        // Plugins in Settings may use this file for some uploads (#5694)
        // Make sure it does not contain a dot, which is a special character
        // when using rcube_session::append() below
        $type = str_replace('.', '-', $type);

        $id = 'undefined';

        if (preg_match('/^rcmfile(\w+)$/', $_GET['_file'], $regs)) {
            $id = $regs[1];
        }

        self::display_uploaded_file($_SESSION[$type]['files'][$id]);

        exit;
    }
}

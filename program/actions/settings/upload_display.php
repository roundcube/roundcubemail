<?php

/*
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
    #[Override]
    public function run($args = [])
    {
        if (!empty($_GET['_file']) && preg_match('/^rcmfile(\w+)$/', $_GET['_file'], $regs)) {
            $id = $regs[1];
        } else {
            exit;
        }

        $file = rcmail::get_instance()->get_uploaded_file($id);

        self::display_uploaded_file($file);

        exit;
    }
}

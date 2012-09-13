<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/headers.inc                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Fetch message headers in raw format for display                     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

if ($uid = get_input_value('_uid', RCUBE_INPUT_POST))
{
    $source = $RCMAIL->storage->get_raw_headers($uid);

    if ($source !== false) {
        $source = trim(rcube_charset::clean($source));
        $source = htmlspecialchars($source);
        $source = preg_replace(
            array(
                '/\n[\t\s]+/',
                '/^([a-z0-9_:-]+)/im',
                '/\r?\n/'
            ),
            array(
                "\n&nbsp;&nbsp;&nbsp;&nbsp;",
                '<font class="bold">\1</font>',
                '<br />'
            ), $source);

        $OUTPUT->command('set_headers', $source);
    }
    else {
        $RCMAIL->output->show_message('messageopenerror', 'error');
    }

    $OUTPUT->send();
}

exit;



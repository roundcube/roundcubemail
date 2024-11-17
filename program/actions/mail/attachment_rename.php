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
 |   Rename attachments in compose form                                  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_attachment_rename extends rcmail_action_mail_attachment_upload
{
    // only process ajax requests
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    #[Override]
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        self::init();

        $filename = rcube_utils::get_input_string('_name', rcube_utils::INPUT_POST);
        $filename = trim($filename);

        if (strlen($filename) && $rcmail->update_uploaded_file(self::$file_id, ['name' => $filename])) {
            $rcmail->output->command('rename_attachment_handler', 'rcmfile' . self::$file_id, $filename);
        }

        $rcmail->output->send();
    }
}

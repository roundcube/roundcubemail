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
 |   Delete attachments from compose form                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_attachment_delete extends rcmail_action_mail_attachment_upload
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
        self::init();

        $rcmail     = rcmail::get_instance();
        $attachment = self::get_attachment();

        if (is_array($attachment)) {
            $attachment = $rcmail->plugins->exec_hook('attachment_delete', $attachment);

            if (!empty($attachment['status'])) {
                $rcmail->session->remove(self::$SESSION_KEY . '.attachments.' . self::$file_id);
                $rcmail->output->command('remove_from_attachment_list', 'rcmfile' . self::$file_id);
            }
        }

        $rcmail->output->send();
    }
}

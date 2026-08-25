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
 |   A rcube_message mock to test w/o IMAP                               |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * rcube_message mock for easier testing (without accessing IMAP)
 */
class MessageMock extends rcube_message
{
    private $part_bodies = [];

    public function __construct($uid, $folder = null, $is_safe = false) // @phpstan-ignore constructor.missingParentCall
    {
        $this->uid = $uid;
        $this->folder = $folder;
        $this->is_safe = $is_safe;
        $this->opt = [
            'get_url' => 'URL',
        ];
    }

    #[\Override]
    public function get_part_body($mime_id, $formatted = false, $max_bytes = 0, $mode = null)
    {
        return $this->part_bodies[$mime_id] ?? null;
    }

    public function set_part_body($mime_id, $body)
    {
        $this->part_bodies[$mime_id] = $body;
    }
}

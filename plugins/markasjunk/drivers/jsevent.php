<?php

/**
 * MarkAsJunk JS events example
 * This is an example of how to interact with the markasjunk JS event
 * markasjunk-update to change the spam/ham options shown for specific
 * folders
 *
 * @version 0.1
 *
 * @author Philip Weir
 *
 * Copyright (C) 2016 Philip Weir
 *
 * This driver is part of the MarkASJunk plugin for Roundcube.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Roundcube. If not, see https://www.gnu.org/licenses/.
 */

class markasjunk_jsevent
{
    private $addition_spam_folders = ['spam2', 'spam3'];
    private $suspicious_folders = ['unknown1', 'unknown2'];

    public function init()
    {
        $rcmail = rcmail::get_instance();

        // only execute this code on page load
        if ($rcmail->output->type != 'html') {
            return;
        }

        $rcmail->output->add_js_call('markasjunk_init', $this->addition_spam_folders, $this->suspicious_folders);
    }

    public function spam(&$uids, $mbox)
    {
        // Treat message as spam...
    }

    public function ham(&$uids, $mbox)
    {
        // Treat message as ham...
    }
}

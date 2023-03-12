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
 |   Updates message page navigation controls                            |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_pagenav extends rcmail_action_mail_index
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();
        $uid    = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GET);
        $index  = $rcmail->storage->index(null, self::sort_column(), self::sort_order());
        $cnt    = $index->count_messages();

        if ($cnt && ($pos = $index->exists($uid, true)) !== false) {
            $prev  = $pos ? $index->get_element($pos-1) : 0;
            $first = $pos ? $index->get_element('FIRST') : 0;
            $next  = $pos < $cnt-1 ? $index->get_element($pos+1) : 0;
            $last  = $pos < $cnt-1 ? $index->get_element('LAST') : 0;
        }
        else {
            // error, this will at least disable page navigation
            $rcmail->output->command('set_rowcount', '');
            $rcmail->output->send();
        }

        // Set UIDs and activate navigation buttons
        if (!empty($prev)) {
            $rcmail->output->set_env('prev_uid', $prev);
            $rcmail->output->command('enable_command', 'previousmessage', 'firstmessage', true);
        }

        if (!empty($next)) {
            $rcmail->output->set_env('next_uid', $next);
            $rcmail->output->command('enable_command', 'nextmessage', 'lastmessage', true);
        }

        if (!empty($first)) {
            $rcmail->output->set_env('first_uid', $first);
        }

        if (!empty($last)) {
            $rcmail->output->set_env('last_uid', $last);
        }

        // Don't need a real messages count value
        $rcmail->output->set_env('messagecount', 1);

        // Set rowcount text
        $rcmail->output->command('set_rowcount', $rcmail->gettext([
                'name' => 'messagenrof',
                'vars' => ['nr'  => ($pos ?? 0) + 1, 'count' => $cnt]
        ]));

        $rcmail->output->send();
    }
}

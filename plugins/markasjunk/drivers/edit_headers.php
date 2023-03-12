<?php

/**
 * Edit headers
 *
 * @version 1.0
 *
 * @author Philip Weir
 *
 * Copyright (C) 2012-2014 Philip Weir
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
class markasjunk_edit_headers
{
    public function spam(&$uids, $src_mbox, $dst_mbox)
    {
        $this->_edit_headers($uids, true, $dst_mbox);
    }

    public function ham(&$uids, $src_mbox, $dst_mbox)
    {
        $this->_edit_headers($uids, false, $dst_mbox);
    }

    private function _edit_headers(&$uids, $spam, $dst_mbox)
    {
        $rcube = rcube::get_instance();
        $args  = $rcube->config->get($spam ? 'markasjunk_spam_patterns' : 'markasjunk_ham_patterns');

        if (empty($args['patterns'])) {
            return;
        }

        $new_uids = [];

        foreach ($uids as $uid) {
            $raw_message = $rcube->storage->get_raw_body($uid);
            $raw_headers = $rcube->storage->get_raw_headers($uid);

            $updated_headers = preg_replace($args['patterns'], $args['replacements'], $raw_headers);
            $raw_message     = str_replace($raw_headers, $updated_headers, $raw_message);

            $saved = $rcube->storage->save_message($dst_mbox, $raw_message);

            if ($saved !== false) {
                $rcube->output->command('markasjunk_move', null, [$uid]);
                array_push($new_uids, $saved);
            }
        }

        if (count($new_uids) > 0) {
            $uids = $new_uids;
        }
    }
}

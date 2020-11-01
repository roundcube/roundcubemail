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
 |   Undelete contacts (CIDs) from last delete action                    |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_undo extends rcmail_action_contacts_index
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
        $undo   = $_SESSION['contact_undo'];
        $delcnt = 0;

        foreach ((array) $undo['data'] as $source => $cid) {
            $contacts = self::contact_source($source);

            $plugin = $rcmail->plugins->exec_hook('contact_undelete', [
                    'id'     => $cid,
                    'source' => $source
            ]);

            $restored = empty($plugin['abort']) ? $contacts->undelete($cid) : $plugin['result'];

            if (!$restored) {
                $error = !empty($plugin['message']) ? $plugin['message'] : 'contactrestoreerror';

                $rcmail->output->show_message($error, 'error');
                $rcmail->output->command('list_contacts');
                $rcmail->output->send();
            }
            else {
                $delcnt += $restored;
            }
        }

        $rcmail->session->remove('contact_undo');

        $rcmail->output->show_message('contactrestored', 'confirmation');
        $rcmail->output->command('list_contacts');

        // send response
        $rcmail->output->send();
    }
}

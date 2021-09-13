<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delete saved search                                                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_search_delete extends rcmail_action
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
        $rcmail = rcmail::get_instance();
        $id     = rcube_utils::get_input_string('_sid', rcube_utils::INPUT_POST);
        $result = false;

        if (!empty($id)) {
            $plugin = $rcmail->plugins->exec_hook('saved_search_delete', ['id' => $id]);

            if (empty($plugin['abort'])) {
                $result = $rcmail->user->delete_search($id);
            }
            else {
                $result = $plugin['result'];
            }
        }

        if ($result) {
            $rcmail->output->show_message('savedsearchdeleted', 'confirmation');
            $rcmail->output->command('remove_search_item', rcube::Q($id));
            // contact list will be cleared, clear also page counter
            $rcmail->output->command('set_rowcount', $rcmail->gettext('nocontactsfound'));
            $rcmail->output->set_env('pagecount', 0);
        }
        else {
            $error = !empty($plugin['message']) ? $plugin['message'] : 'savedsearchdeleteerror';
            $rcmail->output->show_message($error, 'error');
        }

        $rcmail->output->send();
    }
}

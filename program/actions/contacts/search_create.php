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
 |   Create saved search                                                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_search_create extends rcmail_action
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
        $id     = rcube_utils::get_input_value('_search', rcube_utils::INPUT_POST);
        $name   = rcube_utils::get_input_value('_name', rcube_utils::INPUT_POST, true);

        if (
            !empty($_SESSION['contact_search_params'])
            && ($params = $_SESSION['contact_search_params'])
            && $params['id'] == $id
        ) {
            $data = [
                'type' => rcube_user::SEARCH_ADDRESSBOOK,
                'name' => $name,
                'data' => [
                    'fields' => $params['data'][0],
                    'search' => $params['data'][1],
                ],
            ];

            $plugin = $rcmail->plugins->exec_hook('saved_search_create', ['data' => $data]);

            if (empty($plugin['abort'])) {
                $result = $rcmail->user->insert_search($plugin['data']);
            }
            else {
                $result = $plugin['result'];
            }
        }

        if (!empty($result)) {
            $rcmail->output->show_message('savedsearchcreated', 'confirmation');
            $rcmail->output->command('insert_saved_search', rcube::Q($name), rcube::Q($result));
        }
        else {
            $error = !empty($plugin['message']) ? $plugin['message'] : 'savedsearchcreateerror';
            $rcmail->output->show_message($error, 'error');
        }

        $rcmail->output->send();
    }
}

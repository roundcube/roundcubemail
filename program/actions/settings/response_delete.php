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
 |   A handler for canned response deletion                              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_response_delete extends rcmail_action
{
    static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        if ($key = rcube_utils::get_input_value('_key', rcube_utils::INPUT_POST)) {
            $responses = $rcmail->get_compose_responses(false, true);

            foreach ($responses as $i => $response) {
                if (empty($response['key'])) {
                    $response['key'] = substr(md5($response['name']), 0, 16);
                }

                if ($response['key'] == $key) {
                    unset($responses[$i]);
                    $deleted = $rcmail->user->save_prefs(['compose_responses' => $responses]);
                    break;
                }
            }
        }

        if (!empty($deleted)) {
            $rcmail->output->command('display_message', $rcmail->gettext('deletedsuccessfully'), 'confirmation');
            $rcmail->output->command('remove_response', $key);
        }

        $rcmail->output->send();
    }
}

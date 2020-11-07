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
 |   A handler for saving a canned response record                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_response_save extends rcmail_action_settings_response_edit
{
    protected static $mode = self::MODE_HTTP;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        self::set_response();

        if (isset($_POST['_name']) && empty(self::$response['static'])) {
            $name = trim(rcube_utils::get_input_value('_name', rcube_utils::INPUT_POST));
            $text = trim(rcube_utils::get_input_value('_text', rcube_utils::INPUT_POST, true));

            if (!empty($name) && !empty($text)) {
                $dupes = 0;

                foreach (self::$responses as $i => $resp) {
                    if (!empty(self::$response) && self::$response['index'] === $i) {
                        continue;
                    }
                    if (strcasecmp($name, preg_replace('/\s\(\d+\)$/', '', $resp['name'])) == 0) {
                        $dupes++;
                    }
                }

                if ($dupes) {  // require a unique name
                    $name .= ' (' . ++$dupes . ')';
                }

                $response = [
                    'name'   => $name,
                    'text'   => $text,
                    'format' => 'text',
                    'key'    => substr(md5($name), 0, 16)
                ];

                if (!empty(self::$response) && self::$responses[self::$response['index']]) {
                    self::$responses[self::$response['index']] = $response;
                }
                else {
                    self::$responses[] = $response;
                }

                self::$responses = array_filter(self::$responses, function($item) { return empty($item['static']); });

                if ($rcmail->user->save_prefs(['compose_responses' => array_values(self::$responses)])) {
                    $key = !empty(self::$response) ? self::$response['key'] : null;

                    $rcmail->output->show_message('successfullysaved', 'confirmation');
                    $rcmail->output->command('parent.update_response_row', $response, $key);
                    $rcmail->overwrite_action('edit-response');
                    self::$response = $response;
                }
            }
            else {
                $rcmail->output->show_message('formincomplete', 'error');
            }
        }

        // display the form again
        $rcmail->overwrite_action(empty(self::$response) ? 'add-response' : 'edit-response');
    }
}

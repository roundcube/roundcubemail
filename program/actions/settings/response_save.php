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

class rcmail_action_settings_response_save extends rcmail_action_settings_index
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

        $id      = trim(rcube_utils::get_input_string('_id', rcube_utils::INPUT_POST));
        $name    = trim(rcube_utils::get_input_string('_name', rcube_utils::INPUT_POST));
        $text    = trim(rcube_utils::get_input_string('_text', rcube_utils::INPUT_POST, true));
        $is_html = (bool) rcube_utils::get_input_string('_is_html', rcube_utils::INPUT_POST);

        $response = [
            'id'      => $id,
            'name'    => $name,
            'data'    => $text,
            'is_html' => $is_html,
        ];

        if (!empty($text) && $is_html) {
            // replace uploaded images with data URIs
            $text = self::attach_images($text, 'response');
            // XSS protection in HTML signature (#1489251)
            $text = self::wash_html($text);

            $response['data'] = $text;
        }

        if (empty($name) || empty($text)) {
            // TODO: error
            $rcmail->output->show_message('formincomplete', 'error');
            $rcmail->overwrite_action('edit-response', ['post' => $response]);
            return;
        }

        if (!empty($id) && is_numeric($id)) {
            $plugin   = $rcmail->plugins->exec_hook('response_update', ['id' => $id, 'record' => $response]);
            $response = $plugin['record'];

            if (!$plugin['abort']) {
                $updated = $rcmail->user->update_response($id, $response);
            }
            else {
                $updated = $plugin['result'];
            }

            if ($updated) {
                $rcmail->output->show_message('successfullysaved', 'confirmation');
                $rcmail->output->command('parent.update_response_row', $id, rcube::Q($response['name']));
            }
            else {
                // show error message
                $error = !empty($plugin['message']) ? $plugin['message'] : 'errorsaving';
                $rcmail->output->show_message($error, 'error', null, false);
            }
        }
        else {
            $plugin   = $rcmail->plugins->exec_hook('response_create', ['record' => $response]);
            $response = $plugin['record'];

            if (!$plugin['abort']) {
                $insert_id = $rcmail->user->insert_response($response);
            }
            else {
                $insert_id = $plugin['result'];
            }

            if ($insert_id) {
                $rcmail->output->show_message('successfullysaved', 'confirmation');

                $response['id'] = $_GET['_id'] = $insert_id;

                // add a new row to the list
                $rcmail->output->command('parent.update_response_row', $insert_id, rcube::Q($response['name']), true);
            }
            else {
                $error = !empty($plugin['message']) ? $plugin['message'] : 'errorsaving';
                $rcmail->output->show_message($error, 'error', null, false);
                $rcmail->overwrite_action('add-response');
                return;
            }
        }

        // display the form again
        $rcmail->overwrite_action('edit-response', ['post' => $response]);
    }
}

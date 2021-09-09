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
 |   A handler for fetching a canned response content                    |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_response_get extends rcmail_action
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

        $id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GET);

        if ($id && ($response = $rcmail->get_compose_response($id))) {
            $is_html = (bool) rcube_utils::get_input_string('_is_html', rcube_utils::INPUT_GET);

            if ($is_html && empty($response['is_html'])) {
                $converter = new rcube_text2html($response['data'], false, ['wrap' => true]);

                $response['data'] = $converter->get_html();
                $response['is_html'] = true;
            }
            else if (!$is_html && !empty($response['is_html'])) {
                $params = [
                    'width' => $rcmail->config->get('line_length', 72),
                    'links' => false,
                ];

                $response['data'] = $rcmail->html2text($response['data'], $params);
                $response['is_html'] = false;
            }

            $rcmail->output->command('insert_response', $response);
        }

        $rcmail->output->send();
    }
}

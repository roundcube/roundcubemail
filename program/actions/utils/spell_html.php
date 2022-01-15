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
 |   Spellchecker for TinyMCE                                            |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_utils_spell_html extends rcmail_action
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
        $method = rcube_utils::get_input_string('method', rcube_utils::INPUT_POST);
        $lang   = rcube_utils::get_input_string('lang', rcube_utils::INPUT_POST);
        $result = [];

        $spellchecker = new rcube_spellchecker($lang);

        if ($method == 'addToDictionary') {
            $data = rcube_utils::get_input_string('word', rcube_utils::INPUT_POST);

            $spellchecker->add_word($data);
            $result['result'] = true;
        }
        else {
            $data = rcube_utils::get_input_string('text', rcube_utils::INPUT_POST, true);
            $data = html_entity_decode($data, ENT_QUOTES, RCUBE_CHARSET);

            if ($data && !$spellchecker->check($data)) {
                $result['words']      = $spellchecker->get();
                $result['dictionary'] = (bool) $rcmail->config->get('spellcheck_dictionary');
            }
        }

        header("Content-Type: application/json; charset=" . RCUBE_CHARSET);

        if ($error = $spellchecker->error()) {
            rcube::raise_error([
                    'code'    => 500,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => "Spellcheck error: " . $error
                ],
                true,
                false
            );

            echo json_encode(['error' => $rcmail->gettext('internalerror')]);
            exit;
        }

        // send output
        echo json_encode($result);
        exit;
    }
}

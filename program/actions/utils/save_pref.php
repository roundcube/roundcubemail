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
 |   Save preferences setting in database                                |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_utils_save_pref extends rcmail_action
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
        $rcmail   = rcmail::get_instance();
        $name     = rcube_utils::get_input_string('_name', rcube_utils::INPUT_POST);
        $value    = rcube_utils::get_input_string('_value', rcube_utils::INPUT_POST);
        $sessname = rcube_utils::get_input_string('_session', rcube_utils::INPUT_POST);

        // Whitelisted preferences and session variables, others
        // can be added by plugins
        $whitelist = [
            'list_cols',
            'collapsed_folders',
            'collapsed_abooks',
        ];

        $whitelist_sess = [
            'list_attrib/columns',
        ];

        $whitelist      = array_merge($whitelist, $rcmail->plugins->allowed_prefs);
        $whitelist_sess = array_merge($whitelist_sess, $rcmail->plugins->allowed_session_prefs);

        if (!in_array($name, $whitelist) || ($sessname && !in_array($sessname, $whitelist_sess))) {
            rcube::raise_error([
                    'code' => 500,
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'message' => sprintf("Hack attempt detected (user: %s)", $rcmail->get_user_name())
                ],
                true,
                false
            );

            $rcmail->output->reset();
            $rcmail->output->send();
        }

        // save preference value
        $rcmail->user->save_prefs([$name => $value]);

        // update also session if requested
        if ($sessname) {
            // Support multidimensional arrays...
            $vars = explode('/', $sessname);

            // ... up to 3 levels
            if (count($vars) == 1) {
                $_SESSION[$vars[0]] = $value;
            }
            else if (count($vars) == 2) {
                $_SESSION[$vars[0]][$vars[1]] = $value;
            }
            else if (count($vars) == 3) {
                $_SESSION[$vars[0]][$vars[1]][$vars[2]] = $value;
            }
        }

        $rcmail->output->reset();
        $rcmail->output->send();
    }
}

<?php

/*
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
 |   Perform OAuth2 user login                                           |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_login_oauth extends rcmail_action
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    #[Override]
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        $auth_code = rcube_utils::get_input_string('code', rcube_utils::INPUT_GET);
        $auth_error = rcube_utils::get_input_string('error', rcube_utils::INPUT_GET);
        $auth_state = rcube_utils::get_input_string('state', rcube_utils::INPUT_GET);

        // on oauth error
        if (!empty($auth_error)) {
            $error_message = rcube_utils::get_input_string('error_description', rcube_utils::INPUT_GET) ?: $auth_error;
            $rcmail->output->show_message($error_message, 'warning');
            return;
        }

        // auth code return from oauth login
        if (!empty($auth_code)) {
            $auth = $rcmail->oauth->request_access_token($auth_code, $auth_state);
            if (!$auth) {
                $rcmail->output->show_message('oauthloginfailed', 'warning');
                return;
            }

            // next action will be the login
            $args['task'] = 'login';
            $args['action'] = 'login';
            return $args;
        }

        // login action: redirect to `oauth_auth_uri`
        if ($rcmail->task === 'login') {
            // this will always exit() the process
            $rcmail->oauth->login_redirect();
        }
    }
}

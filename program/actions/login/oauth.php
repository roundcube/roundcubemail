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
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        $auth_code  = rcube_utils::get_input_string('code', rcube_utils::INPUT_GET);
        $auth_error = rcube_utils::get_input_string('error', rcube_utils::INPUT_GET);
        $auth_state = rcube_utils::get_input_string('state', rcube_utils::INPUT_GET);

        // auth code return from oauth login
        if (!empty($auth_code)) {
            $auth = $rcmail->oauth->request_access_token($auth_code, $auth_state);

            // oauth success
            if ($auth && isset($auth['username'], $auth['authorization'], $auth['token'])) {
                // enforce XOAUTH2 auth type
                $rcmail->config->set('imap_auth_type', 'XOAUTH2');
                $rcmail->config->set('login_password_maxlen', strlen($auth['authorization']));

                // use access_token and user info for IMAP login
                $storage_host = $rcmail->autoselect_host();
                if ($rcmail->login($auth['username'], $auth['authorization'], $storage_host, true)) {
                    // replicate post-login tasks from index.php
                    $rcmail->session->remove('temp');
                    $rcmail->session->regenerate_id(false);

                    // send auth cookie if necessary
                    $rcmail->session->set_auth_cookie();

                    // save OAuth token in session
                    $_SESSION['oauth_token'] = $auth['token'];

                    // log successful login
                    $rcmail->log_login();

                    // allow plugins to control the redirect url after login success
                    $redir = $rcmail->plugins->exec_hook('login_after', ['_task' => 'mail']);
                    unset($redir['abort'], $redir['_err']);

                    // send redirect
                    header('Location: ' . $rcmail->url($redir, true, false));
                    exit;
                }
                else {
                    $rcmail->output->show_message('loginfailed', 'warning');

                    // log failed login
                    $error_code = $rcmail->login_error();
                    $rcmail->log_login($auth['username'], true, $error_code);

                    $rcmail->plugins->exec_hook('login_failed', [
                            'code' => $error_code,
                            'host' => $storage_host,
                            'user' => $auth['username'],
                    ]);

                    $rcmail->kill_session();
                    // fall through -> login page
                }
            }
            else {
                $rcmail->output->show_message('oauthloginfailed', 'warning');
            }
        }
        // error return from oauth login
        else if (!empty($auth_error)) {
            $error_message = rcube_utils::get_input_string('error_description', rcube_utils::INPUT_GET) ?: $auth_error;
            $rcmail->output->show_message($error_message, 'warning');
        }
        // login action: redirect to `oauth_auth_uri`
        else if ($rcmail->task === 'login') {
            // this will always exit() the process
            $rcmail->oauth->login_redirect();
        }
    }
}

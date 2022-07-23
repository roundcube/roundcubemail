<?php
/**
 +-------------------------------------------------------------------------+
 | Roundcube Webmail IMAP Client                                           |
 | Version 1.6.0                                                           |
 |                                                                         |
 | Copyright (C) The Roundcube Dev Team                                    |
 |                                                                         |
 | This program is free software: you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License (with exceptions   |
 | for skins & plugins) as published by the Free Software Foundation,      |
 | either version 3 of the License, or (at your option) any later version. |
 |                                                                         |
 | This file forms part of the Roundcube Webmail Software for which the    |
 | following exception is added: Plugins and Skins which merely make       |
 | function calls to the Roundcube Webmail Software, and for that purpose  |
 | include it by reference shall not be considered modifications of        |
 | the software.                                                           |
 |                                                                         |
 | If you wish to use this file in another project or create a modified    |
 | version that will not be part of the Roundcube Webmail Software, you    |
 | may remove the exception above and use this source code under the       |
 | original version of the license.                                        |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the            |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License       |
 | along with this program.  If not, see http://www.gnu.org/licenses/.     |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                          |
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

// include environment
require_once 'program/include/iniset.php';

// init application, start session, init output class, etc.
$RCMAIL = rcmail::get_instance(0, isset($GLOBALS['env']) ? $GLOBALS['env'] : null);

// Make the whole PHP output non-cacheable (#1487797)
$RCMAIL->output->nocacheing_headers();
$RCMAIL->output->common_headers(!empty($_SESSION['user_id']));

// turn on output buffering
ob_start();

// check the initial error state
if ($RCMAIL->config->get_error() || $RCMAIL->db->is_error()) {
    rcmail_fatal_error();
}

// error steps
if ($RCMAIL->action == 'error' && !empty($_GET['_code'])) {
    rcmail::raise_error(['code' => hexdec($_GET['_code'])], false, true);
}

// check if https is required (for login) and redirect if necessary
if (empty($_SESSION['user_id']) && ($force_https = $RCMAIL->config->get('force_https', false))) {
    // force_https can be true, <hostname>, <hostname>:<port>, <port>
    if (!is_bool($force_https)) {
        list($host, $port) = explode(':', $force_https);

        if (is_numeric($host) && empty($port)) {
            $port = $host;
            $host = '';
        }
    }

    if (empty($port)) {
        $port = 443;
    }

    if (!rcube_utils::https_check($port)) {
        if (empty($host)) {
            $host = preg_replace('/:[0-9]+$/', '', $_SERVER['HTTP_HOST']);
        }
        if ($port != 443) {
            $host .= ':' . $port;
        }

        header('Location: https://' . $host . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// trigger startup plugin hook
$startup = $RCMAIL->plugins->exec_hook('startup', ['task' => $RCMAIL->task, 'action' => $RCMAIL->action]);
$RCMAIL->set_task($startup['task']);
$RCMAIL->action = $startup['action'];

$session_error = null;

// try to log in
if ($RCMAIL->task == 'login' && $RCMAIL->action == 'login') {
    $request_valid = !empty($_SESSION['temp']) && $RCMAIL->check_request();
    $pass_charset  = $RCMAIL->config->get('password_charset', 'UTF-8');

    // purge the session in case of new login when a session already exists
    if ($request_valid) {
        $RCMAIL->kill_session();
    }

    $auth = $RCMAIL->plugins->exec_hook('authenticate', [
            'host'  => $RCMAIL->autoselect_host(),
            'user'  => trim(rcube_utils::get_input_string('_user', rcube_utils::INPUT_POST)),
            'pass'  => rcube_utils::get_input_string('_pass', rcube_utils::INPUT_POST, true, $pass_charset),
            'valid' => $request_valid,
            'error' => null,
            'cookiecheck' => true,
    ]);

    // Login
    if ($auth['valid'] && !$auth['abort']
        && $RCMAIL->login($auth['user'], $auth['pass'], $auth['host'], $auth['cookiecheck'])
    ) {
        // create new session ID, don't destroy the current session
        // it was destroyed already by $RCMAIL->kill_session() above
        $RCMAIL->session->remove('temp');
        $RCMAIL->session->regenerate_id(false);

        // send auth cookie if necessary
        $RCMAIL->session->set_auth_cookie();

        // log successful login
        $RCMAIL->log_login();

        // restore original request parameters
        $query = [];
        if ($url = rcube_utils::get_input_string('_url', rcube_utils::INPUT_POST)) {
            parse_str($url, $query);

            // prevent endless looping on login page
            if (!empty($query['_task']) && $query['_task'] == 'login') {
                unset($query['_task']);
            }

            // prevent redirect to compose with specified ID (#1488226)
            if (!empty($query['_action']) && $query['_action'] == 'compose' && !empty($query['_id'])) {
                $query = ['_action' => 'compose'];
            }
        }

        // allow plugins to control the redirect url after login success
        $redir = $RCMAIL->plugins->exec_hook('login_after', $query + ['_task' => 'mail']);
        unset($redir['abort'], $redir['_err']);

        // send redirect
        $RCMAIL->output->redirect($redir, 0, true);
    }
    else {
        if (!$auth['valid']) {
            $error_code = rcmail::ERROR_INVALID_REQUEST;
        }
        else {
            $error_code = is_numeric($auth['error']) ? $auth['error'] : $RCMAIL->login_error();
        }

        $error_labels = [
            rcmail::ERROR_STORAGE          => 'storageerror',
            rcmail::ERROR_COOKIES_DISABLED => 'cookiesdisabled',
            rcmail::ERROR_INVALID_REQUEST  => 'invalidrequest',
            rcmail::ERROR_INVALID_HOST     => 'invalidhost',
            rcmail::ERROR_RATE_LIMIT       => 'accountlocked',
        ];

        if (!empty($auth['error']) && !is_numeric($auth['error'])) {
            $error_message = $auth['error'];
        }
        else {
            $error_message = !empty($error_labels[$error_code]) ? $error_labels[$error_code] : 'loginfailed';
        }

        $RCMAIL->output->show_message($error_message, 'warning');

        // log failed login
        $RCMAIL->log_login($auth['user'], true, $error_code);

        $RCMAIL->plugins->exec_hook('login_failed', [
                'code' => $error_code,
                'host' => $auth['host'],
                'user' => $auth['user'],
        ]);

        if (!isset($_SESSION['user_id'])) {
            $RCMAIL->kill_session();
        }
    }
}

// handle oauth login requests
else if ($RCMAIL->task == 'login' && $RCMAIL->action == 'oauth' && $RCMAIL->oauth->is_enabled()) {
    $oauth_handler = new rcmail_action_login_oauth();
    $oauth_handler->run();
}

// end session
else if ($RCMAIL->task == 'logout' && isset($_SESSION['user_id'])) {
    $RCMAIL->request_security_check(rcube_utils::INPUT_GET | rcube_utils::INPUT_POST);

    $userdata = array(
        'user' => $_SESSION['username'],
        'host' => $_SESSION['storage_host'],
        'lang' => $RCMAIL->user->language,
    );

    $RCMAIL->output->show_message('loggedout');

    $RCMAIL->logout_actions();
    $RCMAIL->kill_session();
    $RCMAIL->plugins->exec_hook('logout_after', $userdata);
}

// check session and auth cookie
else if ($RCMAIL->task != 'login' && $_SESSION['user_id']) {
    if (!$RCMAIL->session->check_auth()) {
        $RCMAIL->kill_session();
        $session_error = 'sessionerror';
    }
}

// not logged in -> show login page
if (empty($RCMAIL->user->ID)) {
    if (
        $session_error
        || (!empty($_REQUEST['_err']) && $_REQUEST['_err'] === 'session')
        || ($session_error = $RCMAIL->session_error())
    ) {
        $RCMAIL->output->show_message($session_error ?: 'sessionerror', 'error', null, true, -1);
    }

    if ($RCMAIL->output->ajax_call || $RCMAIL->output->get_env('framed')) {
        $RCMAIL->output->command('session_error', $RCMAIL->url(['_err' => 'session']));
        $RCMAIL->output->send('iframe');
    }

    // check if installer is still active
    if ($RCMAIL->config->get('enable_installer') && is_readable('./installer/index.php')) {
        $RCMAIL->output->add_footer(html::div(['id' => 'login-addon', 'style' => "background:#ef9398; border:2px solid #dc5757; padding:0.5em; margin:2em auto; width:50em"],
            html::tag('h2', array('style' => "margin-top:0.2em"), "Installer script is still accessible") .
            html::p(null, "The install script of your Roundcube installation is still stored in its default location!") .
            html::p(null, "Please <b>remove</b> the whole <tt>installer</tt> folder from the Roundcube directory because
                these files may expose sensitive configuration data like server passwords and encryption keys
                to the public. Make sure you cannot access the <a href=\"./installer/\">installer script</a> from your browser.")
        ));
    }

    $plugin = $RCMAIL->plugins->exec_hook('unauthenticated', [
            'task'      => 'login',
            'error'     => $session_error,
            // Return 401 only on failed logins (#7010)
            'http_code' => empty($session_error) && !empty($error_message) ? 401 : 200
    ]);

    $RCMAIL->set_task($plugin['task']);

    if ($plugin['http_code'] == 401) {
        header('HTTP/1.0 401 Unauthorized');
    }

    $RCMAIL->output->send($plugin['task']);
}
else {
    // CSRF prevention
    $RCMAIL->request_security_check();

    // check access to disabled actions
    $disabled_actions = (array) $RCMAIL->config->get('disabled_actions');
    if (in_array($RCMAIL->task . '.' . ($RCMAIL->action ?: 'index'), $disabled_actions)) {
        rcube::raise_error(['code' => 404, 'message' => "Action disabled"], true, true);
    }
}

$RCMAIL->action_handler();

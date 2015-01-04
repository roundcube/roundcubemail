<?php
/*
 +-------------------------------------------------------------------------+
 | Roundcube Webmail IMAP Client                                           |
 | Version 1.0.4                                                           |
 |                                                                         |
 | Copyright (C) 2005-2014, The Roundcube Dev Team                         |
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
$RCMAIL = rcmail::get_instance($GLOBALS['env']);

// Make the whole PHP output non-cacheable (#1487797)
$RCMAIL->output->nocacheing_headers();

// turn on output buffering
ob_start();

// check if config files had errors
if ($err_str = $RCMAIL->config->get_error()) {
    rcmail::raise_error(array(
        'code' => 601,
        'type' => 'php',
        'message' => $err_str), false, true);
}

// check DB connections and exit on failure
if ($err_str = $RCMAIL->db->is_error()) {
    rcmail::raise_error(array(
        'code' => 603,
        'type' => 'db',
        'message' => $err_str), FALSE, TRUE);
}

// error steps
if ($RCMAIL->action == 'error' && !empty($_GET['_code'])) {
    rcmail::raise_error(array('code' => hexdec($_GET['_code'])), FALSE, TRUE);
}

// check if https is required (for login) and redirect if necessary
if (empty($_SESSION['user_id']) && ($force_https = $RCMAIL->config->get('force_https', false))) {
    $https_port = is_bool($force_https) ? 443 : $force_https;

    if (!rcube_utils::https_check($https_port)) {
        $host  = preg_replace('/:[0-9]+$/', '', $_SERVER['HTTP_HOST']);
        $host .= ($https_port != 443 ? ':' . $https_port : '');

        header('Location: https://' . $host . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// trigger startup plugin hook
$startup = $RCMAIL->plugins->exec_hook('startup', array('task' => $RCMAIL->task, 'action' => $RCMAIL->action));
$RCMAIL->set_task($startup['task']);
$RCMAIL->action = $startup['action'];

// try to log in
if ($RCMAIL->task == 'login' && $RCMAIL->action == 'login') {
    $request_valid = $_SESSION['temp'] && $RCMAIL->check_request(rcube_utils::INPUT_POST, 'login');

    // purge the session in case of new login when a session already exists 
    $RCMAIL->kill_session();

    $auth = $RCMAIL->plugins->exec_hook('authenticate', array(
        'host' => $RCMAIL->autoselect_host(),
        'user' => trim(rcube_utils::get_input_value('_user', rcube_utils::INPUT_POST)),
        'pass' => rcube_utils::get_input_value('_pass', rcube_utils::INPUT_POST, true,
            $RCMAIL->config->get('password_charset', 'ISO-8859-1')),
        'cookiecheck' => true,
        'valid'       => $request_valid,
    ));

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
        $query = array();
        if ($url = rcube_utils::get_input_value('_url', rcube_utils::INPUT_POST)) {
            parse_str($url, $query);

            // prevent endless looping on login page
            if ($query['_task'] == 'login') {
                unset($query['_task']);
            }

            // prevent redirect to compose with specified ID (#1488226)
            if ($query['_action'] == 'compose' && !empty($query['_id'])) {
                $query = array();
            }
        }

        // allow plugins to control the redirect url after login success
        $redir = $RCMAIL->plugins->exec_hook('login_after', $query + array('_task' => 'mail'));
        unset($redir['abort'], $redir['_err']);

        // send redirect
        $OUTPUT->redirect($redir);
    }
    else {
        if (!$auth['valid']) {
            $error_code = RCMAIL::ERROR_INVALID_REQUEST;
        }
        else {
            $error_code = $auth['error'] ? $auth['error'] : $RCMAIL->login_error();
        }

        $error_labels = array(
            RCMAIL::ERROR_STORAGE          => 'storageerror',
            RCMAIL::ERROR_COOKIES_DISABLED => 'cookiesdisabled',
            RCMAIL::ERROR_INVALID_REQUEST  => 'invalidrequest',
            RCMAIL::ERROR_INVALID_HOST     => 'invalidhost',
        );

        $error_message = $error_labels[$error_code] ? $error_labels[$error_code] : 'loginfailed';

        $OUTPUT->show_message($error_message, 'warning');

        // log failed login
        $RCMAIL->log_login($auth['user'], true, $error_code);

        $RCMAIL->plugins->exec_hook('login_failed', array(
            'code' => $error_code, 'host' => $auth['host'], 'user' => $auth['user']));

        $RCMAIL->kill_session();
    }
}

// end session (after optional referer check)
else if ($RCMAIL->task == 'logout' && isset($_SESSION['user_id'])
    && $RCMAIL->check_request(rcube_utils::INPUT_GET)
    && (!$RCMAIL->config->get('referer_check') || rcube_utils::check_referer())
) {
    $userdata = array(
        'user' => $_SESSION['username'],
        'host' => $_SESSION['storage_host'],
        'lang' => $RCMAIL->user->language,
    );

    $OUTPUT->show_message('loggedout');

    $RCMAIL->logout_actions();
    $RCMAIL->kill_session();
    $RCMAIL->plugins->exec_hook('logout_after', $userdata);
}

// check session and auth cookie
else if ($RCMAIL->task != 'login' && $_SESSION['user_id'] && $RCMAIL->action != 'send') {
    if (!$RCMAIL->session->check_auth()) {
        $RCMAIL->kill_session();
        $session_error = true;
    }
}

// not logged in -> show login page
if (empty($RCMAIL->user->ID)) {
    // log session failures
    $task = rcube_utils::get_input_value('_task', rcube_utils::INPUT_GPC);

    if ($task && !in_array($task, array('login','logout'))
        && !$session_error && ($sess_id = $_COOKIE[ini_get('session.name')])
    ) {
        $RCMAIL->session->log("Aborted session $sess_id; no valid session data found");
        $session_error = true;
    }

    if ($session_error || $_REQUEST['_err'] == 'session') {
        $OUTPUT->show_message('sessionerror', 'error', null, true, -1);
    }

    if ($OUTPUT->ajax_call || $OUTPUT->get_env('framed')) {
        $OUTPUT->command('session_error', $RCMAIL->url(array('_err' => 'session')));
        $OUTPUT->send('iframe');
    }

    // check if installer is still active
    if ($RCMAIL->config->get('enable_installer') && is_readable('./installer/index.php')) {
        $OUTPUT->add_footer(html::div(array('style' => "background:#ef9398; border:2px solid #dc5757; padding:0.5em; margin:2em auto; width:50em"),
            html::tag('h2', array('style' => "margin-top:0.2em"), "Installer script is still accessible") .
            html::p(null, "The install script of your Roundcube installation is still stored in its default location!") .
            html::p(null, "Please <b>remove</b> the whole <tt>installer</tt> folder from the Roundcube directory because .
                these files may expose sensitive configuration data like server passwords and encryption keys
                to the public. Make sure you cannot access the <a href=\"./installer/\">installer script</a> from your browser.")
        ));
    }

    $plugin = $RCMAIL->plugins->exec_hook('unauthenticated', array('task' => 'login', 'error' => $session_error));

    $RCMAIL->set_task($plugin['task']);

    $OUTPUT->send($plugin['task']);
}
// CSRF prevention
else {
    // don't check for valid request tokens in these actions
    $request_check_whitelist = array('login'=>1, 'spell'=>1, 'spell_html'=>1);

    if (!$request_check_whitelist[$RCMAIL->action]) {
        // check client X-header to verify request origin
        if ($OUTPUT->ajax_call) {
            if (rcube_utils::request_header('X-Roundcube-Request') != $RCMAIL->get_request_token()) {
                header('HTTP/1.1 403 Forbidden');
                die("Invalid Request");
            }
        }
        // check request token in POST form submissions
        else if (!empty($_POST) && !$RCMAIL->check_request()) {
            $OUTPUT->show_message('invalidrequest', 'error');
            $OUTPUT->send($RCMAIL->task);
        }

        // check referer if configured
        if ($RCMAIL->config->get('referer_check') && !rcube_utils::check_referer()) {
            raise_error(array(
                'code' => 403, 'type' => 'php',
                'message' => "Referer check failed"), true, true);
        }
    }
}

// we're ready, user is authenticated and the request is safe
$plugin = $RCMAIL->plugins->exec_hook('ready', array('task' => $RCMAIL->task, 'action' => $RCMAIL->action));
$RCMAIL->set_task($plugin['task']);
$RCMAIL->action = $plugin['action'];

// handle special actions
if ($RCMAIL->action == 'keep-alive') {
    $OUTPUT->reset();
    $RCMAIL->plugins->exec_hook('keep_alive', array());
    $OUTPUT->send();
}
else if ($RCMAIL->action == 'save-pref') {
    include INSTALL_PATH . 'program/steps/utils/save_pref.inc';
}


// include task specific functions
if (is_file($incfile = INSTALL_PATH . 'program/steps/'.$RCMAIL->task.'/func.inc')) {
    include_once $incfile;
}

// allow 5 "redirects" to another action
$redirects = 0; $incstep = null;
while ($redirects < 5) {
    // execute a plugin action
    if ($RCMAIL->plugins->is_plugin_task($RCMAIL->task)) {
        if (!$RCMAIL->action) $RCMAIL->action = 'index';
        $RCMAIL->plugins->exec_action($RCMAIL->task.'.'.$RCMAIL->action);
        break;
    }
    else if (preg_match('/^plugin\./', $RCMAIL->action)) {
        $RCMAIL->plugins->exec_action($RCMAIL->action);
        break;
    }
    // try to include the step file
    else if (($stepfile = $RCMAIL->get_action_file())
        && is_file($incfile = INSTALL_PATH . 'program/steps/'.$RCMAIL->task.'/'.$stepfile)
    ) {
        // include action file only once (in case it don't exit)
        include_once $incfile;
        $redirects++;
    }
    else {
        break;
    }
}

if ($RCMAIL->action == 'refresh') {
    $RCMAIL->plugins->exec_hook('refresh', array('last' => intval(rcube_utils::get_input_value('_last', rcube_utils::INPUT_GPC))));
}

// parse main template (default)
$OUTPUT->send($RCMAIL->task);

// if we arrive here, something went wrong
rcmail::raise_error(array(
    'code' => 404,
    'type' => 'php',
    'line' => __LINE__,
    'file' => __FILE__,
    'message' => "Invalid request"), true, true);

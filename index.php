<?php
/*
 +-------------------------------------------------------------------------+
 | RoundCube Webmail IMAP Client                                           |
 | Version 0.3-20090814                                                    |
 |                                                                         |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                   |
 |                                                                         |
 | This program is free software; you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License version 2          |
 | as published by the Free Software Foundation.                           |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License along |
 | with this program; if not, write to the Free Software Foundation, Inc., |
 | 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                          |
 +-------------------------------------------------------------------------+

 $Id$

*/

// include environment
require_once 'program/include/iniset.php';

// init application, start session, init output class, etc.
$RCMAIL = rcmail::get_instance();

// turn on output buffering
ob_start();

// check if config files had errors
if ($err_str = $RCMAIL->config->get_error()) {
  raise_error(array(
    'code' => 601,
    'type' => 'php',
    'message' => $err_str), false, true);
}

// check DB connections and exit on failure
if ($err_str = $DB->is_error()) {
  raise_error(array(
    'code' => 603,
    'type' => 'db',
    'message' => $err_str), FALSE, TRUE);
}

// error steps
if ($RCMAIL->action=='error' && !empty($_GET['_code'])) {
  raise_error(array('code' => hexdec($_GET['_code'])), FALSE, TRUE);
}

// check if https is required (for login) and redirect if necessary
if (empty($_SESSION['user_id']) && ($force_https = $RCMAIL->config->get('force_https', false))) {
  $https_port = is_bool($force_https) ? 443 : $force_https;
  if (!rcube_https_check($https_port)) {
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
  // purge the session in case of new login when a session already exists 
  $RCMAIL->kill_session();
  
  $auth = $RCMAIL->plugins->exec_hook('authenticate', array(
    'host' => $RCMAIL->autoselect_host(),
    'user' => trim(get_input_value('_user', RCUBE_INPUT_POST)),
    'cookiecheck' => true,
  ));
  
  if (!isset($auth['pass']))
    $auth['pass'] = get_input_value('_pass', RCUBE_INPUT_POST, true,
        $RCMAIL->config->get('password_charset', 'ISO-8859-1'));

  // check if client supports cookies
  if ($auth['cookiecheck'] && empty($_COOKIE)) {
    $OUTPUT->show_message("cookiesdisabled", 'warning');
  }
  else if ($_SESSION['temp'] && !$auth['abort'] &&
        !empty($auth['host']) && !empty($auth['user']) &&
        $RCMAIL->login($auth['user'], $auth['pass'], $auth['host'])) {
    // create new session ID
    $RCMAIL->session->remove('temp');
    $RCMAIL->session->regenerate_id();

    // send auth cookie if necessary
    $RCMAIL->authenticate_session();

    // log successful login
    rcmail_log_login();

    // restore original request parameters
    $query = array();
    if ($url = get_input_value('_url', RCUBE_INPUT_POST))
      parse_str($url, $query);

    // allow plugins to control the redirect url after login success
    $redir = $RCMAIL->plugins->exec_hook('login_after', $query);
    unset($redir['abort']);

    // send redirect
    $OUTPUT->redirect($redir);
  }
  else {
    $OUTPUT->show_message($IMAP->error_code < -1 ? 'imaperror' : 'loginfailed', 'warning');
    $RCMAIL->plugins->exec_hook('login_failed', array('code' => $IMAP->error_code, 'host' => $auth['host'], 'user' => $auth['user']));
    $RCMAIL->kill_session();
  }
}

// end session
else if ($RCMAIL->task == 'logout' && isset($_SESSION['user_id'])) {
  $userdata = array('user' => $_SESSION['username'], 'host' => $_SESSION['imap_host'], 'lang' => $RCMAIL->user->language);
  $OUTPUT->show_message('loggedout');
  $RCMAIL->logout_actions();
  $RCMAIL->kill_session();
  $RCMAIL->plugins->exec_hook('logout_after', $userdata);
}

// check session and auth cookie
else if ($RCMAIL->task != 'login' && $_SESSION['user_id'] && $RCMAIL->action != 'send') {
  if (!$RCMAIL->authenticate_session()) {
    $OUTPUT->show_message('sessionerror', 'error');
    $RCMAIL->kill_session();
  }
}

// don't check for valid request tokens in these actions
$request_check_whitelist = array('login'=>1, 'spell'=>1);

// check client X-header to verify request origin
if ($OUTPUT->ajax_call) {
  if (!$RCMAIL->config->get('devel_mode') && rc_request_header('X-RoundCube-Request') != $RCMAIL->get_request_token() && !empty($RCMAIL->user->ID)) {
    header('HTTP/1.1 404 Not Found');
    die("Invalid Request");
  }
}
// check request token in POST form submissions
else if (!empty($_POST) && !$request_check_whitelist[$RCMAIL->action] && !$RCMAIL->check_request()) {
  $OUTPUT->show_message('invalidrequest', 'error');
  $OUTPUT->send($RCMAIL->task);
}

// not logged in -> show login page
if (empty($RCMAIL->user->ID)) {
  if ($OUTPUT->ajax_call)
    $OUTPUT->redirect(array(), 2000);

  if (!empty($_REQUEST['_framed']))
    $OUTPUT->command('redirect', '?');

  // check if installer is still active
  if ($RCMAIL->config->get('enable_installer') && is_readable('./installer/index.php')) {
    $OUTPUT->add_footer(html::div(array('style' => "background:#ef9398; border:2px solid #dc5757; padding:0.5em; margin:2em auto; width:50em"),
      html::tag('h2', array('style' => "margin-top:0.2em"), "Installer script is still accessible") .
      html::p(null, "The install script of your RoundCube installation is still stored in its default location!") .
      html::p(null, "Please <b>remove</b> the whole <tt>installer</tt> folder from the RoundCube directory because .
        these files may expose sensitive configuration data like server passwords and encryption keys
        to the public. Make sure you cannot access the <a href=\"./installer/\">installer script</a> from your browser.")
      )
    );
  }
  
  $OUTPUT->set_env('task', 'login');
  $OUTPUT->send('login');
}


// handle keep-alive signal
if ($RCMAIL->action == 'keep-alive') {
  $OUTPUT->reset();
  $OUTPUT->send();
}
// save preference value
else if ($RCMAIL->action == 'save-pref') {
  $RCMAIL->user->save_prefs(array(get_input_value('_name', RCUBE_INPUT_POST) => get_input_value('_value', RCUBE_INPUT_POST)));
  $OUTPUT->reset();
  $OUTPUT->send();
}


// map task/action to a certain include file
$action_map = array(
  'mail' => array(
    'preview' => 'show.inc',
    'print'   => 'show.inc',
    'moveto'  => 'move_del.inc',
    'delete'  => 'move_del.inc',
    'send'    => 'sendmail.inc',
    'expunge' => 'folders.inc',
    'purge'   => 'folders.inc',
    'remove-attachment'  => 'attachments.inc',
    'display-attachment' => 'attachments.inc',
    'upload' => 'attachments.inc',
    'group-expand' => 'autocomplete.inc',
  ),
  
  'addressbook' => array(
    'add' => 'edit.inc',
    'group-create' => 'groups.inc',
    'group-rename' => 'groups.inc',
    'group-delete' => 'groups.inc',
    'group-addmembers' => 'groups.inc',
    'group-delmembers' => 'groups.inc',
  ),
  
  'settings' => array(
    'folders'       => 'manage_folders.inc',
    'create-folder' => 'manage_folders.inc',
    'rename-folder' => 'manage_folders.inc',
    'delete-folder' => 'manage_folders.inc',
    'subscribe'     => 'manage_folders.inc',
    'unsubscribe'   => 'manage_folders.inc',
    'enable-threading'  => 'manage_folders.inc',
    'disable-threading' => 'manage_folders.inc',
    'add-identity'  => 'edit_identity.inc',
  )
);

// include task specific functions
if (is_file($incfile = 'program/steps/'.$RCMAIL->task.'/func.inc'))
  include_once($incfile);

// allow 5 "redirects" to another action
$redirects = 0; $incstep = null;
while ($redirects < 5) {
  $stepfile = !empty($action_map[$RCMAIL->task][$RCMAIL->action]) ?
    $action_map[$RCMAIL->task][$RCMAIL->action] : strtr($RCMAIL->action, '-', '_') . '.inc';

  // execute a plugin action
  if (preg_match('/^plugin\./', $RCMAIL->action)) {
    $RCMAIL->plugins->exec_action($RCMAIL->action);
    break;
  }
  // try to include the step file
  else if (is_file($incfile = 'program/steps/'.$RCMAIL->task.'/'.$stepfile)) {
    include($incfile);
    $redirects++;
  }
  else {
    break;
  }
}


// parse main template (default)
$OUTPUT->send($RCMAIL->task);


// if we arrive here, something went wrong
raise_error(array(
  'code' => 404,
  'type' => 'php',
  'line' => __LINE__,
  'file' => __FILE__,
  'message' => "Invalid request"), true, true);
                      
?>

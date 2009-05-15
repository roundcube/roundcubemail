<?php
/*
 +-------------------------------------------------------------------------+
 | RoundCube Webmail IMAP Client                                           |
 | Version 0.2.2                                                           |
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

// init application and start session with requested task
$RCMAIL = rcmail::get_instance();

// init output class
$OUTPUT = !empty($_REQUEST['_remote']) ? $RCMAIL->init_json() : $RCMAIL->load_gui(!empty($_REQUEST['_framed']));

// set output buffering
if ($RCMAIL->action != 'get' && $RCMAIL->action != 'viewsource') {
  // use gzip compression if supported
  if (function_exists('ob_gzhandler')
      && !ini_get('zlib.output_compression')
      && ini_get('output_handler') != 'ob_gzhandler') {
    ob_start('ob_gzhandler');
  }
  else {
    ob_start();
  }
}

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

// try to log in
if ($RCMAIL->action=='login' && $RCMAIL->task=='mail') {
  // purge the session in case of new login when a session already exists 
  $RCMAIL->kill_session(); 
  
  // set IMAP host
  $host = $RCMAIL->autoselect_host();
  
  // check if client supports cookies
  if (empty($_COOKIE)) {
    $OUTPUT->show_message("cookiesdisabled", 'warning');
  }
  else if ($_SESSION['temp'] && !empty($_POST['_user']) && !empty($_POST['_pass']) &&
           $RCMAIL->login(trim(get_input_value('_user', RCUBE_INPUT_POST), ' '),
              get_input_value('_pass', RCUBE_INPUT_POST, true, 'ISO-8859-1'), $host)) {
    // create new session ID
    unset($_SESSION['temp']);
    rcube_sess_regenerate_id();

    // send auth cookie if necessary
    $RCMAIL->authenticate_session();

    // log successful login
    if ($RCMAIL->config->get('log_logins')) {
      write_log('userlogins', sprintf('Successful login for %s (id %d) from %s',
        $RCMAIL->user->get_username(),
        $RCMAIL->user->ID,
        $_SERVER['REMOTE_ADDR']));
    }

    // send redirect
    $OUTPUT->redirect();
  }
  else {
    $OUTPUT->show_message($IMAP->error_code < -1 ? 'imaperror' : 'loginfailed', 'warning');
    $RCMAIL->kill_session();
  }
}

// end session
else if (($RCMAIL->task=='logout' || $RCMAIL->action=='logout') && isset($_SESSION['user_id'])) {
  $OUTPUT->show_message('loggedout');
  $RCMAIL->logout_actions();
  $RCMAIL->kill_session();
}

// check session and auth cookie
else if ($RCMAIL->action != 'login' && $_SESSION['user_id'] && $RCMAIL->action != 'send') {
  if (!$RCMAIL->authenticate_session()) {
    $OUTPUT->show_message('sessionerror', 'error');
    $RCMAIL->kill_session();
  }
}


// check client X-header to verify request origin
if ($OUTPUT->ajax_call) {
  if (!$RCMAIL->config->get('devel_mode') && !rc_request_header('X-RoundCube-Referer')) {
    header('HTTP/1.1 404 Not Found');
    die("Invalid Request");
  }
}


// not logged in -> show login page
if (empty($RCMAIL->user->ID)) {
  
  if ($OUTPUT->ajax_call)
    $OUTPUT->redirect(array(), 2000);
  
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
  ),
  
  'addressbook' => array(
    'add' => 'edit.inc',
  ),
  
  'settings' => array(
    'folders'       => 'manage_folders.inc',
    'create-folder' => 'manage_folders.inc',
    'rename-folder' => 'manage_folders.inc',
    'delete-folder' => 'manage_folders.inc',
    'subscribe'     => 'manage_folders.inc',
    'unsubscribe'   => 'manage_folders.inc',
    'add-identity'  => 'edit_identity.inc',
  )
);

// include task specific functions
include_once 'program/steps/'.$RCMAIL->task.'/func.inc';

// allow 5 "redirects" to another action
$redirects = 0; $incstep = null;
while ($redirects < 5) {
  $stepfile = !empty($action_map[$RCMAIL->task][$RCMAIL->action]) ?
    $action_map[$RCMAIL->task][$RCMAIL->action] : strtr($RCMAIL->action, '-', '_') . '.inc';
    
  // try to include the step file
  if (is_file(($incfile = 'program/steps/'.$RCMAIL->task.'/'.$stepfile))) {
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

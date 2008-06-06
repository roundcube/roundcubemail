<?php
/*
 +-------------------------------------------------------------------------+
 | RoundCube Webmail IMAP Client                                           |
 | Version 0.1-20080506                                                    |
 |                                                                         |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                   |
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

// define global vars
$OUTPUT_TYPE = 'html';

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


// init application and start session with requested task
$RCMAIL = rcmail::get_instance();

// init output class
$OUTPUT = (!empty($_GET['_remote']) || !empty($_POST['_remote'])) ? $RCMAIL->init_json() : $RCMAIL->load_gui((!empty($_GET['_framed']) || !empty($_POST['_framed'])));


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
  $host = $RCMAIL->autoselect_host();
  
  // check if client supports cookies
  if (empty($_COOKIE)) {
    $OUTPUT->show_message("cookiesdisabled", 'warning');
  }
  else if ($_SESSION['temp'] && !empty($_POST['_user']) && isset($_POST['_pass']) &&
           $RCMAIL->login(trim(get_input_value('_user', RCUBE_INPUT_POST), ' '),
              get_input_value('_pass', RCUBE_INPUT_POST, true, 'ISO-8859-1'), $host)) {
    // create new session ID
    unset($_SESSION['temp']);
    sess_regenerate_id();

    // send auth cookie if necessary
    $RCMAIL->authenticate_session();

    // log successful login
    if ($RCMAIL->config->get('log_logins') && $RCMAIL->config->get('debug_level') & 1)
      console(sprintf('Successful login for %s (id %d) from %s',
                      trim(get_input_value('_user', RCUBE_INPUT_POST), ' '),
                      $_SESSION['user_id'],
                      $_SERVER['REMOTE_ADDR']));

    // send redirect
    header("Location: {$RCMAIL->comm_path}");
    exit;
  }
  else {
    $OUTPUT->show_message($IMAP->error_code == -1 ? 'imaperror' : 'loginfailed', 'warning');
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


// log in to imap server
if (!empty($RCMAIL->user->ID) && $RCMAIL->task == 'mail') {
  if (!$RCMAIL->imap_connect()) {
    $RCMAIL->kill_session();
  }
}


// not logged in -> set task to 'login
if (empty($RCMAIL->user->ID)) {
  if ($OUTPUT->ajax_call)
    $OUTPUT->remote_response("setTimeout(\"location.href='\"+this.env.comm_path+\"'\", 2000);");
  
  $RCMAIL->set_task('login');
}


// check client X-header to verify request origin
if ($OUTPUT->ajax_call) {
  if (empty($CONFIG['devel_mode']) && !rc_request_header('X-RoundCube-Referer')) {
    header('HTTP/1.1 404 Not Found');
    die("Invalid Request");
  }
}


// not logged in -> show login page
if (empty($RCMAIL->user->ID)) {
  // check if installer is still active
  if ($CONFIG['enable_installer'] && is_readable('./installer/index.php')) {
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
  $OUTPUT->task = 'login';
  $OUTPUT->send('login');
  exit;
}


// handle keep-alive signal
if ($RCMAIL->action=='keep-alive') {
  $OUTPUT->reset();
  $OUTPUT->send('');
  exit;
}

// include task specific files
if ($RCMAIL->task=='mail') {
  include_once('program/steps/mail/func.inc');
  
  if ($RCMAIL->action=='show' || $RCMAIL->action=='preview' || $RCMAIL->action=='print')
    include('program/steps/mail/show.inc');

  if ($RCMAIL->action=='get')
    include('program/steps/mail/get.inc');

  if ($RCMAIL->action=='moveto' || $RCMAIL->action=='delete')
    include('program/steps/mail/move_del.inc');

  if ($RCMAIL->action=='mark')
    include('program/steps/mail/mark.inc');

  if ($RCMAIL->action=='viewsource')
    include('program/steps/mail/viewsource.inc');

  if ($RCMAIL->action=='sendmdn')
    include('program/steps/mail/sendmdn.inc');

  if ($RCMAIL->action=='send')
    include('program/steps/mail/sendmail.inc');

  if ($RCMAIL->action=='upload')
    include('program/steps/mail/upload.inc');

  if ($RCMAIL->action=='compose' || $RCMAIL->action=='remove-attachment' || $RCMAIL->action=='display-attachment')
    include('program/steps/mail/compose.inc');

  if ($RCMAIL->action=='addcontact')
    include('program/steps/mail/addcontact.inc');

  if ($RCMAIL->action=='expunge' || $RCMAIL->action=='purge')
    include('program/steps/mail/folders.inc');

  if ($RCMAIL->action=='check-recent')
    include('program/steps/mail/check_recent.inc');

  if ($RCMAIL->action=='getunread')
    include('program/steps/mail/getunread.inc');
    
  if ($RCMAIL->action=='list' && isset($_REQUEST['_remote']))
    include('program/steps/mail/list.inc');

   if ($RCMAIL->action=='search')
     include('program/steps/mail/search.inc');
     
  if ($RCMAIL->action=='spell')
    include('program/steps/mail/spell.inc');

  if ($RCMAIL->action=='rss')
    include('program/steps/mail/rss.inc');
    
  // make sure the message count is refreshed
  $IMAP->messagecount($_SESSION['mbox'], 'ALL', true);
}


// include task specific files
if ($RCMAIL->task=='addressbook') {
  include_once('program/steps/addressbook/func.inc');

  if ($RCMAIL->action=='save')
    include('program/steps/addressbook/save.inc');
  
  if ($RCMAIL->action=='edit' || $RCMAIL->action=='add')
    include('program/steps/addressbook/edit.inc');
  
  if ($RCMAIL->action=='delete')
    include('program/steps/addressbook/delete.inc');

  if ($RCMAIL->action=='show')
    include('program/steps/addressbook/show.inc');  

  if ($RCMAIL->action=='list' && $_REQUEST['_remote'])
    include('program/steps/addressbook/list.inc');

  if ($RCMAIL->action=='search')
    include('program/steps/addressbook/search.inc');

  if ($RCMAIL->action=='copy')
    include('program/steps/addressbook/copy.inc');

  if ($RCMAIL->action=='mailto')
    include('program/steps/addressbook/mailto.inc');
}


// include task specific files
if ($RCMAIL->task=='settings') {
  include_once('program/steps/settings/func.inc');

  if ($RCMAIL->action=='save-identity')
    include('program/steps/settings/save_identity.inc');

  if ($RCMAIL->action=='add-identity' || $RCMAIL->action=='edit-identity')
    include('program/steps/settings/edit_identity.inc');

  if ($RCMAIL->action=='delete-identity')
    include('program/steps/settings/delete_identity.inc');
  
  if ($RCMAIL->action=='identities')
    include('program/steps/settings/identities.inc');  

  if ($RCMAIL->action=='save-prefs')
    include('program/steps/settings/save_prefs.inc');  

  if ($RCMAIL->action=='folders' || $RCMAIL->action=='subscribe' || $RCMAIL->action=='unsubscribe' ||
      $RCMAIL->action=='create-folder' || $RCMAIL->action=='rename-folder' || $RCMAIL->action=='delete-folder')
    include('program/steps/settings/manage_folders.inc');
}


// parse main template
$OUTPUT->send($RCMAIL->task);


// if we arrive here, something went wrong
raise_error(array(
  'code' => 404,
  'type' => 'php',
  'line' => __LINE__,
  'file' => __FILE__,
  'message' => "Invalid request"), true, true);
                      
?>

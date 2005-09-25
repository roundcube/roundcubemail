<?php

/*
 +-----------------------------------------------------------------------+
 | RoundCube Webmail IMAP Client                                         |
 | Version 0.1-20050811                                                  |
 |                                                                       |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 | Redistribution and use in source and binary forms, with or without    |
 | modification, are permitted provided that the following conditions    |
 | are met:                                                              |
 |                                                                       |
 | o Redistributions of source code must retain the above copyright      |
 |   notice, this list of conditions and the following disclaimer.       |
 | o Redistributions in binary form must reproduce the above copyright   |
 |   notice, this list of conditions and the following disclaimer in the |
 |   documentation and/or other materials provided with the distribution.|
 | o The names of the authors may not be used to endorse or promote      |
 |   products derived from this software without specific prior written  |
 |   permission.                                                         |
 |                                                                       |
 | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
 | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
 | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
 | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
 | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
 | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
 | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
 | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
 | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
 | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
 | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// define global vars
$INSTALL_PATH = './';
$OUTPUT_TYPE = 'html';
$JS_OBJECT_NAME = 'rcmail';


// set environment first
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'program'.PATH_SEPARATOR.'program/lib');
ini_set('session.name', 'sessid');
ini_set('session.use_cookies', 1);
//ini_set('session.save_path', $INSTALL_PATH.'session');


// increase maximum execution time for php scripts
set_time_limit('120');


// include base files
require_once('include/rcube_shared.inc');
require_once('include/rcube_imap.inc');
require_once('include/rcube_mysql.inc');
require_once('include/bugs.inc');
require_once('include/main.inc');
require_once('include/cache.inc');


// catch some url/post parameters
$_auth = strlen($_POST['_auth']) ? $_POST['_auth'] : $_GET['_auth'];
$_task = strlen($_POST['_task']) ? $_POST['_task'] : ($_GET['_task'] ? $_GET['_task'] : 'mail');
$_action = strlen($_POST['_action']) ? $_POST['_action'] : $_GET['_action'];
$_framed = ($_GET['_framed'] || $_POST['_framed']);

// start session with requested task
rcmail_startup($_task);


// set session related variables
$COMM_PATH = sprintf('./?_auth=%s&_task=%s', $sess_auth, $_task);
$SESS_HIDDEN_FIELD = sprintf('<input type="hidden" name="_auth" value="%s" />', $sess_auth);


// add framed parameter
if ($_GET['_framed'] || $_POST['_framed'])
  {
  $COMM_PATH .= '&_framed=1';
  $SESS_HIDDEN_FIELD = "\n".'<input type="hidden" name="_framed" value="1" />';
  }


// init necessary objects for GUI
load_gui();


// error steps
if ($_action=='error' && strlen($_GET['_code']))
  {
  raise_error(array('code' => hexdec($_GET['_code'])), FALSE, TRUE);
  }


// try to log in
if ($_action=='login' && $_task=='mail')
  {
  $host = $_POST['_host'] ? $_POST['_host'] : $CONFIG['default_host'];
  
  // check if client supports cookies
  if (!$_COOKIE[session_name()])
    {
    show_message("cookiesdisabled", 'warning');
    }
  else if ($_POST['_user'] && $_POST['_pass'] && rcmail_login($_POST['_user'], $_POST['_pass'], $host))
    {
    // send redirect
    header("Location: $COMM_PATH");
    exit;
    }
  else
    {
    show_message("loginfailed", 'warning');
    $_SESSION['user_id'] = '';
    }
  }

// end session
else if ($_action=='logout' && $_SESSION['user_id'])
  {
  show_message('loggedout');
  rcmail_kill_session();
  }

// check session cookie and auth string
else if ($_action!='login' && $_auth && $sess_auth)
  {
  if ($_auth !== $sess_auth || $_auth != rcmail_auth_hash($_SESSION['client_id'], $_SESSION['auth_time']))
    {
    show_message('sessionerror', 'error');
    rcmail_kill_session();
    }
  }


// log in to imap server
if ($_SESSION['user_id'] && $_task=='mail')
  {
  $conn = $IMAP->connect($_SESSION['imap_host'], $_SESSION['username'], decrypt_passwd($_SESSION['password']));
  if (!$conn)
    {
    show_message('imaperror', 'error');
    $_SESSION['user_id'] = '';
    }
  }


// not logged in -> set task to 'login
if (!$_SESSION['user_id'])
  $_task = 'login';



// set taask and action to client
$script = sprintf("%s.set_env('task', '%s');", $JS_OBJECT_NAME, $_task);
if (!empty($_action))
  $script .= sprintf("\n%s.set_env('action', '%s');", $JS_OBJECT_NAME, $_action);

$OUTPUT->add_script($script);



// not logged in -> show login page
if (!$_SESSION['user_id'])
  {
  parse_template('login');
  exit;
  }



// include task specific files
if ($_task=='mail')
  {
  include_once('program/steps/mail/func.inc');

  if ($_action=='show' || $_action=='print')
    include('program/steps/mail/show.inc');

  if ($_action=='get')
    include('program/steps/mail/get.inc');

  if ($_action=='moveto' || $_action=='delete')
    include('program/steps/mail/move_del.inc');

  if ($_action=='mark')
    include('program/steps/mail/mark.inc');

  if ($_action=='viewsource')
    include('program/steps/mail/viewsource.inc');

  if ($_action=='send')
    include('program/steps/mail/sendmail.inc');

  if ($_action=='upload')
    include('program/steps/mail/upload.inc');

  if ($_action=='compose')
    include('program/steps/mail/compose.inc');

  if ($_action=='addcontact')
    include('program/steps/mail/addcontact.inc');
    
  if ($_action=='list' && $_GET['_remote'])
    include('program/steps/mail/list.inc');

  // kill compose entry from session
  if (isset($_SESSION['compose']))
    rcmail_compose_cleanup();
  }


// include task specific files
if ($_task=='addressbook')
  {
  include_once('program/steps/addressbook/func.inc');

  if ($_action=='save')
    include('program/steps/addressbook/save.inc');
  
  if ($_action=='edit' || $_action=='add')
    include('program/steps/addressbook/edit.inc');
  
  if ($_action=='delete')
    include('program/steps/addressbook/delete.inc');

  if ($_action=='show')
    include('program/steps/addressbook/show.inc');  

  if ($_action=='list' && $_GET['_remote'])
    include('program/steps/addressbook/list.inc');
  }


// include task specific files
if ($_task=='settings')
  {
  include_once('program/steps/settings/func.inc');

  if ($_action=='save-identity')
    include('program/steps/settings/save_identity.inc');

  if ($_action=='add-identity' || $_action=='edit-identity')
    include('program/steps/settings/edit_identity.inc');

  if ($_action=='delete-identity')
    include('program/steps/settings/delete_identity.inc');
  
  if ($_action=='identities')
    include('program/steps/settings/identities.inc');  

  if ($_action=='save-prefs')
    include('program/steps/settings/save_prefs.inc');  

  if ($_action=='folders' || $_action=='subscribe' || $_action=='unsubscribe' || $_action=='create-folder' || $_action=='delete-folder')
    include('program/steps/settings/manage_folders.inc');

  }


// parse main template
parse_template($_task);

?>
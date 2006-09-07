<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/save_prefs.inc                                 |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Save user preferences to DB and to the current session              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$a_user_prefs = $_SESSION['user_prefs'];
if (!is_array($a_user_prefs))
  $a_user_prefs = array();


$a_user_prefs['timezone'] = isset($_POST['_timezone']) ? floatval($_POST['_timezone']) : $CONFIG['timezone'];
$a_user_prefs['dst_active'] = isset($_POST['_dst_active']) ? TRUE : FALSE;
$a_user_prefs['pagesize'] = is_numeric($_POST['_pagesize']) ? (int)$_POST['_pagesize'] : $CONFIG['pagesize'];
$a_user_prefs['prefer_html'] = isset($_POST['_prefer_html']) ? TRUE : FALSE;
$a_user_prefs['draft_autosave'] = isset($_POST['_draft_autosave']) ? intval($_POST['_draft_autosave']) : 0;

// MM: Date format toggle (Pretty / Standard)
$a_user_prefs['prettydate'] = isset($_POST['_pretty_date']) ? TRUE : FALSE;

if (isset($_POST['_language']))
  {
  $sess_user_lang = $_SESSION['user_lang'] = get_input_value('_language', RCUBE_INPUT_POST);
  rcmail_set_locale($sess_user_lang);
  }

if (rcmail_save_user_prefs($a_user_prefs))
  show_message('successfullysaved', 'confirmation');


// go to next step
$_action = 'preferences';

// overwrite action variable  
$OUTPUT->add_script(sprintf("\n%s.set_env('action', '%s');", $JS_OBJECT_NAME, $_action));  

?>
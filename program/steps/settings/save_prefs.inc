<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/save_prefs.inc                                 |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
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


$a_user_prefs['timezone'] = isset($_POST['_timezone']) ? (int)$_POST['_timezone'] : $CONFIG['timezone'];
$a_user_prefs['pagesize'] = is_numeric($_POST['_pagesize']) ? (int)$_POST['_pagesize'] : $CONFIG['pagesize'];
$a_user_prefs['prefer_html'] = isset($_POST['_prefer_html']) ? TRUE : FALSE;

if (isset($_POST['_language']))
  $sess_user_lang = $_SESSION['user_lang'] = $_POST['_language'];


$DB->query(sprintf("UPDATE %s
                    SET    preferences='%s',
                           language='%s'
                    WHERE  user_id=%d",
                   get_table_name('users'),
                   addslashes(serialize($a_user_prefs)),
                   $sess_user_lang,
                   $_SESSION['user_id']));

if ($DB->affected_rows())
  {
  show_message('successfullysaved', 'confirmation');
  
  $_SESSION['user_prefs'] = $a_user_prefs;  
  $CONFIG = array_merge($CONFIG, $a_user_prefs);
  }


// go to next step
$_action = 'preferences';

// overwrite action variable  
$OUTPUT->add_script(sprintf("\n%s.set_env('action', '%s');", $JS_OBJECT_NAME, $_action));  

?>
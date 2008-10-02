<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/session.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide database supported session management                       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


function rcube_sess_open($save_path, $session_name)
{
  return true;
}


function rcube_sess_close()
{
  return true;
}


// read session data
function rcube_sess_read($key)
{
  global $SESS_CHANGED, $SESS_CLIENT_IP;
  
  $DB = rcmail::get_instance()->get_dbh();
  
  if ($DB->is_error()) {
    return false;
  }
  
  $sql_result = $DB->query(
    "SELECT vars, ip, " . $DB->unixtimestamp('changed') . " AS changed
     FROM " . get_table_name('session') . "
     WHERE  sess_id=?",
    $key);

  if ($sql_arr = $DB->fetch_assoc($sql_result)) {
    $SESS_CHANGED = $sql_arr['changed'];
    $SESS_CLIENT_IP = $sql_arr['ip'];

    if (strlen($sql_arr['vars']))
      return $sql_arr['vars'];
  }

  return false;
}
  

// save session data
function rcube_sess_write($key, $vars)
{
  $DB = rcmail::get_instance()->get_dbh();
  
  if ($DB->is_error()) {
    return false;
  }

  $sql_result = $DB->query(
    "SELECT 1 FROM " . get_table_name('session') . "
     WHERE  sess_id=?",
    $key);

  if ($DB->num_rows($sql_result)) {
    $DB->query(
      "UPDATE " . get_table_name('session') . "
       SET    vars=?, changed=" . $DB->now() . "
       WHERE  sess_id=?",
      $vars,
      $key);
  }
  else {
    $DB->query(
      "INSERT INTO " . get_table_name('session') . "
       (sess_id, vars, ip, created, changed)
       VALUES (?, ?, ?, ".$DB->now().", ".$DB->now().")",
      $key,
      $vars,
      (string)$_SERVER['REMOTE_ADDR']);
  }

  return true;
}


// handler for session_destroy()
function rcube_sess_destroy($key)
{
  $rcmail = rcmail::get_instance();
  $DB = $rcmail->get_dbh();
  
  if ($DB->is_error()) {
    return false;
  }

  // delete session entries in cache table
  if ($rcmail->config->get('enable_caching')) {
    $DB->query("DELETE FROM " . get_table_name('cache') . " WHERE session_id=?", $key);
  }
              
  $DB->query("DELETE FROM " . get_table_name('session') . " WHERE sess_id=?", $key);

  return true;
}


// garbage collecting function
function rcube_sess_gc($maxlifetime)
{
  $rcmail = rcmail::get_instance();
  $DB = $rcmail->get_dbh();

  if ($DB->is_error()) {
    return false;
  }

  if ($rcmail->config->get('enable_caching')) {
    // get all expired sessions
    $sql_result = $DB->query(
	"SELECT sess_id
        FROM " . get_table_name('session') . "
        WHERE " . $DB->unixtimestamp($DB->now())."-".$DB->unixtimestamp('changed') . " > ?",
	$maxlifetime);
                                   
    $exp_sessions = array();
    while ($sql_arr = $DB->fetch_assoc($sql_result)) {
      $exp_sessions[] = $sql_arr['sess_id'];
    }

    if (sizeof($exp_sessions)) {
      $exp_sessions = "'" . join("','", $exp_sessions) . "'";
      // delete session cache records
      $DB->query("DELETE FROM " . get_table_name('cache') . "
            WHERE session_id IN (" . $exp_sessions . ")");

      // delete session records
      $DB->query("DELETE FROM " . get_table_name('session') . "
            WHERE sess_id IN (" . $exp_sessions . ")");
    }

    // also run message cache GC
    rcmail_message_cache_gc();
  
  } else {
    // just delete all expired sessions
    $DB->query("DELETE FROM " . get_table_name('session') . "
        WHERE " . $DB->unixtimestamp($DB->now())."-".$DB->unixtimestamp('changed') . " > ?",
	$maxlifetime);
  }

  rcmail_temp_gc();

  return true;
}


function rcube_sess_regenerate_id()
{
  $randval = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

  for ($random = "", $i=1; $i <= 32; $i++) {
    $random .= substr($randval, rand(0,(strlen($randval) - 1)), 1);
  }

  // use md5 value for id or remove capitals from string $randval
  $random = md5($random);

  // delete old session record
  rcube_sess_destroy(session_id());

  session_id($random);

  $cookie   = session_get_cookie_params();
  $lifetime = $cookie['lifetime'] ? time() + $cookie['lifetime'] : 0;

  rcmail::setcookie(session_name(), $random, $lifetime);

  return true;
}


// set custom functions for PHP session management
session_set_save_handler('rcube_sess_open', 'rcube_sess_close', 'rcube_sess_read', 'rcube_sess_write', 'rcube_sess_destroy', 'rcube_sess_gc');

?>

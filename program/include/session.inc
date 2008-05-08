<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/session.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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


function sess_open($save_path, $session_name)
  {
  return TRUE;
  }


function sess_close()
  {
  return TRUE;
  }


// read session data
function sess_read($key)
  {
  global $DB, $SESS_CHANGED, $SESS_CLIENT_IP;
  
  if ($DB->is_error())
    return FALSE;
  
  $sql_result = $DB->query("SELECT vars, ip, ".$DB->unixtimestamp('changed')." AS changed
                            FROM ".get_table_name('session')."
                            WHERE  sess_id=?",
                            $key);

  if ($sql_arr = $DB->fetch_assoc($sql_result))
    {
    $SESS_CHANGED = $sql_arr['changed'];
    $SESS_CLIENT_IP = $sql_arr['ip'];

    if (strlen($sql_arr['vars']))
      return $sql_arr['vars'];
    }

  return FALSE;
  }
  

// save session data
function sess_write($key, $vars)
  {
  global $DB;
  
  if ($DB->is_error())
    return FALSE;

  $sql_result = $DB->query("SELECT 1
                            FROM ".get_table_name('session')."
                            WHERE  sess_id=?",
                            $key);

  if ($DB->num_rows($sql_result))
    {
    session_decode($vars);
    $DB->query("UPDATE ".get_table_name('session')."
                SET    vars=?,
                       changed=".$DB->now()."
                WHERE  sess_id=?",
                $vars,
                $key);
    }
  else
    {
    $DB->query("INSERT INTO ".get_table_name('session')."
                (sess_id, vars, ip, created, changed)
                VALUES (?, ?, ?, ".$DB->now().", ".$DB->now().")",
                $key,
                $vars,
                $_SERVER['REMOTE_ADDR']);
    }

  return TRUE;
  }


// handler for session_destroy()
function sess_destroy($key)
  {
  global $DB, $CONFIG;
  
  if ($DB->is_error())
    return FALSE;

  if ($CONFIG['enable_caching'])
    {
    // delete session entries in cache table
    $DB->query("DELETE FROM ".get_table_name('cache')."
              WHERE session_id=?",
              $key);
    }
              
  $DB->query("DELETE FROM ".get_table_name('session')."
              WHERE sess_id=?",
              $key);

  return TRUE;
  }


// garbage collecting function
function sess_gc($maxlifetime)
  {
  global $DB, $CONFIG;

  if ($DB->is_error())
    return FALSE;

  // get all expired sessions  
  $sql_result = $DB->query("SELECT sess_id
                            FROM ".get_table_name('session')."
                            WHERE ".$DB->unixtimestamp($DB->now())."-".$DB->unixtimestamp('changed')." > ?",
                            $maxlifetime);
                                   
  $a_exp_sessions = array();
  while ($sql_arr = $DB->fetch_assoc($sql_result))
    $a_exp_sessions[] = $sql_arr['sess_id'];

  if (sizeof($a_exp_sessions))
    {
    if ($CONFIG['enable_caching'])
      {
        // delete session cache records
	$DB->query("DELETE FROM ".get_table_name('cache')."
                WHERE session_id IN ('".join("','", $a_exp_sessions)."')");
      }
                  
    // delete session records
    $DB->query("DELETE FROM ".get_table_name('session')."
                WHERE sess_id IN ('".join("','", $a_exp_sessions)."')");
    }

  // also run message cache GC
  if ($CONFIG['enable_caching'])
    rcmail_message_cache_gc();
  rcmail_temp_gc();

  return TRUE;
  }


function sess_regenerate_id()
  {
  $randlen = 32;
  $randval = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $random = "";
  for ($i=1; $i <= $randlen; $i++)
    $random .= substr($randval, rand(0,(strlen($randval) - 1)), 1);

  // use md5 value for id or remove capitals from string $randval
  $random = md5($random);

  // delete old session record
  sess_destroy(session_id());

  session_id($random);

  $cookie    = session_get_cookie_params();
  $_lifetime = $cookie['lifetime'] ? time() + $cookie['lifetime'] : 0;

  setcookie(session_name(), '', time() - 3600);
  setcookie(session_name(), $random, $_lifetime, $cookie['path'],
    $cookie['domain']);

  return true;
  }


// set custom functions for PHP session management
session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy', 'sess_gc');

?>

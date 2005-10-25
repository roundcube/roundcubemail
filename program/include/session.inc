<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/session.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev, - Switzerland                      |
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
  global $DB, $SESS_CHANGED;
  
  $sql_result = $DB->query("SELECT vars, ip, ".$DB->unixtimestamp('changed')." AS changed
                            FROM ".get_table_name('session')."
                            WHERE  sess_id=?",
                            $key);

  if ($sql_arr = $DB->fetch_assoc($sql_result))
    {
    $SESS_CHANGED = $sql_arr['changed'];

    if (strlen($sql_arr['vars']))
      return $sql_arr['vars'];
    }

  return FALSE;
  }
  

// save session data
function sess_write($key, $vars)
  {
  global $DB;
  
  $sql_result = $DB->query("SELECT 1
                            FROM ".get_table_name('session')."
                            WHERE  sess_id=?",
                            $key);

  if ($DB->num_rows($sql_result))
    {
    session_decode($vars);
    $DB->query("UPDATE ".get_table_name('session')."
                SET    vars=?,
                       changed=NOW()
                WHERE  sess_id=?",
                $vars,
                $key);
    }
  else
    {
    $DB->query("INSERT INTO ".get_table_name('session')."
                (sess_id, vars, ip, created, changed)
                VALUES (?, ?, ?, NOW(), NOW())",
                $key,
                $vars,
                $_SERVER['REMOTE_ADDR']);
    }

  return TRUE;
  }


// handler for session_destroy()
function sess_destroy($key)
  {
  global $DB;
  
  // delete session entries in cache table
  $DB->query("DELETE FROM ".get_table_name('cache')."
              WHERE  session_id=?",
              $key);
              
  $DB->query("DELETE FROM ".get_table_name('session')."
              WHERE sess_id=?",
              $key);
                     
  return TRUE;
  }


// garbage collecting function
function sess_gc($maxlifetime)
  {
  global $DB;

  // get all expired sessions  
  $sql_result = $DB->query("SELECT sess_id
                            FROM ".get_table_name('session')."
                            WHERE ".$DB->unixtimestamp('NOW()')."-".$DB->unixtimestamp('created')." > ?",
                            $maxlifetime);
                                   
  $a_exp_sessions = array();
  while ($sql_arr = $DB->fetch_assoc($sql_result))
    $a_exp_sessions[] = $sql_arr['sess_id'];

  
  if (sizeof($a_exp_sessions))
    {
    // delete session cache records
    $DB->query("DELETE FROM ".get_table_name('cache')."
                WHERE  session_id IN ('".join("','", $a_exp_sessions)."')");
                
    // delete session records
    $DB->query("DELETE FROM ".get_table_name('session')."
                WHERE sess_id IN ('".join("','", $a_exp_sessions)."')");
    }

  return TRUE;
  }


// set custom functions for PHP session management
session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy', 'sess_gc');

?>

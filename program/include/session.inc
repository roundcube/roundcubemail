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
  
  $sql_result = $DB->query(sprintf("SELECT vars, UNIX_TIMESTAMP(changed) AS changed
                                    FROM   %s
                                    WHERE  sess_id='%s'",
                                   get_table_name('session'),
                                   $key));

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
  
  $sql_result = $DB->query(sprintf("SELECT 1
                                    FROM   %s
                                    WHERE  sess_id='%s'",
                                   get_table_name('session'),
                                   $key));

  if ($DB->num_rows($sql_result))
    {
    session_decode($vars);
    $DB->query(sprintf("UPDATE %s
                        SET    vars='%s',
                               changed=NOW()
                        WHERE  sess_id='%s'",
                       get_table_name('session'),
                       $vars,
                       $key));
    }
  else
    {
    $DB->query(sprintf("INSERT INTO %s
                        (sess_id, vars, created, changed)
                        VALUES ('%s', '%s', NOW(), NOW())",
                       get_table_name('session'),
                       $key,
                       $vars));
    }

  return TRUE;
  }


// handler for session_destroy()
function sess_destroy($key)
  {
  global $DB;
  
  $DB->query(sprintf("DELETE FROM %s
                      WHERE sess_id='%s'",
                     get_table_name('session'),
                     $key));

  // also delete session entries in cache table
  $DB->query(sprintf("DELETE FROM %s
                      WHERE  session_id='%s'",
                     get_table_name('cache'),
                     $key));
                     
  return TRUE;
  }


// garbage collecting function
function sess_gc($maxlifetime)
  {
  global $DB;

  // get all expired sessions  
  $sql_result = $DB->query(sprintf("SELECT sess_id
                                    FROM   %s
                                    WHERE  UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(created) > %d",
                                   get_table_name('session'),
                                   $maxlifetime));
                                   
  $a_exp_sessions = array();
  while ($sql_arr = $DB->fetch_assoc($sql_result))
    $a_exp_sessions[] = $sql_arr['sess_id'];

  
  if (sizeof($a_exp_sessions))
    {
    // delete session records
    $DB->query(sprintf("DELETE FROM %s
                        WHERE sess_id IN ('%s')",
                       get_table_name('session'),
                       join("','", $a_exp_sessions)));

    // also delete session cache records
    $DB->query(sprintf("DELETE FROM %s
                        WHERE  session_id IN ('%s')",
                       get_table_name('cache'),
                       join("','", $a_exp_sessions)));
    }

  return TRUE;
  }


// set custom functions for PHP session management
session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy', 'sess_gc');

?>

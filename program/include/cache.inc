<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/cache.inc                                             |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev, - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide access to the application cache                             |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


function rcube_read_cache($key)
  {
  global $DB, $CACHE_KEYS;
  
  // query db
  $sql_result = $DB->query("SELECT cache_id, data
                            FROM ".get_table_name('cache')."
                            WHERE  user_id=?
                            AND    cache_key=?",
                            $_SESSION['user_id'],
                            $key);

  // get cached data
  if ($sql_arr = $DB->fetch_assoc($sql_result))
    {
    $data = $sql_arr['data'];
    $CACHE_KEYS[$key] = $sql_arr['cache_id'];
    }
  else
    $data = FALSE;

  return $data;
  }
  
  
function rcube_write_cache($key, $data, $session_cache=FALSE)
  {
  global $DB, $CACHE_KEYS, $sess_id;
  
  // check if we already have a cache entry for this key
  if (!isset($CACHE_KEYS[$key]))
    {
    $sql_result = $DB->query("SELECT cache_id
                              FROM ".get_table_name('cache')."
                              WHERE  user_id=?
                              AND    cache_key=?",
                              $_SESSION['user_id'],
                              $key);
                                     
    if ($sql_arr = $DB->fetch_assoc($sql_result))
      $CACHE_KEYS[$key] = $sql_arr['cache_id'];
    else
      $CACHE_KEYS[$key] = FALSE;
    }

  // update existing cache record
  if ($CACHE_KEYS[$key])
    {
    $DB->query("UPDATE ".get_table_name('cache')."
                SET    created=NOW(),
                       data=?
                WHERE  user_id=?
                AND    cache_key=?",
                $data,
                $_SESSION['user_id'],
                $key);
    }
  // add new cache record
  else
    {
    $DB->query("INSERT INTO ".get_table_name('cache')."
                (created, user_id, session_id, cache_key, data)
                VALUES (NOW(), ?, ?, ?', ?)",
                $_SESSION['user_id'],
                $session_cache ? $sess_id : 'NULL',
                $key,
                $data);
    }
  }



function rcube_clear_cache($key)
  {
  global $DB;

  $DB->query("DELETE FROM ".get_table_name('cache')."
              WHERE  user_id=?
              AND    cache_key=?",
              $_SESSION['user_id'],
              $key);
  }


?>
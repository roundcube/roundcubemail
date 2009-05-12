<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/session.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
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
  
  $now = $DB->fromunixtime(time());

  if ($oldvars = rcube_sess_read($key)) {
    $DB->query(
      "UPDATE " . get_table_name('session') . "
       SET    vars=?, changed= " . $now . "
       WHERE  sess_id=?",
      rcube_sess_serialize(array_merge(rcube_sess_unserialize($oldvars), rcube_sess_unserialize($vars))),
      $key);
  }
  else {
    $DB->query(
      "INSERT INTO " . get_table_name('session') . "
       (sess_id, vars, ip, created, changed)
       VALUES (?, ?, ?, " . $now . ", " . $now .")",
      $key,
      $vars,
      (string)$_SERVER['REMOTE_ADDR']);
  }

  return true;
}


// unset session variable
function rcube_sess_unset($var)
{
  $DB = rcmail::get_instance()->get_dbh();

  $now = $DB->fromunixtime(time());

  $sql_result = $DB->query(
    "SELECT vars
     FROM " . get_table_name('session') . "
     WHERE  sess_id=?",
     session_id());

  if ($sql_arr = $DB->fetch_assoc($sql_result)) {
    $vars = rcube_sess_unserialize($sql_arr['vars']);
    if (isset($vars[$var])) {
      unset($vars[$var]);
      $DB->query(
        "UPDATE " . get_table_name('session') . "
         SET    vars=?, changed= " . $now . "
         WHERE  sess_id=?",
         rcube_sess_serialize($vars),
         session_id());
    }
  }   

  return true;
}


// serialize session data
function rcube_sess_serialize($vars)
{
  $data = '';
  if (is_array($vars))
    foreach ($vars as $var=>$value)
      $data .= $var.'|'.serialize($value);
  else
    $data = 'b:0;';
  return $data;
}


// unserialize session data
// http://www.php.net/manual/en/function.session-decode.php#56106
function rcube_sess_unserialize($str)
{
  $str = (string)$str;
  $endptr = strlen($str);
  $p = 0;

  $serialized = '';
  $items = 0;
  $level = 0;

  while ($p < $endptr) {
    $q = $p;
    while ($str[$q] != '|')
      if (++$q >= $endptr) break 2;

    if ($str[$p] == '!') {
      $p++;
      $has_value = false;
    } else {
      $has_value = true;
    }

    $name = substr($str, $p, $q - $p);
    $q++;

    $serialized .= 's:' . strlen($name) . ':"' . $name . '";';

    if ($has_value) {
      for (;;) {
        $p = $q;
        switch (strtolower($str[$q])) {
          case 'n': /* null */
          case 'b': /* boolean */
          case 'i': /* integer */
          case 'd': /* decimal */
            do $q++;
            while ( ($q < $endptr) && ($str[$q] != ';') );
            $q++;
            $serialized .= substr($str, $p, $q - $p);
            if ($level == 0) break 2;
            break;
          case 'r': /* reference  */
            $q+= 2;
            for ($id = ''; ($q < $endptr) && ($str[$q] != ';'); $q++) $id .= $str[$q];
            $q++;
            $serialized .= 'R:' . ($id + 1) . ';'; /* increment pointer because of outer array */
            if ($level == 0) break 2;
            break;
          case 's': /* string */
            $q+=2;
            for ($length=''; ($q < $endptr) && ($str[$q] != ':'); $q++) $length .= $str[$q];
            $q+=2;
            $q+= (int)$length + 2;
            $serialized .= substr($str, $p, $q - $p);
            if ($level == 0) break 2;
            break;
          case 'a': /* array */
          case 'o': /* object */
            do $q++;
            while ( ($q < $endptr) && ($str[$q] != '{') );
            $q++;
            $level++;
            $serialized .= substr($str, $p, $q - $p);
            break;
          case '}': /* end of array|object */
            $q++;
            $serialized .= substr($str, $p, $q - $p);
            if (--$level == 0) break 2;
            break;
          default:
            return false;
        }
      }
    } else {
      $serialized .= 'N;';
      $q+= 2;
    }
    $items++;
    $p = $q;
  }

  return unserialize( 'a:' . $items . ':{' . $serialized . '}' );
}


// handler for session_destroy()
function rcube_sess_destroy($key)
{
  $DB = rcmail::get_instance()->get_dbh();
  
  $DB->query("DELETE FROM " . get_table_name('session') . " WHERE sess_id=?", $key);

  return true;
}


// garbage collecting function
function rcube_sess_gc($maxlifetime)
{
  $rcmail = rcmail::get_instance();
  $DB = $rcmail->get_dbh();

  // just delete all expired sessions
  $DB->query("DELETE FROM " . get_table_name('session') . "
    WHERE changed < " . $DB->fromunixtime(time() - $maxlifetime));

  if ($rcmail->config->get('enable_caching'))
    rcmail_cache_gc();

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

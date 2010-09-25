<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_session.php                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide database supported session management                       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id: session.inc 2932 2009-09-07 12:51:21Z alec $

*/

/**
 * Class to provide database supported session storage
 *
 * @package    Core
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 */
class rcube_session
{
  private $db;
  private $ip;
  private $changed;
  private $unsets = array();
  private $gc_handlers = array();
  private $start;
  private $vars = false;
  private $key;
  private $keep_alive = 0;

  /**
   * Default constructor
   */
  public function __construct($db, $lifetime=60)
  {
    $this->db = $db;
    $this->lifetime = $lifetime;
    $this->start = microtime(true);

    // set custom functions for PHP session management
    session_set_save_handler(
      array($this, 'open'),
      array($this, 'close'),
      array($this, 'read'),
      array($this, 'write'),
      array($this, 'destroy'),
      array($this, 'gc'));
  }


  public function open($save_path, $session_name)
  {
    return true;
  }


  public function close()
  {
    return true;
  }


  // read session data
  public function read($key)
  {
    $sql_result = $this->db->query(
      sprintf("SELECT vars, ip, %s AS changed FROM %s WHERE sess_id = ?",
        $this->db->unixtimestamp('changed'), get_table_name('session')),
      $key);

    if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
      $this->changed = $sql_arr['changed'];
      $this->vars = $sql_arr['vars'];
      $this->ip = $sql_arr['ip'];
      $this->key = $key; 

      if (!empty($sql_arr['vars']))
        return $sql_arr['vars'];
    }

    return false;
  }
  

  // save session data
  public function write($key, $vars)
  {
    $ts = microtime(true);
    $now = $this->db->fromunixtime((int)$ts);

    // use internal data from read() for fast requests (up to 0.5 sec.)
    if ($key == $this->key && $ts - $this->start < 0.5) {
      $oldvars = $this->vars;
    } else { // else read data again from DB
      $oldvars = $this->read($key);
    }
    
    if ($oldvars !== false) {
      $a_oldvars = $this->unserialize($oldvars); 
      foreach ((array)$this->unsets as $k)
        unset($a_oldvars[$k]);

      $newvars = $this->serialize(array_merge(
        (array)$a_oldvars, (array)$this->unserialize($vars)));

      if ($this->keep_alive>0) {
	$timeout = min($this->lifetime * 0.5, 
		       $this->lifetime - $this->keep_alive);
      } else {
	$timeout = 0;
      }

      if (!($newvars === $oldvars) || ($ts - $this->changed > $timeout)) {
        $this->db->query(
	  sprintf("UPDATE %s SET vars = ?, changed = %s WHERE sess_id = ?",
	    get_table_name('session'), $now),
	  $newvars, $key);
      }
    }
    else {
      $this->db->query(
        sprintf("INSERT INTO %s (sess_id, vars, ip, created, changed) ".
          "VALUES (?, ?, ?, %s, %s)",
	  get_table_name('session'), $now, $now),
        $key, $vars, (string)$_SERVER['REMOTE_ADDR']);
    }

    $this->unsets = array();
    return true;
  }


  // handler for session_destroy()
  public function destroy($key)
  {
    $this->db->query(
      sprintf("DELETE FROM %s WHERE sess_id = ?", get_table_name('session')),
      $key);

    return true;
  }


  // garbage collecting function
  public function gc($maxlifetime)
  {
    // just delete all expired sessions
    $this->db->query(
      sprintf("DELETE FROM %s WHERE changed < %s",
        get_table_name('session'), $this->db->fromunixtime(time() - $maxlifetime)));

    foreach ($this->gc_handlers as $fct)
      $fct();

    return true;
  }


  // registering additional garbage collector functions
  public function register_gc_handler($func_name)
  {
    if ($func_name && !in_array($func_name, $this->gc_handlers))
      $this->gc_handlers[] = $func_name;
  }


  public function regenerate_id()
  {
    $randval = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    for ($random = '', $i=1; $i <= 32; $i++) {
      $random .= substr($randval, mt_rand(0,(strlen($randval) - 1)), 1);
    }

    // use md5 value for id or remove capitals from string $randval
    $random = md5($random);

    // delete old session record
    $this->destroy(session_id());

    session_id($random);

    $cookie   = session_get_cookie_params();
    $lifetime = $cookie['lifetime'] ? time() + $cookie['lifetime'] : 0;

    rcmail::setcookie(session_name(), $random, $lifetime);

    return true;
  }


  // unset session variable
  public function remove($var=NULL)
  {
    if (empty($var))
      return $this->destroy(session_id());

    $this->unsets[] = $var;
    unset($_SESSION[$var]);

    return true;
  }


  // serialize session data
  private function serialize($vars)
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
  private function unserialize($str)
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
        $q += 2;
      }
      $items++;
      $p = $q;
    }

    return unserialize( 'a:' . $items . ':{' . $serialized . '}' );
  }

  public function set_keep_alive($keep_alive)
  {
    $this->keep_alive = $keep_alive;
  }

  public function get_keep_alive()
  {
    return $this->keep_alive;
  }

  // getter for private variables
  public function get_ts()
  {
    return $this->changed;
  }

  // getter for private variables
  public function get_ip()
  {
    return $this->ip;
  }

}

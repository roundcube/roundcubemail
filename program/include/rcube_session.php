<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_session.php                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
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
  private $start;
  private $changed;
  private $unsets = array();
  private $gc_handlers = array();
  private $cookiename = 'roundcube_sessauth';
  private $vars = false;
  private $key;
  private $now;
  private $prev;
  private $secret = '';
  private $ip_check = false;
  private $keep_alive = 0;

  /**
   * Default constructor
   */
  public function __construct($db, $lifetime=60)
  {
    $this->db = $db;
    $this->start = microtime(true);
    $this->ip = $_SERVER['REMOTE_ADDR'];

    $this->set_lifetime($lifetime);

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
      $this->ip      = $sql_arr['ip'];
      $this->vars    = base64_decode($sql_arr['vars']);
      $this->key     = $key;

      if (!empty($this->vars))
        return $this->vars;
    }

    return false;
  }


  /**
   * Save session data.
   * handler for session_read()
   *
   * @param string Session ID
   * @param string Serialized session vars
   * @return boolean True on success
   */
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
      if (is_array($a_oldvars)) {
        foreach ((array)$this->unsets as $k)
          unset($a_oldvars[$k]);

        $newvars = $this->serialize(array_merge(
          (array)$a_oldvars, (array)$this->unserialize($vars)));
      }
      else
        $newvars = $vars;

      if ($newvars !== $oldvars) {
        $this->db->query(
          sprintf("UPDATE %s SET vars=?, changed=%s WHERE sess_id=?",
            get_table_name('session'), $now),
          base64_encode($newvars), $key);
      }
      else if ($ts - $this->changed > $this->lifetime / 2) {
        $this->db->query("UPDATE ".get_table_name('session')." SET changed=$now WHERE sess_id=?", $key);
      }
    }
    else {
      $this->db->query(
        sprintf("INSERT INTO %s (sess_id, vars, ip, created, changed) ".
          "VALUES (?, ?, ?, %s, %s)",
          get_table_name('session'), $now, $now),
        $key, base64_encode($vars), (string)$this->ip);
    }

    $this->unsets = array();
    return true;
  }


  /**
   * Handler for session_destroy()
   *
   * @param string Session ID
   * @return boolean True on success
   */
  public function destroy($key)
  {
    $this->db->query(
      sprintf("DELETE FROM %s WHERE sess_id = ?", get_table_name('session')),
      $key);

    return true;
  }


  /**
   * Garbage collecting function
   *
   * @param string Session lifetime in seconds
   * @return boolean True on success
   */
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


  /**
   * Register additional garbage collector functions
   *
   * @param mixed Callback function
   */
  public function register_gc_handler($func_name)
  {
    if ($func_name && !in_array($func_name, $this->gc_handlers))
      $this->gc_handlers[] = $func_name;
  }


  /**
   * Generate and set new session id
   */
  public function regenerate_id()
  {
    // delete old session record
    $this->destroy(session_id());
    $this->vars = false;

    $randval = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    for ($random = '', $i=1; $i <= 32; $i++) {
      $random .= substr($randval, mt_rand(0,(strlen($randval) - 1)), 1);
    }

    // use md5 value for id
    $this->key = md5($random);
    session_id($this->key);

    $cookie   = session_get_cookie_params();
    $lifetime = $cookie['lifetime'] ? time() + $cookie['lifetime'] : 0;

    rcmail::setcookie(session_name(), $this->key, $lifetime);

    return true;
  }


  /**
   * Unset a session variable
   *
   * @param string Varibale name
   * @return boolean True on success
   */
  public function remove($var=null)
  {
    if (empty($var))
      return $this->destroy(session_id());

    $this->unsets[] = $var;
    unset($_SESSION[$var]);

    return true;
  }
  
  /**
   * Kill this session
   */
  public function kill()
  {
    $this->destroy(session_id());
    rcmail::setcookie($this->cookiename, '-del-', time() - 60);
  }


  /**
   * Serialize session data
   */
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


  /**
   * Unserialize session data
   * http://www.php.net/manual/en/function.session-decode.php#56106
   */
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


  /**
   * Setter for session lifetime
   */
  public function set_lifetime($lifetime)
  {
      $this->lifetime = max(120, $lifetime);

      // valid time range is now - 1/2 lifetime to now + 1/2 lifetime
      $now = time();
      $this->now = $now - ($now % ($this->lifetime / 2));
      $this->prev = $this->now - ($this->lifetime / 2);
  }

  /**
   * Setter for keep_alive interval
   */
  public function set_keep_alive($keep_alive)
  {
    $this->keep_alive = $keep_alive;
    
    if ($this->lifetime < $keep_alive)
        $this->set_lifetime($keep_alive + 30);
  }

  /**
   * Getter for keep_alive interval
   */
  public function get_keep_alive()
  {
    return $this->keep_alive;
  }

  /**
   * Getter for remote IP saved with this session
   */
  public function get_ip()
  {
    return $this->ip;
  }
  
  /**
   * Setter for cookie encryption secret
   */
  function set_secret($secret)
  {
    $this->secret = $secret;
  }


  /**
   * Enable/disable IP check
   */
  function set_ip_check($check)
  {
    $this->ip_check = $check;
  }
  
  /**
   * Setter for the cookie name used for session cookie
   */
  function set_cookiename($cookiename)
  {
    if ($cookiename)
      $this->cookiename = $cookiename;
  }


  /**
   * Check session authentication cookie
   *
   * @return boolean True if valid, False if not
   */
  function check_auth()
  {
    $this->cookie = $_COOKIE[$this->cookiename];
    $result = $this->ip_check ? $_SERVER['REMOTE_ADDR'] == $this->ip : true;

    if ($result && $this->_mkcookie($this->now) != $this->cookie) {
      // Check if using id from previous time slot
      if ($this->_mkcookie($this->prev) == $this->cookie)
        $this->set_auth_cookie();
      else
        $result = false;
    }

    return $result;
  }


  /**
   * Set session authentication cookie
   */
  function set_auth_cookie()
  {
    $this->cookie = $this->_mkcookie($this->now);
    rcmail::setcookie($this->cookiename, $this->cookie, 0);
    $_COOKIE[$this->cookiename] = $this->cookie;
  }


  /**
   * Create session cookie from session data
   *
   * @param int Time slot to use
   */
  function _mkcookie($timeslot)
  {
    $auth_string = "$this->key,$this->secret,$timeslot";
    return "S" . (function_exists('sha1') ? sha1($auth_string) : md5($auth_string));
  }

}

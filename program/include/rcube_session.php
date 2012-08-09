<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_session.php                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
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
  private $vars;
  private $key;
  private $now;
  private $secret = '';
  private $ip_check = false;
  private $logging = false;
  private $keep_alive = 0;
  private $memcache;

  /**
   * Default constructor
   */
  public function __construct($db, $config)
  {
    $this->db      = $db;
    $this->start   = microtime(true);
    $this->ip      = $_SERVER['REMOTE_ADDR'];
    $this->logging = $config->get('log_session', false);

    $lifetime = $config->get('session_lifetime', 1) * 60;
    $this->set_lifetime($lifetime);

    // use memcache backend
    if ($config->get('session_storage', 'db') == 'memcache') {
      $this->memcache = rcmail::get_instance()->get_memcache();

      // set custom functions for PHP session management if memcache is available
      if ($this->memcache) {
        session_set_save_handler(
          array($this, 'open'),
          array($this, 'close'),
          array($this, 'mc_read'),
          array($this, 'mc_write'),
          array($this, 'mc_destroy'),
          array($this, 'gc'));
      }
      else {
        raise_error(array('code' => 604, 'type' => 'db',
          'line' => __LINE__, 'file' => __FILE__,
          'message' => "Failed to connect to memcached. Please check configuration"),
          true, true);
      }
    }
    else {
      // set custom functions for PHP session management
      session_set_save_handler(
        array($this, 'open'),
        array($this, 'close'),
        array($this, 'db_read'),
        array($this, 'db_write'),
        array($this, 'db_destroy'),
        array($this, 'db_gc'));
      }
  }


  public function open($save_path, $session_name)
  {
    return true;
  }


  public function close()
  {
    return true;
  }


  /**
   * Delete session data for the given key
   *
   * @param string Session ID
   */
  public function destroy($key)
  {
    return $this->memcache ? $this->mc_destroy($key) : $this->db_destroy($key);
  }


  /**
   * Read session data from database
   *
   * @param string Session ID
   * @return string Session vars
   */
  public function db_read($key)
  {
    $sql_result = $this->db->query(
      "SELECT vars, ip, changed FROM ".get_table_name('session')
      ." WHERE sess_id = ?", $key);

    if ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result))) {
      $this->changed = strtotime($sql_arr['changed']);
      $this->ip      = $sql_arr['ip'];
      $this->vars    = base64_decode($sql_arr['vars']);
      $this->key     = $key;

      return !empty($this->vars) ? (string) $this->vars : '';
    }

    return null;
  }


  /**
   * Save session data.
   * handler for session_read()
   *
   * @param string Session ID
   * @param string Serialized session vars
   * @return boolean True on success
   */
  public function db_write($key, $vars)
  {
    $ts = microtime(true);
    $now = $this->db->fromunixtime((int)$ts);

    // no session row in DB (db_read() returns false)
    if (!$this->key) {
      $oldvars = null;
    }
    // use internal data from read() for fast requests (up to 0.5 sec.)
    else if ($key == $this->key && (!$this->vars || $ts - $this->start < 0.5)) {
      $oldvars = $this->vars;
    }
    else { // else read data again from DB
      $oldvars = $this->db_read($key);
    }

    if ($oldvars !== null) {
      $newvars = $this->_fixvars($vars, $oldvars);

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

    return true;
  }


  /**
   * Merge vars with old vars and apply unsets
   */
  private function _fixvars($vars, $oldvars)
  {
    if ($oldvars !== null) {
      $a_oldvars = $this->unserialize($oldvars);
      if (is_array($a_oldvars)) {
        foreach ((array)$this->unsets as $k)
          unset($a_oldvars[$k]);

        $newvars = $this->serialize(array_merge(
          (array)$a_oldvars, (array)$this->unserialize($vars)));
      }
      else
        $newvars = $vars;
    }

    $this->unsets = array();
    return $newvars;
  }


  /**
   * Handler for session_destroy()
   *
   * @param string Session ID
   *
   * @return boolean True on success
   */
  public function db_destroy($key)
  {
    if ($key) {
      $this->db->query(sprintf("DELETE FROM %s WHERE sess_id = ?", get_table_name('session')), $key);
    }

    return true;
  }


  /**
   * Garbage collecting function
   *
   * @param string Session lifetime in seconds
   * @return boolean True on success
   */
  public function db_gc($maxlifetime)
  {
    // just delete all expired sessions
    $this->db->query(
      sprintf("DELETE FROM %s WHERE changed < %s",
        get_table_name('session'), $this->db->fromunixtime(time() - $maxlifetime)));

    $this->gc();

    return true;
  }


  /**
   * Read session data from memcache
   *
   * @param string Session ID
   * @return string Session vars
   */
  public function mc_read($key)
  {
    if ($value = $this->memcache->get($key)) {
      $arr = unserialize($value);
      $this->changed = $arr['changed'];
      $this->ip      = $arr['ip'];
      $this->vars    = $arr['vars'];
      $this->key     = $key;

      return !empty($this->vars) ? (string) $this->vars : '';
    }

    return null;
  }


  /**
   * Save session data.
   * handler for session_read()
   *
   * @param string Session ID
   * @param string Serialized session vars
   * @return boolean True on success
   */
  public function mc_write($key, $vars)
  {
    $ts = microtime(true);

    // no session data in cache (mc_read() returns false)
    if (!$this->key)
      $oldvars = null;
    // use internal data for fast requests (up to 0.5 sec.)
    else if ($key == $this->key && (!$this->vars || $ts - $this->start < 0.5))
      $oldvars = $this->vars;
    else // else read data again
      $oldvars = $this->mc_read($key);

    $newvars = $oldvars !== null ? $this->_fixvars($vars, $oldvars) : $vars;

    if ($newvars !== $oldvars || $ts - $this->changed > $this->lifetime / 2)
      return $this->memcache->set($key, serialize(array('changed' => time(), 'ip' => $this->ip, 'vars' => $newvars)), MEMCACHE_COMPRESSED, $this->lifetime);

    return true;
  }


  /**
   * Handler for session_destroy() with memcache backend
   *
   * @param string Session ID
   *
   * @return boolean True on success
   */
  public function mc_destroy($key)
  {
    if ($key) {
      // #1488592: use 2nd argument
      $this->memcache->delete($key, 0);
    }

    return true;
  }


  /**
   * Execute registered garbage collector routines
   */
  public function gc()
  {
    foreach ($this->gc_handlers as $fct)
      call_user_func($fct);
  }


  /**
   * Register additional garbage collector functions
   *
   * @param mixed Callback function
   */
  public function register_gc_handler($func)
  {
    foreach ($this->gc_handlers as $handler) {
      if ($handler == $func) {
        return;
      }
    }

    $this->gc_handlers[] = $func;
  }


  /**
   * Generate and set new session id
   *
   * @param boolean $destroy If enabled the current session will be destroyed
   */
  public function regenerate_id($destroy=true)
  {
    session_regenerate_id($destroy);

    $this->vars = null;
    $this->key  = session_id();

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
    $this->vars = null;
    $this->ip = $_SERVER['REMOTE_ADDR']; // update IP (might have changed)
    $this->destroy(session_id());
    rcmail::setcookie($this->cookiename, '-del-', time() - 60);
  }


  /**
   * Re-read session data from storage backend
   */
  public function reload()
  {
    if ($this->key && $this->memcache)
      $data = $this->mc_read($this->key);
    else if ($this->key)
      $data = $this->db_read($this->key);

    if ($data)
     session_decode($data);
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

    if (!$result)
      $this->log("IP check failed for " . $this->key . "; expected " . $this->ip . "; got " . $_SERVER['REMOTE_ADDR']);

    if ($result && $this->_mkcookie($this->now) != $this->cookie) {
      $this->log("Session auth check failed for " . $this->key . "; timeslot = " . date('Y-m-d H:i:s', $this->now));
      $result = false;

      // Check if using id from a previous time slot
      for ($i = 1; $i <= 2; $i++) {
        $prev = $this->now - ($this->lifetime / 2) * $i;
        if ($this->_mkcookie($prev) == $this->cookie) {
          $this->log("Send new auth cookie for " . $this->key . ": " . $this->cookie);
          $this->set_auth_cookie();
          $result = true;
        }
      }
	}

    if (!$result)
      $this->log("Session authentication failed for " . $this->key . "; invalid auth cookie sent; timeslot = " . date('Y-m-d H:i:s', $prev));

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
  
  /**
   * 
   */
  function log($line)
  {
    if ($this->logging)
      write_log('session', $line);
  }

}

<?php

/*
 +-----------------------------------------------------------------------+
 | rcube_install.php                                                     |
 |                                                                       |
 | This file is part of the RoundCube Webmail package                    |
 | Copyright (C) 2008, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU Public License                                 |
 +-----------------------------------------------------------------------+

 $Id:  $

*/


/**
 * Class to control the installation process of the RoundCube Webmail package
 *
 * @category Install
 * @package  RoundCube
 * @author Thomas Bruederli
 */
class rcube_install
{
  var $step;
  var $is_post = false;
  var $failures = 0;
  var $config = array();
  var $configured = false;
  var $last_error = null;
  var $email_pattern = '([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9])';
  var $config_props = array();
  
  /**
   * Constructor
   */
  function rcube_install()
  {
    $this->step = intval($_REQUEST['_step']);
    $this->is_post = $_SERVER['REQUEST_METHOD'] == 'POST';
  }
  
  /**
   * Singleton getter
   */
  function get_instance()
  {
    static $inst;
    
    if (!$inst)
      $inst = new rcube_install();
    
    return $inst;
  }
  
  /**
   * Read the default config files and store properties
   */
  function load_defaults()
  {
    $this->_load_config('.php.dist');
  }


  /**
   * Read the local config files and store properties
   */
  function load_config()
  {
    $this->config = array();
    $this->_load_config('.php');
    $this->configured = !empty($this->config);
  }

  /**
   * Read the default config file and store properties
   * @access private
   */
  function _load_config($suffix)
  {
    @include '../config/main.inc' . $suffix;
    if (is_array($rcmail_config)) {
      $this->config += $rcmail_config;
    }
      
    @include '../config/db.inc'. $suffix;
    if (is_array($rcmail_config)) {
      $this->config += $rcmail_config;
    }
  }
  
  
  /**
   * Getter for a certain config property
   *
   * @param string Property name
   * @param string Default value
   * @return string The property value
   */
  function getprop($name, $default = '')
  {
    $value = $this->config[$name];
    
    if ($name == 'des_key' && !$this->configured && !isset($_REQUEST["_$name"]))
      $value = rcube_install::random_key(24);
    
    return $value !== null && $value !== '' ? $value : $default;
  }
  
  
  /**
   * Take the default config file and replace the parameters
   * with the submitted form data
   *
   * @param string Which config file (either 'main' or 'db')
   * @return string The complete config file content
   */
  function create_config($which)
  {
    $out = file_get_contents("../config/{$which}.inc.php.dist");
    
    if (!$out)
      return '[Warning: could not read the template file]';

    foreach ($this->config as $prop => $default) {
      $value = (isset($_POST["_$prop"]) || $this->config_props[$prop]) ? $_POST["_$prop"] : $default;
      
      // convert some form data
      if ($prop == 'debug_level') {
        $val = 0;
        if (isset($value))
	  foreach ($value as $dbgval)
            $val += intval($dbgval);
	$value = $val;
      }
      else if ($which == 'db' && $prop == 'db_dsnw' && !empty($_POST['_dbtype'])) {
        if ($_POST['_dbtype'] == 'sqlite')
          $value = sprintf('%s://%s?mode=0646', $_POST['_dbtype'], $_POST['_dbname']{0} == '/' ? '/' . $_POST['_dbname'] : $_POST['_dbname']);
        else
          $value = sprintf('%s://%s:%s@%s/%s', $_POST['_dbtype'], 
		    rawurlencode($_POST['_dbuser']), rawurlencode($_POST['_dbpass']),
		    $_POST['_dbhost'], $_POST['_dbname']);
      }
      else if ($prop == 'smtp_auth_type' && $value == '0') {
        $value = '';
      }
      else if ($prop == 'default_host' && is_array($value)) {
        $value = rcube_install::_clean_array($value);
        if (count($value) <= 1)
          $value = $value[0];
      }
      else if ($prop == 'pagesize') {
        $value = max(2, intval($value));
      }
      else if ($prop == 'smtp_user' && !empty($_POST['_smtp_user_u'])) {
        $value = '%u';
      }
      else if ($prop == 'smtp_pass' && !empty($_POST['_smtp_user_u'])) {
        $value = '%p';
      }
      else if (is_bool($default)) {
        $value = (bool)$value;
      }
      else if (is_numeric($value)) {
        $value = intval($value);
      }
      
      // skip this property
      if ($value == $default)
        continue;

      // save change
      $this->config[$prop] = $value;

      // replace the matching line in config file
      $out = preg_replace(
        '/(\$rcmail_config\[\''.preg_quote($prop).'\'\])\s+=\s+(.+);/Uie',
        "'\\1 = ' . var_export(\$value, true) . ';'",
        $out);
    }

    return trim($out);
  }
  
  
  /**
   * Getter for the last error message
   *
   * @return string Error message or null if none exists
   */
  function get_error()
  {
      return $this->last_error['message'];
  }
  
  
  /**
   * Return a list with all imap hosts configured
   *
   * @return array Clean list with imap hosts
   */
  function get_hostlist()
  {
    $default_hosts = (array)$this->getprop('default_host');
    $out = array();
    
    foreach ($default_hosts as $key => $name) {
      if (!empty($name))
        $out[] = is_numeric($key) ? $name : $key;
    }
    
    return $out;
  }
  
  
  /**
   * Display OK status
   *
   * @param string Test name
   * @param string Confirm message
   */
  function pass($name, $message = '')
  {
    echo Q($name) . ':&nbsp; <span class="success">OK</span>';
    $this->_showhint($message);
  }
  
  
  /**
   * Display an error status and increase failure count
   *
   * @param string Test name
   * @param string Error message
   * @param string URL for details
   */
  function fail($name, $message = '', $url = '')
  {
    $this->failures++;
    
    echo Q($name) . ':&nbsp; <span class="fail">NOT OK</span>';
    $this->_showhint($message, $url);
  }
  
  
  /**
   * Display warning status
   *
   * @param string Test name
   * @param string Warning message
   * @param string URL for details
   */
  function na($name, $message = '', $url = '')
  {
    echo Q($name) . ':&nbsp; <span class="na">NOT AVAILABLE</span>';
    $this->_showhint($message, $url);
  }
  
  
  function _showhint($message, $url = '')
  {
    $hint = Q($message);
    
    if ($url)
      $hint .= ($hint ? '; ' : '') . 'See <a href="' . Q($url) . '" target="_blank">' . Q($url) . '</a>';
      
    if ($hint)
      echo '<span class="indent">(' . $hint . ')</span>';
  }
  
  
  function _clean_array($arr)
  {
    $out = array();
    
    foreach (array_unique($arr) as $i => $val)
      if (!empty($val))
        $out[] = $val;
    
    return $out;
  }
  
  
  /**
   * Initialize the database with the according schema
   *
   * @param object rcube_db Database connection
   * @return boolen True on success, False on error
   */
  function init_db($DB)
  {
    $db_map = array('pgsql' => 'postgres', 'mysqli' => 'mysql');
    $engine = isset($db_map[$DB->db_provider]) ? $db_map[$DB->db_provider] : $DB->db_provider;
    
    // find out db version
    if ($engine == 'mysql') {
      $DB->query('SELECT VERSION() AS version');
      $sql_arr = $DB->fetch_assoc();
      $version = floatval($sql_arr['version']);
      
      if ($version >= 4.1)
        $engine = 'mysql5';
    }
    
    // read schema file from /SQL/*
    $fname = "../SQL/$engine.initial.sql";
    if ($lines = @file($fname, FILE_SKIP_EMPTY_LINES)) {
      $buff = '';
      foreach ($lines as $i => $line) {
        if (eregi('^--', $line))
          continue;
          
        $buff .= $line . "\n";
        if (eregi(';$', trim($line))) {
          $DB->query($buff);
          $buff = '';
          if ($this->get_error())
            break;
        }
      }
    }
    else {
      $this->fail('DB Schema', "Cannot read the schema file: $fname");
      return false;
    }
    
    if ($err = $this->get_error()) {
      $this->fail('DB Schema', "Error creating database schema: $err");
      return false;
    }

    return true;
  }
  
  /**
   * Handler for RoundCube errors
   */
  function raise_error($p)
  {
      $this->last_error = $p;
  }
  
  
  /**
   * Generarte a ramdom string to be used as encryption key
   *
   * @param int Key length
   * @return string The generated random string
   * @static
   */
  function random_key($length)
  {
    $alpha = 'ABCDEFGHIJKLMNOPQERSTUVXYZabcdefghijklmnopqrtsuvwxyz0123456789+*%&?!$-_=';
    $out = '';
    
    for ($i=0; $i < $length; $i++)
      $out .= $alpha{rand(0, strlen($alpha)-1)};
    
    return $out;
  }
  
}


/**
 * Shortcut function for htmlentities()
 *
 * @param string String to quote
 * @return string The html-encoded string
 */
function Q($string)
{
  return htmlentities($string);
}


/**
 * Fake rinternal error handler to catch errors
 */
function raise_error($p)
{
  $rci = rcube_install::get_instance();
  $rci->raise_error($p);
}


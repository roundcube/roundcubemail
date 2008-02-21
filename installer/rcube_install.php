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
  var $failures = 0;
  var $defaults = array();
  
  /**
   * Constructor
   */
  function rcube_install()
  {
    $this->step = intval($_REQUEST['_step']);
    $this->get_defaults();
  }
  
  
  /**
   * Read the default config file and store properties
   */
  function get_defaults()
  {
    $suffix = is_readable('../config/main.inc.php.dist') ? '.dist' : '';
    
    include '../config/main.inc.php' . $suffix;
    if (is_array($rcmail_config)) {
      $this->defaults = $rcmail_config;
    }
      
    include '../config/db.inc.php'. $suffix;
    if (is_array($rcmail_config)) {
      $this->defaults += $rcmail_config;
    }
  }
  
  
  /**
   * Getter for a certain config property
   *
   * @param string Property name
   * @return string The property value
   */
  function getprop($name)
  {
    $value = isset($_REQUEST["_$name"]) ? $_REQUEST["_$name"] : $this->defaults[$name];
    
    if ($name == 'des_key' && !isset($_REQUEST["_$name"]))
      $value = self::random_key(24);
    
    return $value;
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
    
    foreach ($this->defaults as $prop => $default) {
      $value = $_POST["_$prop"] ? $_POST["_$prop"] : $default;
      
      // skip this property
      if (!isset($_POST["_$prop"]) || $value == $default)
        continue;
      
      // convert some form data
      if ($prop == 'debug_level' && is_array($value)) {
        $val = 0;
        foreach ($value as $i => $dbgval)
          $val += intval($dbgval);
        $value = $val;
      }
      else if (is_bool($default))
        $value = is_numeric($value) ? (bool)$value : $value;
      
      // replace the matching line in config file
      $out = preg_replace(
        '/(\$rcmail_config\[\''.preg_quote($prop).'\'\])\s+=\s+(.+);/Uie',
        "'\\1 = ' . var_export(\$value, true) . ';'",
        $out);
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


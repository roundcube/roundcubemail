<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_config.php                                      |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2008, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Class to read configuration settings                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: $

*/

/**
 * Configuration class for RoundCube
 *
 * @package Core
 */
class rcube_config
{
  private $prop = array();


  /**
   * Object constructor
   */
  public function __construct()
  {
    $this->load();
  }


  /**
   * Load config from local config file
   *
   * @todo Remove global $CONFIG
   */
  private function load()
  {
    // start output buffering, we don't need any output yet, 
    // it'll be cleared after reading of config files, etc.
    ob_start();
    
    // load main config file
    include_once(INSTALL_PATH . 'config/main.inc.php');
    $this->prop = (array)$rcmail_config;

    // load database config
    include_once(INSTALL_PATH . 'config/db.inc.php');
    $this->prop += (array)$rcmail_config;
    
    // load host-specific configuration
    $this->load_host_config();

    // fix paths
    $this->prop['default_skin'] = $this->prop['default_skin'] ? unslashify($this->prop['default_skin']) : 'default';
    $this->prop['log_dir'] = $this->prop['log_dir'] ? unslashify($this->prop['log_dir']) : INSTALL_PATH . 'logs';
    
    // handle aliases
    if (isset($this->prop['locale_string']) && empty($this->prop['language']))
      $this->prop['language'] = $this->prop['locale_string'];

    // set PHP error logging according to config
    if ($this->prop['debug_level'] & 1) {
      ini_set('log_errors', 1);
      ini_set('error_log', $this->prop['log_dir'] . '/errors');
    }
    if ($this->prop['debug_level'] & 4) {
      ini_set('display_errors', 1);
    }
    else {
      ini_set('display_errors', 0);
    }
    
    // clear output buffer
    ob_end_clean();

    // export config data
    $GLOBALS['CONFIG'] = &$this->prop;
  }
  
  
  /**
   * Load a host-specific config file if configured
   * This will merge the host specific configuration with the given one
   */
  private function load_host_config()
  {
    $fname = null;

    if (is_array($this->prop['include_host_config'])) {
      $fname = $this->prop['include_host_config'][$_SERVER['HTTP_HOST']];
    }
    else if (!empty($this->prop['include_host_config'])) {
      $fname = preg_replace('/[^a-z0-9\.\-_]/i', '', $_SERVER['HTTP_HOST']) . '.inc.php';
    }

    if ($fname && is_file(INSTALL_PATH . 'config/' . $fname)) {
      include(INSTALL_PATH . 'config/' . $fname);
      $this->prop = array_merge($this->prop, (array)$rcmail_config);
    }
  }
  
  
  /**
   * Getter for a specific config parameter
   *
   * @param  string Parameter name
   * @param  mixed  Default value if not set
   * @return mixed  The requested config value
   */
  public function get($name, $def = null)
  {
    return isset($this->prop[$name]) ? $this->prop[$name] : $def;
  }
  
  
  /**
   * Setter for a config parameter
   *
   * @param string Parameter name
   * @param mixed  Parameter value
   */
  public function set($name, $value)
  {
    $this->prop[$name] = $value;
  }
  
  
  /**
   * Override config options with the given values (eg. user prefs)
   *
   * @param array Hash array with config props to merge over
   */
  public function merge($prefs)
  {
    $this->prop = array_merge($this->prop, $prefs);
  }
  
  
  /**
   * Getter for all config options
   *
   * @return array  Hash array containg all config properties
   */
  public function all()
  {
    return $this->prop;
  }
  
  
  /**
   * Return a 24 byte key for the DES encryption
   *
   * @return string DES encryption key
   */
  public function get_des_key()
  {
    $key = !empty($this->prop['des_key']) ? $this->prop['des_key'] : 'rcmail?24BitPwDkeyF**ECB';
    $len = strlen($key);

    // make sure the key is exactly 24 chars long
    if ($len<24)
      $key .= str_repeat('_', 24-$len);
    else if ($len>24)
      substr($key, 0, 24);

    return $key;
  }
  
  
  /**
   * Try to autodetect operating system and find the correct line endings
   *
   * @return string The appropriate mail header delimiter
   */
  public function header_delimiter()
  {
    // use the configured delimiter for headers
    if (!empty($this->prop['mail_header_delimiter']))
      return $this->prop['mail_header_delimiter'];
    else if (strtolower(substr(PHP_OS, 0, 3) == 'win'))
      return "\r\n";
    else if (strtolower(substr(PHP_OS, 0, 3) == 'mac'))
      return "\r\n";
    else
      return "\n";
  }

  
  
  /**
   * Return the mail domain configured for the given host
   *
   * @param string IMAP host
   * @return string Resolved SMTP host
   */
  public function mail_domain($host)
  {
    $domain = $host;
    
    if (is_array($this->prop['mail_domain'])) {
      if (isset($this->prop['mail_domain'][$host]))
        $domain = $this->prop['mail_domain'][$host];
    }
    else if (!empty($this->prop['mail_domain']))
      $domain = $this->prop['mail_domain'];
    
    return $domain;
  }


}


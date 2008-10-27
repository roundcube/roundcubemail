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
  var $bool_config_props = array();

  var $obsolete_config = array('db_backend');
  var $replaced_config = array('skin_path' => 'skin', 'locale_string' => 'language');
  
  // these config options are optional or can be set to null
  var $optional_config = array(
    'log_driver', 'syslog_id', 'syslog_facility', 'imap_auth_type',
    'smtp_helo_host', 'sendmail_delay', 'double_auth', 'language',
    'mail_header_delimiter', 'create_default_folders',
    'quota_zero_as_unlimited', 'spellcheck_uri', 'spellcheck_languages',
    'http_received_header', 'session_domain', 'mime_magic', 'log_logins',
    'enable_installer', 'skin_include_php');
  
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
    @include RCMAIL_CONFIG_DIR . '/main.inc' . $suffix;
    if (is_array($rcmail_config)) {
      $this->config += $rcmail_config;
    }
      
    @include RCMAIL_CONFIG_DIR . '/db.inc'. $suffix;
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
  function create_config($which, $force = false)
  {
    $out = file_get_contents(RCMAIL_CONFIG_DIR . "/{$which}.inc.php.dist");
    
    if (!$out)
      return '[Warning: could not read the template file]';

    foreach ($this->config as $prop => $default) {
      $value = (isset($_POST["_$prop"]) || $this->bool_config_props[$prop]) ? $_POST["_$prop"] : $default;
      
      // convert some form data
      if ($prop == 'debug_level') {
        $val = 0;
        if (is_array($value))
          foreach ($value as $dbgval)
            $val += intval($dbgval);
        $value = $val;
      }
      else if ($which == 'db' && $prop == 'db_dsnw' && !empty($_POST['_dbtype'])) {
        if ($_POST['_dbtype'] == 'sqlite')
          $value = sprintf('%s://%s?mode=0646', $_POST['_dbtype'], $_POST['_dbname']{0} == '/' ? '/' . $_POST['_dbname'] : $_POST['_dbname']);
        else
          $value = sprintf('%s://%s:%s@%s/%s', $_POST['_dbtype'], 
            rawurlencode($_POST['_dbuser']), rawurlencode($_POST['_dbpass']), $_POST['_dbhost'], $_POST['_dbname']);
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
      if (!$force && ($value == $default))
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
   * Check the current configuration for missing properties
   * and deprecated or obsolete settings
   *
   * @return array List with problems detected
   */
  function check_config()
  {
    $this->config = array();
    $this->load_defaults();
    $defaults = $this->config;
    
    $this->load_config();
    if (!$this->configured)
      return null;
    
    $out = $seen = array();
    $optional = array_flip($this->optional_config);
    
    // ireate over the current configuration
    foreach ($this->config as $prop => $value) {
      if ($replacement = $this->replaced_config[$prop]) {
        $out['replaced'][] = array('prop' => $prop, 'replacement' => $replacement);
        $seen[$replacement] = true;
      }
      else if (!$seen[$prop] && in_array($prop, $this->obsolete_config)) {
        $out['obsolete'][] = array('prop' => $prop);
        $seen[$prop] = true;
      }
    }
    
    // iterate over default config
    foreach ($defaults as $prop => $value) {
      if (!$seen[$prop] && !isset($this->config[$prop]) && !isset($optional[$prop]))
        $out['missing'][] = array('prop' => $prop);
    }
    
    // check config dependencies and contradictions
    if ($this->config['enable_spellcheck'] && $this->config['spellcheck_engine'] == 'pspell') {
      if (!extension_loaded('pspell')) {
        $out['dependencies'][] = array('prop' => 'spellcheck_engine',
          'explain' => 'This requires the <tt>pspell</tt> extension which could not be loaded.');
      }
      if (empty($this->config['spellcheck_languages'])) {
        $out['dependencies'][] = array('prop' => 'spellcheck_languages',
          'explain' => 'You should specify the list of languages supported by your local pspell installation.');
      }
    }
    
    if ($this->config['log_driver'] == 'syslog') {
      if (!function_exists('openlog')) {
        $out['dependencies'][] = array('prop' => 'log_driver',
          'explain' => 'This requires the <tt>sylog</tt> extension which could not be loaded.');
      }
      if (empty($this->config['syslog_id'])) {
        $out['dependencies'][] = array('prop' => 'syslog_id',
          'explain' => 'Using <tt>syslog</tt> for logging requires a syslog ID to be configured');
      }
      
    }
    
    return $out;
  }
  
  
  /**
   * Merge the current configuration with the defaults
   * and copy replaced values to the new options.
   */
  function merge_config()
  {
    $current = $this->config;
    $this->config = array();
    $this->load_defaults();
    
    foreach ($this->replaced_config as $prop => $replacement)
      if (isset($current[$prop])) {
        if ($prop == 'skin_path')
          $this->config[$replacement] = preg_replace('#skins/(\w+)/?$#', '\\1', $current[$prop]);
        else
          $this->config[$replacement] = $current[$prop];
        
        unset($current[$prop]);
    }
    
    foreach ($this->obsolete_config as $prop) {
      unset($current[$prop]);
    }
    
    $this->config  = array_merge($current, $this->config);
  }
  
  
  /**
   * Compare the local database schema with the reference schema
   * required for this version of RoundCube
   *
   * @param boolean True if the schema schould be updated
   * @return boolean True if the schema is up-to-date, false if not or an error occured
   */
  function db_schema_check($update = false)
  {
    if (!$this->configured)
      return false;
    
    $options = array(
      'use_transactions' => false,
      'log_line_break' => "\n",
      'idxname_format' => '%s',
      'debug' => false,
      'quote_identifier' => true,
      'force_defaults' => false,
      'portability' => true
    );
    
    $schema =& MDB2_Schema::factory($this->config['db_dsnw'], $options);
    $schema->db->supported['transactions'] = false;
    
    if (PEAR::isError($schema)) {
      $this->raise_error(array('code' => $schema->getCode(), 'message' => $schema->getMessage() . ' ' . $schema->getUserInfo()));
      return false;
    }
    else {
      $definition = $schema->getDefinitionFromDatabase();
      $definition['charset'] = 'utf8';
      
      if (PEAR::isError($definition)) {
        $this->raise_error(array('code' => $definition->getCode(), 'message' => $definition->getMessage() . ' ' . $definition->getUserInfo()));
        return false;
      }
      
      // load reference schema
      $dsn = MDB2::parseDSN($this->config['db_dsnw']);
      $ref_schema = INSTALL_PATH . 'SQL/' . $dsn['phptype'] . '.schema.xml';
      
      if (is_file($ref_schema)) {
        $reference = $schema->parseDatabaseDefinition($ref_schema, false, array(), $schema->options['fail_on_invalid_names']);
        
        if (PEAR::isError($reference)) {
          $this->raise_error(array('code' => $reference->getCode(), 'message' => $reference->getMessage() . ' ' . $reference->getUserInfo()));
        }
        else {
          $diff = $schema->compareDefinitions($reference, $definition);
          
          if (empty($diff)) {
            return true;
          }
          else if ($update) {
            // update database schema with the diff from the above check
            $success = $schema->alterDatabase($reference, $definition, $diff);
            
            if (PEAR::isError($success)) {
              $this->raise_error(array('code' => $success->getCode(), 'message' => $success->getMessage() . ' ' . $success->getUserInfo()));
            }
            else
              return true;
          }
          echo '<pre>'; var_dump($diff); echo '</pre>';
          return false;
        }
      }
      else
        $this->raise_error(array('message' => "Could not find reference schema file ($ref_schema)"));
        return false;
    }
    
    return false;
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


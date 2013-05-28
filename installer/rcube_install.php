<?php

/*
 +-----------------------------------------------------------------------+
 | rcube_install.php                                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail package                    |
 | Copyright (C) 2008-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
*/


/**
 * Class to control the installation process of the Roundcube Webmail package
 *
 * @category Install
 * @package  Roundcube
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

  var $obsolete_config = array('db_backend', 'double_auth');
  var $replaced_config = array(
    'skin_path'            => 'skin',
    'locale_string'        => 'language',
    'multiple_identities'  => 'identities_level',
    'addrbook_show_images' => 'show_images',
    'imap_root'            => 'imap_ns_personal',
    'pagesize'             => 'mail_pagesize',
    'default_imap_folders' => 'default_folders',
    'top_posting'          => 'reply_mode',
  );

  // these config options are required for a working system
  var $required_config = array(
    'db_dsnw', 'des_key', 'session_lifetime',
  );

  // list of supported database drivers
  var $supported_dbs = array(
    'MySQL'               => 'pdo_mysql',
    'PostgreSQL'          => 'pdo_pgsql',
    'SQLite'              => 'pdo_sqlite',
    'SQLite (v2)'         => 'pdo_sqlite2',
    'SQL Server (SQLSRV)' => 'pdo_sqlsrv',
    'SQL Server (DBLIB)'  => 'pdo_dblib',
  );


  /**
   * Constructor
   */
  function __construct()
  {
    $this->step = intval($_REQUEST['_step']);
    $this->is_post = $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  /**
   * Singleton getter
   */
  static function get_instance()
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
    if (is_readable($main_inc = RCUBE_CONFIG_DIR . 'main.inc' . $suffix)) {
      include($main_inc);
      if (is_array($rcmail_config))
        $this->config += $rcmail_config;
    }
    if (is_readable($db_inc = RCUBE_CONFIG_DIR . 'db.inc'. $suffix)) {
      include($db_inc);
      if (is_array($rcmail_config))
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
    $out = @file_get_contents(RCUBE_CONFIG_DIR . $which . '.inc.php.dist');

    if (!$out)
      return '[Warning: could not read the config template file]';

    foreach ($this->config as $prop => $default) {

      $is_default = !isset($_POST["_$prop"]);
      $value      = !$is_default || $this->bool_config_props[$prop] ? $_POST["_$prop"] : $default;

      // convert some form data
      if ($prop == 'debug_level' && !$is_default) {
        if (is_array($value)) {
          $val = 0;
          foreach ($value as $dbgval)
            $val += intval($dbgval);
          $value = $val;
        }
      }
      else if ($which == 'db' && $prop == 'db_dsnw' && !empty($_POST['_dbtype'])) {
        if ($_POST['_dbtype'] == 'sqlite')
          $value = sprintf('%s://%s?mode=0646', $_POST['_dbtype'], $_POST['_dbname']{0} == '/' ? '/' . $_POST['_dbname'] : $_POST['_dbname']);
        else if ($_POST['_dbtype'])
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
      else if ($prop == 'mail_pagesize' || $prop == 'addressbook_pagesize') {
        $value = max(2, intval($value));
      }
      else if ($prop == 'smtp_user' && !empty($_POST['_smtp_user_u'])) {
        $value = '%u';
      }
      else if ($prop == 'smtp_pass' && !empty($_POST['_smtp_user_u'])) {
        $value = '%p';
      }
      else if ($prop == 'default_folders') {
        $value = array();
        foreach ($this->config['default_folders'] as $_folder) {
          switch ($_folder) {
          case 'Drafts': $_folder = $this->config['drafts_mbox']; break;
          case 'Sent':   $_folder = $this->config['sent_mbox']; break;
          case 'Junk':   $_folder = $this->config['junk_mbox']; break;
          case 'Trash':  $_folder = $this->config['trash_mbox']; break;
          }
        if (!in_array($_folder, $value))
          $value[] = $_folder;
        }
      }
      else if (is_bool($default)) {
        $value = (bool)$value;
      }
      else if (is_numeric($value)) {
        $value = intval($value);
      }

      // skip this property
      if (!$force && !$this->configured && ($value == $default))
        continue;

      // save change
      $this->config[$prop] = $value;

      // replace the matching line in config file
      $out = preg_replace(
        '/(\$rcmail_config\[\''.preg_quote($prop).'\'\])\s+=\s+(.+);/Uie',
        "'\\1 = ' . rcube_install::_dump_var(\$value, \$prop) . ';'",
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
    $required = array_flip($this->required_config);

    // iterate over the current configuration
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

    // the old default mime_magic reference is obsolete
    if ($this->config['mime_magic'] == '/usr/share/misc/magic') {
        $out['obsolete'][] = array('prop' => 'mime_magic', 'explain' => "Set value to null in order to use system default");
    }

    // iterate over default config
    foreach ($defaults as $prop => $value) {
      if (!isset($seen[$prop]) && isset($required[$prop]) && !(is_bool($this->config[$prop]) || strlen($this->config[$prop])))
        $out['missing'][] = array('prop' => $prop);
    }

    // check config dependencies and contradictions
    if ($this->config['enable_spellcheck'] && $this->config['spellcheck_engine'] == 'pspell') {
      if (!extension_loaded('pspell')) {
        $out['dependencies'][] = array('prop' => 'spellcheck_engine',
          'explain' => 'This requires the <tt>pspell</tt> extension which could not be loaded.');
      }
      else if (!empty($this->config['spellcheck_languages'])) {
        foreach ($this->config['spellcheck_languages'] as $lang => $descr)
          if (!@pspell_new($lang))
            $out['dependencies'][] = array('prop' => 'spellcheck_languages',
              'explain' => "You are missing pspell support for language $lang ($descr)");
      }
    }

    if ($this->config['log_driver'] == 'syslog') {
      if (!function_exists('openlog')) {
        $out['dependencies'][] = array('prop' => 'log_driver',
          'explain' => 'This requires the <tt>syslog</tt> extension which could not be loaded.');
      }
      if (empty($this->config['syslog_id'])) {
        $out['dependencies'][] = array('prop' => 'syslog_id',
          'explain' => 'Using <tt>syslog</tt> for logging requires a syslog ID to be configured');
      }
    }

    // check ldap_public sources having global_search enabled
    if (is_array($this->config['ldap_public']) && !is_array($this->config['autocomplete_addressbooks'])) {
      foreach ($this->config['ldap_public'] as $ldap_public) {
        if ($ldap_public['global_search']) {
          $out['replaced'][] = array('prop' => 'ldap_public::global_search', 'replacement' => 'autocomplete_addressbooks');
          break;
        }
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

    foreach ($this->replaced_config as $prop => $replacement) {
      if (isset($current[$prop])) {
        if ($prop == 'skin_path')
          $this->config[$replacement] = preg_replace('#skins/(\w+)/?$#', '\\1', $current[$prop]);
        else if ($prop == 'multiple_identities')
          $this->config[$replacement] = $current[$prop] ? 2 : 0;
        else
          $this->config[$replacement] = $current[$prop];
      }
      unset($current[$prop]);
    }

    foreach ($this->obsolete_config as $prop) {
      unset($current[$prop]);
    }

    // add all ldap_public sources having global_search enabled to autocomplete_addressbooks
    if (is_array($current['ldap_public'])) {
      foreach ($current['ldap_public'] as $key => $ldap_public) {
        if ($ldap_public['global_search']) {
          $this->config['autocomplete_addressbooks'][] = $key;
          unset($current['ldap_public'][$key]['global_search']);
        }
      }
    }

    $this->config  = array_merge($this->config, $current);

    foreach (array_keys((array)$current['ldap_public']) as $key) {
      $this->config['ldap_public'][$key] = $current['ldap_public'][$key];
    }
  }

  /**
   * Compare the local database schema with the reference schema
   * required for this version of Roundcube
   *
   * @param rcube_db Database object
   *
   * @return boolean True if the schema is up-to-date, false if not or an error occured
   */
  function db_schema_check($DB)
  {
    if (!$this->configured)
      return false;

    // read reference schema from mysql.initial.sql
    $db_schema = $this->db_read_schema(INSTALL_PATH . 'SQL/mysql.initial.sql');
    $errors = array();

    // check list of tables
    $existing_tables = $DB->list_tables();

    foreach ($db_schema as $table => $cols) {
      $table = $this->config['db_prefix'] . $table;
      if (!in_array($table, $existing_tables)) {
        $errors[] = "Missing table '".$table."'";
      }
      else {  // compare cols
        $db_cols = $DB->list_cols($table);
        $diff = array_diff(array_keys($cols), $db_cols);
        if (!empty($diff))
          $errors[] = "Missing columns in table '$table': " . join(',', $diff);
      }
    }

    return !empty($errors) ? $errors : false;
  }

  /**
   * Utility function to read database schema from an .sql file
   */
  private function db_read_schema($schemafile)
  {
    $lines = file($schemafile);
    $table_block = false;
    $schema = array();
    foreach ($lines as $line) {
      if (preg_match('/^\s*create table `?([a-z0-9_]+)`?/i', $line, $m)) {
        $table_block = $m[1];
      }
      else if ($table_block && preg_match('/^\s*`?([a-z0-9_-]+)`?\s+([a-z]+)/', $line, $m)) {
        $col = $m[1];
        if (!in_array(strtoupper($col), array('PRIMARY','KEY','INDEX','UNIQUE','CONSTRAINT','REFERENCES','FOREIGN'))) {
          $schema[$table_block][$col] = $m[2];
        }
      }
    }

    return $schema;
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
        $out[] = rcube_parse_host(is_numeric($key) ? $name : $key);
    }

    return $out;
  }

  /**
   * Create a HTML dropdown to select a previous version of Roundcube
   */
  function versions_select($attrib = array())
  {
    $select = new html_select($attrib);
    $select->add(array(
        '0.1-stable', '0.1.1',
        '0.2-alpha', '0.2-beta', '0.2-stable',
        '0.3-stable', '0.3.1',
        '0.4-beta', '0.4.2',
        '0.5-beta', '0.5', '0.5.1', '0.5.2', '0.5.3', '0.5.4',
        '0.6-beta', '0.6',
        '0.7-beta', '0.7', '0.7.1', '0.7.2', '0.7.3', '0.7.4',
        '0.8-beta', '0.8-rc', '0.8.0', '0.8.1', '0.8.2', '0.8.3', '0.8.4', '0.8.5', '0.8.6',
        '0.9-beta', '0.9-rc', '0.9-rc2', '0.9.0', '0.9.1'
    ));
    return $select;
  }

  /**
   * Return a list with available subfolders of the skin directory
   */
  function list_skins()
  {
    $skins = array();
    $skindir = INSTALL_PATH . 'skins/';
    foreach (glob($skindir . '*') as $path) {
      if (is_dir($path) && is_readable($path)) {
        $skins[] = substr($path, strlen($skindir));
      }
    }
    return $skins;
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
   * Display an error status for optional settings/features
   *
   * @param string Test name
   * @param string Error message
   * @param string URL for details
   */
  function optfail($name, $message = '', $url = '')
  {
    echo Q($name) . ':&nbsp; <span class="na">NOT OK</span>';
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


  static function _clean_array($arr)
  {
    $out = array();

    foreach (array_unique($arr) as $k => $val) {
      if (!empty($val)) {
        if (is_numeric($k))
          $out[] = $val;
        else
          $out[$k] = $val;
      }
    }

    return $out;
  }


  static function _dump_var($var, $name=null) {
    // special values
    switch ($name) {
    case 'syslog_facility':
      $list = array(32 => 'LOG_AUTH', 80 => 'LOG_AUTHPRIV', 72 => ' LOG_CRON',
                    24 => 'LOG_DAEMON', 0 => 'LOG_KERN', 128 => 'LOG_LOCAL0',
                    136 => 'LOG_LOCAL1', 144 => 'LOG_LOCAL2', 152 => 'LOG_LOCAL3',
                    160 => 'LOG_LOCAL4', 168 => 'LOG_LOCAL5', 176 => 'LOG_LOCAL6',
                    184 => 'LOG_LOCAL7', 48 => 'LOG_LPR', 16 => 'LOG_MAIL',
                    56 => 'LOG_NEWS', 40 => 'LOG_SYSLOG', 8 => 'LOG_USER', 64 => 'LOG_UUCP');
      if ($val = $list[$var])
        return $val;
      break;
    }


    if (is_array($var)) {
      if (empty($var)) {
        return 'array()';
      }
      else {  // check if all keys are numeric
        $isnum = true;
        foreach (array_keys($var) as $key) {
          if (!is_numeric($key)) {
            $isnum = false;
            break;
          }
        }

        if ($isnum)
          return 'array(' . join(', ', array_map(array('rcube_install', '_dump_var'), $var)) . ')';
      }
    }

    return var_export($var, true);
  }


  /**
   * Initialize the database with the according schema
   *
   * @param object rcube_db Database connection
   * @return boolen True on success, False on error
   */
  function init_db($DB)
  {
    $engine = $DB->db_provider;

    // read schema file from /SQL/*
    $fname = INSTALL_PATH . "SQL/$engine.initial.sql";
    if ($sql = @file_get_contents($fname)) {
      $this->exec_sql($sql, $DB);
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
   * Update database schema
   *
   * @param string Version to update from
   *
   * @return boolen True on success, False on error
   */
  function update_db($version)
  {
    system(INSTALL_PATH . "bin/updatedb.sh --package=roundcube"
      . " --version=" . escapeshellarg($version)
      . " --dir=" . INSTALL_PATH . "SQL"
      . " 2>&1", $result);

    return !$result;
  }


  /**
   * Execute the given SQL queries on the database connection
   *
   * @param string SQL queries to execute
   * @param object rcube_db Database connection
   * @return boolen True on success, False on error
   */
  function exec_sql($sql, $DB)
  {
    $sql = $this->fix_table_names($sql, $DB);
    $buff = '';
    foreach (explode("\n", $sql) as $line) {
      if (preg_match('/^--/', $line) || trim($line) == '')
        continue;

      $buff .= $line . "\n";
      if (preg_match('/(;|^GO)$/', trim($line))) {
        $DB->query($buff);
        $buff = '';
        if ($DB->is_error())
          break;
      }
    }

    return !$DB->is_error();
  }


  /**
   * Parse SQL file and fix table names according to db_prefix
   * Note: This need to be a complete database initial file
   */
  private function fix_table_names($sql, $DB)
  {
    if (empty($this->config['db_prefix'])) {
        return $sql;
    }

    // replace table names
    if (preg_match_all('/CREATE TABLE (\[dbo\]\.|IF NOT EXISTS )?[`"\[\]]*([^`"\[\] \r\n]+)/i', $sql, $matches)) {
      foreach ($matches[2] as $table) {
        $real_table = $this->config['db_prefix'] . $table;
        $sql = preg_replace("/([^a-zA-Z0-9_])$table([^a-zA-Z0-9_])/", "\\1$real_table\\2", $sql);
      }
    }
    // replace sequence names
    if ($DB->db_provider == 'postgres' && preg_match_all('/CREATE SEQUENCE (IF NOT EXISTS )?"?([^" \n\r]+)/i', $sql, $matches)) {
      foreach ($matches[2] as $sequence) {
        $real_sequence = $this->config['db_prefix'] . $sequence;
        $sql = preg_replace("/([^a-zA-Z0-9_])$sequence([^a-zA-Z0-9_])/", "\\1$real_sequence\\2", $sql);
      }
    }

    return $sql;
  }


  /**
   * Handler for Roundcube errors
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


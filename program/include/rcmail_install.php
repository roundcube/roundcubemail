<?php

/*
 +-----------------------------------------------------------------------+
 | rcmail_install.php                                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail package                    |
 | Copyright (C) 2008-2014, The Roundcube Dev Team                       |
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
class rcmail_install
{
  var $step;
  var $is_post = false;
  var $failures = 0;
  var $config = array();
  var $configured = false;
  var $legacy_config = false;
  var $last_error = null;
  var $email_pattern = '([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9])';
  var $bool_config_props = array();

  var $local_config = array('db_dsnw', 'default_host', 'support_url', 'des_key', 'plugins');
  var $obsolete_config = array('db_backend', 'db_max_length', 'double_auth');
  var $replaced_config = array(
    'skin_path'            => 'skin',
    'locale_string'        => 'language',
    'multiple_identities'  => 'identities_level',
    'addrbook_show_images' => 'show_images',
    'imap_root'            => 'imap_ns_personal',
    'pagesize'             => 'mail_pagesize',
    'top_posting'          => 'reply_mode',
    'keep_alive'           => 'refresh_interval',
    'min_keep_alive'       => 'min_refresh_interval',
  );

  // list of supported database drivers
  var $supported_dbs = array(
    'MySQL'               => 'pdo_mysql',
    'PostgreSQL'          => 'pdo_pgsql',
    'SQLite'              => 'pdo_sqlite',
    'SQLite (v2)'         => 'pdo_sqlite2',
    'SQL Server (SQLSRV)' => 'pdo_sqlsrv',
    'SQL Server (DBLIB)'  => 'pdo_dblib',
    'Oracle'              => 'oci8',
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
      $inst = new rcmail_install();

    return $inst;
  }

  /**
   * Read the local config files and store properties
   */
  function load_config()
  {
    // defaults
    if ($config = $this->load_config_file(RCUBE_CONFIG_DIR . 'defaults.inc.php')) {
        $this->config = (array) $config;
        $this->defaults = $this->config;
    }

    $config = null;

    // config
    if ($config = $this->load_config_file(RCUBE_CONFIG_DIR . 'config.inc.php')) {
        $this->config = array_merge($this->config, $config);
    }
    else {
      if ($config = $this->load_config_file(RCUBE_CONFIG_DIR . 'main.inc.php')) {
        $this->config = array_merge($this->config, $config);
        $this->legacy_config = true;
      }
      if ($config = $this->load_config_file(RCUBE_CONFIG_DIR . 'db.inc.php')) {
        $this->config = array_merge($this->config, $config);
        $this->legacy_config = true;
      }
    }

    $this->configured = !empty($config);
  }

  /**
   * Read the default config file and store properties
   */
  public function load_config_file($file)
  {
    if (is_readable($file)) {
      include $file;

      // read comments from config file
      if (function_exists('token_get_all')) {
        $tokens = token_get_all(file_get_contents($file));
        $in_config = false;
        $buffer = '';
        for ($i=0; $i < count($tokens); $i++) {
          $token = $tokens[$i];
          if ($token[0] == T_VARIABLE && $token[1] == '$config' || $token[1] == '$rcmail_config') {
            $in_config = true;
            if ($buffer && $tokens[$i+1] == '[' && $tokens[$i+2][0] == T_CONSTANT_ENCAPSED_STRING) {
              $propname = trim($tokens[$i+2][1], "'\"");
              $this->comments[$propname] = $buffer;
              $buffer = '';
              $i += 3;
            }
          }
          else if ($in_config && $token[0] == T_COMMENT) {
            $buffer .= strtr($token[1], array('\n' => "\n"));
          }
        }
      }

      // deprecated name of config variable
      if (is_array($rcmail_config)) {
        return $rcmail_config;
      }

      return $config;
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
      $value = self::random_key(24);

    return $value !== null && $value !== '' ? $value : $default;
  }


  /**
   * Create configuration file that contains parameters
   * that differ from default values.
   *
   * @return string The complete config file content
   */
  function create_config()
  {
    $config = array();

    foreach ($this->config as $prop => $default) {
      $is_default = !isset($_POST["_$prop"]);
      $value      = !$is_default || $this->bool_config_props[$prop] ? $_POST["_$prop"] : $default;

      // always disable installer
      if ($prop == 'enable_installer')
        $value = false;

      // reset useragent to default (keeps version up-to-date)
      if ($prop == 'useragent' && stripos($value, 'Roundcube Webmail/') !== false)
        $value = $this->defaults[$prop];

      // generate new encryption key, never use the default value
      if ($prop == 'des_key' && $value == $this->defaults[$prop])
        $value = $this->random_key(24);

      // convert some form data
      if ($prop == 'debug_level' && !$is_default) {
        if (is_array($value)) {
          $val = 0;
          foreach ($value as $dbgval)
            $val += intval($dbgval);
          $value = $val;
        }
      }
      else if ($prop == 'db_dsnw' && !empty($_POST['_dbtype'])) {
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
        $value = self::_clean_array($value);
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
      else if (is_bool($default)) {
        $value = (bool)$value;
      }
      else if (is_numeric($value)) {
        $value = intval($value);
      }

      // skip this property
      if (($value == $this->defaults[$prop]) && !in_array($prop, $this->local_config)
          || in_array($prop, array_merge($this->obsolete_config, array_keys($this->replaced_config)))
          || preg_match('/^db_(table|sequence)_/', $prop)) {
        continue;
      }

      // save change
      $this->config[$prop] = $value;
      $config[$prop] = $value;
    }

    $out = "<?php\n\n";
    $out .= "/* Local configuration for Roundcube Webmail */\n\n";
    foreach ($config as $prop => $value) {
      // copy option descriptions from existing config or defaults.inc.php
      $out .= $this->comments[$prop];
      $out .= "\$config['$prop'] = " . self::_dump_var($value, $prop) . ";\n\n";
    }

    return $out;
  }


  /**
   * save generated config file in RCUBE_CONFIG_DIR
   *
   * @return boolean True if the file was saved successfully, false if not
   */
  function save_configfile($config)
  {
    if (is_writable(RCUBE_CONFIG_DIR)) {
      return file_put_contents(RCUBE_CONFIG_DIR . 'config.inc.php', $config);
    }

    return false;
  }

  /**
   * Check the current configuration for missing properties
   * and deprecated or obsolete settings
   *
   * @return array List with problems detected
   */
  function check_config()
  {
    $this->load_config();

    if (!$this->configured) {
      return null;
    }

    $out = $seen = array();

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

    $this->config = array_merge($this->config, $current);

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
   * @return boolean True if the schema is up-to-date, false if not or an error occurred
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
   * Try to detect some file's mimetypes to test the correct behavior of fileinfo
   */
  function check_mime_detection()
  {
    $files = array(
      'skins/larry/images/roundcube_logo.png' => 'image/png',
      'program/resources/blank.tif' => 'image/tiff',
      'program/resources/blocked.gif' => 'image/gif',
      'skins/larry/README' => 'text/plain',
    );

    $errors = array();
    foreach ($files as $path => $expected) {
      $mimetype = rcube_mime::file_content_type(INSTALL_PATH . $path, basename($path));
      if ($mimetype != $expected) {
        $errors[] = array($path, $mimetype, $expected);
      }
    }

    return $errors;
  }

  /**
   * Check the correct configuration of the 'mime_types' mapping option
   */
  function check_mime_extensions()
  {
    $types = array(
      'application/zip'   => 'zip',
      'application/x-tar' => 'tar',
      'application/pdf'   => 'pdf',
      'image/gif'     => 'gif',
      'image/svg+xml' => 'svg',
    );

    $errors = array();
    foreach ($types as $mimetype => $expected) {
      $ext = rcube_mime::get_mime_extensions($mimetype);
      if (!in_array($expected, (array) $ext)) {
        $errors[] = array($mimetype, $ext, $expected);
      }
    }

    return $errors;
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
        '0.9-beta', '0.9-rc', '0.9-rc2',
        // Note: Do not add newer versions here
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
   * @param bool   Do not count this failure
   */
  function fail($name, $message = '', $url = '', $optional=false)
  {
    if (!$optional) {
      $this->failures++;
    }

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


  static function _dump_var($var, $name=null)
  {
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

    case 'mail_header_delimiter':
      $var = str_replace(array("\r", "\n"), array('\r', '\n'), $var);
      return '"' . $var. '"';
      break;
/*
    // RCMAIL_VERSION is undefined here
    case 'useragent':
      if (preg_match('|^(.*)/('.preg_quote(RCMAIL_VERSION, '|').')$|i', $var, $m)) {
        return '"' . addcslashes($var, '"') . '/" . RCMAIL_VERSION';
      }
      break;
*/
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
          return 'array(' . join(', ', array_map(array('rcmail_install', '_dump_var'), $var)) . ')';
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
      $DB->set_option('table_prefix', $this->config['db_prefix']);
      $DB->exec_script($sql);
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


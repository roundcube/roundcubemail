<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcmail.php                                            |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2011, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Application class providing core functions and holding              |
 |   instances of all 'global' objects like db- and imap-connections     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Application class of Roundcube Webmail
 * implemented as singleton
 *
 * @package Core
 */
class rcmail
{
  /**
   * Main tasks.
   *
   * @var array
   */
  static public $main_tasks = array('mail','settings','addressbook','login','logout','utils','dummy');

  /**
   * Singleton instace of rcmail
   *
   * @var rcmail
   */
  static private $instance;

  /**
   * Stores instance of rcube_config.
   *
   * @var rcube_config
   */
  public $config;

  /**
   * Stores rcube_user instance.
   *
   * @var rcube_user
   */
  public $user;

  /**
   * Instace of database class.
   *
   * @var rcube_mdb2
   */
  public $db;

  /**
   * Instace of Memcache class.
   *
   * @var rcube_mdb2
   */
  public $memcache;

  /**
   * Instace of rcube_session class.
   *
   * @var rcube_session
   */
  public $session;

  /**
   * Instance of rcube_smtp class.
   *
   * @var rcube_smtp
   */
  public $smtp;

  /**
   * Instance of rcube_imap class.
   *
   * @var rcube_imap
   */
  public $imap;

  /**
   * Instance of rcube_template class.
   *
   * @var rcube_template
   */
  public $output;

  /**
   * Instance of rcube_plugin_api.
   *
   * @var rcube_plugin_api
   */
  public $plugins;

  /**
   * Current task.
   *
   * @var string
   */
  public $task;

  /**
   * Current action.
   *
   * @var string
   */
  public $action = '';
  public $comm_path = './';

  private $texts;
  private $address_books = array();
  private $caches = array();
  private $action_map = array();
  private $shutdown_functions = array();


  /**
   * This implements the 'singleton' design pattern
   *
   * @return rcmail The one and only instance
   */
  static function get_instance()
  {
    if (!self::$instance) {
      self::$instance = new rcmail();
      self::$instance->startup();  // init AFTER object was linked with self::$instance
    }

    return self::$instance;
  }


  /**
   * Private constructor
   */
  private function __construct()
  {
    // load configuration
    $this->config = new rcube_config();

    register_shutdown_function(array($this, 'shutdown'));
  }


  /**
   * Initial startup function
   * to register session, create database and imap connections
   *
   * @todo Remove global vars $DB, $USER
   */
  private function startup()
  {
    // initialize syslog
    if ($this->config->get('log_driver') == 'syslog') {
      $syslog_id = $this->config->get('syslog_id', 'roundcube');
      $syslog_facility = $this->config->get('syslog_facility', LOG_USER);
      openlog($syslog_id, LOG_ODELAY, $syslog_facility);
    }

    // connect to database
    $GLOBALS['DB'] = $this->get_dbh();

    // start session
    $this->session_init();

    // create user object
    $this->set_user(new rcube_user($_SESSION['user_id']));

    // configure session (after user config merge!)
    $this->session_configure();

    // set task and action properties
    $this->set_task(get_input_value('_task', RCUBE_INPUT_GPC));
    $this->action = asciiwords(get_input_value('_action', RCUBE_INPUT_GPC));

    // reset some session parameters when changing task
    if ($this->task != 'utils') {
      if ($this->session && $_SESSION['task'] != $this->task)
        $this->session->remove('page');
      // set current task to session
      $_SESSION['task'] = $this->task;
    }

    // init output class
    if (!empty($_REQUEST['_remote']))
      $GLOBALS['OUTPUT'] = $this->json_init();
    else
      $GLOBALS['OUTPUT'] = $this->load_gui(!empty($_REQUEST['_framed']));

    // create plugin API and load plugins
    $this->plugins = rcube_plugin_api::get_instance();

    // init plugins
    $this->plugins->init();
  }


  /**
   * Setter for application task
   *
   * @param string Task to set
   */
  public function set_task($task)
  {
    $task = asciiwords($task);

    if ($this->user && $this->user->ID)
      $task = !$task ? 'mail' : $task;
    else
      $task = 'login';

    $this->task = $task;
    $this->comm_path = $this->url(array('task' => $this->task));

    if ($this->output)
      $this->output->set_env('task', $this->task);
  }


  /**
   * Setter for system user object
   *
   * @param rcube_user Current user instance
   */
  public function set_user($user)
  {
    if (is_object($user)) {
      $this->user = $user;
      $GLOBALS['USER'] = $this->user;

      // overwrite config with user preferences
      $this->config->set_user_prefs((array)$this->user->get_prefs());
    }

    $_SESSION['language'] = $this->user->language = $this->language_prop($this->config->get('language', $_SESSION['language']));

    // set localization
    setlocale(LC_ALL, $_SESSION['language'] . '.utf8', 'en_US.utf8');

    // workaround for http://bugs.php.net/bug.php?id=18556
    if (in_array($_SESSION['language'], array('tr_TR', 'ku', 'az_AZ')))
      setlocale(LC_CTYPE, 'en_US' . '.utf8');
  }


  /**
   * Check the given string and return a valid language code
   *
   * @param string Language code
   * @return string Valid language code
   */
  private function language_prop($lang)
  {
    static $rcube_languages, $rcube_language_aliases;

    // user HTTP_ACCEPT_LANGUAGE if no language is specified
    if (empty($lang) || $lang == 'auto') {
       $accept_langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
       $lang = str_replace('-', '_', $accept_langs[0]);
     }

    if (empty($rcube_languages)) {
      @include(INSTALL_PATH . 'program/localization/index.inc');
    }

    // check if we have an alias for that language
    if (!isset($rcube_languages[$lang]) && isset($rcube_language_aliases[$lang])) {
      $lang = $rcube_language_aliases[$lang];
    }
    // try the first two chars
    else if (!isset($rcube_languages[$lang])) {
      $short = substr($lang, 0, 2);

      // check if we have an alias for the short language code
      if (!isset($rcube_languages[$short]) && isset($rcube_language_aliases[$short])) {
        $lang = $rcube_language_aliases[$short];
      }
      // expand 'nn' to 'nn_NN'
      else if (!isset($rcube_languages[$short])) {
        $lang = $short.'_'.strtoupper($short);
      }
    }

    if (!isset($rcube_languages[$lang]) || !is_dir(INSTALL_PATH . 'program/localization/' . $lang)) {
      $lang = 'en_US';
    }

    return $lang;
  }


  /**
   * Get the current database connection
   *
   * @return rcube_mdb2  Database connection object
   */
  public function get_dbh()
  {
    if (!$this->db) {
      $config_all = $this->config->all();

      $this->db = new rcube_mdb2($config_all['db_dsnw'], $config_all['db_dsnr'], $config_all['db_persistent']);
      $this->db->sqlite_initials = INSTALL_PATH . 'SQL/sqlite.initial.sql';
      $this->db->set_debug((bool)$config_all['sql_debug']);
    }

    return $this->db;
  }
  
  
  /**
   * Get global handle for memcache access
   *
   * @return object Memcache
   */
  public function get_memcache()
  {
    if (!isset($this->memcache)) {
      // no memcache support in PHP
      if (!class_exists('Memcache')) {
        $this->memcache = false;
        return false;
      }

      $this->memcache = new Memcache;
      $this->mc_available = 0;
      
      // add alll configured hosts to pool
      $pconnect = $this->config->get('memcache_pconnect', true);
      foreach ($this->config->get('memcache_hosts', array()) as $host) {
        list($host, $port) = explode(':', $host);
        if (!$port) $port = 11211;
        $this->mc_available += intval($this->memcache->addServer($host, $port, $pconnect, 1, 1, 15, false, array($this, 'memcache_failure')));
      }
      
      // test connection and failover (will result in $this->mc_available == 0 on complete failure)
      $this->memcache->increment('__CONNECTIONTEST__', 1);  // NOP if key doesn't exist

      if (!$this->mc_available)
        $this->memcache = false;
    }

    return $this->memcache;
  }
  
  /**
   * Callback for memcache failure
   */
  public function memcache_failure($host, $port)
  {
    static $seen = array();
    
    // only report once
    if (!$seen["$host:$port"]++) {
      $this->mc_available--;
      raise_error(array('code' => 604, 'type' => 'db',
        'line' => __LINE__, 'file' => __FILE__,
        'message' => "Memcache failure on host $host:$port"),
        true, false);
    }
  }


  /**
   * Initialize and get cache object
   *
   * @param string $name   Cache identifier
   * @param string $type   Cache type ('db', 'apc' or 'memcache')
   * @param int    $ttl    Expiration time for cache items in seconds
   * @param bool   $packed Enables/disables data serialization
   *
   * @return rcube_cache Cache object
   */
  public function get_cache($name, $type='db', $ttl=0, $packed=true)
  {
    if (!isset($this->caches[$name])) {
      $this->caches[$name] = new rcube_cache($type, $_SESSION['user_id'], $name, $ttl, $packed);
    }

    return $this->caches[$name];
  }


  /**
   * Return instance of the internal address book class
   *
   * @param string  Address book identifier
   * @param boolean True if the address book needs to be writeable
   *
   * @return rcube_contacts Address book object
   */
  public function get_address_book($id, $writeable = false)
  {
    $contacts    = null;
    $ldap_config = (array)$this->config->get('ldap_public');
    $abook_type  = strtolower($this->config->get('address_book_type'));

    // 'sql' is the alias for '0' used by autocomplete
    if ($id == 'sql')
        $id = '0';

    // use existing instance
    if (isset($this->address_books[$id]) && is_object($this->address_books[$id])
      && is_a($this->address_books[$id], 'rcube_addressbook')
      && (!$writeable || !$this->address_books[$id]->readonly)
    ) {
      $contacts = $this->address_books[$id];
    }
    else if ($id && $ldap_config[$id]) {
      $contacts = new rcube_ldap($ldap_config[$id], $this->config->get('ldap_debug'), $this->config->mail_domain($_SESSION['imap_host']));
    }
    else if ($id === '0') {
      $contacts = new rcube_contacts($this->db, $this->user->ID);
    }
    else {
      $plugin = $this->plugins->exec_hook('addressbook_get', array('id' => $id, 'writeable' => $writeable));

      // plugin returned instance of a rcube_addressbook
      if ($plugin['instance'] instanceof rcube_addressbook) {
        $contacts = $plugin['instance'];
      }
      // get first source from the list
      else if (!$id) {
        $source = reset($this->get_address_sources($writeable));
        if (!empty($source)) {
          $contacts = $this->get_address_book($source['id']);
          if ($contacts)
            $id = $source['id'];
        }
      }
    }

    if (!$contacts) {
      raise_error(array(
        'code' => 700, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Addressbook source ($id) not found!"),
        true, true);
    }

    // add to the 'books' array for shutdown function
    if (!isset($this->address_books[$id]))
      $this->address_books[$id] = $contacts;

    return $contacts;
  }


  /**
   * Return address books list
   *
   * @param boolean True if the address book needs to be writeable
   *
   * @return array  Address books array
   */
  public function get_address_sources($writeable = false)
  {
    $abook_type = strtolower($this->config->get('address_book_type'));
    $ldap_config = $this->config->get('ldap_public');
    $autocomplete = (array) $this->config->get('autocomplete_addressbooks');
    $list = array();

    // We are using the DB address book
    if ($abook_type != 'ldap') {
      if (!isset($this->address_books['0']))
        $this->address_books['0'] = new rcube_contacts($this->db, $this->user->ID);
      $list['0'] = array(
        'id'       => '0',
        'name'     => rcube_label('personaladrbook'),
        'groups'   => $this->address_books['0']->groups,
        'readonly' => $this->address_books['0']->readonly,
        'autocomplete' => in_array('sql', $autocomplete)
      );
    }

    if ($ldap_config) {
      $ldap_config = (array) $ldap_config;
      foreach ($ldap_config as $id => $prop)
        $list[$id] = array(
          'id'       => $id,
          'name'     => $prop['name'],
          'groups'   => is_array($prop['groups']),
          'readonly' => !$prop['writable'],
          'hidden'   => $prop['hidden'],
          'autocomplete' => in_array($id, $autocomplete)
        );
    }

    $plugin = $this->plugins->exec_hook('addressbooks_list', array('sources' => $list));
    $list = $plugin['sources'];

    foreach ($list as $idx => $item) {
      // register source for shutdown function
      if (!is_object($this->address_books[$item['id']]))
        $this->address_books[$item['id']] = $item;
      // remove from list if not writeable as requested
      if ($writeable && $item['readonly'])
          unset($list[$idx]);
    }

    return $list;
  }


  /**
   * Init output object for GUI and add common scripts.
   * This will instantiate a rcmail_template object and set
   * environment vars according to the current session and configuration
   *
   * @param boolean True if this request is loaded in a (i)frame
   * @return rcube_template Reference to HTML output object
   */
  public function load_gui($framed = false)
  {
    // init output page
    if (!($this->output instanceof rcube_template))
      $this->output = new rcube_template($this->task, $framed);

    // set keep-alive/check-recent interval
    if ($this->session && ($keep_alive = $this->session->get_keep_alive())) {
      $this->output->set_env('keep_alive', $keep_alive);
    }

    if ($framed) {
      $this->comm_path .= '&_framed=1';
      $this->output->set_env('framed', true);
    }

    $this->output->set_env('task', $this->task);
    $this->output->set_env('action', $this->action);
    $this->output->set_env('comm_path', $this->comm_path);
    $this->output->set_charset(RCMAIL_CHARSET);

    // add some basic labels to client
    $this->output->add_label('loading', 'servererror');

    return $this->output;
  }


  /**
   * Create an output object for JSON responses
   *
   * @return rcube_json_output Reference to JSON output object
   */
  public function json_init()
  {
    if (!($this->output instanceof rcube_json_output))
      $this->output = new rcube_json_output($this->task);

    return $this->output;
  }


  /**
   * Create SMTP object and connect to server
   *
   * @param boolean True if connection should be established
   */
  public function smtp_init($connect = false)
  {
    $this->smtp = new rcube_smtp();

    if ($connect)
      $this->smtp->connect();
  }


  /**
   * Create global IMAP object and connect to server
   *
   * @param boolean True if connection should be established
   * @todo Remove global $IMAP
   */
  public function imap_init($connect = false)
  {
    // already initialized
    if (is_object($this->imap))
      return;

    $this->imap = new rcube_imap();
    $this->imap->debug_level = $this->config->get('debug_level');
    $this->imap->skip_deleted = $this->config->get('skip_deleted');

    // enable caching of imap data
    $imap_cache = $this->config->get('imap_cache');
    $messages_cache = $this->config->get('messages_cache');
    // for backward compatybility
    if ($imap_cache === null && $messages_cache === null && $this->config->get('enable_caching')) {
        $imap_cache     = 'db';
        $messages_cache = true;
    }
    if ($imap_cache)
        $this->imap->set_caching($imap_cache);
    if ($messages_cache)
        $this->imap->set_messages_caching(true);

    // set pagesize from config
    $this->imap->set_pagesize($this->config->get('pagesize', 50));

    // Setting root and delimiter before establishing the connection
    // can save time detecting them using NAMESPACE and LIST
    $options = array(
      'auth_method' => $this->config->get('imap_auth_type', 'check'),
      'auth_cid'    => $this->config->get('imap_auth_cid'),
      'auth_pw'     => $this->config->get('imap_auth_pw'),
      'debug'       => (bool) $this->config->get('imap_debug', 0),
      'force_caps'  => (bool) $this->config->get('imap_force_caps'),
      'timeout'     => (int) $this->config->get('imap_timeout', 0),
    );

    $this->imap->set_options($options);

    // set global object for backward compatibility
    $GLOBALS['IMAP'] = $this->imap;

    $hook = $this->plugins->exec_hook('imap_init', array('fetch_headers' => $this->imap->fetch_add_headers));
    if ($hook['fetch_headers'])
      $this->imap->fetch_add_headers = $hook['fetch_headers'];

    // support this parameter for backward compatibility but log warning
    if ($connect) {
      $this->imap_connect();
      raise_error(array(
        'code' => 800, 'type' => 'imap',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "rcube::imap_init(true) is deprecated, use rcube::imap_connect() instead"),
        true, false);
    }
  }


  /**
   * Connect to IMAP server with stored session data
   *
   * @return bool True on success, false on error
   */
  public function imap_connect()
  {
    if (!$this->imap)
      $this->imap_init();

    if ($_SESSION['imap_host'] && !$this->imap->conn->connected()) {
      if (!$this->imap->connect($_SESSION['imap_host'], $_SESSION['username'], $this->decrypt($_SESSION['password']), $_SESSION['imap_port'], $_SESSION['imap_ssl'])) {
        if ($this->output)
          $this->output->show_message($this->imap->get_error_code() == -1 ? 'imaperror' : 'sessionerror', 'error');
      }
      else {
        $this->set_imap_prop();
        return $this->imap->conn;
      }
    }

    return false;
  }


  /**
   * Create session object and start the session.
   */
  public function session_init()
  {
    // session started (Installer?)
    if (session_id())
      return;

    // set session domain
    if ($domain = $this->config->get('session_domain')) {
      ini_set('session.cookie_domain', $domain);
    }
    // set session garbage collecting time according to session_lifetime
    $lifetime = $this->config->get('session_lifetime', 0) * 60;
    if ($lifetime) {
      ini_set('session.gc_maxlifetime', $lifetime * 2);
    }

    ini_set('session.cookie_secure', rcube_https_check());
    ini_set('session.name', 'roundcube_sessid');
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.serialize_handler', 'php');

    // use database for storing session data
    $this->session = new rcube_session($this->get_dbh(), $this->config);

    $this->session->register_gc_handler('rcmail_temp_gc');
    if ($this->config->get('enable_caching'))
      $this->session->register_gc_handler('rcmail_cache_gc');

    // start PHP session (if not in CLI mode)
    if ($_SERVER['REMOTE_ADDR'])
      session_start();

    // set initial session vars
    if (!$_SESSION['user_id'])
      $_SESSION['temp'] = true;
  }


  /**
   * Configure session object internals
   */
  public function session_configure()
  {
    if (!$this->session)
      return;

    $lifetime = $this->config->get('session_lifetime', 0) * 60;

    // set keep-alive/check-recent interval
    if ($keep_alive = $this->config->get('keep_alive')) {
      // be sure that it's less than session lifetime
      if ($lifetime)
        $keep_alive = min($keep_alive, $lifetime - 30);
      $keep_alive = max(60, $keep_alive);
      $this->session->set_keep_alive($keep_alive);
    }
    
    $this->session->set_secret($this->config->get('des_key') . $_SERVER['HTTP_USER_AGENT']);
    $this->session->set_ip_check($this->config->get('ip_check'));
  }


  /**
   * Perfom login to the IMAP server and to the webmail service.
   * This will also create a new user entry if auto_create_user is configured.
   *
   * @param string IMAP user name
   * @param string IMAP password
   * @param string IMAP host
   * @return boolean True on success, False on failure
   */
  function login($username, $pass, $host=NULL)
  {
    $user = NULL;
    $config = $this->config->all();

    if (!$host)
      $host = $config['default_host'];

    // Validate that selected host is in the list of configured hosts
    if (is_array($config['default_host'])) {
      $allowed = false;
      foreach ($config['default_host'] as $key => $host_allowed) {
        if (!is_numeric($key))
          $host_allowed = $key;
        if ($host == $host_allowed) {
          $allowed = true;
          break;
        }
      }
      if (!$allowed)
        return false;
      }
    else if (!empty($config['default_host']) && $host != rcube_parse_host($config['default_host']))
      return false;

    // parse $host URL
    $a_host = parse_url($host);
    if ($a_host['host']) {
      $host = $a_host['host'];
      $imap_ssl = (isset($a_host['scheme']) && in_array($a_host['scheme'], array('ssl','imaps','tls'))) ? $a_host['scheme'] : null;
      if (!empty($a_host['port']))
        $imap_port = $a_host['port'];
      else if ($imap_ssl && $imap_ssl != 'tls' && (!$config['default_port'] || $config['default_port'] == 143))
        $imap_port = 993;
    }

    $imap_port = $imap_port ? $imap_port : $config['default_port'];

    /* Modify username with domain if required
       Inspired by Marco <P0L0_notspam_binware.org>
    */
    // Check if we need to add domain
    if (!empty($config['username_domain']) && strpos($username, '@') === false) {
      if (is_array($config['username_domain']) && isset($config['username_domain'][$host]))
        $username .= '@'.rcube_parse_host($config['username_domain'][$host], $host);
      else if (is_string($config['username_domain']))
        $username .= '@'.rcube_parse_host($config['username_domain'], $host);
    }

    // Convert username to lowercase. If IMAP backend
    // is case-insensitive we need to store always the same username (#1487113)
    if ($config['login_lc']) {
      $username = mb_strtolower($username);
    }

    // try to resolve email address from virtuser table
    if (strpos($username, '@') && ($virtuser = rcube_user::email2user($username))) {
      $username = $virtuser;
    }

    // Here we need IDNA ASCII
    // Only rcube_contacts class is using domain names in Unicode
    $host = rcube_idn_to_ascii($host);
    if (strpos($username, '@')) {
      // lowercase domain name
      list($local, $domain) = explode('@', $username);
      $username = $local . '@' . mb_strtolower($domain);
      $username = rcube_idn_to_ascii($username);
    }

    // user already registered -> overwrite username
    if ($user = rcube_user::query($username, $host))
      $username = $user->data['username'];

    if (!$this->imap)
      $this->imap_init();

    // try IMAP login
    if (!($imap_login = $this->imap->connect($host, $username, $pass, $imap_port, $imap_ssl))) {
      // try with lowercase
      $username_lc = mb_strtolower($username);
      if ($username_lc != $username) {
        // try to find user record again -> overwrite username
        if (!$user && ($user = rcube_user::query($username_lc, $host)))
          $username_lc = $user->data['username'];

        if ($imap_login = $this->imap->connect($host, $username_lc, $pass, $imap_port, $imap_ssl))
          $username = $username_lc;
      }
    }

    // exit if IMAP login failed
    if (!$imap_login)
      return false;

    $this->set_imap_prop();

    // user already registered -> update user's record
    if (is_object($user)) {
      // fix some old settings according to namespace prefix
      $this->fix_namespace_settings($user);

      // create default folders on first login
      if (!$user->data['last_login'] && $config['create_default_folders'])
        $this->imap->create_default_folders();
      // update last login timestamp
      $user->touch();
    }
    // create new system user
    else if ($config['auto_create_user']) {
      if ($created = rcube_user::create($username, $host)) {
        $user = $created;
        // create default folders on first login
        if ($config['create_default_folders'])
          $this->imap->create_default_folders();
      }
      else {
        raise_error(array(
          'code' => 620, 'type' => 'php',
          'file' => __FILE__, 'line' => __LINE__,
          'message' => "Failed to create a user record. Maybe aborted by a plugin?"
          ), true, false);
      }
    }
    else {
      raise_error(array(
        'code' => 621, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Access denied for new user $username. 'auto_create_user' is disabled"
        ), true, false);
    }

    // login succeeded
    if (is_object($user) && $user->ID) {
      $this->set_user($user);
      $this->session_configure();

      // set session vars
      $_SESSION['user_id']   = $user->ID;
      $_SESSION['username']  = $user->data['username'];
      $_SESSION['imap_host'] = $host;
      $_SESSION['imap_port'] = $imap_port;
      $_SESSION['imap_ssl']  = $imap_ssl;
      $_SESSION['password']  = $this->encrypt($pass);
      $_SESSION['login_time'] = mktime();
      
      if (isset($_REQUEST['_timezone']) && $_REQUEST['_timezone'] != '_default_')
        $_SESSION['timezone'] = floatval($_REQUEST['_timezone']);

      // force reloading complete list of subscribed mailboxes
      $this->imap->clear_cache('mailboxes', true);

      return true;
    }

    return false;
  }


  /**
   * Set root dir and last stored mailbox
   * This must be done AFTER connecting to the server!
   */
  public function set_imap_prop()
  {
    $this->imap->set_charset($this->config->get('default_charset', RCMAIL_CHARSET));

    if ($default_folders = $this->config->get('default_imap_folders')) {
      $this->imap->set_default_mailboxes($default_folders);
    }
    if (isset($_SESSION['mbox'])) {
      $this->imap->set_mailbox($_SESSION['mbox']);
    }
    if (isset($_SESSION['page'])) {
      $this->imap->set_page($_SESSION['page']);
    }
  }


  /**
   * Auto-select IMAP host based on the posted login information
   *
   * @return string Selected IMAP host
   */
  public function autoselect_host()
  {
    $default_host = $this->config->get('default_host');
    $host = null;

    if (is_array($default_host)) {
      $post_host = get_input_value('_host', RCUBE_INPUT_POST);

      // direct match in default_host array
      if ($default_host[$post_host] || in_array($post_host, array_values($default_host))) {
        $host = $post_host;
      }

      // try to select host by mail domain
      list($user, $domain) = explode('@', get_input_value('_user', RCUBE_INPUT_POST));
      if (!empty($domain)) {
        foreach ($default_host as $imap_host => $mail_domains) {
          if (is_array($mail_domains) && in_array($domain, $mail_domains)) {
            $host = $imap_host;
            break;
          }
        }
      }

      // take the first entry if $host is still an array
      if (empty($host)) {
        $host = array_shift($default_host);
      }
    }
    else if (empty($default_host)) {
      $host = get_input_value('_host', RCUBE_INPUT_POST);
    }
    else
      $host = rcube_parse_host($default_host);

    return $host;
  }


  /**
   * Get localized text in the desired language
   *
   * @param mixed   $attrib  Named parameters array or label name
   * @param string  $domain  Label domain (plugin) name
   *
   * @return string Localized text
   */
  public function gettext($attrib, $domain=null)
  {
    // load localization files if not done yet
    if (empty($this->texts))
      $this->load_language();

    // extract attributes
    if (is_string($attrib))
      $attrib = array('name' => $attrib);

    $nr = is_numeric($attrib['nr']) ? $attrib['nr'] : 1;
    $name = $attrib['name'] ? $attrib['name'] : '';

    // attrib contain text values: use them from now
    if (($setval = $attrib[strtolower($_SESSION['language'])]) || ($setval = $attrib['en_us']))
        $this->texts[$name] = $setval;

    // check for text with domain
    if ($domain && ($text_item = $this->texts[$domain.'.'.$name]))
      ;
    // text does not exist
    else if (!($text_item = $this->texts[$name])) {
      return "[$name]";
    }

    // make text item array
    $a_text_item = is_array($text_item) ? $text_item : array('single' => $text_item);

    // decide which text to use
    if ($nr == 1) {
      $text = $a_text_item['single'];
    }
    else if ($nr > 0) {
      $text = $a_text_item['multiple'];
    }
    else if ($nr == 0) {
      if ($a_text_item['none'])
        $text = $a_text_item['none'];
      else if ($a_text_item['single'])
        $text = $a_text_item['single'];
      else if ($a_text_item['multiple'])
        $text = $a_text_item['multiple'];
    }

    // default text is single
    if ($text == '') {
      $text = $a_text_item['single'];
    }

    // replace vars in text
    if (is_array($attrib['vars'])) {
      foreach ($attrib['vars'] as $var_key => $var_value)
        $text = str_replace($var_key[0]!='$' ? '$'.$var_key : $var_key, $var_value, $text);
    }

    // format output
    if (($attrib['uppercase'] && strtolower($attrib['uppercase']=='first')) || $attrib['ucfirst'])
      return ucfirst($text);
    else if ($attrib['uppercase'])
      return mb_strtoupper($text);
    else if ($attrib['lowercase'])
      return mb_strtolower($text);

    return $text;
  }


  /**
   * Check if the given text label exists
   *
   * @param string  $name       Label name
   * @param string  $domain     Label domain (plugin) name or '*' for all domains
   * @param string  $ref_domain Sets domain name if label is found
   *
   * @return boolean True if text exists (either in the current language or in en_US)
   */
  public function text_exists($name, $domain = null, &$ref_domain = null)
  {
    // load localization files if not done yet
    if (empty($this->texts))
      $this->load_language();

    if (isset($this->texts[$name])) {
        $ref_domain = '';
        return true;
    }

    // any of loaded domains (plugins)
    if ($domain == '*') {
      foreach ($this->plugins->loaded_plugins() as $domain)
        if (isset($this->texts[$domain.'.'.$name])) {
          $ref_domain = $domain;
          return true;
        }
    }
    // specified domain
    else if ($domain) {
      $ref_domain = $domain;
      return isset($this->texts[$domain.'.'.$name]);
    }

    return false;
  }

  /**
   * Load a localization package
   *
   * @param string Language ID
   */
  public function load_language($lang = null, $add = array())
  {
    $lang = $this->language_prop(($lang ? $lang : $_SESSION['language']));

    // load localized texts
    if (empty($this->texts) || $lang != $_SESSION['language']) {
      $this->texts = array();

      // handle empty lines after closing PHP tag in localization files
      ob_start();

      // get english labels (these should be complete)
      @include(INSTALL_PATH . 'program/localization/en_US/labels.inc');
      @include(INSTALL_PATH . 'program/localization/en_US/messages.inc');

      if (is_array($labels))
        $this->texts = $labels;
      if (is_array($messages))
        $this->texts = array_merge($this->texts, $messages);

      // include user language files
      if ($lang != 'en' && is_dir(INSTALL_PATH . 'program/localization/' . $lang)) {
        include_once(INSTALL_PATH . 'program/localization/' . $lang . '/labels.inc');
        include_once(INSTALL_PATH . 'program/localization/' . $lang . '/messages.inc');

        if (is_array($labels))
          $this->texts = array_merge($this->texts, $labels);
        if (is_array($messages))
          $this->texts = array_merge($this->texts, $messages);
      }

      ob_end_clean();

      $_SESSION['language'] = $lang;
    }

    // append additional texts (from plugin)
    if (is_array($add) && !empty($add))
      $this->texts += $add;
  }


  /**
   * Read directory program/localization and return a list of available languages
   *
   * @return array List of available localizations
   */
  public function list_languages()
  {
    static $sa_languages = array();

    if (!sizeof($sa_languages)) {
      @include(INSTALL_PATH . 'program/localization/index.inc');

      if ($dh = @opendir(INSTALL_PATH . 'program/localization')) {
        while (($name = readdir($dh)) !== false) {
          if ($name[0] == '.' || !is_dir(INSTALL_PATH . 'program/localization/' . $name))
            continue;

          if ($label = $rcube_languages[$name])
            $sa_languages[$name] = $label;
        }
        closedir($dh);
      }
    }

    return $sa_languages;
  }


  /**
   * Destroy session data and remove cookie
   */
  public function kill_session()
  {
    $this->plugins->exec_hook('session_destroy');

    $this->session->kill();
    $_SESSION = array('language' => $this->user->language, 'temp' => true);
    $this->user->reset();
  }


  /**
   * Do server side actions on logout
   */
  public function logout_actions()
  {
    $config = $this->config->all();

    // on logout action we're not connected to imap server
    if (($config['logout_purge'] && !empty($config['trash_mbox'])) || $config['logout_expunge']) {
      if (!$this->session->check_auth())
        return;

      $this->imap_connect();
    }

    if ($config['logout_purge'] && !empty($config['trash_mbox'])) {
      $this->imap->clear_mailbox($config['trash_mbox']);
    }

    if ($config['logout_expunge']) {
      $this->imap->expunge('INBOX');
    }

    // Try to save unsaved user preferences
    if (!empty($_SESSION['preferences'])) {
      $this->user->save_prefs(unserialize($_SESSION['preferences']));
    }
  }


  /**
   * Function to be executed in script shutdown
   * Registered with register_shutdown_function()
   */
  public function shutdown()
  {
    foreach ($this->shutdown_functions as $function)
      call_user_func($function);

    if (is_object($this->smtp))
      $this->smtp->disconnect();

    foreach ($this->address_books as $book) {
      if (is_object($book) && is_a($book, 'rcube_addressbook'))
        $book->close();
    }

    foreach ($this->caches as $cache) {
        if (is_object($cache))
            $cache->close();
    }

    if (is_object($this->imap))
      $this->imap->close();

    // before closing the database connection, write session data
    if ($_SERVER['REMOTE_ADDR'] && is_object($this->session)) {
      $this->session->cleanup();
      session_write_close();
    }

    // write performance stats to logs/console
    if ($this->config->get('devel_mode')) {
      if (function_exists('memory_get_usage'))
        $mem = show_bytes(memory_get_usage());
      if (function_exists('memory_get_peak_usage'))
        $mem .= '/'.show_bytes(memory_get_peak_usage());

      $log = $this->task . ($this->action ? '/'.$this->action : '') . ($mem ? " [$mem]" : '');
      if (defined('RCMAIL_START'))
        rcube_print_time(RCMAIL_START, $log);
      else
        console($log);
    }
  }


  /**
   * Registers shutdown function to be executed on shutdown.
   * The functions will be executed before destroying any
   * objects like smtp, imap, session, etc.
   *
   * @param callback Function callback
   */
  public function add_shutdown_function($function)
  {
    $this->shutdown_functions[] = $function;
  }


  /**
   * Generate a unique token to be used in a form request
   *
   * @return string The request token
   */
  public function get_request_token()
  {
    $sess_id = $_COOKIE[ini_get('session.name')];
    if (!$sess_id) $sess_id = session_id();
    $plugin = $this->plugins->exec_hook('request_token', array('value' => md5('RT' . $this->task . $this->config->get('des_key') . $sess_id)));
    return $plugin['value'];
  }


  /**
   * Check if the current request contains a valid token
   *
   * @param int Request method
   * @return boolean True if request token is valid false if not
   */
  public function check_request($mode = RCUBE_INPUT_POST)
  {
    $token = get_input_value('_token', $mode);
    $sess_id = $_COOKIE[ini_get('session.name')];
    return !empty($sess_id) && $token == $this->get_request_token();
  }


  /**
   * Create unique authorization hash
   *
   * @param string Session ID
   * @param int Timestamp
   * @return string The generated auth hash
   */
  private function get_auth_hash($sess_id, $ts)
  {
    $auth_string = sprintf('rcmail*sess%sR%s*Chk:%s;%s',
      $sess_id,
      $ts,
      $this->config->get('ip_check') ? $_SERVER['REMOTE_ADDR'] : '***.***.***.***',
      $_SERVER['HTTP_USER_AGENT']);

    if (function_exists('sha1'))
      return sha1($auth_string);
    else
      return md5($auth_string);
  }


  /**
   * Encrypt using 3DES
   *
   * @param string $clear clear text input
   * @param string $key encryption key to retrieve from the configuration, defaults to 'des_key'
   * @param boolean $base64 whether or not to base64_encode() the result before returning
   *
   * @return string encrypted text
   */
  public function encrypt($clear, $key = 'des_key', $base64 = true)
  {
    if (!$clear)
      return '';
    /*-
     * Add a single canary byte to the end of the clear text, which
     * will help find out how much of padding will need to be removed
     * upon decryption; see http://php.net/mcrypt_generic#68082
     */
    $clear = pack("a*H2", $clear, "80");

    if (function_exists('mcrypt_module_open') &&
        ($td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_CBC, "")))
    {
      $iv = $this->create_iv(mcrypt_enc_get_iv_size($td));
      mcrypt_generic_init($td, $this->config->get_crypto_key($key), $iv);
      $cipher = $iv . mcrypt_generic($td, $clear);
      mcrypt_generic_deinit($td);
      mcrypt_module_close($td);
    }
    else {
      @include_once 'des.inc';

      if (function_exists('des')) {
        $des_iv_size = 8;
        $iv = $this->create_iv($des_iv_size);
        $cipher = $iv . des($this->config->get_crypto_key($key), $clear, 1, 1, $iv);
      }
      else {
        raise_error(array(
          'code' => 500, 'type' => 'php',
          'file' => __FILE__, 'line' => __LINE__,
          'message' => "Could not perform encryption; make sure Mcrypt is installed or lib/des.inc is available"
        ), true, true);
      }
    }

    return $base64 ? base64_encode($cipher) : $cipher;
  }

  /**
   * Decrypt 3DES-encrypted string
   *
   * @param string $cipher encrypted text
   * @param string $key encryption key to retrieve from the configuration, defaults to 'des_key'
   * @param boolean $base64 whether or not input is base64-encoded
   *
   * @return string decrypted text
   */
  public function decrypt($cipher, $key = 'des_key', $base64 = true)
  {
    if (!$cipher)
      return '';

    $cipher = $base64 ? base64_decode($cipher) : $cipher;

    if (function_exists('mcrypt_module_open') &&
        ($td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_CBC, "")))
    {
      $iv_size = mcrypt_enc_get_iv_size($td);
      $iv = substr($cipher, 0, $iv_size);

      // session corruption? (#1485970)
      if (strlen($iv) < $iv_size)
        return '';

      $cipher = substr($cipher, $iv_size);
      mcrypt_generic_init($td, $this->config->get_crypto_key($key), $iv);
      $clear = mdecrypt_generic($td, $cipher);
      mcrypt_generic_deinit($td);
      mcrypt_module_close($td);
    }
    else {
      @include_once 'des.inc';

      if (function_exists('des')) {
        $des_iv_size = 8;
        $iv = substr($cipher, 0, $des_iv_size);
        $cipher = substr($cipher, $des_iv_size);
        $clear = des($this->config->get_crypto_key($key), $cipher, 0, 1, $iv);
      }
      else {
        raise_error(array(
          'code' => 500, 'type' => 'php',
          'file' => __FILE__, 'line' => __LINE__,
          'message' => "Could not perform decryption; make sure Mcrypt is installed or lib/des.inc is available"
        ), true, true);
      }
    }

    /*-
     * Trim PHP's padding and the canary byte; see note in
     * rcmail::encrypt() and http://php.net/mcrypt_generic#68082
     */
    $clear = substr(rtrim($clear, "\0"), 0, -1);

    return $clear;
  }

  /**
   * Generates encryption initialization vector (IV)
   *
   * @param int Vector size
   * @return string Vector string
   */
  private function create_iv($size)
  {
    // mcrypt_create_iv() can be slow when system lacks entrophy
    // we'll generate IV vector manually
    $iv = '';
    for ($i = 0; $i < $size; $i++)
        $iv .= chr(mt_rand(0, 255));
    return $iv;
  }

  /**
   * Build a valid URL to this instance of Roundcube
   *
   * @param mixed Either a string with the action or url parameters as key-value pairs
   * @return string Valid application URL
   */
  public function url($p)
  {
    if (!is_array($p))
      $p = array('_action' => @func_get_arg(0));

    $task = $p['_task'] ? $p['_task'] : ($p['task'] ? $p['task'] : $this->task);
    $p['_task'] = $task;
    unset($p['task']);

    $url = './';
    $delm = '?';
    foreach (array_reverse($p) as $key => $val) {
      if ($val !== '') {
        $par = $key[0] == '_' ? $key : '_'.$key;
        $url .= $delm.urlencode($par).'='.urlencode($val);
        $delm = '&';
      }
    }
    return $url;
  }


  /**
   * Use imagemagick or GD lib to read image properties
   *
   * @param string Absolute file path
   * @return mixed Hash array with image props like type, width, height or False on error
   */
  public static function imageprops($filepath)
  {
    $rcmail = rcmail::get_instance();
    if ($cmd = $rcmail->config->get('im_identify_path', false)) {
      list(, $type, $size) = explode(' ', strtolower(rcmail::exec($cmd. ' 2>/dev/null {in}', array('in' => $filepath))));
      if ($size)
        list($width, $height) = explode('x', $size);
    }
    else if (function_exists('getimagesize')) {
      $imsize = @getimagesize($filepath);
      $width = $imsize[0];
      $height = $imsize[1];
      $type = preg_replace('!image/!', '', $imsize['mime']);
    }

    return $type ? array('type' => $type, 'width' => $width, 'height' => $height) : false;
  }


  /**
   * Convert an image to a given size and type using imagemagick (ensures input is an image)
   *
   * @param $p['in']  Input filename (mandatory)
   * @param $p['out'] Output filename (mandatory)
   * @param $p['size']  Width x height of resulting image, e.g. "160x60"
   * @param $p['type']  Output file type, e.g. "jpg"
   * @param $p['-opts'] Custom command line options to ImageMagick convert
   * @return Success of convert as true/false
   */
  public static function imageconvert($p)
  {
    $result = false;
    $rcmail = rcmail::get_instance();
    $convert  = $rcmail->config->get('im_convert_path', false);
    $identify = $rcmail->config->get('im_identify_path', false);

    // imagemagick is required for this
    if (!$convert)
        return false;

    if (!(($imagetype = @exif_imagetype($p['in'])) && ($type = image_type_to_extension($imagetype, false))))
      list(, $type) = explode(' ', strtolower(rcmail::exec($identify . ' 2>/dev/null {in}', $p))); # for things like eps

    $type = strtr($type, array("jpeg" => "jpg", "tiff" => "tif", "ps" => "eps", "ept" => "eps"));
    $p += array('type' => $type, 'types' => "bmp,eps,gif,jp2,jpg,png,svg,tif", 'quality' => 75);
    $p['-opts'] = array('-resize' => $p['size'].'>') + (array)$p['-opts'];

    if (in_array($type, explode(',', $p['types']))) # Valid type?
      $result = rcmail::exec($convert . ' 2>&1 -flatten -auto-orient -colorspace RGB -quality {quality} {-opts} {in} {type}:{out}', $p) === "";

    return $result;
  }


  /**
   * Construct shell command, execute it and return output as string.
   * Keywords {keyword} are replaced with arguments
   *
   * @param $cmd Format string with {keywords} to be replaced
   * @param $values (zero, one or more arrays can be passed)
   * @return output of command. shell errors not detectable
   */
  public static function exec(/* $cmd, $values1 = array(), ... */)
  {
    $args = func_get_args();
    $cmd = array_shift($args);
    $values = $replacements = array();

    // merge values into one array
    foreach ($args as $arg)
      $values += (array)$arg;

    preg_match_all('/({(-?)([a-z]\w*)})/', $cmd, $matches, PREG_SET_ORDER);
    foreach ($matches as $tags) {
      list(, $tag, $option, $key) = $tags;
      $parts = array();

      if ($option) {
        foreach ((array)$values["-$key"] as $key => $value) {
          if ($value === true || $value === false || $value === null)
            $parts[] = $value ? $key : "";
          else foreach ((array)$value as $val)
            $parts[] = "$key " . escapeshellarg($val);
        }
      }
      else {
        foreach ((array)$values[$key] as $value)
          $parts[] = escapeshellarg($value);
      }

      $replacements[$tag] = join(" ", $parts);
    }

    // use strtr behaviour of going through source string once
    $cmd = strtr($cmd, $replacements);
    
    return (string)shell_exec($cmd);
  }


  /**
   * Helper method to set a cookie with the current path and host settings
   *
   * @param string Cookie name
   * @param string Cookie value
   * @param string Expiration time
   */
  public static function setcookie($name, $value, $exp = 0)
  {
    if (headers_sent())
      return;

    $cookie = session_get_cookie_params();

    setcookie($name, $value, $exp, $cookie['path'], $cookie['domain'],
      rcube_https_check(), true);
  }

  /**
   * Registers action aliases for current task
   *
   * @param array $map Alias-to-filename hash array
   */
  public function register_action_map($map)
  {
    if (is_array($map)) {
      foreach ($map as $idx => $val) {
        $this->action_map[$idx] = $val;
      }
    }
  }
  
  /**
   * Returns current action filename
   *
   * @param array $map Alias-to-filename hash array
   */
  public function get_action_file()
  {
    if (!empty($this->action_map[$this->action])) {
      return $this->action_map[$this->action];
    }

    return strtr($this->action, '-', '_') . '.inc';
  }

  /**
   * Fixes some user preferences according to namespace handling change.
   * Old Roundcube versions were using folder names with removed namespace prefix.
   * Now we need to add the prefix on servers where personal namespace has prefix.
   *
   * @param rcube_user $user User object
   */
  private function fix_namespace_settings($user)
  {
    $prefix     = $this->imap->get_namespace('prefix');
    $prefix_len = strlen($prefix);

    if (!$prefix_len)
      return;

    $prefs = $user->get_prefs();
    if (empty($prefs) || $prefs['namespace_fixed'])
      return;

    // Build namespace prefix regexp
    $ns     = $this->imap->get_namespace();
    $regexp = array();

    foreach ($ns as $entry) {
      if (!empty($entry)) {
        foreach ($entry as $item) {
          if (strlen($item[0])) {
            $regexp[] = preg_quote($item[0], '/');
          }
        }
      }
    }
    $regexp = '/^('. implode('|', $regexp).')/';

    // Fix preferences
    $opts = array('drafts_mbox', 'junk_mbox', 'sent_mbox', 'trash_mbox', 'archive_mbox');
    foreach ($opts as $opt) {
      if ($value = $prefs[$opt]) {
        if ($value != 'INBOX' && !preg_match($regexp, $value)) {
          $prefs[$opt] = $prefix.$value;
        }
      }
    }

    if (!empty($prefs['default_imap_folders'])) {
      foreach ($prefs['default_imap_folders'] as $idx => $name) {
        if ($name != 'INBOX' && !preg_match($regexp, $name)) {
          $prefs['default_imap_folders'][$idx] = $prefix.$name;
        }
      }
    }

    if (!empty($prefs['search_mods'])) {
      $folders = array();
      foreach ($prefs['search_mods'] as $idx => $value) {
        if ($idx != 'INBOX' && $idx != '*' && !preg_match($regexp, $idx)) {
          $idx = $prefix.$idx;
        }
        $folders[$idx] = $value;
      }
      $prefs['search_mods'] = $folders;
    }

    if (!empty($prefs['message_threading'])) {
      $folders = array();
      foreach ($prefs['message_threading'] as $idx => $value) {
        if ($idx != 'INBOX' && !preg_match($regexp, $idx)) {
          $idx = $prefix.$idx;
        }
        $folders[$prefix.$idx] = $value;
      }
      $prefs['message_threading'] = $folders;
    }

    if (!empty($prefs['collapsed_folders'])) {
      $folders     = explode('&&', $prefs['collapsed_folders']);
      $count       = count($folders);
      $folders_str = '';

      if ($count) {
          $folders[0]        = substr($folders[0], 1);
          $folders[$count-1] = substr($folders[$count-1], 0, -1);
      }

      foreach ($folders as $value) {
        if ($value != 'INBOX' && !preg_match($regexp, $value)) {
          $value = $prefix.$value;
        }
        $folders_str .= '&'.$value.'&';
      }
      $prefs['collapsed_folders'] = $folders_str;
    }

    $prefs['namespace_fixed'] = true;

    // save updated preferences and reset imap settings (default folders)
    $user->save_prefs($prefs);
    $this->set_imap_prop();
  }

}

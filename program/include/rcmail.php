<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcmail.php                                            |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2010, Roundcube Dev. - Switzerland                 |
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
  private $books = array();


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
   * Return instance of the internal address book class
   *
   * @param string  Address book identifier
   * @param boolean True if the address book needs to be writeable
   * @return rcube_contacts Address book object
   */
  public function get_address_book($id, $writeable = false)
  {
    $contacts = null;
    $ldap_config = (array)$this->config->get('ldap_public');
    $abook_type = strtolower($this->config->get('address_book_type'));

    $plugin = $this->plugins->exec_hook('addressbook_get', array('id' => $id, 'writeable' => $writeable));

    // plugin returned instance of a rcube_addressbook
    if ($plugin['instance'] instanceof rcube_addressbook) {
      $contacts = $plugin['instance'];
    }
    else if ($id && $ldap_config[$id]) {
      $contacts = new rcube_ldap($ldap_config[$id], $this->config->get('ldap_debug'), $this->config->mail_domain($_SESSION['imap_host']));
    }
    else if ($id === '0') {
      $contacts = new rcube_contacts($this->db, $this->user->ID);
    }
    else if ($abook_type == 'ldap') {
      // Use the first writable LDAP address book.
      foreach ($ldap_config as $id => $prop) {
        if (!$writeable || $prop['writable']) {
          $contacts = new rcube_ldap($prop, $this->config->get('ldap_debug'), $this->config->mail_domain($_SESSION['imap_host']));
          break;
        }
      }
    }
    else { // $id == 'sql'
      $contacts = new rcube_contacts($this->db, $this->user->ID);
    }

    // add to the 'books' array for shutdown function
    if (!in_array($contacts, $this->books))
      $this->books[] = $contacts;

    return $contacts;
  }


  /**
   * Return address books list
   *
   * @param boolean True if the address book needs to be writeable
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
      $contacts = new rcube_contacts($this->db, null);
      $list['0'] = array(
        'id' => 0,
        'name' => rcube_label('personaladrbook'),
        'groups' => $contacts->groups,
        'readonly' => false,
        'autocomplete' => in_array('sql', $autocomplete)
      );
    }

    if ($ldap_config) {
      $ldap_config = (array) $ldap_config;
      foreach ($ldap_config as $id => $prop)
        $list[$id] = array(
          'id' => $id,
          'name' => $prop['name'],
          'groups' => false,
          'readonly' => !$prop['writable'],
          'autocomplete' => in_array('sql', $autocomplete)
        );
    }

    $plugin = $this->plugins->exec_hook('addressbooks_list', array('sources' => $list));
    $list = $plugin['sources'];

    if ($writeable && !empty($list)) {
      foreach ($list as $idx => $item) {
        if ($item['readonly']) {
          unset($list[$idx]);
        }
      }
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

    // add some basic label to client
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

    $this->imap = new rcube_imap($this->db);
    $this->imap->debug_level = $this->config->get('debug_level');
    $this->imap->skip_deleted = $this->config->get('skip_deleted');

    // enable caching of imap data
    if ($this->config->get('enable_caching')) {
      $this->imap->set_caching(true);
    }

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

    $lifetime = $this->config->get('session_lifetime', 0) * 60;

    // set session domain
    if ($domain = $this->config->get('session_domain')) {
      ini_set('session.cookie_domain', $domain);
    }
    // set session garbage collecting time according to session_lifetime
    if ($lifetime) {
      ini_set('session.gc_maxlifetime', $lifetime * 2);
    }

    ini_set('session.cookie_secure', rcube_https_check());
    ini_set('session.name', 'roundcube_sessid');
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.serialize_handler', 'php');

    // use database for storing session data
    $this->session = new rcube_session($this->get_dbh(), $lifetime);

    $this->session->register_gc_handler('rcmail_temp_gc');
    if ($this->config->get('enable_caching'))
      $this->session->register_gc_handler('rcmail_cache_gc');

    // start PHP session (if not in CLI mode)
    if ($_SERVER['REMOTE_ADDR'])
      session_start();

    // set initial session vars
    if (!isset($_SESSION['auth_time'])) {
      $_SESSION['auth_time'] = time();
      $_SESSION['temp'] = true;
    }
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
      // create default folders on first login
      if (!$user->data['last_login'] && $config['create_default_folders'])
        $this->imap->create_default_folders();
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
          'code' => 600, 'type' => 'php',
          'file' => __FILE__, 'line' => __LINE__,
          'message' => "Failed to create a user record. Maybe aborted by a plugin?"
          ), true, false);
      }
    }
    else {
      raise_error(array(
        'code' => 600, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Acces denied for new user $username. 'auto_create_user' is disabled"
        ), true, false);
    }

    // login succeeded
    if (is_object($user) && $user->ID) {
      $this->set_user($user);

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
      $this->imap->clear_cache('mailboxes');

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
   * @param mixed Named parameters array or label name
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
   * Check if the given text lable exists
   *
   * @param string Label name
   * @return boolean True if text exists (either in the current language or in en_US)
   */
  public function text_exists($name, $domain=null)
  {
    // load localization files if not done yet
    if (empty($this->texts))
      $this->load_language();

    // check for text with domain first
    return ($domain && isset($this->texts[$domain.'.'.$name])) || isset($this->texts[$name]);
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
   * Check the auth hash sent by the client against the local session credentials
   *
   * @return boolean True if valid, False if not
   */
  function authenticate_session()
  {
    // advanced session authentication
    if ($this->config->get('double_auth')) {
      $now = time();
      $valid = ($_COOKIE['sessauth'] == $this->get_auth_hash(session_id(), $_SESSION['auth_time']) ||
                $_COOKIE['sessauth'] == $this->get_auth_hash(session_id(), $_SESSION['last_auth']));

      // renew auth cookie every 5 minutes (only for GET requests)
      if (!$valid || ($_SERVER['REQUEST_METHOD']!='POST' && $now - $_SESSION['auth_time'] > 300)) {
        $_SESSION['last_auth'] = $_SESSION['auth_time'];
        $_SESSION['auth_time'] = $now;
        rcmail::setcookie('sessauth', $this->get_auth_hash(session_id(), $now), 0);
      }
    }
    else {
      $valid = $this->config->get('ip_check') ? $_SERVER['REMOTE_ADDR'] == $this->session->get_ip() : true;
    }

    // check session filetime
    $lifetime = $this->config->get('session_lifetime');
    $sess_ts = $this->session->get_ts();
    if (!empty($lifetime) && !empty($sess_ts) && $sess_ts + $lifetime*60 < time()) {
      $valid = false;
    }

    return $valid;
  }


  /**
   * Destroy session data and remove cookie
   */
  public function kill_session()
  {
    $this->plugins->exec_hook('session_destroy');

    $this->session->remove();
    $_SESSION = array('language' => $this->user->language, 'auth_time' => time(), 'temp' => true);
    rcmail::setcookie('sessauth', '-del-', time() - 60);
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
      if (!$this->authenticate_session())
        return;

      $this->imap_connect();
    }

    if ($config['logout_purge'] && !empty($config['trash_mbox'])) {
      $this->imap->clear_mailbox($config['trash_mbox']);
    }

    if ($config['logout_expunge']) {
      $this->imap->expunge('INBOX');
    }
  }


  /**
   * Function to be executed in script shutdown
   * Registered with register_shutdown_function()
   */
  public function shutdown()
  {
    if (is_object($this->smtp))
      $this->smtp->disconnect();

    foreach ($this->books as $book)
      if (is_object($book))
        $book->close();

    if (is_object($this->imap))
      $this->imap->close();

    // before closing the database connection, write session data
    if ($_SERVER['REMOTE_ADDR'])
      session_write_close();

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
   * Generate a unique token to be used in a form request
   *
   * @return string The request token
   */
  public function get_request_token()
  {
    $sess_id = $_COOKIE[ini_get('session.name')];
    if (!$sess_id) $sess_id = session_id();
    return md5('RT' . $this->task . $this->config->get('des_key') . $sess_id);
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
      @include_once('lib/des.inc');

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
      @include_once('lib/des.inc');

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
    foreach (array_reverse($p) as $key => $val)
    {
      if (!empty($val)) {
        $par = $key[0] == '_' ? $key : '_'.$key;
        $url .= $delm.urlencode($par).'='.urlencode($val);
        $delm = '&';
      }
    }
    return $url;
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
}



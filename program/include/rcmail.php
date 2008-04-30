<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcmail.php                                            |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2008, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Application class providing core functions and holding              |
 |   instances of all 'global' objects like db- and imap-connections     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: rcube_browser.php 328 2006-08-30 17:41:21Z thomasb $

*/


/**
 * Application class of RoundCube Webmail
 * implemented as singleton
 *
 * @package Core
 */
class rcmail
{
  static public $main_tasks = array('mail','settings','addressbook','logout');
  
  static private $instance;
  
  public $config;
  public $user;
  public $db;
  public $imap;
  public $output;
  public $task = 'mail';
  public $action = '';
  public $comm_path = './';
  
  private $texts;
  
  
  /**
   * This implements the 'singleton' design pattern
   *
   * @return object qvert The one and only instance
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
   *
   * @todo Remove global $CONFIG
   */
  private function __construct()
  {
    // load configuration
    $this->config = new rcube_config();
    $GLOBALS['CONFIG'] = $this->config->all();
    
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
    $config_all = $this->config->all();

    // set task and action properties
    $this->set_task(strip_quotes(get_input_value('_task', RCUBE_INPUT_GPC)));
    $this->action = strip_quotes(get_input_value('_action', RCUBE_INPUT_GPC));

    // connect to database
    $GLOBALS['DB'] = $this->get_dbh();

    // use database for storing session data
    include_once('include/session.inc');

    // set session domain
    if (!empty($config_all['session_domain'])) {
      ini_set('session.cookie_domain', $config_all['session_domain']);
    }
    // set session garbage collecting time according to session_lifetime
    if (!empty($config_all['session_lifetime'])) {
      ini_set('session.gc_maxlifetime', ($config_all['session_lifetime']) * 120);
    }

    // start PHP session
    session_start();

    // set initial session vars
    if (!isset($_SESSION['auth_time'])) {
      $_SESSION['auth_time'] = time();
      $_SESSION['temp'] = true;
    }


    // create user object
    $this->set_user(new rcube_user($_SESSION['user_id']));

    // reset some session parameters when changing task
    if ($_SESSION['task'] != $this->task)
      unset($_SESSION['page']);

    // set current task to session
    $_SESSION['task'] = $this->task;

    // create IMAP object
    if ($this->task == 'mail')
      $this->imap_init();
  }
  
  
  /**
   * Setter for application task
   *
   * @param string Task to set
   */
  public function set_task($task)
  {
    if (!in_array($task, self::$main_tasks))
      $task = 'mail';
    
    $this->task = $task;
    $this->comm_path = './?_task=' . $task;
    
    if ($this->output)
      $this->output->set_env('task', $task);
  }
  
  
  /**
   * Setter for system user object
   *
   * @param object rcube_user Current user instance
   */
  public function set_user($user)
  {
    if (is_object($user)) {
      $this->user = $user;
      $GLOBALS['USER'] = $this->user;
      
      // overwrite config with user preferences
      $this->config->merge((array)$this->user->get_prefs());
    }
    
    $_SESSION['language'] = $this->user->language = $this->language_prop($this->config->get('language'));
    
    // set localization
    setlocale(LC_ALL, $_SESSION['language']);
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

    if (empty($rcube_languages)) {
      @include(INSTALL_PATH . 'program/localization/index.inc');
    }

    // check if we have an alias for that language
    if (!isset($rcube_languages[$lang]) && isset($rcube_language_aliases[$lang])) {
      $lang = $rcube_language_aliases[$lang];
    }

    // try the first two chars
    if (!isset($rcube_languages[$lang]) && strlen($lang)>2) {
      $lang = $this->language_prop(substr($lang, 0, 2));
    }

    if (!isset($rcube_languages[$lang])) {
      $lang = 'en_US';
    }

    return $lang;
  }
  
  
  /**
   * Get the current database connection
   *
   * @return object rcube_db  Database connection object
   */
  public function get_dbh()
  {
    if (!$this->db) {
      $dbclass = "rcube_" . $this->config->get('db_backend', 'mdb2');
      $config_all = $this->config->all();

      $this->db = new $dbclass($config_all['db_dsnw'], $config_all['db_dsnr'], $config_all['db_persistent']);
      $this->db->sqlite_initials = INSTALL_PATH . 'SQL/sqlite.initial.sql';
      $this->db->set_debug((bool)$config_all['sql_debug']);
      $this->db->db_connect('w');
    }

    return $this->db;
  }
  
  
  /**
   * Init output object for GUI and add common scripts.
   * This will instantiate a rcmail_template object and set
   * environment vars according to the current session and configuration
   */
  public function load_gui($framed = false)
  {
    // init output page
    $this->output = new rcube_template($this->task, $framed);

    foreach (array('flag_for_deletion') as $js_config_var) {
      $this->output->set_env($js_config_var, $this->config->get($js_config_var));
    }

    if ($framed) {
      $this->comm_path .= '&_framed=1';
      $this->output->set_env('framed', true);
    }

    $this->output->set_env('task', $this->task);
    $this->output->set_env('action', $this->action);
    $this->output->set_env('comm_path', $this->comm_path);
    $this->output->set_charset($this->config->get('charset', RCMAIL_CHARSET));

    // add some basic label to client
    $this->output->add_label('loading');
    
    return $this->output;
  }
  
  
  /**
   * Create an output object for JSON responses
   */
  public function init_json()
  {
    $this->output = new rcube_json_output($this->task);
    
    return $this->output;
  }
  
  
  /**
   * Create global IMAP object and connect to server
   *
   * @param boolean True if connection should be established
   * @todo Remove global $IMAP
   */
  function imap_init($connect = false)
  {
    $this->imap = new rcube_imap($this->db);
    $this->imap->debug_level = $this->config->get('debug_level');
    $this->imap->skip_deleted = $this->config->get('skip_deleted');

    // connect with stored session data
    if ($connect && $_SESSION['imap_host']) {
      if (!($conn = $this->imap->connect($_SESSION['imap_host'], $_SESSION['username'], decrypt_passwd($_SESSION['password']), $_SESSION['imap_port'], $_SESSION['imap_ssl'])))
        ; #$OUTPUT->show_message('imaperror', 'error');

      $this->set_imap_prop();
    }

    // enable caching of imap data
    if ($this->config->get('enable_caching')) {
      $this->imap->set_caching(true);
    }

    // set pagesize from config
    $this->imap->set_pagesize($this->config->get('pagesize', 50));
    
    // set global object for backward compatibility
    $GLOBALS['IMAP'] = $this->imap;
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
    else if (!empty($config['default_host']) && $host != $config['default_host'])
      return false;

    // parse $host URL
    $a_host = parse_url($host);
    if ($a_host['host']) {
      $host = $a_host['host'];
      $imap_ssl = (isset($a_host['scheme']) && in_array($a_host['scheme'], array('ssl','imaps','tls'))) ? $a_host['scheme'] : null;
      $imap_port = isset($a_host['port']) ? $a_host['port'] : ($imap_ssl ? 993 : $config['default_port']);
    }
    else
      $imap_port = $config['default_port'];


    /* Modify username with domain if required  
       Inspired by Marco <P0L0_notspam_binware.org>
    */
    // Check if we need to add domain
    if (!empty($config['username_domain']) && !strpos($username, '@')) {
      if (is_array($config['username_domain']) && isset($config['username_domain'][$host]))
        $username .= '@'.$config['username_domain'][$host];
      else if (is_string($config['username_domain']))
        $username .= '@'.$config['username_domain'];
    }

    // try to resolve email address from virtuser table    
    if (!empty($config['virtuser_file']) && strpos($username, '@'))
      $username = rcube_user::email2user($username);

    // lowercase username if it's an e-mail address (#1484473)
    if (strpos($username, '@'))
      $username = strtolower($username);

    // user already registered -> overwrite username
    if ($user = rcube_user::query($username, $host))
      $username = $user->data['username'];

    // exit if IMAP login failed
    if (!($imap_login  = $this->imap->connect($host, $username, $pass, $imap_port, $imap_ssl)))
      return false;

    // user already registered -> update user's record
    if (is_object($user)) {
      $user->touch();
    }
    // create new system user
    else if ($config['auto_create_user']) {
      if ($created = rcube_user::create($username, $host)) {
        $user = $created;

        // get existing mailboxes (but why?)
        // $a_mailboxes = $this->imap->list_mailboxes();
      }
    }
    else {
      raise_error(array(
        'code' => 600,
        'type' => 'php',
        'file' => "config/main.inc.php",
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
      $_SESSION['password']  = encrypt_passwd($pass);
      $_SESSION['login_time'] = mktime();

      // force reloading complete list of subscribed mailboxes
      $this->set_imap_prop();
      $this->imap->clear_cache('mailboxes');

      if ($config['create_default_folders'])
          $this->imap->create_default_folders();

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

    // set root dir from config
    if ($imap_root = $this->config->get('imap_root')) {
      $this->imap->set_rootdir($imap_root);
    }
    if ($default_folders = $this->config->get('default_imap_folders')) {
      $this->imap->set_default_mailboxes($default_folders);
    }
    if (!empty($_SESSION['mbox'])) {
      $this->imap->set_mailbox($_SESSION['mbox']);
    }
    if (isset($_SESSION['page'])) {
      $this->imap->set_page($_SESSION['page']);
    }
  }

  
  public function shutdown()
  {
    if (is_object($this->imap)) {
      $this->imap->close();
      $this->imap->write_cache();
    }

    if (is_object($this->contacts))
      $this->contacts->close();

    // before closing the database connection, write session data
    session_write_close();
  }
  
}



<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/main.inc                                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev, - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide basic functions for the webmail package                     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

/**
 * RoundCube Webmail common functions
 *
 * @package Core
 * @author Thomas Bruederli <roundcube@gmail.com>
 */

require_once('lib/des.inc');
require_once('lib/utf7.inc');
require_once('lib/utf8.class.php');
require_once('include/rcmail_template.inc');


// define constannts for input reading
define('RCUBE_INPUT_GET', 0x0101);
define('RCUBE_INPUT_POST', 0x0102);
define('RCUBE_INPUT_GPC', 0x0103);


/**
 * Initial startup function
 * to register session, create database and imap connections
 *
 * @param string Current task
 */
function rcmail_startup($task='mail')
  {
  global $sess_id, $sess_user_lang;
  global $CONFIG, $INSTALL_PATH, $BROWSER, $OUTPUT, $_SESSION, $IMAP, $DB;

  // check client
  $BROWSER = rcube_browser();

  // load configuration
  $CONFIG = rcmail_load_config();

  // set session garbage collecting time according to session_lifetime
  if (!empty($CONFIG['session_lifetime']))
    ini_set('session.gc_maxlifetime', ($CONFIG['session_lifetime']) * 120);

  // prepare DB connection
  $dbwrapper = empty($CONFIG['db_backend']) ? 'db' : $CONFIG['db_backend'];
  $dbclass = "rcube_" . $dbwrapper;
  require_once("include/$dbclass.inc");
  
  $DB = new $dbclass($CONFIG['db_dsnw'], $CONFIG['db_dsnr'], $CONFIG['db_persistent']);
  $DB->sqlite_initials = $INSTALL_PATH.'SQL/sqlite.initial.sql';
  $DB->db_connect('w');

  // use database for storing session data
  include_once('include/session.inc');

  // init session
  session_start();
  $sess_id = session_id();

  // create session and set session vars
  if (!isset($_SESSION['auth_time']))
    {
    $_SESSION['user_lang'] = rcube_language_prop($CONFIG['locale_string']);
    $_SESSION['auth_time'] = time();
    $_SESSION['temp'] = true;
    }

  // set session vars global
  $sess_user_lang = rcube_language_prop($_SESSION['user_lang']);


  // overwrite config with user preferences
  if (is_array($_SESSION['user_prefs']))
    $CONFIG = array_merge($CONFIG, $_SESSION['user_prefs']);


  // reset some session parameters when changing task
  if ($_SESSION['task'] != $task)
    unset($_SESSION['page']);

  // set current task to session
  $_SESSION['task'] = $task;

  // create IMAP object
  if ($task=='mail')
    rcmail_imap_init();


  // set localization
  if ($CONFIG['locale_string'])
    setlocale(LC_ALL, $CONFIG['locale_string']);
  else if ($sess_user_lang)
    setlocale(LC_ALL, $sess_user_lang);


  register_shutdown_function('rcmail_shutdown');
  }


/**
 * Load roundcube configuration array
 *
 * @return array Named configuration parameters
 */
function rcmail_load_config()
  {
  global $INSTALL_PATH;

  // load config file
  include_once('config/main.inc.php');
  $conf = is_array($rcmail_config) ? $rcmail_config : array();

  // load host-specific configuration
  rcmail_load_host_config($conf);

  $conf['skin_path'] = $conf['skin_path'] ? unslashify($conf['skin_path']) : 'skins/default';

  // load db conf
  include_once('config/db.inc.php');
  $conf = array_merge($conf, $rcmail_config);

  if (empty($conf['log_dir']))
    $conf['log_dir'] = $INSTALL_PATH.'logs';
  else
    $conf['log_dir'] = unslashify($conf['log_dir']);

  // set PHP error logging according to config
  if ($conf['debug_level'] & 1)
    {
    ini_set('log_errors', 1);
    ini_set('error_log', $conf['log_dir'].'/errors');
    }
  if ($conf['debug_level'] & 4)
    ini_set('display_errors', 1);
  else
    ini_set('display_errors', 0);

  return $conf;
  }


/**
 * Load a host-specific config file if configured
 * This will merge the host specific configuration with the given one
 *
 * @param array Global configuration parameters
 */
function rcmail_load_host_config(&$config)
  {
  $fname = NULL;
  
  if (is_array($config['include_host_config']))
    $fname = $config['include_host_config'][$_SERVER['HTTP_HOST']];
  else if (!empty($config['include_host_config']))
    $fname = preg_replace('/[^a-z0-9\.\-_]/i', '', $_SERVER['HTTP_HOST']) . '.inc.php';

   if ($fname && is_file('config/'.$fname))
     {
     include('config/'.$fname);
     $config = array_merge($config, $rcmail_config);
     }
  }


/**
 * Create unique authorization hash
 *
 * @param string Session ID
 * @param int Timestamp
 * @return string The generated auth hash
 */
function rcmail_auth_hash($sess_id, $ts)
  {
  global $CONFIG;
  
  $auth_string = sprintf('rcmail*sess%sR%s*Chk:%s;%s',
                         $sess_id,
                         $ts,
                         $CONFIG['ip_check'] ? $_SERVER['REMOTE_ADDR'] : '***.***.***.***',
                         $_SERVER['HTTP_USER_AGENT']);
  
  if (function_exists('sha1'))
    return sha1($auth_string);
  else
    return md5($auth_string);
  }


/**
 * Check the auth hash sent by the client against the local session credentials
 *
 * @return boolean True if valid, False if not
 */
function rcmail_authenticate_session()
  {
  global $CONFIG, $SESS_CLIENT_IP, $SESS_CHANGED;
  
  // advanced session authentication
  if ($CONFIG['double_auth'])
  {
    $now = time();
    $valid = ($_COOKIE['sessauth'] == rcmail_auth_hash(session_id(), $_SESSION['auth_time']) ||
              $_COOKIE['sessauth'] == rcmail_auth_hash(session_id(), $_SESSION['last_auth']));

    // renew auth cookie every 5 minutes (only for GET requests)
    if (!$valid || ($_SERVER['REQUEST_METHOD']!='POST' && $now-$_SESSION['auth_time'] > 300))
    {
      $_SESSION['last_auth'] = $_SESSION['auth_time'];
      $_SESSION['auth_time'] = $now;
      setcookie('sessauth', rcmail_auth_hash(session_id(), $now));
    }
  }
  else
    $valid = $CONFIG['ip_check'] ? $_SERVER['REMOTE_ADDR'] == $SESS_CLIENT_IP : true;
  
  // check session filetime
  if (!empty($CONFIG['session_lifetime']) && isset($SESS_CHANGED) && $SESS_CHANGED + $CONFIG['session_lifetime']*60 < time())
    $valid = false;
  
  return $valid;
  }


/**
 * Create global IMAP object and connect to server
 *
 * @param boolean True if connection should be established
 */
function rcmail_imap_init($connect=FALSE)
  {
  global $CONFIG, $DB, $IMAP, $OUTPUT;

  $IMAP = new rcube_imap($DB);
  $IMAP->debug_level = $CONFIG['debug_level'];
  $IMAP->skip_deleted = $CONFIG['skip_deleted'];


  // connect with stored session data
  if ($connect)
    {
    if (!($conn = $IMAP->connect($_SESSION['imap_host'], $_SESSION['username'], decrypt_passwd($_SESSION['password']), $_SESSION['imap_port'], $_SESSION['imap_ssl'])))
      $OUTPUT->show_message('imaperror', 'error');
      
    rcmail_set_imap_prop();
    }

  // enable caching of imap data
  if ($CONFIG['enable_caching']===TRUE)
    $IMAP->set_caching(TRUE);

  // set pagesize from config
  if (isset($CONFIG['pagesize']))
    $IMAP->set_pagesize($CONFIG['pagesize']);
  }


/**
 * Set root dir and last stored mailbox
 * This must be done AFTER connecting to the server!
 */
function rcmail_set_imap_prop()
  {
  global $CONFIG, $IMAP;

  // set root dir from config
  if (!empty($CONFIG['imap_root']))
    $IMAP->set_rootdir($CONFIG['imap_root']);

  if (is_array($CONFIG['default_imap_folders']))
    $IMAP->set_default_mailboxes($CONFIG['default_imap_folders']);

  if (!empty($_SESSION['mbox']))
    $IMAP->set_mailbox($_SESSION['mbox']);
  if (isset($_SESSION['page']))
    $IMAP->set_page($_SESSION['page']);
  }


/**
 * Do these things on script shutdown
 */
function rcmail_shutdown()
  {
  global $IMAP;
  
  if (is_object($IMAP))
    {
    $IMAP->close();
    $IMAP->write_cache();
    }
    
  // before closing the database connection, write session data
  session_write_close();
  }


/**
 * Destroy session data and remove cookie
 */
function rcmail_kill_session()
  {
  // save user preferences
  $a_user_prefs = $_SESSION['user_prefs'];
  if (!is_array($a_user_prefs))
    $a_user_prefs = array();
    
  if ((isset($_SESSION['sort_col']) && $_SESSION['sort_col']!=$a_user_prefs['message_sort_col']) ||
      (isset($_SESSION['sort_order']) && $_SESSION['sort_order']!=$a_user_prefs['message_sort_order']))
    {
    $a_user_prefs['message_sort_col'] = $_SESSION['sort_col'];
    $a_user_prefs['message_sort_order'] = $_SESSION['sort_order'];
    rcmail_save_user_prefs($a_user_prefs);
    }

  $_SESSION = array('user_lang' => $GLOBALS['sess_user_lang'], 'auth_time' => time(), 'temp' => true);
  setcookie('sessauth', '-del-', time()-60);
  }


/**
 * Return correct name for a specific database table
 *
 * @param string Table name
 * @return string Translated table name
 */
function get_table_name($table)
  {
  global $CONFIG;
  
  // return table name if configured
  $config_key = 'db_table_'.$table;

  if (strlen($CONFIG[$config_key]))
    return $CONFIG[$config_key];
  
  return $table;
  }


/**
 * Return correct name for a specific database sequence
 * (used for Postres only)
 *
 * @param string Secuence name
 * @return string Translated sequence name
 */
function get_sequence_name($sequence)
  {
  global $CONFIG;
  
  // return table name if configured
  $config_key = 'db_sequence_'.$sequence;

  if (strlen($CONFIG[$config_key]))
    return $CONFIG[$config_key];
  
  return $table;
  }


/**
 * Check the given string and returns language properties
 *
 * @param string Language code
 * @param string Peropert name
 * @return string Property value
 */
function rcube_language_prop($lang, $prop='lang')
  {
  global $INSTALL_PATH;
  static $rcube_languages, $rcube_language_aliases, $rcube_charsets;

  if (empty($rcube_languages))
    @include($INSTALL_PATH.'program/localization/index.inc');
    
  // check if we have an alias for that language
  if (!isset($rcube_languages[$lang]) && isset($rcube_language_aliases[$lang]))
    $lang = $rcube_language_aliases[$lang];
    
  // try the first two chars
  if (!isset($rcube_languages[$lang]) && strlen($lang)>2)
    {
    $lang = substr($lang, 0, 2);
    $lang = rcube_language_prop($lang);
    }

  if (!isset($rcube_languages[$lang]))
    $lang = 'en_US';

  // language has special charset configured
  if (isset($rcube_charsets[$lang]))
    $charset = $rcube_charsets[$lang];
  else
    $charset = 'UTF-8';    


  if ($prop=='charset')
    return $charset;
  else
    return $lang;
  }
  

/**
 * Init output object for GUI and add common scripts.
 * This will instantiate a rcmail_template object and set
 * environment vars according to the current session and configuration
 */
function rcmail_load_gui()
  {
  global $CONFIG, $OUTPUT, $sess_user_lang;

  // init output page
  $OUTPUT = new rcmail_template($CONFIG, $GLOBALS['_task']);
  $OUTPUT->set_env('comm_path', $GLOBALS['COMM_PATH']);

  if (is_array($CONFIG['javascript_config']))
  {
    foreach ($CONFIG['javascript_config'] as $js_config_var)
      $OUTPUT->set_env($js_config_var, $CONFIG[$js_config_var]);
  }

  if (!empty($GLOBALS['_framed']))
    $OUTPUT->set_env('framed', true);

  // set locale setting
  rcmail_set_locale($sess_user_lang);

  // set user-selected charset
  if (!empty($CONFIG['charset']))
    $OUTPUT->set_charset($CONFIG['charset']);
    
  // register common UI objects
  $OUTPUT->add_handlers(array(
    'loginform' => 'rcmail_login_form',
    'username'  => 'rcmail_current_username',
    'message' => 'rcmail_message_container',
    'charsetselector' => 'rcmail_charset_selector',
  ));

  // add some basic label to client
  if (!$OUTPUT->ajax_call)
    rcube_add_label('loading');
  }


/**
 * Set localization charset based on the given language.
 * This also creates a global property for mbstring usage.
 */
function rcmail_set_locale($lang)
  {
  global $OUTPUT, $MBSTRING;
  static $s_mbstring_loaded = NULL;
  
  // settings for mbstring module (by Tadashi Jokagi)
  if (is_null($s_mbstring_loaded)) 
    $MBSTRING = $s_mbstring_loaded = extension_loaded("mbstring"); 
  else
    $MBSTRING = $s_mbstring_loaded = FALSE;
  
  if ($MBSTRING)
    mb_internal_encoding(RCMAIL_CHARSET);

  $OUTPUT->set_charset(rcube_language_prop($lang, 'charset'));
  }


/**
 * Auto-select IMAP host based on the posted login information
 *
 * @return string Selected IMAP host
 */
function rcmail_autoselect_host()
  {
  global $CONFIG;
  
  $host = isset($_POST['_host']) ? get_input_value('_host', RCUBE_INPUT_POST) : $CONFIG['default_host'];
  if (is_array($host))
    {
    list($user, $domain) = explode('@', get_input_value('_user', RCUBE_INPUT_POST));
    if (!empty($domain))
      {
      foreach ($host as $imap_host => $mail_domains)
        if (is_array($mail_domains) && in_array($domain, $mail_domains))
          {
          $host = $imap_host;
          break;
          }
      }

    // take the first entry if $host is still an array
    if (is_array($host))
      $host = array_shift($host);
    }
  
  return $host;
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
function rcmail_login($user, $pass, $host=NULL)
  {
  global $CONFIG, $IMAP, $DB, $sess_user_lang;
  $user_id = NULL;
  
  if (!$host)
    $host = $CONFIG['default_host'];

  // Validate that selected host is in the list of configured hosts
  if (is_array($CONFIG['default_host']))
    {
    $allowed = FALSE;
    foreach ($CONFIG['default_host'] as $key => $host_allowed)
      {
      if (!is_numeric($key))
        $host_allowed = $key;
      if ($host == $host_allowed)
        {
        $allowed = TRUE;
        break;
        }
      }
    if (!$allowed)
      return FALSE;
    }
  else if (!empty($CONFIG['default_host']) && $host != $CONFIG['default_host'])
    return FALSE;

  // parse $host URL
  $a_host = parse_url($host);
  if ($a_host['host'])
    {
    $host = $a_host['host'];
    $imap_ssl = (isset($a_host['scheme']) && in_array($a_host['scheme'], array('ssl','imaps','tls'))) ? TRUE : FALSE;
    $imap_port = isset($a_host['port']) ? $a_host['port'] : ($imap_ssl ? 993 : $CONFIG['default_port']);
    }
  else
    $imap_port = $CONFIG['default_port'];


  /* Modify username with domain if required  
     Inspired by Marco <P0L0_notspam_binware.org>
  */
  // Check if we need to add domain
  if (!empty($CONFIG['username_domain']) && !strstr($user, '@'))
    {
    if (is_array($CONFIG['username_domain']) && isset($CONFIG['username_domain'][$host]))
      $user .= '@'.$CONFIG['username_domain'][$host];
    else if (is_string($CONFIG['username_domain']))
      $user .= '@'.$CONFIG['username_domain'];
    }


  // query if user already registered
  $sql_result = $DB->query("SELECT user_id, username, language, preferences
                            FROM ".get_table_name('users')."
                            WHERE  mail_host=? AND (username=? OR alias=?)",
                            $host,
                            $user,
                            $user);

  // user already registered -> overwrite username
  if ($sql_arr = $DB->fetch_assoc($sql_result))
    {
    $user_id = $sql_arr['user_id'];
    $user = $sql_arr['username'];
    }

  // try to resolve email address from virtuser table    
  if (!empty($CONFIG['virtuser_file']) && strstr($user, '@'))
    $user = rcmail_email2user($user);


  // exit if IMAP login failed
  if (!($imap_login  = $IMAP->connect($host, $user, $pass, $imap_port, $imap_ssl)))
    return FALSE;

  // user already registered
  if ($user_id && !empty($sql_arr))
    {
    // get user prefs
    if (strlen($sql_arr['preferences']))
      {
      $user_prefs = unserialize($sql_arr['preferences']);
      $_SESSION['user_prefs'] = $user_prefs;
      array_merge($CONFIG, $user_prefs);
      }


    // set user specific language
    if (strlen($sql_arr['language']))
      $sess_user_lang = $_SESSION['user_lang'] = $sql_arr['language'];
      
    // update user's record
    $DB->query("UPDATE ".get_table_name('users')."
                SET    last_login=".$DB->now()."
                WHERE  user_id=?",
                $user_id);
    }
  // create new system user
  else if ($CONFIG['auto_create_user'])
    {
    $user_id = rcmail_create_user($user, $host);
    }

  if ($user_id)
    {
    $_SESSION['user_id']   = $user_id;
    $_SESSION['imap_host'] = $host;
    $_SESSION['imap_port'] = $imap_port;
    $_SESSION['imap_ssl']  = $imap_ssl;
    $_SESSION['username']  = $user;
    $_SESSION['user_lang'] = $sess_user_lang;
    $_SESSION['password']  = encrypt_passwd($pass);
    $_SESSION['login_time'] = mktime();

    // force reloading complete list of subscribed mailboxes
    rcmail_set_imap_prop();
    $IMAP->clear_cache('mailboxes');
    $IMAP->create_default_folders();

    return TRUE;
    }

  return FALSE;
  }


/**
 * Create new entry in users and identities table
 *
 * @param string User name
 * @param string IMAP host
 * @return mixed New user ID or False on failure
 */
function rcmail_create_user($user, $host)
{
  global $DB, $CONFIG, $IMAP;

  $user_email = '';

  // try to resolve user in virtusertable
  if (!empty($CONFIG['virtuser_file']) && strstr($user, '@')==FALSE)
    $user_email = rcmail_user2email($user);

  $DB->query("INSERT INTO ".get_table_name('users')."
              (created, last_login, username, mail_host, alias, language)
              VALUES (".$DB->now().", ".$DB->now().", ?, ?, ?, ?)",
              strip_newlines($user),
              strip_newlines($host),
              strip_newlines($user_email),
              $_SESSION['user_lang']);

  if ($user_id = $DB->insert_id(get_sequence_name('users')))
  {
    $mail_domain = rcmail_mail_domain($host);
   
    if ($user_email=='')
      $user_email = strstr($user, '@') ? $user : sprintf('%s@%s', $user, $mail_domain);

    $user_name = $user!=$user_email ? $user : '';

    // try to resolve the e-mail address from the virtuser table
    if (!empty($CONFIG['virtuser_query']) &&
        ($sql_result = $DB->query(preg_replace('/%u/', $DB->quote($user), $CONFIG['virtuser_query']))) &&
        ($DB->num_rows()>0))
    {
      while ($sql_arr = $DB->fetch_array($sql_result))
      {
        $DB->query("INSERT INTO ".get_table_name('identities')."
                   (user_id, del, standard, name, email)
                   VALUES (?, 0, 1, ?, ?)",
                   $user_id,
                   strip_newlines($user_name),
                   preg_replace('/^@/', $user . '@', $sql_arr[0]));
      }
    }
    else
    {
      // also create new identity records
      $DB->query("INSERT INTO ".get_table_name('identities')."
                  (user_id, del, standard, name, email)
                  VALUES (?, 0, 1, ?, ?)",
                  $user_id,
                  strip_newlines($user_name),
                  strip_newlines($user_email));
    }
                       
    // get existing mailboxes
    $a_mailboxes = $IMAP->list_mailboxes();
  }
  else
  {
    raise_error(array(
      'code' => 500,
      'type' => 'php',
      'line' => __LINE__,
      'file' => __FILE__,
      'message' => "Failed to create new user"), TRUE, FALSE);
  }
    
  return $user_id;
}


/**
 * Load virtuser table in array
 *
 * @return array Virtuser table entries
 */
function rcmail_getvirtualfile()
  {
  global $CONFIG;
  if (empty($CONFIG['virtuser_file']) || !is_file($CONFIG['virtuser_file']))
    return FALSE;
  
  // read file 
  $a_lines = file($CONFIG['virtuser_file']);
  return $a_lines;
  }


/**
 * Find matches of the given pattern in virtuser table
 * 
 * @param string Regular expression to search for
 * @return array Matching entries
 */
function rcmail_findinvirtual($pattern)
  {
  $result = array();
  $virtual = rcmail_getvirtualfile();
  if ($virtual==FALSE)
    return $result;

  // check each line for matches
  foreach ($virtual as $line)
    {
    $line = trim($line);
    if (empty($line) || $line{0}=='#')
      continue;
      
    if (eregi($pattern, $line))
      $result[] = $line;
    }

  return $result;
  }


/**
 * Resolve username using a virtuser table
 *
 * @param string E-mail address to resolve
 * @return string Resolved IMAP username
 */
function rcmail_email2user($email)
  {
  $user = $email;
  $r = rcmail_findinvirtual("^$email");

  for ($i=0; $i<count($r); $i++)
    {
    $data = $r[$i];
    $arr = preg_split('/\s+/', $data);
    if(count($arr)>0)
      {
      $user = trim($arr[count($arr)-1]);
      break;
      }
    }

  return $user;
  }


/**
 * Resolve e-mail address from virtuser table
 *
 * @param string User name
 * @return string Resolved e-mail address
 */
function rcmail_user2email($user)
  {
  $email = "";
  $r = rcmail_findinvirtual("$user$");

  for ($i=0; $i<count($r); $i++)
    {
    $data=$r[$i];
    $arr = preg_split('/\s+/', $data);
    if (count($arr)>0)
      {
      $email = trim($arr[0]);
      break;
      }
    }

  return $email;
  } 


/**
 * Write the given user prefs to the user's record
 *
 * @param mixed User prefs to save
 * @return boolean True on success, False on failure
 */
function rcmail_save_user_prefs($a_user_prefs)
  {
  global $DB, $CONFIG, $sess_user_lang;
  
  $DB->query("UPDATE ".get_table_name('users')."
              SET    preferences=?,
                     language=?
              WHERE  user_id=?",
              serialize($a_user_prefs),
              $sess_user_lang,
              $_SESSION['user_id']);

  if ($DB->affected_rows())
    {
    $_SESSION['user_prefs'] = $a_user_prefs;  
    $CONFIG = array_merge($CONFIG, $a_user_prefs);
    return TRUE;
    }
    
  return FALSE;
  }


/**
 * Overwrite action variable
 *
 * @param string New action value
 */
function rcmail_overwrite_action($action)
  {
  global $OUTPUT;
  $GLOBALS['_action'] = $action;
  $OUTPUT->set_env('action', $action);
  }


/**
 * Compose an URL for a specific action
 *
 * @param string  Request action
 * @param array   More URL parameters
 * @param string  Request task (omit if the same)
 * @return The application URL
 */
function rcmail_url($action, $p=array(), $task=null)
{
  global $MAIN_TASKS, $COMM_PATH;
  $qstring = '';
  $base = $COMM_PATH;
  
  if ($task && in_array($task, $MAIN_TASKS))
    $base = ereg_replace('_task=[a-z]+', '_task='.$task, $COMM_PATH);
  
  if (is_array($p))
    foreach ($p as $key => $val)
      $qstring .= '&'.urlencode($key).'='.urlencode($val);
  
  return $base . ($action ? '&_action='.$action : '') . $qstring;
}


// @deprecated
function show_message($message, $type='notice', $vars=NULL)
  {
  global $OUTPUT;
  $OUTPUT->show_message($message, $type, $vars);
  }


/**
 * Encrypt IMAP password using DES encryption
 *
 * @param string Password to encrypt
 * @return string Encryprted string
 */
function encrypt_passwd($pass)
  {
  $cypher = des(get_des_key(), $pass, 1, 0, NULL);
  return base64_encode($cypher);
  }


/**
 * Decrypt IMAP password using DES encryption
 *
 * @param string Encrypted password
 * @return string Plain password
 */
function decrypt_passwd($cypher)
  {
  $pass = des(get_des_key(), base64_decode($cypher), 0, 0, NULL);
  return preg_replace('/\x00/', '', $pass);
  }


/**
 * Return a 24 byte key for the DES encryption
 *
 * @return string DES encryption key
 */
function get_des_key()
  {
  $key = !empty($GLOBALS['CONFIG']['des_key']) ? $GLOBALS['CONFIG']['des_key'] : 'rcmail?24BitPwDkeyF**ECB';
  $len = strlen($key);
  
  // make sure the key is exactly 24 chars long
  if ($len<24)
    $key .= str_repeat('_', 24-$len);
  else if ($len>24)
    substr($key, 0, 24);
  
  return $key;
  }


/**
 * Read directory program/localization and return a list of available languages
 *
 * @return array List of available localizations
 */
function rcube_list_languages()
  {
  global $CONFIG, $INSTALL_PATH;
  static $sa_languages = array();

  if (!sizeof($sa_languages))
    {
    @include($INSTALL_PATH.'program/localization/index.inc');

    if ($dh = @opendir($INSTALL_PATH.'program/localization'))
      {
      while (($name = readdir($dh)) !== false)
        {
        if ($name{0}=='.' || !is_dir($INSTALL_PATH.'program/localization/'.$name))
          continue;

        if ($label = $rcube_languages[$name])
          $sa_languages[$name] = $label ? $label : $name;
        }
      closedir($dh);
      }
    }
  return $sa_languages;
  }


/**
 * Add a localized label to the client environment
 */
function rcube_add_label()
  {
  global $OUTPUT;
  
  $arg_list = func_get_args();
  foreach ($arg_list as $i => $name)
    $OUTPUT->command('add_label', $name, rcube_label($name));
  }


/**
 * Garbage collector function for temp files.
 * Remove temp files older than two days
 */
function rcmail_temp_gc()
  {
  $tmp = unslashify($CONFIG['temp_dir']);
  $expire = mktime() - 172800;  // expire in 48 hours

  if ($dir = opendir($tmp))
    {
    while (($fname = readdir($dir)) !== false)
      {
      if ($fname{0} == '.')
        continue;

      if (filemtime($tmp.'/'.$fname) < $expire)
        @unlink($tmp.'/'.$fname);
      }

    closedir($dir);
    }
  }


/**
 * Garbage collector for cache entries.
 * Remove all expired message cache records
 */
function rcmail_message_cache_gc()
  {
  global $DB, $CONFIG;
  
  // no cache lifetime configured
  if (empty($CONFIG['message_cache_lifetime']))
    return;
  
  // get target timestamp
  $ts = get_offset_time($CONFIG['message_cache_lifetime'], -1);
  
  $DB->query("DELETE FROM ".get_table_name('messages')."
             WHERE  created < ".$DB->fromunixtime($ts));
  }


/**
 * Convert a string from one charset to another.
 * Uses mbstring and iconv functions if possible
 *
 * @param  string Input string
 * @param  string Suspected charset of the input string
 * @param  string Target charset to convert to; defaults to RCMAIL_CHARSET
 * @return Converted string
 */
function rcube_charset_convert($str, $from, $to=NULL)
  {
  global $MBSTRING;

  $from = strtoupper($from);
  $to = $to==NULL ? strtoupper(RCMAIL_CHARSET) : strtoupper($to);

  if ($from==$to || $str=='' || empty($from))
    return $str;

  // convert charset using iconv module  
  if (function_exists('iconv') && $from != 'UTF-7' && $to != 'UTF-7')
    return iconv($from, $to . "//IGNORE", $str);

  // convert charset using mbstring module  
  if ($MBSTRING)
    {
    $to = $to=="UTF-7" ? "UTF7-IMAP" : $to; 
    $from = $from=="UTF-7" ? "UTF7-IMAP": $from;
    
    // return if convert succeeded
    if (($out = mb_convert_encoding($str, $to, $from)) != '')
      return $out;
    }

  $conv = new utf8();

  // convert string to UTF-8
  if ($from=='UTF-7')
    $str = utf7_to_utf8($str);
  else if (($from=='ISO-8859-1') && function_exists('utf8_encode'))
    $str = utf8_encode($str);
  else if ($from!='UTF-8')
    {
    $conv->loadCharset($from);
    $str = $conv->strToUtf8($str);
    }

  // encode string for output
  if ($to=='UTF-7')
    return utf8_to_utf7($str);
  else if ($to=='ISO-8859-1' && function_exists('utf8_decode'))
    return utf8_decode($str);
  else if ($to!='UTF-8')
    {
    $conv->loadCharset($to);
    return $conv->utf8ToStr($str);
    }

  // return UTF-8 string
  return $str;
  }


/**
 * Replacing specials characters to a specific encoding type
 *
 * @param  string  Input string
 * @param  string  Encoding type: text|html|xml|js|url
 * @param  string  Replace mode for tags: show|replace|remove
 * @param  boolean Convert newlines
 * @return The quoted string
 */
function rep_specialchars_output($str, $enctype='', $mode='', $newlines=TRUE)
  {
  global $OUTPUT_TYPE, $OUTPUT;
  static $html_encode_arr, $js_rep_table, $xml_rep_table;

  if (!$enctype)
    $enctype = $GLOBALS['OUTPUT_TYPE'];

  // convert nbsps back to normal spaces if not html
  if ($enctype!='html')
    $str = str_replace(chr(160), ' ', $str);

  // encode for plaintext
  if ($enctype=='text')
    return str_replace("\r\n", "\n", $mode=='remove' ? strip_tags($str) : $str);

  // encode for HTML output
  if ($enctype=='html')
    {
    if (!$html_encode_arr)
      {
      $html_encode_arr = get_html_translation_table(HTML_SPECIALCHARS);        
      unset($html_encode_arr['?']);
      }

    $ltpos = strpos($str, '<');
    $encode_arr = $html_encode_arr;

    // don't replace quotes and html tags
    if (($mode=='show' || $mode=='') && $ltpos!==false && strpos($str, '>', $ltpos)!==false)
      {
      unset($encode_arr['"']);
      unset($encode_arr['<']);
      unset($encode_arr['>']);
      unset($encode_arr['&']);
      }
    else if ($mode=='remove')
      $str = strip_tags($str);
    
    // avoid douple quotation of &
    $out = preg_replace('/&amp;([a-z]{2,5}|#[0-9]{2,4});/', '&\\1;', strtr($str, $encode_arr));
      
    return $newlines ? nl2br($out) : $out;
    }

  if ($enctype=='url')
    return rawurlencode($str);

  // if the replace tables for XML and JS are not yet defined
  if (!$js_rep_table)
    {
    $js_rep_table = $xml_rep_table = array();
    $xml_rep_table['&'] = '&amp;';

    for ($c=160; $c<256; $c++)  // can be increased to support more charsets
      {
      $hex = dechex($c);
      $xml_rep_table[Chr($c)] = "&#$c;";
      
      if ($OUTPUT->get_charset()=='ISO-8859-1')
        $js_rep_table[Chr($c)] = sprintf("\u%s%s", str_repeat('0', 4-strlen($hex)), $hex);
      }

    $xml_rep_table['"'] = '&quot;';
    }

  // encode for XML
  if ($enctype=='xml')
    return strtr($str, $xml_rep_table);

  // encode for javascript use
  if ($enctype=='js')
    {
    if ($OUTPUT->get_charset()!='UTF-8')
      $str = rcube_charset_convert($str, RCMAIL_CHARSET, $OUTPUT->get_charset());
      
    return preg_replace(array("/\r?\n/", "/\r/"), array('\n', '\n'), addslashes(strtr($str, $js_rep_table)));
    }

  // no encoding given -> return original string
  return $str;
  }
  
/**
 * Quote a given string.
 * Shortcut function for rep_specialchars_output
 *
 * @return string HTML-quoted string
 * @see rep_specialchars_output()
 */
function Q($str, $mode='strict', $newlines=TRUE)
  {
  return rep_specialchars_output($str, 'html', $mode, $newlines);
  }

/**
 * Quote a given string for javascript output.
 * Shortcut function for rep_specialchars_output
 * 
 * @return string JS-quoted string
 * @see rep_specialchars_output()
 */
function JQ($str)
  {
  return rep_specialchars_output($str, 'js');
  }


/**
 * Read input value and convert it for internal use
 * Performs stripslashes() and charset conversion if necessary
 * 
 * @param  string   Field name to read
 * @param  int      Source to get value from (GPC)
 * @param  boolean  Allow HTML tags in field value
 * @param  string   Charset to convert into
 * @return string   Field value or NULL if not available
 */
function get_input_value($fname, $source, $allow_html=FALSE, $charset=NULL)
  {
  global $OUTPUT;
  $value = NULL;
  
  if ($source==RCUBE_INPUT_GET && isset($_GET[$fname]))
    $value = $_GET[$fname];
  else if ($source==RCUBE_INPUT_POST && isset($_POST[$fname]))
    $value = $_POST[$fname];
  else if ($source==RCUBE_INPUT_GPC)
    {
    if (isset($_POST[$fname]))
      $value = $_POST[$fname];
    else if (isset($_GET[$fname]))
      $value = $_GET[$fname];
    else if (isset($_COOKIE[$fname]))
      $value = $_COOKIE[$fname];
    }
  
  // strip slashes if magic_quotes enabled
  if ((bool)get_magic_quotes_gpc())
    $value = stripslashes($value);

  // remove HTML tags if not allowed    
  if (!$allow_html)
    $value = strip_tags($value);
  
  // convert to internal charset
  if (is_object($OUTPUT))
    return rcube_charset_convert($value, $OUTPUT->get_charset(), $charset);
  else
    return $value;
  }


/**
 * Remove single and double quotes from given string
 *
 * @param string Input value
 * @return string Dequoted string
 */
function strip_quotes($str)
{
  return preg_replace('/[\'"]/', '', $str);
}


/**
 * Remove new lines characters from given string
 *
 * @param string Input value
 * @return string Stripped string
 */
function strip_newlines($str)
{
  return preg_replace('/[\r\n]/', '', $str);
}


/**
 * Check if a specific template exists
 *
 * @param string Template name
 * @return boolean True if template exists
 */
function template_exists($name)
  {
  global $CONFIG;
  $skin_path = $CONFIG['skin_path'];

  // check template file
  return is_file("$skin_path/templates/$name.html");
  }


/**
 * Wrapper for rcmail_template::parse()
 * @deprecated
 */
function parse_template($name='main', $exit=true)
  {
  $GLOBALS['OUTPUT']->parse($name, $exit);
  }


/**
 * Create a HTML table based on the given data
 *
 * @param  array  Named table attributes
 * @param  mixed  Table row data. Either a two-dimensional array or a valid SQL result set
 * @param  array  List of cols to show
 * @param  string Name of the identifier col
 * @return string HTML table code
 */
function rcube_table_output($attrib, $table_data, $a_show_cols, $id_col)
  {
  global $DB;
  
  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));
  
  $table = '<table' . $attrib_str . ">\n";
    
  // add table title
  $table .= "<thead><tr>\n";

  foreach ($a_show_cols as $col)
    $table .= '<td class="'.$col.'">' . Q(rcube_label($col)) . "</td>\n";

  $table .= "</tr></thead>\n<tbody>\n";
  
  $c = 0;
  if (!is_array($table_data)) 
    {
    while ($table_data && ($sql_arr = $DB->fetch_assoc($table_data)))
      {
      $zebra_class = $c%2 ? 'even' : 'odd';

      $table .= sprintf('<tr id="rcmrow%d" class="contact '.$zebra_class.'">'."\n", $sql_arr[$id_col]);

      // format each col
      foreach ($a_show_cols as $col)
        {
        $cont = Q($sql_arr[$col]);
        $table .= '<td class="'.$col.'">' . $cont . "</td>\n";
        }

      $table .= "</tr>\n";
      $c++;
      }
    }
  else 
    {
    foreach ($table_data as $row_data)
      {
      $zebra_class = $c%2 ? 'even' : 'odd';

      $table .= sprintf('<tr id="rcmrow%d" class="contact '.$zebra_class.'">'."\n", $row_data[$id_col]);

      // format each col
      foreach ($a_show_cols as $col)
        {
        $cont = Q($row_data[$col]);
        $table .= '<td class="'.$col.'">' . $cont . "</td>\n";
        }

      $table .= "</tr>\n";
      $c++;
      }
    }

  // complete message table
  $table .= "</tbody></table>\n";
  
  return $table;
  }


/**
 * Create an edit field for inclusion on a form
 * 
 * @param string col field name
 * @param string value field value
 * @param array attrib HTML element attributes for field
 * @param string type HTML element type (default 'text')
 * @return string HTML field definition
 */
function rcmail_get_edit_field($col, $value, $attrib, $type='text')
  {
  $fname = '_'.$col;
  $attrib['name'] = $fname;
  
  if ($type=='checkbox')
    {
    $attrib['value'] = '1';
    $input = new checkbox($attrib);
    }
  else if ($type=='textarea')
    {
    $attrib['cols'] = $attrib['size'];
    $input = new textarea($attrib);
    }
  else
    $input = new textfield($attrib);

  // use value from post
  if (!empty($_POST[$fname]))
    $value = $_POST[$fname];

  $out = $input->show($value);
         
  return $out;
  }


/**
 * Return the mail domain configured for the given host
 *
 * @param string IMAP host
 * @return string Resolved SMTP host
 */
function rcmail_mail_domain($host)
  {
  global $CONFIG;

  $domain = $host;
  if (is_array($CONFIG['mail_domain']))
    {
    if (isset($CONFIG['mail_domain'][$host]))
      $domain = $CONFIG['mail_domain'][$host];
    }
  else if (!empty($CONFIG['mail_domain']))
    $domain = $CONFIG['mail_domain'];

  return $domain;
  }


/**
 * Compose a valid attribute string for HTML tags
 *
 * @param array Named tag attributes
 * @param array List of allowed attributes
 * @return string HTML formatted attribute string
 */
function create_attrib_string($attrib, $allowed_attribs=array('id', 'class', 'style'))
  {
  // allow the following attributes to be added to the <iframe> tag
  $attrib_str = '';
  foreach ($allowed_attribs as $a)
    if (isset($attrib[$a]))
      $attrib_str .= sprintf(' %s="%s"', $a, str_replace('"', '&quot;', $attrib[$a]));

  return $attrib_str;
  }


/**
 * Convert a HTML attribute string attributes to an associative array (name => value)
 *
 * @param string Input string
 * @return array Key-value pairs of parsed attributes
 */
function parse_attrib_string($str)
  {
  $attrib = array();
  preg_match_all('/\s*([-_a-z]+)=(["\'])([^"]+)\2/Ui', stripslashes($str), $regs, PREG_SET_ORDER);

  // convert attributes to an associative array (name => value)
  if ($regs)
    foreach ($regs as $attr)
      $attrib[strtolower($attr[1])] = $attr[3];

  return $attrib;
  }


/**
 * Convert the given date to a human readable form
 * This uses the date formatting properties from config
 *
 * @param mixed Date representation (string or timestamp)
 * @param string Date format to use
 * @return string Formatted date string
 */
function format_date($date, $format=NULL)
  {
  global $CONFIG, $sess_user_lang;
  
  $ts = NULL;
  
  if (is_numeric($date))
    $ts = $date;
  else if (!empty($date))
    $ts = @strtotime($date);
    
  if (empty($ts))
    return '';
   
  // get user's timezone
  $tz = $CONFIG['timezone'];
  if ($CONFIG['dst_active'])
    $tz++;

  // convert time to user's timezone
  $timestamp = $ts - date('Z', $ts) + ($tz * 3600);
  
  // get current timestamp in user's timezone
  $now = time();  // local time
  $now -= (int)date('Z'); // make GMT time
  $now += ($tz * 3600); // user's time
  $now_date = getdate($now);

  $today_limit = mktime(0, 0, 0, $now_date['mon'], $now_date['mday'], $now_date['year']);
  $week_limit = mktime(0, 0, 0, $now_date['mon'], $now_date['mday']-6, $now_date['year']);

  // define date format depending on current time  
  if ($CONFIG['prettydate'] && !$format && $timestamp > $today_limit && $timestamp < $now)
    return sprintf('%s %s', rcube_label('today'), date($CONFIG['date_today'] ? $CONFIG['date_today'] : 'H:i', $timestamp));
  else if ($CONFIG['prettydate'] && !$format && $timestamp > $week_limit && $timestamp < $now)
    $format = $CONFIG['date_short'] ? $CONFIG['date_short'] : 'D H:i';
  else if (!$format)
    $format = $CONFIG['date_long'] ? $CONFIG['date_long'] : 'd.m.Y H:i';


  // parse format string manually in order to provide localized weekday and month names
  // an alternative would be to convert the date() format string to fit with strftime()
  $out = '';
  for($i=0; $i<strlen($format); $i++)
    {
    if ($format{$i}=='\\')  // skip escape chars
      continue;
    
    // write char "as-is"
    if ($format{$i}==' ' || $format{$i-1}=='\\')
      $out .= $format{$i};
    // weekday (short)
    else if ($format{$i}=='D')
      $out .= rcube_label(strtolower(date('D', $timestamp)));
    // weekday long
    else if ($format{$i}=='l')
      $out .= rcube_label(strtolower(date('l', $timestamp)));
    // month name (short)
    else if ($format{$i}=='M')
      $out .= rcube_label(strtolower(date('M', $timestamp)));
    // month name (long)
    else if ($format{$i}=='F')
      $out .= rcube_label(strtolower(date('F', $timestamp)));
    else
      $out .= date($format{$i}, $timestamp);
    }
  
  return $out;
  }


/**
 * Compose a valid representaion of name and e-mail address
 *
 * @param string E-mail address
 * @param string Person name
 * @return string Formatted string
 */
function format_email_recipient($email, $name='')
  {
  if ($name && $name != $email)
    return sprintf('%s <%s>', strpos($name, ",") ? '"'.$name.'"' : $name, $email);
  else
    return $email;
  }



/****** debugging functions ********/


/**
 * Print or write debug messages
 *
 * @param mixed Debug message or data
 */
function console($msg)
  {
  if (!is_string($msg))
    $msg = var_export($msg, true);

  if (!($GLOBALS['CONFIG']['debug_level'] & 4))
    write_log('console', $msg);
  else if ($GLOBALS['REMOTE_REQUEST'])
    print "/*\n $msg \n*/\n";
  else
    {
    print '<div style="background:#eee; border:1px solid #ccc; margin-bottom:3px; padding:6px"><pre>';
    print $msg;
    print "</pre></div>\n";
    }
  }


/**
 * Append a line to a logfile in the logs directory.
 * Date will be added automatically to the line.
 *
 * @param $name Name of logfile
 * @param $line Line to append
 */
function write_log($name, $line)
  {
  global $CONFIG, $INSTALL_PATH;

  if (!is_string($line))
    $line = var_export($line, true);
  
  $log_entry = sprintf("[%s]: %s\n",
                 date("d-M-Y H:i:s O", mktime()),
                 $line);
                 
  if (empty($CONFIG['log_dir']))
    $CONFIG['log_dir'] = $INSTALL_PATH.'logs';
      
  // try to open specific log file for writing
  if ($fp = @fopen($CONFIG['log_dir'].'/'.$name, 'a'))    
    {
    fwrite($fp, $log_entry);
    fclose($fp);
    }
  }


/**
 * @access private
 */
function rcube_timer()
  {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
  }
  

/**
 * @access private
 */
function rcube_print_time($timer, $label='Timer')
  {
  static $print_count = 0;
  
  $print_count++;
  $now = rcube_timer();
  $diff = $now-$timer;
  
  if (empty($label))
    $label = 'Timer '.$print_count;
  
  console(sprintf("%s: %0.4f sec", $label, $diff));
  }


/**
 * Return the mailboxlist in HTML
 *
 * @param array Named parameters
 * @return string HTML code for the gui object
 */
function rcmail_mailbox_list($attrib)
  {
  global $IMAP, $CONFIG, $OUTPUT, $COMM_PATH;
  static $s_added_script = FALSE;
  static $a_mailboxes;

  // add some labels to client
  rcube_add_label('purgefolderconfirm');
  rcube_add_label('deletemessagesconfirm');
  
// $mboxlist_start = rcube_timer();
  
  $type = $attrib['type'] ? $attrib['type'] : 'ul';
  $add_attrib = $type=='select' ? array('style', 'class', 'id', 'name', 'onchange') :
                                  array('style', 'class', 'id');
                                  
  if ($type=='ul' && !$attrib['id'])
    $attrib['id'] = 'rcmboxlist';

  // allow the following attributes to be added to the <ul> tag
  $attrib_str = create_attrib_string($attrib, $add_attrib);
 
  $out = '<' . $type . $attrib_str . ">\n";
  
  // add no-selection option
  if ($type=='select' && $attrib['noselection'])
    $out .= sprintf('<option value="0">%s</option>'."\n",
                    rcube_label($attrib['noselection']));
  
  // get mailbox list
  $mbox_name = $IMAP->get_mailbox_name();
  
  // for these mailboxes we have localized labels
  $special_mailboxes = array('inbox', 'sent', 'drafts', 'trash', 'junk');


  // build the folders tree
  if (empty($a_mailboxes))
    {
    // get mailbox list
    $a_folders = $IMAP->list_mailboxes();
    $delimiter = $IMAP->get_hierarchy_delimiter();
    $a_mailboxes = array();

// rcube_print_time($mboxlist_start, 'list_mailboxes()');

    foreach ($a_folders as $folder)
      rcmail_build_folder_tree($a_mailboxes, $folder, $delimiter);
    }

// var_dump($a_mailboxes);

  if ($type=='select')
    $out .= rcmail_render_folder_tree_select($a_mailboxes, $special_mailboxes, $mbox_name, $attrib['maxlength']);
   else
    $out .= rcmail_render_folder_tree_html($a_mailboxes, $special_mailboxes, $mbox_name, $attrib['maxlength']);

// rcube_print_time($mboxlist_start, 'render_folder_tree()');


  if ($type=='ul')
    $OUTPUT->add_gui_object('mailboxlist', $attrib['id']);

  return $out . "</$type>";
  }




/**
 * Create a hierarchical array of the mailbox list
 * @access private
 */
function rcmail_build_folder_tree(&$arrFolders, $folder, $delm='/', $path='')
  {
  $pos = strpos($folder, $delm);
  if ($pos !== false)
    {
    $subFolders = substr($folder, $pos+1);
    $currentFolder = substr($folder, 0, $pos);
    }
  else
    {
    $subFolders = false;
    $currentFolder = $folder;
    }

  $path .= $currentFolder;

  if (!isset($arrFolders[$currentFolder]))
    {
    $arrFolders[$currentFolder] = array('id' => $path,
                                        'name' => rcube_charset_convert($currentFolder, 'UTF-7'),
                                        'folders' => array());
    }

  if (!empty($subFolders))
    rcmail_build_folder_tree($arrFolders[$currentFolder]['folders'], $subFolders, $delm, $path.$delm);
  }
  

/**
 * Return html for a structured list &lt;ul&gt; for the mailbox tree
 * @access private
 */
function rcmail_render_folder_tree_html(&$arrFolders, &$special, &$mbox_name, $maxlength, $nestLevel=0)
  {
  global $COMM_PATH, $IMAP, $CONFIG, $OUTPUT;

  $idx = 0;
  $out = '';
  foreach ($arrFolders as $key => $folder)
    {
    $zebra_class = ($nestLevel*$idx)%2 ? 'even' : 'odd';
    $title = '';

    $folder_lc = strtolower($folder['id']);
    if (in_array($folder_lc, $special))
      $foldername = rcube_label($folder_lc);
    else
      {
      $foldername = $folder['name'];

      // shorten the folder name to a given length
      if ($maxlength && $maxlength>1)
        {
        $fname = abbrevate_string($foldername, $maxlength);
        if ($fname != $foldername)
          $title = ' title="'.Q($foldername).'"';
        $foldername = $fname;
        }
      }

    // add unread message count display
    if ($unread_count = $IMAP->messagecount($folder['id'], 'RECENT', ($folder['id']==$mbox_name)))
      $foldername .= sprintf(' (%d)', $unread_count);

    // make folder name safe for ids and class names
    $folder_id = preg_replace('/[^A-Za-z0-9\-_]/', '', $folder['id']);
    $class_name = preg_replace('/[^a-z0-9\-_]/', '', $folder_lc);

    // set special class for Sent, Drafts, Trash and Junk
    if ($folder['id']==$CONFIG['sent_mbox'])
      $class_name = 'sent';
    else if ($folder['id']==$CONFIG['drafts_mbox'])
      $class_name = 'drafts';
    else if ($folder['id']==$CONFIG['trash_mbox'])
      $class_name = 'trash';
    else if ($folder['id']==$CONFIG['junk_mbox'])
      $class_name = 'junk';

    $js_name = htmlspecialchars(JQ($folder['id']));
    $out .= sprintf('<li id="rcmli%s" class="mailbox %s %s%s%s"><a href="%s"'.
                    ' onclick="return %s.command(\'list\',\'%s\',this)"'.
                    ' onmouseover="return %s.focus_folder(\'%s\')"' .
                    ' onmouseout="return %s.unfocus_folder(\'%s\')"' .
                    ' onmouseup="return %s.folder_mouse_up(\'%s\')"%s>%s</a>',
                    $folder_id,
                    $class_name,
                    $zebra_class,
                    $unread_count ? ' unread' : '',
                    $folder['id']==$mbox_name ? ' selected' : '',
                    Q(rcmail_url('', array('_mbox' => $folder['id']))),
                    JS_OBJECT_NAME,
                    $js_name,
                    JS_OBJECT_NAME,
                    $js_name,
                    JS_OBJECT_NAME,
                    $js_name,
                    JS_OBJECT_NAME,
                    $js_name,
                    $title,
                    Q($foldername));

    if (!empty($folder['folders']))
      $out .= "\n<ul>\n" . rcmail_render_folder_tree_html($folder['folders'], $special, $mbox_name, $maxlength, $nestLevel+1) . "</ul>\n";

    $out .= "</li>\n";
    $idx++;
    }

  return $out;
  }


/**
 * Return html for a flat list <select> for the mailbox tree
 * @access private
 */
function rcmail_render_folder_tree_select(&$arrFolders, &$special, &$mbox_name, $maxlength, $nestLevel=0)
  {
  global $IMAP, $OUTPUT;

  $idx = 0;
  $out = '';
  foreach ($arrFolders as $key=>$folder)
    {
    $folder_lc = strtolower($folder['id']);
    if (in_array($folder_lc, $special))
      $foldername = rcube_label($folder_lc);
    else
      {
      $foldername = $folder['name'];
      
      // shorten the folder name to a given length
      if ($maxlength && $maxlength>1)
        $foldername = abbrevate_string($foldername, $maxlength);
      }

    $out .= sprintf('<option value="%s">%s%s</option>'."\n",
                    htmlspecialchars($folder['id']),
                    str_repeat('&nbsp;', $nestLevel*4),
                    Q($foldername));

    if (!empty($folder['folders']))
      $out .= rcmail_render_folder_tree_select($folder['folders'], $special, $mbox_name, $maxlength, $nestLevel+1);

    $idx++;
    }

  return $out;
  }

?>

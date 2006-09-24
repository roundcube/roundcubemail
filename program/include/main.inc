<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/main.inc                                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev, - Switzerland                      |
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

require_once('lib/des.inc');
require_once('lib/utf7.inc');
require_once('lib/utf8.class.php');


// define constannts for input reading
define('RCUBE_INPUT_GET', 0x0101);
define('RCUBE_INPUT_POST', 0x0102);
define('RCUBE_INPUT_GPC', 0x0103);


// register session and connect to server
function rcmail_startup($task='mail')
  {
  global $sess_id, $sess_auth, $sess_user_lang;
  global $CONFIG, $INSTALL_PATH, $BROWSER, $OUTPUT, $_SESSION, $IMAP, $DB, $JS_OBJECT_NAME;

  // check client
  $BROWSER = rcube_browser();

  // load configuration
  $CONFIG = rcmail_load_config();

  // set session garbage collecting time according to session_lifetime
  if (!empty($CONFIG['session_lifetime']))
    ini_set('session.gc_maxlifetime', ($CONFIG['session_lifetime']) * 120);

  // prepare DB connection
  require_once('include/rcube_'.(empty($CONFIG['db_backend']) ? 'db' : $CONFIG['db_backend']).'.inc');
  
  $DB = new rcube_db($CONFIG['db_dsnw'], $CONFIG['db_dsnr'], $CONFIG['db_persistent']);
  $DB->sqlite_initials = $INSTALL_PATH.'SQL/sqlite.initial.sql';
  $DB->db_connect('w');

  // we can use the database for storing session data
  if (!$DB->is_error())
    include_once('include/session.inc');

  // init session
  session_start();
  $sess_id = session_id();

  // create session and set session vars
  if (!isset($_SESSION['auth_time']))
    {
    $_SESSION['user_lang'] = rcube_language_prop($CONFIG['locale_string']);
    $_SESSION['auth_time'] = mktime();
    setcookie('sessauth', rcmail_auth_hash($sess_id, $_SESSION['auth_time']));
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


// load roundcube configuration into global var
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


// load a host-specific config file if configured
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


// create authorization hash
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


// compare the auth hash sent by the client with the local session credentials
function rcmail_authenticate_session()
  {
  $now = mktime();
  $valid = ($_COOKIE['sessauth'] == rcmail_auth_hash(session_id(), $_SESSION['auth_time']) ||
						$_COOKIE['sessauth'] == rcmail_auth_hash(session_id(), $_SESSION['last_auth']));

  // renew auth cookie every 5 minutes (only for GET requests)
  if (!$valid || ($_SERVER['REQUEST_METHOD']!='POST' && $now-$_SESSION['auth_time'] > 300))
    {
    $_SESSION['last_auth'] = $_SESSION['auth_time'];
    $_SESSION['auth_time'] = $now;
    setcookie('sessauth', rcmail_auth_hash(session_id(), $now));
    }

  if (!$valid)
    write_log('timeouts',
      "REQUEST: " . var_export($_REQUEST, true) .
      "\nEXPECTED: " . rcmail_auth_hash(session_id(), $_SESSION['auth_time']) .
      "\nOR LAST: " . rcmail_auth_hash(session_id(), $_SESSION['last_auth']) .
      "\nSESSION: " . var_export($_SESSION, true));

  return $valid;
  }


// create IMAP object and connect to server
function rcmail_imap_init($connect=FALSE)
  {
  global $CONFIG, $DB, $IMAP;

  $IMAP = new rcube_imap($DB);
  $IMAP->debug_level = $CONFIG['debug_level'];
  $IMAP->skip_deleted = $CONFIG['skip_deleted'];


  // connect with stored session data
  if ($connect)
    {
    if (!($conn = $IMAP->connect($_SESSION['imap_host'], $_SESSION['username'], decrypt_passwd($_SESSION['password']), $_SESSION['imap_port'], $_SESSION['imap_ssl'])))
      show_message('imaperror', 'error');
      
    rcmail_set_imap_prop();
    }

  // enable caching of imap data
  if ($CONFIG['enable_caching']===TRUE)
    $IMAP->set_caching(TRUE);

  // set pagesize from config
  if (isset($CONFIG['pagesize']))
    $IMAP->set_pagesize($CONFIG['pagesize']);
  }


// set root dir and last stored mailbox
// this must be done AFTER connecting to the server
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


// do these things on script shutdown
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


// destroy session data and remove cookie
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

  $_SESSION = array();
  session_destroy();
  }


// return correct name for a specific database table
function get_table_name($table)
  {
  global $CONFIG;
  
  // return table name if configured
  $config_key = 'db_table_'.$table;

  if (strlen($CONFIG[$config_key]))
    return $CONFIG[$config_key];
  
  return $table;
  }


// return correct name for a specific database sequence
// (used for Postres only)
function get_sequence_name($sequence)
  {
  global $CONFIG;
  
  // return table name if configured
  $config_key = 'db_sequence_'.$sequence;

  if (strlen($CONFIG[$config_key]))
    return $CONFIG[$config_key];
  
  return $table;
  }


// check the given string and returns language properties
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
  

// init output object for GUI and add common scripts
function load_gui()
  {
  global $CONFIG, $OUTPUT, $COMM_PATH, $JS_OBJECT_NAME, $sess_user_lang;

  // init output page
  $OUTPUT = new rcube_html_page();
  
  // add common javascripts
  $javascript = "var $JS_OBJECT_NAME = new rcube_webmail();\n";
  $javascript .= sprintf("%s.set_env('comm_path', '%s');\n", $JS_OBJECT_NAME, str_replace('&amp;', '&', $COMM_PATH));

  if (isset($CONFIG['javascript_config'] )){
    foreach ($CONFIG['javascript_config'] as $js_config_var){
      $javascript .= "$JS_OBJECT_NAME.set_env('$js_config_var', '" . $CONFIG[$js_config_var] . "');\n";
    }
  }

  // don't wait for page onload. Call init at the bottom of the page (delayed)
  $javascript_foot = "if (window.call_init)\n call_init('$JS_OBJECT_NAME');";

  if (!empty($GLOBALS['_framed']))
    $javascript .= "$JS_OBJECT_NAME.set_env('framed', true);\n";
    
  $OUTPUT->add_script($javascript, 'head');
  $OUTPUT->add_script($javascript_foot, 'foot');
  $OUTPUT->include_script('common.js');
  $OUTPUT->include_script('app.js');
  $OUTPUT->scripts_path = 'program/js/';

  // set locale setting
  rcmail_set_locale($sess_user_lang);

  // set user-selected charset
  if (!empty($CONFIG['charset']))
    $OUTPUT->set_charset($CONFIG['charset']);

  // add some basic label to client
  rcube_add_label('loading','checkingmail');
  }


// set localization charset based on the given language
function rcmail_set_locale($lang)
  {
  global $OUTPUT, $MBSTRING;
  static $s_mbstring_loaded = NULL;
  
  // settings for mbstring module (by Tadashi Jokagi)
  if (is_null($s_mbstring_loaded))
    $MBSTRING = $s_mbstring_loaded = extension_loaded("mbstring");
  else
    $MBSTRING = $s_mbstring_loaded = FALSE;

  $OUTPUT->set_charset(rcube_language_prop($lang, 'charset'));
  }


// perfom login to the IMAP server and to the webmail service
function rcmail_login($user, $pass, $host=NULL)
  {
  global $CONFIG, $IMAP, $DB, $sess_user_lang;
  $user_id = NULL;
  
  if (!$host)
    $host = $CONFIG['default_host'];

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

    // force reloading complete list of subscribed mailboxes
    rcmail_set_imap_prop();
    $IMAP->clear_cache('mailboxes');
    $IMAP->create_default_folders();

    return TRUE;
    }

  return FALSE;
  }


// create new entry in users and identities table
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
              $user,
              $host,
              $user_email,
		      $_SESSION['user_lang']);

  if ($user_id = $DB->insert_id(get_sequence_name('users')))
    {
    $mail_domain = rcmail_mail_domain($host);
   
    if ($user_email=='')
      $user_email = strstr($user, '@') ? $user : sprintf('%s@%s', $user, $mail_domain);

    $user_name = $user!=$user_email ? $user : '';

    // try to resolve the e-mail address from the virtuser table
	if (!empty($CONFIG['virtuser_query']))
	  {
      $sql_result = $DB->query(preg_replace('/%u/', $user, $CONFIG['virtuser_query']));
      if ($sql_arr = $DB->fetch_array($sql_result))
        $user_email = $sql_arr[0];
      }

    // also create new identity records
    $DB->query("INSERT INTO ".get_table_name('identities')."
                (user_id, del, standard, name, email)
                VALUES (?, 0, 1, ?, ?)",
                $user_id,
                $user_name,
                $user_email);

                       
    // get existing mailboxes
    $a_mailboxes = $IMAP->list_mailboxes();
    }
  else
    {
    raise_error(array('code' => 500,
                      'type' => 'php',
                      'line' => __LINE__,
                      'file' => __FILE__,
                      'message' => "Failed to create new user"), TRUE, FALSE);
    }
    
  return $user_id;
  }


// load virtuser table in array
function rcmail_getvirtualfile()
  {
  global $CONFIG;
  if (empty($CONFIG['virtuser_file']) || !is_file($CONFIG['virtuser_file']))
    return FALSE;
  
  // read file 
  $a_lines = file($CONFIG['virtuser_file']);
  return $a_lines;
  }


// find matches of the given pattern in virtuser table
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


// resolve username with virtuser table
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


// resolve e-mail address with virtuser table
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


// overwrite action variable  
function rcmail_overwrite_action($action)
  {
  global $OUTPUT, $JS_OBJECT_NAME;
  $GLOBALS['_action'] = $action;

  $OUTPUT->add_script(sprintf("\n%s.set_env('action', '%s');", $JS_OBJECT_NAME, $action));  
  }


function show_message($message, $type='notice', $vars=NULL)
  {
  global $OUTPUT, $JS_OBJECT_NAME, $REMOTE_REQUEST;
  
  $framed = $GLOBALS['_framed'];
  $command = sprintf("display_message('%s', '%s');",
                     rep_specialchars_output(rcube_label(array('name' => $message, 'vars' => $vars)), 'js'),
                     $type);
                     
  if ($REMOTE_REQUEST)
    return 'this.'.$command;
  
  else
    $OUTPUT->add_script(sprintf("%s%s.%s\n",
                                $framed ? sprintf('if(parent.%s)parent.', $JS_OBJECT_NAME) : '',
                                $JS_OBJECT_NAME,
                                $command));
  }


// encrypt IMAP password using DES encryption
function encrypt_passwd($pass)
  {
  $cypher = des(get_des_key(), $pass, 1, 0, NULL);
  return base64_encode($cypher);
  }


// decrypt IMAP password using DES encryption
function decrypt_passwd($cypher)
  {
  $pass = des(get_des_key(), base64_decode($cypher), 0, 0, NULL);
  return preg_replace('/\x00/', '', $pass);
  }


// return a 24 byte key for the DES encryption
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


// send correct response on a remote request
function rcube_remote_response($js_code, $flush=FALSE)
  {
  global $OUTPUT, $CHARSET;
  static $s_header_sent = FALSE;
  
  if (!$s_header_sent)
    {
    $s_header_sent = TRUE;
    send_nocacheing_headers();
    header('Content-Type: application/x-javascript; charset='.$CHARSET);
    print '/** remote response ['.date('d/M/Y h:i:s O')."] **/\n";
    }

  // send response code
  print rcube_charset_convert($js_code, $CHARSET, $OUTPUT->get_charset());

  if ($flush)  // flush the output buffer
    flush();
  else         // terminate script
    exit;
  }


// send correctly formatted response for a request posted to an iframe
function rcube_iframe_response($js_code='')
  {
  global $OUTPUT, $JS_OBJECT_NAME;

  if (!empty($js_code))
    $OUTPUT->add_script("if(parent.$JS_OBJECT_NAME){\n" . $js_code . "\n}");

  $OUTPUT->write();
  exit;
  }


// read directory program/localization/ and return a list of available languages
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


// add a localized label to the client environment
function rcube_add_label()
  {
  global $OUTPUT, $JS_OBJECT_NAME;
  
  $arg_list = func_get_args();
  foreach ($arg_list as $i => $name)
    $OUTPUT->add_script(sprintf("%s.add_label('%s', '%s');",
                                $JS_OBJECT_NAME,
                                $name,
                                rep_specialchars_output(rcube_label($name), 'js')));  
  }


// remove temp files older than two day
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


// remove all expired message cache records
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


// convert a string from one charset to another
// this function is not complete and not tested well
function rcube_charset_convert($str, $from, $to=NULL)
  {
  global $MBSTRING;

  $from = strtoupper($from);
  $to = $to==NULL ? strtoupper($GLOBALS['CHARSET']) : strtoupper($to);

  if ($from==$to || $str=='' || empty($from))
    return $str;

  // convert charset using mbstring module  
  if ($MBSTRING)
    {
    $to = $to=="UTF-7" ? "UTF7-IMAP" : $to;
    $from = $from=="UTF-7" ? "UTF7-IMAP": $from;

    // return if convert succeeded
    if (($out = mb_convert_encoding($str, $to, $from)) != '')
      return $out;
    }

  // convert charset using iconv module  
  if (function_exists('iconv') && $from!='UTF-7' && $to!='UTF-7')
    return iconv($from, $to, $str);

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



// replace specials characters to a specific encoding type
function rep_specialchars_output($str, $enctype='', $mode='', $newlines=TRUE)
  {
  global $OUTPUT_TYPE, $OUTPUT;
  static $html_encode_arr, $js_rep_table, $rtf_rep_table, $xml_rep_table;

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
      
    $out = strtr($str, $encode_arr);
      
    return $newlines ? nl2br($out) : $out;
    }


  if ($enctype=='url')
    return rawurlencode($str);


  // if the replace tables for RTF, XML and JS are not yet defined
  if (!$js_rep_table)
    {
    $js_rep_table = $rtf_rep_table = $xml_rep_table = array();
    $xml_rep_table['&'] = '&amp;';

    for ($c=160; $c<256; $c++)  // can be increased to support more charsets
      {
      $hex = dechex($c);
      $rtf_rep_table[Chr($c)] = "\\'$hex";
      $xml_rep_table[Chr($c)] = "&#$c;";
      
      if ($OUTPUT->get_charset()=='ISO-8859-1')
        $js_rep_table[Chr($c)] = sprintf("\u%s%s", str_repeat('0', 4-strlen($hex)), $hex);
      }

    $js_rep_table['"'] = sprintf("\u%s%s", str_repeat('0', 4-strlen(dechex(34))), dechex(34));
    $xml_rep_table['"'] = '&quot;';
    }

  // encode for RTF
  if ($enctype=='xml')
    return strtr($str, $xml_rep_table);

  // encode for javascript use
  if ($enctype=='js')
    {
    if ($OUTPUT->get_charset()!='UTF-8')
      $str = rcube_charset_convert($str, $GLOBALS['CHARSET'], $OUTPUT->get_charset());
      
    return addslashes(preg_replace(array("/\r\n/", "/\r/"), array('\n', '\n'), strtr($str, $js_rep_table)));
    }

  // encode for RTF
  if ($enctype=='rtf')
    return preg_replace("/\r\n/", "\par ", strtr($str, $rtf_rep_table));

  // no encoding given -> return original string
  return $str;
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




// ************** template parsing and gui functions **************


// return boolean if a specific template exists
function template_exists($name)
  {
  global $CONFIG, $OUTPUT;
  $skin_path = $CONFIG['skin_path'];

  // check template file
  return is_file("$skin_path/templates/$name.html");
  }


// get page template an replace variable
// similar function as used in nexImage
function parse_template($name='main', $exit=TRUE)
  {
  global $CONFIG, $OUTPUT;
  $skin_path = $CONFIG['skin_path'];

  // read template file
  $templ = '';
  $path = "$skin_path/templates/$name.html";

  if($fp = @fopen($path, 'r'))
    {
    $templ = fread($fp, filesize($path));
    fclose($fp);
    }
  else
    {
    raise_error(array('code' => 500,
                      'type' => 'php',
                      'line' => __LINE__,
                      'file' => __FILE__,
                      'message' => "Error loading template for '$name'"), TRUE, TRUE);
    return FALSE;
    }


  // parse for specialtags
  $output = parse_rcube_xml($templ);
  
  $OUTPUT->write(trim(parse_with_globals($output)), $skin_path);

  if ($exit)
    exit;
  }



// replace all strings ($varname) with the content of the according global variable
function parse_with_globals($input)
  {
  $GLOBALS['__comm_path'] = $GLOBALS['COMM_PATH'];
  $output = preg_replace('/\$(__[a-z0-9_\-]+)/e', '$GLOBALS["\\1"]', $input);
  return $output;
  }



function parse_rcube_xml($input)
  {
  $output = preg_replace('/<roundcube:([-_a-z]+)\s+([^>]+)>/Uie', "rcube_xml_command('\\1', '\\2')", $input);
  return $output;
  }


function rcube_xml_command($command, $str_attrib, $add_attrib=array())
  {
  global $IMAP, $CONFIG, $OUTPUT;
  
  $command = strtolower($command);
  $attrib = parse_attrib_string($str_attrib) + $add_attrib;

  // execute command
  switch ($command)
    {
    // return a button
    case 'button':
      if ($attrib['command'])
        return rcube_button($attrib);
      break;

    // show a label
    case 'label':
      if ($attrib['name'] || $attrib['command'])
        return rep_specialchars_output(rcube_label($attrib));
      break;

    // create a menu item
    case 'menu':
      if ($attrib['command'] && $attrib['group'])
        rcube_menu($attrib);
      break;

    // include a file 
    case 'include':
      $path = realpath($CONFIG['skin_path'].$attrib['file']);
      
      if($fp = @fopen($path, 'r'))
        {
        $incl = fread($fp, filesize($path));
        fclose($fp);        
        return parse_rcube_xml($incl);
        }
      break;

    // return code for a specific application object
    case 'object':
      $object = strtolower($attrib['name']);

      $object_handlers = array(
        // GENERAL
        'loginform' => 'rcmail_login_form',
        'username'  => 'rcmail_current_username',
        
        // MAIL
        'mailboxlist' => 'rcmail_mailbox_list',
        'message' => 'rcmail_message_container',
        'messages' => 'rcmail_message_list',
        'messagecountdisplay' => 'rcmail_messagecount_display',
        'quotadisplay' => 'rcmail_quota_display',
        'messageheaders' => 'rcmail_message_headers',
        'messagebody' => 'rcmail_message_body',
        'messageattachments' => 'rcmail_message_attachments',
        'blockedobjects' => 'rcmail_remote_objects_msg',
        'messagecontentframe' => 'rcmail_messagecontent_frame',
        'messagepartframe' => 'rcmail_message_part_frame',
        'messagepartcontrols' => 'rcmail_message_part_controls',
        'composeheaders' => 'rcmail_compose_headers',
        'composesubject' => 'rcmail_compose_subject',
        'composebody' => 'rcmail_compose_body',
        'composeattachmentlist' => 'rcmail_compose_attachment_list',
        'composeattachmentform' => 'rcmail_compose_attachment_form',
        'composeattachment' => 'rcmail_compose_attachment_field',
        'priorityselector' => 'rcmail_priority_selector',
        'charsetselector' => 'rcmail_charset_selector',
        'editorselector' => 'rcmail_editor_selector',
        'searchform' => 'rcmail_search_form',
        'receiptcheckbox' => 'rcmail_receipt_checkbox',
        
        // ADDRESS BOOK
        'addresslist' => 'rcmail_contacts_list',
        'addressframe' => 'rcmail_contact_frame',
        'recordscountdisplay' => 'rcmail_rowcount_display',
        'contactdetails' => 'rcmail_contact_details',
        'contacteditform' => 'rcmail_contact_editform',
        'ldappublicsearch' => 'rcmail_ldap_public_search_form',
        'ldappublicaddresslist' => 'rcmail_ldap_public_list',

        // USER SETTINGS
        'userprefs' => 'rcmail_user_prefs_form',
        'itentitieslist' => 'rcmail_identities_list',
        'identityframe' => 'rcmail_identity_frame',
        'identityform' => 'rcube_identity_form',
        'foldersubscription' => 'rcube_subscription_form',
        'createfolder' => 'rcube_create_folder_form',
        'renamefolder' => 'rcube_rename_folder_form',
        'composebody' => 'rcmail_compose_body'
      );

      
      // execute object handler function
      if ($object_handlers[$object] && function_exists($object_handlers[$object]))
        return call_user_func($object_handlers[$object], $attrib);
        
      else if ($object=='productname')
        {
        $name = !empty($CONFIG['product_name']) ? $CONFIG['product_name'] : 'RoundCube Webmail';
        return rep_specialchars_output($name, 'html', 'all');
        }
      else if ($object=='version')
        {
        return (string)RCMAIL_VERSION;
        }
      else if ($object=='pagetitle')
        {
        $task = $GLOBALS['_task'];
        $title = !empty($CONFIG['product_name']) ? $CONFIG['product_name'].' :: ' : '';
        
        if ($task=='login')
          $title = rcube_label(array('name' => 'welcome', 'vars' => array('product' => $CONFIG['product_name'])));
        else if ($task=='mail' && isset($GLOBALS['MESSAGE']['subject']))
          $title .= $GLOBALS['MESSAGE']['subject'];
        else if (isset($GLOBALS['PAGE_TITLE']))
          $title .= $GLOBALS['PAGE_TITLE'];
        else if ($task=='mail' && ($mbox_name = $IMAP->get_mailbox_name()))
          $title .= rcube_charset_convert($mbox_name, 'UTF-7', 'UTF-8');
        else
          $title .= ucfirst($task);
          
        return rep_specialchars_output($title, 'html', 'all');
        }

      break;
    }

  return '';
  }


// create and register a button
function rcube_button($attrib)
  {
  global $CONFIG, $OUTPUT, $JS_OBJECT_NAME, $BROWSER, $COMM_PATH, $MAIN_TASKS;
  static $sa_buttons = array();
  static $s_button_count = 100;
  
  // these commands can be called directly via url
  $a_static_commands = array('compose', 'list');
  
  $skin_path = $CONFIG['skin_path'];
  
  if (!($attrib['command'] || $attrib['name']))
    return '';

  // try to find out the button type
  if ($attrib['type'])
    $attrib['type'] = strtolower($attrib['type']);
  else
    $attrib['type'] = ($attrib['image'] || $attrib['imagepas'] || $attrib['imageact']) ? 'image' : 'link';
  
  $command = $attrib['command'];
  
  // take the button from the stack
  if($attrib['name'] && $sa_buttons[$attrib['name']])
    $attrib = $sa_buttons[$attrib['name']];

  // add button to button stack
  else if($attrib['image'] || $attrib['imageact'] || $attrib['imagepas'] || $attrib['class'])
    {
    if(!$attrib['name'])
      $attrib['name'] = $command;

    if (!$attrib['image'])
      $attrib['image'] = $attrib['imagepas'] ? $attrib['imagepas'] : $attrib['imageact'];

    $sa_buttons[$attrib['name']] = $attrib;
    }

  // get saved button for this command/name
  else if ($command && $sa_buttons[$command])
    $attrib = $sa_buttons[$command];

  //else
  //  return '';


  // set border to 0 because of the link arround the button
  if ($attrib['type']=='image' && !isset($attrib['border']))
    $attrib['border'] = 0;
    
  if (!$attrib['id'])
    $attrib['id'] =  sprintf('rcmbtn%d', $s_button_count++);

  // get localized text for labels and titles
  if ($attrib['title'])
    $attrib['title'] = rep_specialchars_output(rcube_label($attrib['title']));
  if ($attrib['label'])
    $attrib['label'] = rep_specialchars_output(rcube_label($attrib['label']));

  if ($attrib['alt'])
    $attrib['alt'] = rep_specialchars_output(rcube_label($attrib['alt']));

  // set title to alt attribute for IE browsers
  if ($BROWSER['ie'] && $attrib['title'] && !$attrib['alt'])
    {
    $attrib['alt'] = $attrib['title'];
    unset($attrib['title']);
    }

  // add empty alt attribute for XHTML compatibility
  if (!isset($attrib['alt']))
    $attrib['alt'] = '';


  // register button in the system
  if ($attrib['command'])
    {
    $OUTPUT->add_script(sprintf("%s.register_button('%s', '%s', '%s', '%s', '%s', '%s');",
                                $JS_OBJECT_NAME,
                                $command,
                                $attrib['id'],
                                $attrib['type'],
                                $attrib['imageact'] ? $skin_path.$attrib['imageact'] : $attrib['classact'],
                                $attrib['imagesel'] ? $skin_path.$attrib['imagesel'] : $attrib['classsel'],
                                $attrib['imageover'] ? $skin_path.$attrib['imageover'] : ''));

    // make valid href to specific buttons
    if (in_array($attrib['command'], $MAIN_TASKS))
      $attrib['href'] = htmlentities(ereg_replace('_task=[a-z]+', '_task='.$attrib['command'], $COMM_PATH));
    else if (in_array($attrib['command'], $a_static_commands))
      $attrib['href'] = htmlentities($COMM_PATH.'&_action='.$attrib['command']);
    }

  // overwrite attributes
  if (!$attrib['href'])
    $attrib['href'] = '#';

  if ($command)
    $attrib['onclick'] = sprintf("return %s.command('%s','%s',this)", $JS_OBJECT_NAME, $command, $attrib['prop']);
    
  if ($command && $attrib['imageover'])
    {
    $attrib['onmouseover'] = sprintf("return %s.button_over('%s','%s')", $JS_OBJECT_NAME, $command, $attrib['id']);
    $attrib['onmouseout'] = sprintf("return %s.button_out('%s','%s')", $JS_OBJECT_NAME, $command, $attrib['id']);
    }

  if ($command && $attrib['imagesel'])
    {
    $attrib['onmousedown'] = sprintf("return %s.button_sel('%s','%s')", $JS_OBJECT_NAME, $command, $attrib['id']);
    $attrib['onmouseup'] = sprintf("return %s.button_out('%s','%s')", $JS_OBJECT_NAME, $command, $attrib['id']);
    }

  $out = '';

  // generate image tag
  if ($attrib['type']=='image')
    {
    $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'width', 'height', 'border', 'hspace', 'vspace', 'align', 'alt'));
    $img_tag = sprintf('<img src="%%s"%s />', $attrib_str);
    $btn_content = sprintf($img_tag, $skin_path.$attrib['image']);
    if ($attrib['label'])
      $btn_content .= ' '.$attrib['label'];
    
    $link_attrib = array('href', 'onclick', 'onmouseover', 'onmouseout', 'onmousedown', 'onmouseup', 'title');
    }
  else if ($attrib['type']=='link')
    {
    $btn_content = $attrib['label'] ? $attrib['label'] : $attrib['command'];
    $link_attrib = array('href', 'onclick', 'title', 'id', 'class', 'style');
    }
  else if ($attrib['type']=='input')
    {
    $attrib['type'] = 'button';
    
    if ($attrib['label'])
      $attrib['value'] = $attrib['label'];
      
    $attrib_str = create_attrib_string($attrib, array('type', 'value', 'onclick', 'id', 'class', 'style'));
    $out = sprintf('<input%s disabled />', $attrib_str);
    }

  // generate html code for button
  if ($btn_content)
    {
    $attrib_str = create_attrib_string($attrib, $link_attrib);
    $out = sprintf('<a%s>%s</a>', $attrib_str, $btn_content);
    }

  return $out;
  }


function rcube_menu($attrib)
  {
  
  return '';
  }



function rcube_table_output($attrib, $table_data, $a_show_cols, $id_col)
  {
  global $DB;
  
  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));
  
  $table = '<table' . $attrib_str . ">\n";
    
  // add table title
  $table .= "<thead><tr>\n";

  foreach ($a_show_cols as $col)
    $table .= '<td class="'.$col.'">' . rep_specialchars_output(rcube_label($col)) . "</td>\n";

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
        $cont = rep_specialchars_output($sql_arr[$col]);
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
        $cont = rep_specialchars_output($row_data[$col]);
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


// compose a valid attribute string for HTML tags
function create_attrib_string($attrib, $allowed_attribs=array('id', 'class', 'style'))
  {
  // allow the following attributes to be added to the <iframe> tag
  $attrib_str = '';
  foreach ($allowed_attribs as $a)
    if (isset($attrib[$a]))
      $attrib_str .= sprintf(' %s="%s"', $a, str_replace('"', '&quot;', $attrib[$a]));

  return $attrib_str;
  }


// convert a HTML attribute string attributes to an associative array (name => value)
function parse_attrib_string($str)
  {
  $attrib = array();
  preg_match_all('/\s*([-_a-z]+)=["]([^"]+)["]?/i', stripslashes($str), $regs, PREG_SET_ORDER);

  // convert attributes to an associative array (name => value)
  if ($regs)
    foreach ($regs as $attr)
      $attrib[strtolower($attr[1])] = $attr[2];

  return $attrib;
  }


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
  $now_date = getdate();

  $today_limit = mktime(0, 0, 0, $now_date['mon'], $now_date['mday'], $now_date['year']);
  $week_limit = mktime(0, 0, 0, $now_date['mon'], $now_date['mday']-6, $now_date['year']);

  // define date format depending on current time  
  if ($CONFIG['prettydate'] && !$format && $timestamp > $today_limit)
    return sprintf('%s %s', rcube_label('today'), date('H:i', $timestamp));
  else if ($CONFIG['prettydate'] && !$format && $timestamp > $week_limit)
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


// ************** functions delivering gui objects **************



function rcmail_message_container($attrib)
  {
  global $OUTPUT, $JS_OBJECT_NAME;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmMessageContainer';

  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id'));
  $out = '<div' . $attrib_str . "></div>";
  
  $OUTPUT->add_script("$JS_OBJECT_NAME.gui_object('message', '$attrib[id]');");
  
  return $out;
  }


// return the IMAP username of the current session
function rcmail_current_username($attrib)
  {
  global $DB;
  static $s_username;

  // alread fetched  
  if (!empty($s_username))
    return $s_username;

  // get e-mail address form default identity
  $sql_result = $DB->query("SELECT email AS mailto
                            FROM ".get_table_name('identities')."
                            WHERE  user_id=?
                            AND    standard=1
                            AND    del<>1",
                            $_SESSION['user_id']);
                                   
  if ($DB->num_rows($sql_result))
    {
    $sql_arr = $DB->fetch_assoc($sql_result);
    $s_username = $sql_arr['mailto'];
    }
  else if (strstr($_SESSION['username'], '@'))
    $s_username = $_SESSION['username'];
  else
    $s_username = $_SESSION['username'].'@'.$_SESSION['imap_host'];

  return $s_username;
  }


// return the mail domain configured for the given host
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


// return code for the webmail login form
function rcmail_login_form($attrib)
  {
  global $CONFIG, $OUTPUT, $JS_OBJECT_NAME, $SESS_HIDDEN_FIELD;
  
  $labels = array();
  $labels['user'] = rcube_label('username');
  $labels['pass'] = rcube_label('password');
  $labels['host'] = rcube_label('server');
  
  $input_user = new textfield(array('name' => '_user', 'id' => 'rcmloginuser', 'size' => 30));
  $input_pass = new passwordfield(array('name' => '_pass', 'id' => 'rcmloginpwd', 'size' => 30));
  $input_action = new hiddenfield(array('name' => '_action', 'value' => 'login'));
    
  $fields = array();
  $fields['user'] = $input_user->show(get_input_value('_user', RCUBE_INPUT_POST));
  $fields['pass'] = $input_pass->show();
  $fields['action'] = $input_action->show();
  
  if (is_array($CONFIG['default_host']))
    {
    $select_host = new select(array('name' => '_host', 'id' => 'rcmloginhost'));
    
    foreach ($CONFIG['default_host'] as $key => $value)
      $select_host->add($value, (is_numeric($key) ? $value : $key));
      
    $fields['host'] = $select_host->show($_POST['_host']);
    }
  else if (!strlen($CONFIG['default_host']))
    {
	$input_host = new textfield(array('name' => '_host', 'id' => 'rcmloginhost', 'size' => 30));
	$fields['host'] = $input_host->show($_POST['_host']);
    }

  $form_name = strlen($attrib['form']) ? $attrib['form'] : 'form';
  $form_start = !strlen($attrib['form']) ? '<form name="form" action="./" method="post">' : '';
  $form_end = !strlen($attrib['form']) ? '</form>' : '';
  
  if ($fields['host'])
    $form_host = <<<EOF
    
</tr><tr>

<td class="title"><label for="rcmloginhost">$labels[host]</label></td>
<td>$fields[host]</td>

EOF;

  $OUTPUT->add_script("$JS_OBJECT_NAME.gui_object('loginform', '$form_name');");
  
  $out = <<<EOF
$form_start
$SESS_HIDDEN_FIELD
$fields[action]
<table><tr>

<td class="title"><label for="rcmloginuser">$labels[user]</label></td>
<td>$fields[user]</td>

</tr><tr>

<td class="title"><label for="rcmloginpwd">$labels[pass]</label></td>
<td>$fields[pass]</td>
$form_host
</tr></table>
$form_end
EOF;

  return $out;
  }


function rcmail_charset_selector($attrib)
  {
  global $OUTPUT;
  
  // pass the following attributes to the form class
  $field_attrib = array('name' => '_charset');
  foreach ($attrib as $attr => $value)
    if (in_array($attr, array('id', 'class', 'style', 'size', 'tabindex')))
      $field_attrib[$attr] = $value;
      
  $charsets = array(
    'US-ASCII'     => 'ASCII (English)',
    'EUC-JP'       => 'EUC-JP (Japanese)',
    'EUC-KR'       => 'EUC-KR (Korean)',
    'BIG5'         => 'BIG5 (Chinese)',
    'GB2312'       => 'GB2312 (Chinese)',
    'ISO-2022-JP'  => 'ISO-2022-JP (Japanese)',
    'ISO-8859-1'   => 'ISO-8859-1 (Latin-1)',
    'ISO-8859-2'   => 'ISO-8895-2 (Central European)',
    'ISO-8859-7'   => 'ISO-8859-7 (Greek)',
    'ISO-8859-9'   => 'ISO-8859-9 (Turkish)',
    'Windows-1251' => 'Windows-1251 (Cyrillic)',
    'Windows-1252' => 'Windows-1252 (Western)',
    'Windows-1255' => 'Windows-1255 (Hebrew)',
    'Windows-1256' => 'Windows-1256 (Arabic)',
    'Windows-1257' => 'Windows-1257 (Baltic)',
    'UTF-8'        => 'UTF-8'
    );

  $select = new select($field_attrib);
  $select->add(array_values($charsets), array_keys($charsets));
  
  $set = $_POST['_charset'] ? $_POST['_charset'] : $OUTPUT->get_charset();
  return $select->show($set);
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
  global $CONFIG;

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


function rcube_timer()
  {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
  }
  

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

?>

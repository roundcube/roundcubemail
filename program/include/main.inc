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


// register session and connect to server
function rcmail_startup($task='mail')
  {
  global $sess_id, $sess_auth, $sess_user_lang;
  global $CONFIG, $INSTALL_PATH, $BROWSER, $OUTPUT, $_SESSION, $IMAP, $DB, $JS_OBJECT_NAME;

  // check client
  $BROWSER = rcube_browser();

  // load config file
  include_once('config/main.inc.php');
  $CONFIG = is_array($rcmail_config) ? $rcmail_config : array();
  $CONFIG['skin_path'] = $CONFIG['skin_path'] ? preg_replace('/\/$/', '', $CONFIG['skin_path']) : 'skins/default';

  // load db conf
  include_once('config/db.inc.php');
  $CONFIG = array_merge($CONFIG, $rcmail_config);

  if (empty($CONFIG['log_dir']))
    $CONFIG['log_dir'] = $INSTALL_PATH.'logs';
  else
    $CONFIG['log_dir'] = ereg_replace('\/$', '', $CONFIG['log_dir']);

  // set PHP error logging according to config
  if ($CONFIG['debug_level'] & 1)
    {
    ini_set('log_errors', 1);
    ini_set('error_log', $CONFIG['log_dir'].'/errors');
    }
  if ($CONFIG['debug_level'] & 4)
    ini_set('display_errors', 1);
  else
    ini_set('display_errors', 0);
  
  // set session garbage collecting time according to session_lifetime
  if (!empty($CONFIG['session_lifetime']))
    ini_set('session.gc_maxlifetime', ($CONFIG['session_lifetime']+2)*60);


  // prepare DB connection
  require_once('include/rcube_'.(empty($CONFIG['db_backend']) ? 'db' : $CONFIG['db_backend']).'.inc');
  
  $DB = new rcube_db($CONFIG['db_dsnw'], $CONFIG['db_dsnr']);
  $DB->sqlite_initials = $INSTALL_PATH.'SQL/sqlite.initial.sql';

  // we can use the database for storing session data
  // session queries do not work with MDB2
  if ($CONFIG['db_backend']!='mdb2' && is_object($DB) /* && $DB->db_provider!='sqlite' */)
    include_once('include/session.inc');


  // init session
  session_start();
  $sess_id = session_id();
  
  // create session and set session vars
  if (!$_SESSION['client_id'])
    {
    $_SESSION['client_id'] = $sess_id;
    $_SESSION['user_lang'] = substr($CONFIG['locale_string'], 0, 2);
    $_SESSION['auth_time'] = mktime();
    $_SESSION['auth'] = rcmail_auth_hash($sess_id, $_SESSION['auth_time']);
    unset($GLOBALS['_auth']);
    }

  // set session vars global
  $sess_auth = $_SESSION['auth'];
  $sess_user_lang = $_SESSION['user_lang'];


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



// create IMAP object and connect to server
function rcmail_imap_init($connect=FALSE)
  {
  global $CONFIG, $DB, $IMAP;

  $IMAP = new rcube_imap($DB);

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

  if (is_array($CONFIG['default_imap_folders']))
    $IMAP->set_default_mailboxes($CONFIG['default_imap_folders']);

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
  if (strlen($CONFIG['imap_root']))
    $IMAP->set_rootdir($CONFIG['imap_root']);

  if (strlen($_SESSION['mbox']))
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
  }


// destroy session data and remove cookie
function rcmail_kill_session()
  {
/* $sess_name = session_name();
  if (isset($_COOKIE[$sess_name]))
   setcookie($sess_name, '', time()-42000, '/');
*/
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
  
  

// init output object for GUI and add common scripts
function load_gui()
  {
  global $CONFIG, $OUTPUT, $COMM_PATH, $JS_OBJECT_NAME, $CHARSET, $sess_user_lang;

  // init output page
  $OUTPUT = new rcube_html_page();
  
  // add common javascripts
  $javascript = "var $JS_OBJECT_NAME = new rcube_webmail();\n";
  $javascript .= "$JS_OBJECT_NAME.set_env('comm_path', '$COMM_PATH');\n";

  if (!empty($GLOBALS['_framed']))
    $javascript .= "$JS_OBJECT_NAME.set_env('framed', true);\n";
    
  $OUTPUT->add_script($javascript);
  $OUTPUT->include_script('program/js/common.js');
  $OUTPUT->include_script('program/js/app.js');

  // set user-selected charset
  if (!empty($CONFIG['charset']))
    {
    $OUTPUT->set_charset($CONFIG['charset']);
    $CHARSET = $CONFIG['charset'];
    }
  else
    rcmail_set_locale($sess_user_lang);
  
  // add some basic label to client
  rcube_add_label('loading');
  }  


// set localization charset based on the given language
function rcmail_set_locale($lang)
  {
  global $OUTPUT, $INSTLL_PATH, $CHARSET;
  static $rcube_charsets;

  if (!$rcube_charsets)
    @include($INSTLL_PATH.'program/localization/index.inc');

  if (isset($rcube_charsets[$lang]))
    {
    $OUTPUT->set_charset($rcube_charsets[$lang]);
    $CHARSET = $rcube_charsets[$lang];
    }
  else
    {
    $OUTPUT->set_charset('ISO-8859-1');
    $CHARSET = 'ISO-8859-1';
    }
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
                SET    last_login=now()
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
    $IMAP->clear_cache('mailboxes');

    return TRUE;
    }

  return FALSE;
  }


// create new entry in users and identities table
function rcmail_create_user($user, $host)
  {
  global $DB, $CONFIG, $IMAP;
  
  $DB->query("INSERT INTO ".get_table_name('users')."
              (created, last_login, username, mail_host, language)
              VALUES (now(), now(), ?, ?, ?)",
              $user,
              $host,
		      $_SESSION['user_lang']);

  if ($user_id = $DB->insert_id(get_sequence_name('users')))
    {
    $user_email = strstr($user, '@') ? $user : sprintf('%s@%s', $user, $host);
    $user_name = $user!=$user_email ? $user : '';
    
    // also create a new identity record
    $DB->query("INSERT INTO ".get_table_name('identities')."
                (user_id, del, standard, name, email)
                VALUES (?, 0, 1, ?, ?)",
                $user_id,
                $user_name,
                $user_email);
                       
    // get existing mailboxes
    $a_mailboxes = $IMAP->list_mailboxes();
    
    // check if the configured mailbox for sent messages exists
    if ($CONFIG['sent_mbox'] && !in_array_nocase($CONFIG['sent_mbox'], $a_mailboxes))
      $IMAP->create_mailbox($CONFIG['sent_mbox'], TRUE);

    // check if the configured mailbox for sent messages exists
    if ($CONFIG['trash_mbox'] && !in_array_nocase($CONFIG['trash_mbox'], $a_mailboxes))
      $IMAP->create_mailbox($CONFIG['trash_mbox'], TRUE);
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


// overwrite action variable  
function rcmail_overwrite_action($action)
  {
  global $OUTPUT, $JS_OBJECT_NAME;
  $GLOBALS['_action'] = $action;

  $OUTPUT->add_script(sprintf("\n%s.set_env('action', '%s');", $JS_OBJECT_NAME, $action));  
  }


function show_message($message, $type='notice')
  {
  global $OUTPUT, $JS_OBJECT_NAME, $REMOTE_REQUEST;

  $framed = $GLOBALS['_framed'];
  $command = sprintf("display_message('%s', '%s');",
                     addslashes(rep_specialchars_output(rcube_label($message))),
                     $type);
                     
  if ($REMOTE_REQUEST)
    return 'this.'.$command;
  
  else
    $OUTPUT->add_script(sprintf("%s%s.%s",
                                $framed ? sprintf('if(parent.%s)parent.', $JS_OBJECT_NAME) : '',
                                $JS_OBJECT_NAME,
                                $command));
  
  // console(rcube_label($message));
  }


function console($msg, $type=1)
  {
  if ($GLOBALS['REMOTE_REQUEST'])
    print "// $msg\n";
  else
    {
    print $msg;
    print "\n<hr>\n";
    }
  }


function encrypt_passwd($pass)
  {
  $cypher = des('rcmail?24BitPwDkeyF**ECB', $pass, 1, 0, NULL);
  return base64_encode($cypher);
  }


function decrypt_passwd($cypher)
  {
  $pass = des('rcmail?24BitPwDkeyF**ECB', base64_decode($cypher), 0, 0, NULL);
  return trim($pass);
  }


// send correct response on a remote request
function rcube_remote_response($js_code)
  {
  send_nocacheing_headers();
  header('Content-Type: application/x-javascript');

  print '/** remote response ['.date('d/M/Y h:i:s O')."] **/\n";
  print $js_code;
  exit;
  }


// read directory program/localization/ and return a list of available languages
function rcube_list_languages()
  {
  global $CONFIG, $INSTALL_PATH;
  static $sa_languages = array();

  if (!sizeof($sa_languages))
    {
    @include($INSTLL_PATH.'program/localization/index.inc');

    if ($dh = @opendir($INSTLL_PATH.'program/localization'))
      {
      while (($name = readdir($dh)) !== false)
        {
        if ($name{0}=='.' || !is_dir($INSTLL_PATH.'program/localization/'.$name))
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


// remove temp files of a session
function rcmail_clear_session_temp($sess_id)
  {
  global $CONFIG;

  $temp_dir = $CONFIG['temp_dir'].(!eregi('\/$', $CONFIG['temp_dir']) ? '/' : '');
  $cache_dir = $temp_dir.$sess_id;

  if (is_dir($cache_dir))
    {
    clear_directory($cache_dir);
    rmdir($cache_dir);
    }  
  }



// replace specials characters to a specific encoding type
function rep_specialchars_output($str, $enctype='', $mode='', $newlines=TRUE)
  {
  global $OUTPUT_TYPE, $CHARSET;
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
      if ($CHARSET=='ISO-8859-1')
        {
        $html_encode_arr = get_html_translation_table(HTML_ENTITIES);
        $html_encode_arr[chr(128)] = '&euro;';
        }
      else
        $html_encode_arr = get_html_translation_table(HTML_SPECIALCHARS);
        
      unset($html_encode_arr['?']);
      unset($html_encode_arr['&']);
      }

    $ltpos = strpos($str, '<');
    $encode_arr = $html_encode_arr;

    // don't replace quotes and html tags
    if (($mode=='show' || $mode=='') && $ltpos!==false && strpos($str, '>', $ltpos)!==false)
      {
      unset($encode_arr['"']);
      unset($encode_arr['<']);
      unset($encode_arr['>']);
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

    for ($c=160; $c<256; $c++)  // can be increased to support more charsets
      {
      $hex = dechex($c);
      $rtf_rep_table[Chr($c)] = "\\'$hex";
      $xml_rep_table[Chr($c)] = "&#$c;";
      
      if ($CHARSET=='ISO-8859-1')
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
    return preg_replace(array("/\r\n/", '/"/', "/([^\\\])'/"), array('\n', '\"', "$1\'"), strtr($str, $js_rep_table));

  // encode for RTF
  if ($enctype=='rtf')
    return preg_replace("/\r\n/", "\par ", strtr($str, $rtf_rep_table));

  // no encoding given -> return original string
  return $str;
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


function rcube_xml_command($command, $str_attrib, $a_attrib=NULL)
  {
  global $IMAP, $CONFIG;
  
  $attrib = array();
  $command = strtolower($command);

  preg_match_all('/\s*([-_a-z]+)=["]([^"]+)["]?/i', stripslashes($str_attrib), $regs, PREG_SET_ORDER);

  // convert attributes to an associative array (name => value)
  if ($regs)
    foreach ($regs as $attr)
      $attrib[strtolower($attr[1])] = $attr[2];
  else if ($a_attrib)
    $attrib = $a_attrib;

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
        // MAIL
        'mailboxlist' => 'rcmail_mailbox_list',
        'messages' => 'rcmail_message_list',
        'messagecountdisplay' => 'rcmail_messagecount_display',
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
        
        // ADDRESS BOOK
        'addresslist' => 'rcmail_contacts_list',
        'addressframe' => 'rcmail_contact_frame',
        'recordscountdisplay' => 'rcmail_rowcount_display',
        'contactdetails' => 'rcmail_contact_details',
        'contacteditform' => 'rcmail_contact_editform',

        // USER SETTINGS
        'userprefs' => 'rcmail_user_prefs_form',
        'itentitieslist' => 'rcmail_identities_list',
        'identityframe' => 'rcmail_identity_frame',
        'identityform' => 'rcube_identity_form',
        'foldersubscription' => 'rcube_subscription_form',
        'createfolder' => 'rcube_create_folder_form',
        'composebody' => 'rcmail_compose_body'
      );

      if ($object=='loginform')
        return rcmail_login_form($attrib);

      else if ($object=='message')
        return rcmail_message_container($attrib);
      
      // execute object handler function
      else if ($object_handlers[$object] && function_exists($object_handlers[$object]))
        return call_user_func($object_handlers[$object], $attrib);

      else if ($object=='pagetitle')
        {
        $task = $GLOBALS['_task'];
        if ($task=='mail' && isset($GLOBALS['MESSAGE']['subject']))
          return rep_specialchars_output("RoundCube|Mail :: ".$GLOBALS['MESSAGE']['subject']);
        else if (isset($GLOBALS['PAGE_TITLE']))
          return rep_specialchars_output("RoundCube|Mail :: ".$GLOBALS['PAGE_TITLE']);
        else if ($task=='mail' && ($mbox_name = $IMAP->get_mailbox_name()))
          return "RoundCube|Mail :: ".rep_specialchars_output(UTF7DecodeString($mbox_name), 'html', 'all');
        else
          return "RoundCube|Mail :: $task";
        }

      else if ($object=='about')
        return '';

      break;
    }

  return '';
  }


// create and register a button
function rcube_button($attrib)
  {
  global $CONFIG, $OUTPUT, $JS_OBJECT_NAME, $BROWSER;
  static $sa_buttons = array();
  static $s_button_count = 100;
  
  $skin_path = $CONFIG['skin_path'];
  
  if (!($attrib['command'] || $attrib['name']))
    return '';

  // try to find out the button type
  if ($attrib['type'])
    $attrib['type'] = strtolower($attrib['type']);
  else
    $attrib['type'] = ($attrib['image'] || $attrib['imagepas'] || $arg['imagect']) ? 'image' : 'link';
  
  
  $command = $attrib['command'];
  
  // take the button from the stack
  if($attrib['name'] && $sa_buttons[$attrib['name']])
    $attrib = $sa_buttons[$attrib['name']];

  // add button to button stack
  else if($attrib['image'] || $arg['imagect'] || $attrib['imagepas'] || $attrib['class'])
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
    $OUTPUT->add_script(sprintf("%s.register_button('%s', '%s', '%s', '%s', '%s', '%s');",
                                $JS_OBJECT_NAME,
                                $command,
                                $attrib['id'],
                                $attrib['type'],
                                $attrib['imageact'] ? $skin_path.$attrib['imageact'] : $attrib['classact'],
                                $attirb['imagesel'] ? $skin_path.$attirb['imagesel'] : $attrib['classsel'],
                                $attrib['imageover'] ? $skin_path.$attrib['imageover'] : ''));

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


  $out = '';

  // generate image tag
  if ($attrib['type']=='image')
    {
    $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'width', 'height', 'border', 'hspace', 'vspace', 'align', 'alt'));
    $img_tag = sprintf('<img src="%%s"%s />', $attrib_str);
    $btn_content = sprintf($img_tag, $skin_path.$attrib['image']);
    if ($attrib['label'])
      $btn_content .= ' '.$attrib['label'];
    
    $link_attrib = array('href', 'onclick', 'onmouseover', 'onmouseout', 'title');
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



function rcube_table_output($attrib, $sql_result, $a_show_cols, $id_col)
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
  while ($sql_result && ($sql_arr = $DB->fetch_assoc($sql_result)))
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

  // complete message table
  $table .= "</tbody></table>\n";
  
  return $table;
  }



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


function create_attrib_string($attrib, $allowed_attribs=array('id', 'class', 'style'))
  {
  // allow the following attributes to be added to the <iframe> tag
  $attrib_str = '';
  foreach ($allowed_attribs as $a)
    if (isset($attrib[$a]))
      $attrib_str .= sprintf(' %s="%s"', $a, $attrib[$a]);

  return $attrib_str;
  }



function format_date($date, $format=NULL)
  {
  global $CONFIG, $sess_user_lang;
  
  if (is_numeric($date))
    $ts = $date;
  else if (!empty($date))
    $ts = strtotime($date);
  else
    return '';

  // convert time to user's timezone
  $timestamp = $ts - date('Z', $ts) + ($CONFIG['timezone'] * 3600);
  
  // get current timestamp in user's timezone
  $now = time();  // local time
  $now -= (int)date('Z'); // make GMT time
  $now += ($CONFIG['timezone'] * 3600); // user's time
  $now_date = getdate();

  //$day_secs = 60*((int)date('H', $now)*60 + (int)date('i', $now));
  //$week_secs = 60 * 60 * 24 * 7;
  //$diff = $now - $timestamp;
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


// return code for the webmail login form
function rcmail_login_form($attrib)
  {
  global $CONFIG, $OUTPUT, $JS_OBJECT_NAME, $SESS_HIDDEN_FIELD;
  
  $labels = array();
  $labels['user'] = rcube_label('username');
  $labels['pass'] = rcube_label('password');
  $labels['host'] = rcube_label('server');
  
  $input_user = new textfield(array('name' => '_user', 'size' => 30));
  $input_pass = new passwordfield(array('name' => '_pass', 'size' => 30));
  $input_action = new hiddenfield(array('name' => '_action', 'value' => 'login'));
    
  $fields = array();
  $fields['user'] = $input_user->show($_POST['_user']);
  $fields['pass'] = $input_pass->show();
  $fields['action'] = $input_action->show();
  
  if (is_array($CONFIG['default_host']))
    {
    $select_host = new select(array('name' => '_host'));
    
    foreach ($CONFIG['default_host'] as $key => $value)
      $select_host->add($value, (is_numeric($key) ? $value : $key));
      
    $fields['host'] = $select_host->show($_POST['_host']);
    }
  else if (!strlen($CONFIG['default_host']))
    {
	$input_host = new textfield(array('name' => '_host', 'size' => 30));
	$fields['host'] = $input_host->show($_POST['_host']);
    }

  $form_name = strlen($attrib['form']) ? $attrib['form'] : 'form';
  $form_start = !strlen($attrib['form']) ? '<form name="form" action="./" method="post">' : '';
  $form_end = !strlen($attrib['form']) ? '</form>' : '';
  
  if ($fields['host'])
    $form_host = <<<EOF
    
</tr><tr>

<td class="title">$labels[host]</td>
<td>$fields[host]</td>

EOF;

  $OUTPUT->add_script("$JS_OBJECT_NAME.gui_object('loginform', '$form_name');");
  
  $out = <<<EOF
$form_start
$SESS_HIDDEN_FIELD
$fields[action]
<table><tr>

<td class="title">$labels[user]</td>
<td>$fields[user]</td>

</tr><tr>

<td class="title">$labels[pass]</td>
<td>$fields[pass]</td>
$form_host
</tr></table>
$form_end
EOF;

  return $out;
  }



function rcmail_charset_selector($attrib)
  {
  // pass the following attributes to the form class
  $field_attrib = array('name' => '_charset');
  foreach ($attrib as $attr => $value)
    if (in_array($attr, array('id', 'class', 'style', 'size', 'tabindex')))
      $field_attrib[$attr] = $value;
      
  $charsets = array(
    'US-ASCII'     => 'ASCII (English)',
    'X-EUC-JP'     => 'EUC-JP (Japanese)',
    'EUC-KR'       => 'EUC-KR (Korean)',
    'BIG5'         => 'BIG5 (Chinese)',
    'GB2312'       => 'GB2312 (Chinese)',
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
  
  $set = $_POST['_charset'] ? $_POST['_charset'] : $GLOBALS['CHARSET'];
  return $select->show($set);
  }


?>
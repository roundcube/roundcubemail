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


  // set PHP error logging according to config
  if ($CONFIG['debug_level'] & 1)
    {
    ini_set('log_errors', 1);
    ini_set('error_log', $INSTALL_PATH.'logs/errors');  
    }
  if ($CONFIG['debug_level'] & 4)
    ini_set('display_errors', 1);
  else
    ini_set('display_errors', 0);


  // prepare DB connection
  $DB = new rcube_db($CONFIG['db_dsnw'], $CONFIG['db_dsnr']);
  $DB->sqlite_initials = $INSTALL_PATH.'SQL/sqlite.initial.sql';

  // we can use the database for storing session data
  if (is_object($DB) && $DB->db_provider!='sqlite')
    include_once('include/session.inc');


  // init session
  session_start();
  $sess_id = session_id();
  
  // create session and set session vars
  if (!$_SESSION['client_id'])
    {
    $_SESSION['client_id'] = $sess_id;
    $_SESSION['user_lang'] = 'en';
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
  global $CONFIG, $IMAP;

  $IMAP = new rcube_imap();

  // enable caching of imap data
  if ($CONFIG['enable_caching']===TRUE)
    $IMAP->set_caching(TRUE);

  // set root dir from config
  if (strlen($CONFIG['imap_root']))
    $IMAP->set_rootdir($CONFIG['imap_root']);
    
  if (is_array($CONFIG['default_imap_folders']))
    $IMAP->set_default_mailboxes($CONFIG['default_imap_folders']);

  if (strlen($_SESSION['mbox']))
    $IMAP->set_mailbox($_SESSION['mbox']);

  if (isset($_SESSION['page']))
    $IMAP->set_page($_SESSION['page']);

  // set pagesize from config
  if (isset($CONFIG['pagesize']))
    $IMAP->set_pagesize($CONFIG['pagesize']);


  // connect with stored session data
  if ($connect)
    {
    if (!($conn = $IMAP->connect($_SESSION['imap_host'], $_SESSION['username'], decrypt_passwd($_SESSION['password']))))
      show_message('imaperror', 'error');
    }
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



// init output object for GUI and add common scripts
function load_gui()
  {
  global $CONFIG, $OUTPUT, $COMM_PATH, $IMAP, $JS_OBJECT_NAME;

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
  }  


// perfom login to the IMAP server and to the webmail service
function rcmail_login($user, $pass, $host=NULL)
  {
  global $CONFIG, $IMAP, $DB, $sess_user_lang;
  $user_id = NULL;
  
  if (!$host)
    $host = $CONFIG['default_host'];

  // query if user already registered
  $sql_result = $DB->query(sprintf("SELECT user_id, username, language, preferences
                                    FROM   %s
                                    WHERE  mail_host='%s' AND (username='%s' OR alias='%s')",
                                   get_table_name('users'),
                                   addslashes($host),
                                   addslashes($user),
                                   addslashes($user)));

  // user already registered -> overwrite username
  if ($sql_arr = $DB->fetch_assoc($sql_result))
    {
    $user_id = $sql_arr['user_id'];
    $user = $sql_arr['username'];
    }

  // parse $host URL
  $a_host = parse_url($host);
  if ($a_host['host'])
    {
    $host = $a_host['host'];
    $imap_ssl = (isset($a_host['scheme']) && in_array($a_host['scheme'], array('ssl','imaps','tls'))) ? TRUE : FALSE;
    $imap_port = isset($a_host['post']) ? $a_host['post'] : ($imap_ssl ? 993 : $CONFIG['default_port']);
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
    $DB->query(sprintf("UPDATE %s
                        SET    last_login=NOW()
                        WHERE  user_id=%d",
                       get_table_name('users'),
                       $user_id));
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
    $_SESSION['username']  = $user;
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

  $DB->query(sprintf("INSERT INTO %s
                      (created, last_login, username, mail_host)
                      VALUES (NOW(), NOW(), '%s', '%s')",
                     get_table_name('users'),
                     addslashes($user),
                     addslashes($host)));

  if ($user_id = $DB->insert_id())
    {
    $user_email = strstr($user, '@') ? $user : sprintf('%s@%s', $user, $host);
    $user_name = $user!=$user_email ? $user : '';
    
    // also create a new identity record
    $DB->query(sprintf("INSERT INTO %s
                        (user_id, `default`, name, email)
                        VALUES (%d, '1', '%s', '%s')",
                       get_table_name('identities'),
                       $user_id,
                       addslashes($user_name),
                       addslashes($user_email)));
                       
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
  //header('Content-Type: text/javascript');
  header('Content-Type: application/x-javascript');

  print '/** remote response ['.date('d/M/Y h:i:s O')."] **/\n";
  print $js_code;
  exit;
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
        return rcube_label($attrib);
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

      if ($object=='loginform')
        return rcmail_login_form($attrib);

      else if ($object=='message')
        return rcmail_message_container($attrib);

      // MAIL
      else if ($object=='mailboxlist' && function_exists('rcmail_mailbox_list'))
        return rcmail_mailbox_list($attrib);
        
      else if ($object=='messages' && function_exists('rcmail_message_list'))
        return rcmail_message_list($attrib);

      else if ($object=='messagecountdisplay' && function_exists('rcmail_messagecount_display'))
        return rcmail_messagecount_display($attrib);

      else if ($object=='messageheaders' && function_exists('rcmail_message_headers'))
        return rcmail_message_headers($attrib);

      else if ($object=='messageattachments' && function_exists('rcmail_message_attachments'))
        return rcmail_message_attachments($attrib);

      else if ($object=='messagebody' && function_exists('rcmail_message_body'))
        return rcmail_message_body($attrib);
        
      else if ($object=='blockedobjects' && function_exists('rcmail_remote_objects_msg'))
        return rcmail_remote_objects_msg($attrib);

      else if ($object=='messagecontentframe' && function_exists('rcmail_messagecontent_frame'))
        return rcmail_messagecontent_frame($attrib);

      else if ($object=='messagepartframe' && function_exists('rcmail_message_part_frame'))
        return rcmail_message_part_frame($attrib);

      else if ($object=='messagepartcontrols' && function_exists('rcmail_message_part_controls'))
        return rcmail_message_part_controls($attrib);

      else if ($object=='composeheaders' && function_exists('rcmail_compose_headers'))
        return rcmail_compose_headers($attrib);

      else if ($object=='composesubject' && function_exists('rcmail_compose_subject'))
        return rcmail_compose_subject($attrib);

      else if ($object=='composebody' && function_exists('rcmail_compose_body'))
        return rcmail_compose_body($attrib);

      else if ($object=='composeattachmentlist' && function_exists('rcmail_compose_attachment_list'))
        return rcmail_compose_attachment_list($attrib);

      else if ($object=='composeattachmentform' && function_exists('rcmail_compose_attachment_form'))
        return rcmail_compose_attachment_form($attrib);

      else if ($object=='composeattachment' && function_exists('rcmail_compose_attachment_field'))
        return rcmail_compose_attachment_field($attrib);

      else if ($object=='priorityselector' && function_exists('rcmail_priority_selector'))
        return rcmail_priority_selector($attrib);
        
      else if ($object=='priorityselector' && function_exists('rcmail_priority_selector'))
        return rcmail_priority_selector($attrib);


      // ADDRESS BOOK
      else if ($object=='addresslist' && function_exists('rcmail_contacts_list'))
        return rcmail_contacts_list($attrib);

      else if ($object=='addressframe' && function_exists('rcmail_contact_frame'))
        return rcmail_contact_frame($attrib);

      else if ($object=='recordscountdisplay' && function_exists('rcmail_rowcount_display'))
        return rcmail_rowcount_display($attrib);
        
      else if ($object=='contactdetails' && function_exists('rcmail_contact_details'))
        return rcmail_contact_details($attrib);

      else if ($object=='contacteditform' && function_exists('rcmail_contact_editform'))
        return rcmail_contact_editform($attrib);


      // USER SETTINGS
      else if ($object=='userprefs' && function_exists('rcmail_user_prefs_form'))
        return rcmail_user_prefs_form($attrib);

      else if ($object=='itentitieslist' && function_exists('rcmail_identities_list'))
        return rcmail_identities_list($attrib);

      else if ($object=='identityframe' && function_exists('rcmail_identity_frame'))
        return rcmail_identity_frame($attrib);

      else if ($object=='identityform' && function_exists('rcube_identity_form'))
        return rcube_identity_form($attrib);

      else if ($object=='foldersubscription' && function_exists('rcube_subscription_form'))
        return rcube_subscription_form($attrib);

      else if ($object=='createfolder' && function_exists('rcube_create_folder_form'))
        return rcube_create_folder_form($attrib);


      else if ($object=='pagetitle')
        {
        $task = $GLOBALS['_task'];
        if ($task=='mail' && isset($GLOBALS['MESSAGE']['subject']))
          return rep_specialchars_output("RoundCube|Mail :: ".$GLOBALS['MESSAGE']['subject']);
        else if (isset($GLOBALS['PAGE_TITLE']))
          return rep_specialchars_output("RoundCube|Mail :: ".$GLOBALS['PAGE_TITLE']);
        else if ($task=='mail' && ($mbox_name = $IMAP->get_mailbox_name()))
          return "RoundCube|Mail :: $mbox_name";
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
    $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'width', 'height', 'border', 'hspace', 'vspace', 'alt'));
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
    $table .= '<td class="'.$col.'">' . rcube_label($col) . "</td>\n";

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
  else
    $ts = strtotime($date);

  // convert time to user's timezone
  $timestamp = $ts - date('Z', $ts) + ($CONFIG['timezone'] * 3600);
  
  // get current timestamp in user's timezone
  $now = time();  // local time
  $now -= (int)date('Z'); // make GMT time
  $now += ($CONFIG['timezone'] * 3600); // user's time

  $day_secs = 60*((int)date('H', $now)*60 + (int)date('i', $now));
  $week_secs = 60 * 60 * 24 * 7;
  $diff = $now - $timestamp;

  // define date format depending on current time  
  if ($CONFIG['prettydate'] && !$format && $diff < $day_secs)
    return sprintf('%s %s', rcube_label('today'), date('H:i', $timestamp));
  else if ($CONFIG['prettydate'] && !$format && $diff < $week_secs)
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


?>
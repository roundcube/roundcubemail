<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcmail_template.inc                                   |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2007, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Class to handle HTML page output using a skin template.             |
 |   Extends rcube_html_page class from rcube_html.inc                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id:  $

*/


/**
 * Classes and functions for HTML output
 *
 * @package View
 */

require_once('include/rcube_html.inc');


/**
 * Class to create HTML page output using a skin template
 */
class rcmail_template extends rcube_html_page
{
  var $config;
  var $task = '';
  var $framed = false;
  var $ajax_call = false;
  var $pagetitle = '';
  var $env = array();
  var $js_env = array();
  var $js_commands = array();
  var $object_handlers = array();


  /**
   * Constructor
   *
   * @param array Configuration array
   * @param string Current task
   */
  function __construct(&$config, $task)
  {
    $this->task = $task;
    $this->config = $config;
    $this->ajax_call = !empty($_GET['_remote']) || !empty($_POST['_remote']);
    
    // add common javascripts
    if (!$this->ajax_call)
    {
      $javascript = "var ".JS_OBJECT_NAME." = new rcube_webmail();";

      // don't wait for page onload. Call init at the bottom of the page (delayed)
      $javascript_foot = "if (window.call_init)\n call_init('".JS_OBJECT_NAME."');";

      $this->add_script($javascript, 'head_top');
      $this->add_script($javascript_foot, 'foot');
      $this->scripts_path = 'program/js/';
      $this->include_script('common.js');
      $this->include_script('app.js');
    }
  }

  /**
   * PHP 4 compatibility
   * @see rcmail_template::__construct()
   */
  function rcmail_template(&$config, $task)
  {
    $this->__construct($config, $task);
  }
  
  
  /**
   * Set environment variable
   *
   * @param string Property name
   * @param mixed Property value
   * @param boolean True if this property should be added to client environment
   */
  function set_env($name, $value, $addtojs=true)
  {
    $this->env[$name] = $value;
    if ($addtojs || isset($this->js_env[$name]))
      $this->js_env[$name] = $value;
  }


  /**
   * Set page title variable
   */
  function set_pagetitle($title)
  {
    $this->pagetitle = $title;
  }


  /**
   * Register a template object handler
   *
   * @param string Object name
   * @param string Function name to call
   */
  function add_handler($obj, $func)
  {
    $this->object_handlers[$obj] = $func;
  }

  /**
   * Register a list of template object handlers
   *
   * @param array Hash array with object=>handler pairs
   */
  function add_handlers($arr)
  {
    $this->object_handlers = array_merge($this->object_handlers, $arr);
  }

  /**
   * Register a GUI object to the client script
   *
   * @param string Object name
   * @param string Object ID
   */
  function add_gui_object($obj, $id)
  {
    $this->add_script(JS_OBJECT_NAME.".gui_object('$obj', '$id');");
  }


  /**
   * Call a client method
   *
   * @param string Method to call
   * @param ... Additional arguments
   */
  function command()
  {
    $this->js_commands[] = func_get_args();
  }


  /**
   * Invoke display_message command
   *
   * @param string Message to display
   * @param string Message type [notice|confirm|error]
   * @param array Key-value pairs to be replaced in localized text
   */
  function show_message($message, $type='notice', $vars=NULL)
  {
    $this->command(
      'display_message',
      rcube_label(array('name' => $message, 'vars' => $vars)),
      $type);
  }


  /**
   * Delete all stored env variables and commands
   */
  function reset()
  {
    $this->env = array();
    $this->js_env = array();
    $this->js_commands = array();
    $this->object_handlers = array();    
    parent::reset();
  }

  /**
   * Send the request output to the client.
   * This will either parse a skin tempalte or send an AJAX response
   *
   * @param string  Template name
   * @param boolean True if script should terminate (default)
   */
  function send($templ=null, $exit=true)
  {
    if ($this->ajax_call)
      $this->remote_response('', !$exit);
    else if ($templ != 'iframe')
      $this->parse($templ, false);
    else
    {
      $this->framed = $templ == 'iframe' ? true : $this->framed;
      $this->write();
    }
    
    if ($exit)
      exit;
  }


  /**
   * Send an AJAX response with executable JS code
   * 
   * @param string  Additional JS code
   * @param boolean True if output buffer should be flushed
   */
  function remote_response($add='', $flush=false)
  {
    static $s_header_sent = FALSE;

    if (!$s_header_sent)
    {
      $s_header_sent = TRUE;
      send_nocacheing_headers();
      header('Content-Type: application/x-javascript; charset='.RCMAIL_CHARSET);
      print '/** ajax response ['.date('d/M/Y h:i:s O')."] **/\n";
    }
    
    // unset default env vars
    unset($this->js_env['task'], $this->js_env['action'], $this->js_env['comm_path']);

    // send response code
    print rcube_charset_convert($this->get_js_commands() . $add, RCMAIL_CHARSET, $this->get_charset());

    if ($flush)  // flush the output buffer
      flush();
  }
  
  
  /**
   * Process template and write to stdOut
   *
   * @param string HTML template
   * @see rcube_html_page::write()
   */
  function write($template='')
  {
    // write all env variables to client
    $js = $this->framed ? "if(window.parent) {\n" : '';
    $js .= $this->get_js_commands() . ($this->framed ? ' }' : '');
    $this->add_script($js, 'head_top');

    // call super method
    parent::write($template, $this->config['skin_path']);
  }


  /**
   * Parse a specific skin template and deliver to stdout
   *
   * @param string  Template name
   * @param boolean Exit script
   */  
  function parse($name='main', $exit=true)
  {
    $skin_path = $this->config['skin_path'];

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
      raise_error(array(
        'code' => 501,
        'type' => 'php',
        'line' => __LINE__,
        'file' => __FILE__,
        'message' => "Error loading template for '$name'"), TRUE, TRUE);
      return FALSE;
    }

    // parse for specialtags
    $output = $this->parse_xml($this->parse_conditions($templ));

    // add debug console
    if ($this->config['debug_level'] & 8)
      $this->add_footer('<div style="position:absolute;top:5px;left:5px;width:400px;padding:0.2em;background:white;opacity:0.8;z-index:9000">
        <a href="#toggle" onclick="con=document.getElementById(\'dbgconsole\');con.style.display=(con.style.display==\'none\'?\'block\':\'none\');return false">console</a>
        <form action="/" name="debugform"><textarea name="console" id="dbgconsole" rows="20" cols="40" wrap="off" style="display:none;width:400px;border:none;font-size:x-small"></textarea></form></div>');

    $this->write(trim($this->parse_with_globals($output)), $skin_path);

    if ($exit)
      exit;
  }


  /**
   * Return executable javascript code for all registered commands
   * @access private
   */
  function get_js_commands()
  {
    $out = '';
    if (!$this->framed)
      $out .= ($this->ajax_call ? 'this' : JS_OBJECT_NAME) . '.set_env('.json_serialize($this->js_env).");\n";
    
    foreach ($this->js_commands as $i => $args)
    {
      $method = array_shift($args);
      foreach ($args as $i => $arg)
        $args[$i] = json_serialize($arg);

      $parent = $this->framed || preg_match('/^parent\./', $method);
      $out .= sprintf(
        "%s.%s(%s);\n",
        $this->ajax_call ? 'this' : ($parent ? 'parent.' : '') . JS_OBJECT_NAME,
        preg_replace('/^parent\./', '', $method),
        join(',', $args));
    }
    
    return $out;
  }
  
  /**
   * Make URLs starting with a slash point to skin directory
   * @access private
   */
  function abs_url($str)
  {
    return preg_replace('/^\//', $this->config['skin_path'].'/', $str);
  }



  /*****  Template parsing methods  *****/
  
  /**
   * Replace all strings ($varname)
   * with the content of the according global variable.
   * @access private
   */
  function parse_with_globals($input)
  {
    $GLOBALS['__comm_path'] = $GLOBALS['COMM_PATH'];
    return preg_replace('/\$(__[a-z0-9_\-]+)/e', '$GLOBALS["\\1"]', $input);
  }
  
  
  /**
   * Parse for conditional tags
   * @access private
   */
  function parse_conditions($input)
  {
    if (($matches = preg_split('/<roundcube:(if|elseif|else|endif)\s+([^>]+)>/is', $input, 2, PREG_SPLIT_DELIM_CAPTURE)) && count($matches)==4)
    {
      if (preg_match('/^(else|endif)$/i', $matches[1]))
        return $matches[0] . $this->parse_conditions($matches[3]);
      else
      {
        $attrib = parse_attrib_string($matches[2]);
        if (isset($attrib['condition']))
        {
          $condmet = $this->check_condition($attrib['condition']);
          $submatches = preg_split('/<roundcube:(elseif|else|endif)\s+([^>]+)>/is', $matches[3], 2, PREG_SPLIT_DELIM_CAPTURE);

          if ($condmet)
            $result = $submatches[0] . ($submatches[1] != 'endif' ? preg_replace('/.*<roundcube:endif\s+[^>]+>/Uis', '', $submatches[3], 1) : $submatches[3]);
          else
            $result = "<roundcube:$submatches[1] $submatches[2]>" . $submatches[3];

          return $matches[0] . $this->parse_conditions($result);
        }
        else
        {
          raise_error(array('code' => 500, 'type' => 'php', 'line' => __LINE__, 'file' => __FILE__,
                            'message' => "Unable to parse conditional tag " . $matches[2]), TRUE, FALSE);
        }
      }
    }

    return $input;
  }


  /**
   * Determines if a given condition is met
   *
   * @return True if condition is valid, False is not
   * @access private
   */
  function check_condition($condition)
  {
    $condition = preg_replace(
        array('/session:([a-z0-9_]+)/i', '/config:([a-z0-9_]+)/i', '/env:([a-z0-9_]+)/i', '/request:([a-z0-9_]+)/ie'),
        array("\$_SESSION['\\1']", "\$this->config['\\1']", "\$this->env['\\1']", "get_input_value('\\1', RCUBE_INPUT_GPC)"),
        $condition);

    return @eval("return (".$condition.");");
  }


  /**
   * Search for special tags in input and replace them
   * with the appropriate content
   *
   * @param string Input string to parse
   * @return Altered input string
   * @access private
   */
  function parse_xml($input)
  {
    return preg_replace('/<roundcube:([-_a-z]+)\s+([^>]+)>/Uie', "\$this->xml_command('\\1', '\\2')", $input);
  }


  /**
   * Convert a xml command tag into real content
   *
   * @param string Tag command: object,button,label, etc.
   * @param string Attribute string
   * @return Tag/Object content string
   * @access private
   */
  function xml_command($command, $str_attrib, $add_attrib=array())
  {
    $command = strtolower($command);
    $attrib = parse_attrib_string($str_attrib) + $add_attrib;

    // empty output if required condition is not met
    if (!empty($attrib['condition']) && !$this->check_condition($attrib['condition']))
      return '';

    // execute command
    switch ($command)
    {
      // return a button
      case 'button':
        if ($attrib['command'])
          return $this->button($attrib);
        break;

      // show a label
      case 'label':
        if ($attrib['name'] || $attrib['command'])
          return Q(rcube_label($attrib + array('vars' => array('product' => $this->config['product_name']))));
        break;

      // include a file 
      case 'include':
        $path = realpath($this->config['skin_path'].$attrib['file']);
        if (filesize($path) && ($fp = @fopen($path, 'r')))
        {
          $incl = fread($fp, filesize($path));
          fclose($fp);        
          return $this->parse_xml($incl);
        }
        break;

      // return code for a specific application object
      case 'object':
        $object = strtolower($attrib['name']);

        // execute object handler function
        if ($this->object_handlers[$object] && function_exists($this->object_handlers[$object]))
          return call_user_func($this->object_handlers[$object], $attrib);

        else if ($object=='productname')
        {
          $name = !empty($this->config['product_name']) ? $this->config['product_name'] : 'RoundCube Webmail';
          return Q($name);
        }
        else if ($object=='version')
        {
          return (string)RCMAIL_VERSION;
        }
        else if ($object=='pagetitle')
        {
          $task = $this->task;
          $title = !empty($this->config['product_name']) ? $this->config['product_name'].' :: ' : '';

          if (!empty($this->pagetitle))
            $title .= $this->pagetitle;
          else if ($task == 'login')
            $title = rcube_label(array('name' => 'welcome', 'vars' => array('product' => $this->config['product_name'])));
          else
            $title .= ucfirst($task);

          return Q($title);
        }

        break;
      }

    return '';
  }


  /**
   * Create and register a button
   *
   * @param array Button attributes
   * @return HTML button
   * @access private
   */
  function button($attrib)
  {
    global $CONFIG, $OUTPUT, $BROWSER, $MAIN_TASKS;
    static $sa_buttons = array();
    static $s_button_count = 100;

    // these commands can be called directly via url
    $a_static_commands = array('compose', 'list');

    $skin_path = $this->config['skin_path'];

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
      if (!$attrib['name'])
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
      $attrib['title'] = Q(rcube_label($attrib['title']));
    if ($attrib['label'])
      $attrib['label'] = Q(rcube_label($attrib['label']));

    if ($attrib['alt'])
      $attrib['alt'] = Q(rcube_label($attrib['alt']));

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
      $this->add_script(sprintf(
        "%s.register_button('%s', '%s', '%s', '%s', '%s', '%s');",
        JS_OBJECT_NAME,
        $command,
        $attrib['id'],
        $attrib['type'],
        $attrib['imageact'] ? $skin_path.$attrib['imageact'] : $attrib['classact'],
        $attrib['imagesel'] ? $skin_path.$attrib['imagesel'] : $attrib['classsel'],
        $attrib['imageover'] ? $skin_path.$attrib['imageover'] : '')
      );

      // make valid href to specific buttons
      if (in_array($attrib['command'], $MAIN_TASKS))
        $attrib['href'] = Q(rcmail_url(null, null, $attrib['command']));
      else if (in_array($attrib['command'], $a_static_commands))
        $attrib['href'] = Q(rcmail_url($attrib['command']));
    }

    // overwrite attributes
    if (!$attrib['href'])
      $attrib['href'] = '#';

    if ($command)
      $attrib['onclick'] = sprintf("return %s.command('%s','%s',this)", JS_OBJECT_NAME, $command, $attrib['prop']);

    if ($command && $attrib['imageover'])
    {
      $attrib['onmouseover'] = sprintf("return %s.button_over('%s','%s')", JS_OBJECT_NAME, $command, $attrib['id']);
      $attrib['onmouseout'] = sprintf("return %s.button_out('%s','%s')", JS_OBJECT_NAME, $command, $attrib['id']);
    }

    if ($command && $attrib['imagesel'])
    {
      $attrib['onmousedown'] = sprintf("return %s.button_sel('%s','%s')", JS_OBJECT_NAME, $command, $attrib['id']);
      $attrib['onmouseup'] = sprintf("return %s.button_out('%s','%s')", JS_OBJECT_NAME, $command, $attrib['id']);
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
      $out = sprintf('<input%s disabled="disabled" />', $attrib_str);
    }

    // generate html code for button
    if ($btn_content)
    {
      $attrib_str = create_attrib_string($attrib, $link_attrib);
      $out = sprintf('<a%s>%s</a>', $attrib_str, $btn_content);
    }

    return $out;
  }

}  // end class rcmail_template



// ************** common functions delivering gui objects **************


/**
 * Builder for GUI object 'message'
 *
 * @param array Named tag parameters
 * @return string HTML code for the gui object
 */
function rcmail_message_container($attrib)
  {
  global $OUTPUT;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmMessageContainer';

  // allow the following attributes to be added to the <table> tag
  $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id'));
  $out = '<div' . $attrib_str . "></div>";
  
  $OUTPUT->add_gui_object('message', $attrib['id']);
  
  return $out;
  }


/**
 * GUI object 'username'
 * Showing IMAP username of the current session
 *
 * @param array Named tag parameters (currently not used)
 * @return string HTML code for the gui object
 */
function rcmail_current_username($attrib)
  {
  global $DB;
  static $s_username;

  // alread fetched  
  if (!empty($s_username))
    return $s_username;

  // get e-mail address form default identity
  $sql_result = $DB->query(
    "SELECT email AS mailto
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


/**
 * GUI object 'loginform'
 * Returns code for the webmail login form
 *
 * @param array Named parameters
 * @return string HTML code for the gui object
 */
function rcmail_login_form($attrib)
  {
  global $CONFIG, $OUTPUT, $SESS_HIDDEN_FIELD;
  
  $labels = array();
  $labels['user'] = rcube_label('username');
  $labels['pass'] = rcube_label('password');
  $labels['host'] = rcube_label('server');
  
  $input_user = new textfield(array('name' => '_user', 'id' => 'rcmloginuser', 'size' => 30) + $attrib);
  $input_pass = new passwordfield(array('name' => '_pass', 'id' => 'rcmloginpwd', 'size' => 30) + $attrib);
  $input_action = new hiddenfield(array('name' => '_action', 'value' => 'login'));
    
  $fields = array();
  $fields['user'] = $input_user->show(get_input_value('_user', RCUBE_INPUT_POST));
  $fields['pass'] = $input_pass->show();
  $fields['action'] = $input_action->show();
  
  if (is_array($CONFIG['default_host']))
    {
    $select_host = new select(array('name' => '_host', 'id' => 'rcmloginhost'));
    
    foreach ($CONFIG['default_host'] as $key => $value)
    {
      if (!is_array($value))
        $select_host->add($value, (is_numeric($key) ? $value : $key));
      else
        {
        unset($select_host);
        break;
        }
    }
      
    $fields['host'] = isset($select_host) ? $select_host->show($_POST['_host']) : null;
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

  $OUTPUT->add_gui_object('loginform', $form_name);
  
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


/**
 * GUI object 'charsetselector'
 *
 * @param array Named parameters for the select tag
 * @return string HTML code for the gui object
 */
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


/**
 * GUI object 'searchform'
 * Returns code for search function
 *
 * @param array Named parameters
 * @return string HTML code for the gui object
 */
function rcmail_search_form($attrib)
  {
  global $OUTPUT;

  // add some labels to client
  rcube_add_label('searching');

  $attrib['name'] = '_q';

  if (empty($attrib['id']))
    $attrib['id'] = 'rcmqsearchbox';

  $input_q = new textfield($attrib);
  $out = $input_q->show();

  $OUTPUT->add_gui_object('qsearchbox', $attrib['id']);

  // add form tag around text field
  if (empty($attrib['form']))
    $out = sprintf(
      '<form name="rcmqsearchform" action="./" '.
      'onsubmit="%s.command(\'search\');return false" style="display:inline;">%s</form>',
      JS_OBJECT_NAME,
      $out);

  return $out;
  } 


?>

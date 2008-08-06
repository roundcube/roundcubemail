<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_template.php                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2006-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Class to handle HTML page output using a skin template.             |
 |   Extends rcube_html_page class from rcube_shared.inc                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

 */


/**
 * Class to create HTML page output using a skin template
 *
 * @package View
 * @todo Documentation
 * @uses rcube_html_page
 */
class rcube_template extends rcube_html_page
{
    var $app;
    var $config;
    var $framed = false;
    var $pagetitle = '';
    var $env = array();
    var $js_env = array();
    var $js_commands = array();
    var $object_handlers = array();

    public $ajax_call = false;

    /**
     * Constructor
     *
     * @todo   Use jQuery's $(document).ready() here.
     * @todo   Replace $this->config with the real rcube_config object
     */
    public function __construct($task, $framed = false)
    {
        parent::__construct();

        $this->app = rcmail::get_instance();
        $this->config = $this->app->config->all();
        
        //$this->framed = $framed;
        $this->set_env('task', $task);

        // load the correct skin (in case user-defined)
        $this->set_skin($this->config['skin']);

        // add common javascripts
        $javascript = 'var '.JS_OBJECT_NAME.' = new rcube_webmail();';

        // don't wait for page onload. Call init at the bottom of the page (delayed)
        $javascript_foot = "if (window.call_init)\n call_init('".JS_OBJECT_NAME."');";

        $this->add_script($javascript, 'head_top');
        $this->add_script($javascript_foot, 'foot');
        $this->scripts_path = 'program/js/';
        $this->include_script('common.js');
        $this->include_script('app.js');

        // register common UI objects
        $this->add_handlers(array(
            'loginform'       => array($this, 'login_form'),
            'username'        => array($this, 'current_username'),
            'message'         => array($this, 'message_container'),
            'charsetselector' => array($this, 'charset_selector'),
        ));
    }

    /**
     * Set environment variable
     *
     * @param string Property name
     * @param mixed Property value
     * @param boolean True if this property should be added to client environment
     */
    public function set_env($name, $value, $addtojs = true)
    {
        $this->env[$name] = $value;
        if ($addtojs || isset($this->js_env[$name])) {
            $this->js_env[$name] = $value;
        }
    }


    /**
     * Set page title variable
     */
    public function set_pagetitle($title)
    {
        $this->pagetitle = $title;
    }

    /**
     * Set skin
     */
    public function set_skin($skin)
    {
        if (!empty($skin) && is_dir('skins/'.$skin) && is_readable('skins/'.$skin))
            $skin_path = 'skins/'.$skin;
        else
            $skin_path = $this->config['skin_path'] ? $this->config['skin_path'] : 'skins/default';

        $this->app->config->set('skin_path', $skin_path);
        $this->config['skin_path'] = $skin_path;
    }

    /**
     * Check if a specific template exists
     *
     * @param string Template name
     * @return boolean True if template exists
     */
    public function template_exists($name)
    {
        $filename = $this->config['skin_path'] . '/templates/' . $name . '.html';

        return (is_file($filename) && is_readable($filename));
    }

    /**
     * Register a template object handler
     *
     * @param  string Object name
     * @param  string Function name to call
     * @return void
     */
    public function add_handler($obj, $func)
    {
        $this->object_handlers[$obj] = $func;
    }

    /**
     * Register a list of template object handlers
     *
     * @param  array Hash array with object=>handler pairs
     * @return void
     */
    public function add_handlers($arr)
    {
        $this->object_handlers = array_merge($this->object_handlers, $arr);
    }

    /**
     * Register a GUI object to the client script
     *
     * @param  string Object name
     * @param  string Object ID
     * @return void
     */
    public function add_gui_object($obj, $id)
    {
        $this->add_script(JS_OBJECT_NAME.".gui_object('$obj', '$id');");
    }

    /**
     * Call a client method
     *
     * @param string Method to call
     * @param ... Additional arguments
     */
    public function command()
    {
        $this->js_commands[] = func_get_args();
    }


    /**
     * Add a localized label to the client environment
     */
    public function add_label()
    {
        $arg_list = func_get_args();
        foreach ($arg_list as $i => $name) {
            $this->command('add_label', $name, rcube_label($name));
        }
    }


    /**
     * Invoke display_message command
     *
     * @param string Message to display
     * @param string Message type [notice|confirm|error]
     * @param array Key-value pairs to be replaced in localized text
     * @uses self::command()
     */
    public function show_message($message, $type='notice', $vars=NULL)
    {
        $this->command(
            'display_message',
            rcube_label(array('name' => $message, 'vars' => $vars)),
            $type);
    }


    /**
     * Delete all stored env variables and commands
     *
     * @return void
     * @uses   rcube_html::reset()
     * @uses   self::$env
     * @uses   self::$js_env
     * @uses   self::$js_commands
     * @uses   self::$object_handlers
     */
    public function reset()
    {
        $this->env = array();
        $this->js_env = array();
        $this->js_commands = array();
        $this->object_handlers = array();
        parent::reset();
    }


    /**
     * Redirect to a certain url
     *
     * @param mixed Either a string with the action or url parameters as key-value pairs
     * @see rcmail::url()
     */
    public function redirect($p = array())
    {
        $location = $this->app->url($p);
        header('Location: ' . $location);
        exit;
    }


    /**
     * Send the request output to the client.
     * This will either parse a skin tempalte or send an AJAX response
     *
     * @param string  Template name
     * @param boolean True if script should terminate (default)
     */
    public function send($templ = null, $exit = true)
    {
        if ($templ != 'iframe') {
            $this->parse($templ, false);
        }
        else {
            $this->framed = $templ == 'iframe' ? true : $this->framed;
            $this->write();
        }

        if ($exit) {
            exit;
        }
    }

    /**
     * Process template and write to stdOut
     *
     * @param string HTML template
     * @see rcube_html_page::write()
     * @override
     */
    public function write($template = '')
    {
        // unlock interface after iframe load
        if ($this->framed) {
            array_unshift($this->js_commands, array('set_busy', false));
        }
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
     * Either returns nothing, or exists hard (exit();)
     *
     * @param  string  Template name
     * @param  boolean Exit script
     * @return void
     * @link   http://php.net/manual/en/function.exit.php
     */
    private function parse($name = 'main', $exit = true)
    {
        $skin_path = $this->config['skin_path'];
        $path = "$skin_path/templates/$name.html";

        // read template file
	if (($templ = file_get_contents($path)) === false) {
            ob_start();
            file_get_contents($path);
            $message = ob_get_contents();
            ob_end_clean();
            raise_error(array(
                'code' => 501,
                'type' => 'php',
                'line' => __LINE__,
                'file' => __FILE__,
                'message' => 'Error loading template for '.$name.': '.$message
                ), true, true);
            return false;
        }

        // parse for specialtags
        $output = $this->parse_conditions($templ);
        $output = $this->parse_xml($output);

        // add debug console
        if ($this->config['debug_level'] & 8) {
            $this->add_footer('<div style="position:absolute;top:5px;left:5px;width:400px;padding:0.2em;background:white;opacity:0.8;z-index:9000">
                <a href="#toggle" onclick="con=document.getElementById(\'dbgconsole\');con.style.display=(con.style.display==\'none\'?\'block\':\'none\');return false">console</a>
                <form action="/" name="debugform"><textarea name="console" id="dbgconsole" rows="20" cols="40" wrap="off" style="display:none;width:400px;border:none;font-size:x-small"></textarea></form></div>'
            );
        }
        $output = $this->parse_with_globals($output);
        $this->write(trim($output), $skin_path);
        if ($exit) {
            exit;
        }
    }


    /**
     * Return executable javascript code for all registered commands
     *
     * @return string $out
     */
    private function get_js_commands()
    {
        $out = '';
        if (!$this->framed && !empty($this->js_env)) {
            $out .= JS_OBJECT_NAME . '.set_env('.json_serialize($this->js_env).");\n";
        }
        foreach ($this->js_commands as $i => $args) {
            $method = array_shift($args);
            foreach ($args as $i => $arg) {
                $args[$i] = json_serialize($arg);
            }
            $parent = $this->framed || preg_match('/^parent\./', $method);
            $out .= sprintf(
                "%s.%s(%s);\n",
            ($parent ? 'parent.' : '') . JS_OBJECT_NAME,
            preg_replace('/^parent\./', '', $method),
            implode(',', $args)
            );
        }
        // add command to set page title
        if ($this->ajax_call && !empty($this->pagetitle)) {
            $out .= sprintf(
                "this.set_pagetitle('%s');\n",
                JQ((!empty($this->config['product_name']) ? $this->config['product_name'].' :: ' : '') . $this->pagetitle)
            );
        }
        return $out;
    }

    /**
     * Make URLs starting with a slash point to skin directory
     *
     * @param  string Input string
     * @return string
     */
    public function abs_url($str)
    {
        return preg_replace('/^\//', $this->config['skin_path'].'/', $str);
    }


    /*****  Template parsing methods  *****/

    /**
     * Replace all strings ($varname)
     * with the content of the according global variable.
     */
    private function parse_with_globals($input)
    {
        $GLOBALS['__comm_path'] = Q($this->app->comm_path);
        return preg_replace('/\$(__[a-z0-9_\-]+)/e', '$GLOBALS["\\1"]', $input);
    }

    /**
     * Public wrapper to dipp into template parsing.
     *
     * @param  string $input
     * @return string
     * @uses   rcube_template::parse_xml()
     * @since  0.1-rc1
     */
    public function just_parse($input)
    {
        return $this->parse_xml($input);
    }

    /**
     * Parse for conditional tags
     *
     * @param  string $input
     * @return string
     */
    private function parse_conditions($input)
    {
        $matches = preg_split('/<roundcube:(if|elseif|else|endif)\s+([^>]+)>/is', $input, 2, PREG_SPLIT_DELIM_CAPTURE);
        if ($matches && count($matches) == 4) {
            if (preg_match('/^(else|endif)$/i', $matches[1])) {
                return $matches[0] . $this->parse_conditions($matches[3]);
            }
            $attrib = parse_attrib_string($matches[2]);
            if (isset($attrib['condition'])) {
                $condmet = $this->check_condition($attrib['condition']);
                $submatches = preg_split('/<roundcube:(elseif|else|endif)\s+([^>]+)>/is', $matches[3], 2, PREG_SPLIT_DELIM_CAPTURE);
                if ($condmet) {
                    $result = $submatches[0];
                    $result.= ($submatches[1] != 'endif' ? preg_replace('/.*<roundcube:endif\s+[^>]+>/Uis', '', $submatches[3], 1) : $submatches[3]);
                }
                else {
                    $result = "<roundcube:$submatches[1] $submatches[2]>" . $submatches[3];
                }
                return $matches[0] . $this->parse_conditions($result);
            }
            raise_error(array(
                'code' => 500,
                'type' => 'php',
                'line' => __LINE__,
                'file' => __FILE__,
                'message' => "Unable to parse conditional tag " . $matches[2]
            ), true, false);
        }
        return $input;
    }


    /**
     * Determines if a given condition is met
     *
     * @todo   Get rid off eval() once I understand what this does.
     * @todo   Extend this to allow real conditions, not just "set"
     * @param  string Condition statement
     * @return boolean True if condition is met, False is not
     */
    private function check_condition($condition)
    {
        $condition = preg_replace(
            array(
                '/session:([a-z0-9_]+)/i',
                '/config:([a-z0-9_]+)/i',
                '/env:([a-z0-9_]+)/i',
                '/request:([a-z0-9_]+)/ie'
            ),
            array(
                "\$_SESSION['\\1']",
                "\$this->config['\\1']",
                "\$this->env['\\1']",
                "get_input_value('\\1', RCUVE_INPUT_GPC)"
            ),
            $condition);
            
            return eval("return (".$condition.");");
    }


    /**
     * Search for special tags in input and replace them
     * with the appropriate content
     *
     * @param  string Input string to parse
     * @return string Altered input string
     * @todo   Maybe a cache.
     */
    private function parse_xml($input)
    {
        return preg_replace('/<roundcube:([-_a-z]+)\s+([^>]+)>/Uie', "\$this->xml_command('\\1', '\\2')", $input);
    }


    /**
     * Convert a xml command tag into real content
     *
     * @param  string Tag command: object,button,label, etc.
     * @param  string Attribute string
     * @return string Tag/Object content
     */
    private function xml_command($command, $str_attrib, $add_attrib = array())
    {
        $command = strtolower($command);
        $attrib  = parse_attrib_string($str_attrib) + $add_attrib;

        // empty output if required condition is not met
        if (!empty($attrib['condition']) && !$this->check_condition($attrib['condition'])) {
            return '';
        }

        // execute command
        switch ($command) {
            // return a button
            case 'button':
                if ($attrib['name'] || $attrib['command']) {
                    return $this->button($attrib);
                }
                break;

            // show a label
            case 'label':
                if ($attrib['name'] || $attrib['command']) {
                    return Q(rcube_label($attrib + array('vars' => array('product' => $this->config['product_name']))));
                }
                break;

            // include a file
            case 'include':
                $path = realpath($this->config['skin_path'].$attrib['file']);
                if (is_readable($path)) {
                    if ($this->config['skin_include_php']) {
                        $incl = $this->include_php($path);
                    }
                    else {
		        $incl = file_get_contents($path);
		    }
                    return $this->parse_xml($incl);
                }
                break;

            case 'plugin.include':
                //rcube::tfk_debug(var_export($this->config['skin_path'], true));
                $path = realpath($this->config['skin_path'].$attrib['file']);
                if (!$path) {
                    //rcube::tfk_debug("Does not exist:");
                    //rcube::tfk_debug($this->config['skin_path']);
                    //rcube::tfk_debug($attrib['file']);
                    //rcube::tfk_debug($path);
                }
                $incl = file_get_contents($path);
                if ($incl) {
                    return $this->parse_xml($incl);
                }
                break;

            // return code for a specific application object
            case 'object':
                $object = strtolower($attrib['name']);

                // we are calling a class/method
                if (($handler = $this->object_handlers[$object]) && is_array($handler)) {
                    if ((is_object($handler[0]) && method_exists($handler[0], $handler[1])) ||
                    (is_string($handler[0]) && class_exists($handler[0])))
                    return call_user_func($handler, $attrib);
                }
                else if (function_exists($handler)) {
                    // execute object handler function
                    return call_user_func($handler, $attrib);
                }

                if ($object=='productname') {
                    $name = !empty($this->config['product_name']) ? $this->config['product_name'] : 'RoundCube Webmail';
                    return Q($name);
                }
                if ($object=='version') {
                    $ver = (string)RCMAIL_VERSION;
                    if (is_file(INSTALL_PATH . '.svn/entries')) {
                        if (preg_match('/Revision:\s(\d+)/', @shell_exec('svn info'), $regs))
                          $ver .= ' [SVN r'.$regs[1].']';
                    }
                    return $ver;
                }
                if ($object=='pagetitle') {
                    $task  = $this->env['task'];
                    $title = !empty($this->config['product_name']) ? $this->config['product_name'].' :: ' : '';

                    if (!empty($this->pagetitle)) {
                        $title .= $this->pagetitle;
                    }
                    else if ($task == 'login') {
                        $title = rcube_label(array('name' => 'welcome', 'vars' => array('product' => $this->config['product_name'])));
                    }
                    else {
                        $title .= ucfirst($task);
                    }

                    return Q($title);
                }
                break;
            
            // return variable
            case 'var':
                $var = explode(':', $attrib['name']);
                $name = $var[1];
                $value = '';

                switch ($var[0]) {
                    case 'env':
                        $value = $this->env[$name];
                        break;
                    case 'config':
                        $value = $this->config[$name];
                        if (is_array($value) && $value[$_SESSION['imap_host']]) {
                            $value = $value[$_SESSION['imap_host']];
                        }
                        break;
                    case 'request':
                        $value = get_input_value($name, RCUBE_INPUT_GPC);
                        break;
                    case 'session':
                        $value = $_SESSION[$name];
                        break;
                }

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                return Q($value);
                break;
        }
        return '';
    }

    /**
     * Include a specific file and return it's contents
     *
     * @param string File path
     * @return string Contents of the processed file
     */
    private function include_php($file)
    {
        ob_start();
        include $file;
        $out = ob_get_contents();
        ob_end_clean();

        return $out;
    }

    /**
     * Create and register a button
     *
     * @param  array Named button attributes
     * @return string HTML button
     * @todo   Remove all inline JS calls and use jQuery instead.
     * @todo   Remove all sprintf()'s - they are pretty, but also slow.
     */
    private function button($attrib)
    {
        static $sa_buttons = array();
        static $s_button_count = 100;

        // these commands can be called directly via url
        $a_static_commands = array('compose', 'list');

        if (!($attrib['command'] || $attrib['name'])) {
            return '';
        }

        $browser   = new rcube_browser();

        // try to find out the button type
        if ($attrib['type']) {
            $attrib['type'] = strtolower($attrib['type']);
        }
        else {
            $attrib['type'] = ($attrib['image'] || $attrib['imagepas'] || $attrib['imageact']) ? 'image' : 'link';
        }
        $command = $attrib['command'];

        // take the button from the stack
        if ($attrib['name'] && $sa_buttons[$attrib['name']]) {
            $attrib = $sa_buttons[$attrib['name']];
        }
        else if($attrib['image'] || $attrib['imageact'] || $attrib['imagepas'] || $attrib['class']) {
            // add button to button stack
            if (!$attrib['name']) {
                $attrib['name'] = $command;
            }
            if (!$attrib['image']) {
                $attrib['image'] = $attrib['imagepas'] ? $attrib['imagepas'] : $attrib['imageact'];
            }
            $sa_buttons[$attrib['name']] = $attrib;
        }
        else if ($command && $sa_buttons[$command]) {
            // get saved button for this command/name
            $attrib = $sa_buttons[$command];
        }

        // set border to 0 because of the link arround the button
        if ($attrib['type']=='image' && !isset($attrib['border'])) {
            $attrib['border'] = 0;
        }
        if (!$attrib['id']) {
            $attrib['id'] =  sprintf('rcmbtn%d', $s_button_count++);
        }
        // get localized text for labels and titles
        if ($attrib['title']) {
            $attrib['title'] = Q(rcube_label($attrib['title']));
        }
        if ($attrib['label']) {
            $attrib['label'] = Q(rcube_label($attrib['label']));
        }
        if ($attrib['alt']) {
            $attrib['alt'] = Q(rcube_label($attrib['alt']));
        }
        // set title to alt attribute for IE browsers
        if ($browser->ie && $attrib['title'] && !$attrib['alt']) {
            $attrib['alt'] = $attrib['title'];
            unset($attrib['title']);
        }

        // add empty alt attribute for XHTML compatibility
        if (!isset($attrib['alt'])) {
            $attrib['alt'] = '';
        }

        // register button in the system
        if ($attrib['command']) {
            $this->add_script(sprintf(
                "%s.register_button('%s', '%s', '%s', '%s', '%s', '%s');",
                JS_OBJECT_NAME,
                $command,
                $attrib['id'],
                $attrib['type'],
                $attrib['imageact'] ? $this->abs_url($attrib['imageact']) : $attrib['classact'],
                $attrib['imagesel'] ? $this->abs_url($attrib['imagesel']) : $attrib['classsel'],
                $attrib['imageover'] ? $this->abs_url($attrib['imageover']) : ''
            ));

            // make valid href to specific buttons
            if (in_array($attrib['command'], rcmail::$main_tasks)) {
                $attrib['href'] = Q(rcmail_url(null, null, $attrib['command']));
            }
            else if (in_array($attrib['command'], $a_static_commands)) {
                $attrib['href'] = Q(rcmail_url($attrib['command']));
            }
        }

        // overwrite attributes
        if (!$attrib['href']) {
            $attrib['href'] = '#';
        }
        if ($command) {
            $attrib['onclick'] = sprintf(
                "return %s.command('%s','%s',this)",
                JS_OBJECT_NAME,
                $command,
                $attrib['prop']
            );
        }
        if ($command && $attrib['imageover']) {
            $attrib['onmouseover'] = sprintf(
                "return %s.button_over('%s','%s')",
                JS_OBJECT_NAME,
                $command,
                $attrib['id']
            );
            $attrib['onmouseout'] = sprintf(
                "return %s.button_out('%s','%s')",
                JS_OBJECT_NAME,
                $command,
                $attrib['id']
            );
        }

        if ($command && $attrib['imagesel']) {
            $attrib['onmousedown'] = sprintf(
                "return %s.button_sel('%s','%s')",
                JS_OBJECT_NAME,
                $command,
                $attrib['id']
            );
            $attrib['onmouseup'] = sprintf(
                "return %s.button_out('%s','%s')",
                JS_OBJECT_NAME,
                $command,
                $attrib['id']
            );
        }

        $out = '';

        // generate image tag
        if ($attrib['type']=='image') {
            $attrib_str = html::attrib_string(
                $attrib,
                array(
                    'style', 'class', 'id', 'width',
                    'height', 'border', 'hspace',
                    'vspace', 'align', 'alt', 'tabindex'
                )
            );
            $btn_content = sprintf('<img src="%s"%s />', $this->abs_url($attrib['image']), $attrib_str);
            if ($attrib['label']) {
                $btn_content .= ' '.$attrib['label'];
            }
            $link_attrib = array('href', 'onclick', 'onmouseover', 'onmouseout', 'onmousedown', 'onmouseup', 'title');
        }
        else if ($attrib['type']=='link') {
            $btn_content = $attrib['label'] ? $attrib['label'] : $attrib['command'];
            $link_attrib = array('href', 'onclick', 'title', 'id', 'class', 'style', 'tabindex');
        }
        else if ($attrib['type']=='input') {
            $attrib['type'] = 'button';

            if ($attrib['label']) {
                $attrib['value'] = $attrib['label'];
            }

            $attrib_str = html::attrib_string(
                $attrib,
                array(
                    'type', 'value', 'onclick',
                    'id', 'class', 'style', 'tabindex'
                )
            );
            $out = sprintf('<input%s disabled="disabled" />', $attrib_str);
        }

        // generate html code for button
        if ($btn_content) {
            $attrib_str = html::attrib_string($attrib, $link_attrib);
            $out = sprintf('<a%s>%s</a>', $attrib_str, $btn_content);
        }

        return $out;
    }


    /*  ************* common functions delivering gui objects **************  */


    /**
     * Create a form tag with the necessary hidden fields
     *
     * @param array Named tag parameters
     * @return string HTML code for the form
     */
    public function form_tag($attrib, $content = null)
    {
      if ($this->framed) {
        $hiddenfield = new html_hiddenfield(array('name' => '_framed', 'value' => '1'));
        $hidden = $hiddenfield->show();
      }
      
      if (!$content)
        $attrib['noclose'] = true;
      
      return html::tag('form',
        $attrib + array('action' => "./", 'method' => "get"),
        $hidden . $content);
    }


    /**
     * GUI object 'username'
     * Showing IMAP username of the current session
     *
     * @param array Named tag parameters (currently not used)
     * @return string HTML code for the gui object
     */
    public function current_username($attrib)
    {
        static $username;

        // alread fetched
        if (!empty($username)) {
            return $username;
        }

        // get e-mail address form default identity
        if ($sql_arr = $this->app->user->get_identity()) {
            $username = $sql_arr['email'];
        }
        else {
            $username = $this->app->user->get_username();
        }

        return $username;
    }


    /**
     * GUI object 'loginform'
     * Returns code for the webmail login form
     *
     * @param array Named parameters
     * @return string HTML code for the gui object
     */
    private function login_form($attrib)
    {
        $default_host = $this->config['default_host'];

        $_SESSION['temp'] = true;

        $input_user   = new html_inputfield(array('name' => '_user', 'id' => 'rcmloginuser', 'size' => 30) + $attrib);
        $input_pass   = new html_passwordfield(array('name' => '_pass', 'id' => 'rcmloginpwd', 'size' => 30) + $attrib);
        $input_action = new html_hiddenfield(array('name' => '_action', 'value' => 'login'));
        $input_host   = null;

        if (is_array($default_host)) {
            $input_host = new html_select(array('name' => '_host', 'id' => 'rcmloginhost'));

            foreach ($default_host as $key => $value) {
                if (!is_array($value)) {
                    $input_host->add($value, (is_numeric($key) ? $value : $key));
                }
                else {
                    $input_host = null;
                    break;
                }
            }
        }
        else if (empty($default_host)) {
            $input_host = new html_inputfield(array('name' => '_host', 'id' => 'rcmloginhost', 'size' => 30));
        }

        $form_name  = !empty($attrib['form']) ? $attrib['form'] : 'form';
        $this->add_gui_object('loginform', $form_name);

        // create HTML table with two cols
        $table = new html_table(array('cols' => 2));

        $table->add('title', html::label('rcmloginuser', Q(rcube_label('username'))));
        $table->add(null, $input_user->show(get_input_value('_user', RCUBE_INPUT_POST)));

        $table->add('title', html::label('rcmloginpwd', Q(rcube_label('password'))));
        $table->add(null, $input_pass->show());

        // add host selection row
        if (is_object($input_host)) {
            $table->add('title', html::label('rcmloginhost', Q(rcube_label('server'))));
            $table->add(null, $input_host->show(get_input_value('_host', RCUBE_INPUT_POST)));
        }

        $out = $input_action->show();
        $out .= $table->show();

        // surround html output with a form tag
        if (empty($attrib['form'])) {
            $out = $this->form_tag(array('name' => $form_name, 'method' => "post"), $out);
        }

        return $out;
    }


    /**
     * GUI object 'searchform'
     * Returns code for search function
     *
     * @param array Named parameters
     * @return string HTML code for the gui object
     */
    private function search_form($attrib)
    {
        // add some labels to client
        $this->add_label('searching');

        $attrib['name'] = '_q';

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmqsearchbox';
        }
        $input_q = new html_inputfield($attrib);
        $out = $input_q->show();

        $this->add_gui_object('qsearchbox', $attrib['id']);

        // add form tag around text field
        if (empty($attrib['form'])) {
            $out = $this->form_tag(array(
                'name' => "rcmqsearchform",
                'onsubmit' => JS_OBJECT_NAME . ".command('search');return false;",
                'style' => "display:inline"),
              $out);
        }

        return $out;
    }


    /**
     * Builder for GUI object 'message'
     *
     * @param array Named tag parameters
     * @return string HTML code for the gui object
     */
    private function message_container($attrib)
    {
        if (isset($attrib['id']) === false) {
            $attrib['id'] = 'rcmMessageContainer';
        }

        $this->add_gui_object('message', $attrib['id']);
        return html::div($attrib, "");
    }


    /**
     * GUI object 'charsetselector'
     *
     * @param array Named parameters for the select tag
     * @return string HTML code for the gui object
     */
    static function charset_selector($attrib)
    {
        // pass the following attributes to the form class
        $field_attrib = array('name' => '_charset');
        foreach ($attrib as $attr => $value) {
            if (in_array($attr, array('id', 'class', 'style', 'size', 'tabindex'))) {
                $field_attrib[$attr] = $value;
            }
        }
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

            $select = new html_select($field_attrib);
            $select->add(array_values($charsets), array_keys($charsets));

            $set = $_POST['_charset'] ? $_POST['_charset'] : $this->get_charset();
            return $select->show($set);
    }

}  // end class rcube_template



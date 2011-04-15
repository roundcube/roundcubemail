<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_template.php                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2006-2011, The Roundcube Dev Team                       |
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
    private $app;
    private $config;
    private $pagetitle = '';
    private $message = null;
    private $js_env = array();
    private $js_commands = array();
    private $object_handlers = array();
    private $plugin_skin_path;

    public $browser;
    public $framed = false;
    public $env = array();
    public $type = 'html';
    public $ajax_call = false;

    // deprecated names of templates used before 0.5
    private $deprecated_templates = array(
        'contact' => 'showcontact',
        'contactadd' => 'addcontact',
        'contactedit' => 'editcontact',
        'identityedit' => 'editidentity',
        'messageprint' => 'printmessage',
    );

    /**
     * Constructor
     *
     * @todo   Replace $this->config with the real rcube_config object
     */
    public function __construct($task, $framed = false)
    {
        parent::__construct();

        $this->app = rcmail::get_instance();
        $this->config = $this->app->config->all();
        $this->browser = new rcube_browser();

        //$this->framed = $framed;
        $this->set_env('task', $task);

        // load the correct skin (in case user-defined)
        $this->set_skin($this->config['skin']);

        // add common javascripts
        $this->add_script('var '.JS_OBJECT_NAME.' = new rcube_webmail();', 'head_top');

        // don't wait for page onload. Call init at the bottom of the page (delayed)
        $this->add_script(JS_OBJECT_NAME.'.init();', 'docready');

        $this->scripts_path = 'program/js/';
        $this->include_script('jquery-1.5.min.js');
        $this->include_script('common.js');
        $this->include_script('app.js');

        // register common UI objects
        $this->add_handlers(array(
            'loginform'       => array($this, 'login_form'),
            'preloader'       => array($this, 'preloader'),
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
     * Getter for the current page title
     *
     * @return string The page title
     */
    public function get_pagetitle()
    {
        if (!empty($this->pagetitle)) {
            $title = $this->pagetitle;
        }
        else if ($this->env['task'] == 'login') {
            $title = rcube_label(array('name' => 'welcome', 'vars' => array('product' => $this->config['product_name'])));
        }
        else {
            $title = ucfirst($this->env['task']);
        }

        return $title;
    }


    /**
     * Set skin
     */
    public function set_skin($skin)
    {
        $valid = false;

        if (!empty($skin) && is_dir('skins/'.$skin) && is_readable('skins/'.$skin)) {
            $skin_path = 'skins/'.$skin;
            $valid = true;
        }
        else {
            $skin_path = $this->config['skin_path'] ? $this->config['skin_path'] : 'skins/default';
            $valid = !$skin;
        }

        $this->app->config->set('skin_path', $skin_path);
        $this->config['skin_path'] = $skin_path;

        return $valid;
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
        return (is_file($filename) && is_readable($filename)) || ($this->deprecated_templates[$name] && $this->template_exists($this->deprecated_templates[$name]));
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
        $cmd = func_get_args();
        if (strpos($cmd[0], 'plugin.') === false)
          $this->js_commands[] = $cmd;
    }


    /**
     * Add a localized label to the client environment
     */
    public function add_label()
    {
        $args = func_get_args();
        if (count($args) == 1 && is_array($args[0]))
          $args = $args[0];

        foreach ($args as $name) {
            $this->command('add_label', $name, rcube_label($name));
        }
    }


    /**
     * Invoke display_message command
     *
     * @param string Message to display
     * @param string Message type [notice|confirm|error]
     * @param array Key-value pairs to be replaced in localized text
     * @param boolean Override last set message
     * @uses self::command()
     */
    public function show_message($message, $type='notice', $vars=null, $override=true)
    {
        if ($override || !$this->message) {
            $this->message = $message;
            $msgtext = rcube_label_exists($message) ? rcube_label(array('name' => $message, 'vars' => $vars)) : $message;
            $this->command('display_message', $msgtext, $type);
        }
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
            // prevent from endless loops
            if ($exit != 'recur' && $this->app->plugins->is_processing('render_page')) {
                raise_error(array('code' => 505, 'type' => 'php',
                  'file' => __FILE__, 'line' => __LINE__,
                  'message' => 'Recursion alert: ignoring output->send()'), true, false);
                return;
            }
            $this->parse($templ, false);
        }
        else {
            $this->framed = $templ == 'iframe' ? true : $this->framed;
            $this->write();
        }

        // set output asap
        ob_flush();
        flush();

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
        $unlock = preg_replace('/[^a-z0-9]/i', '', $_GET['_unlock']);
        if ($this->framed) {
            array_unshift($this->js_commands, array('set_busy', false, null, $unlock));
        }
        else if ($unlock) {
            array_unshift($this->js_commands, array('hide_message', $unlock));
        }

        $this->set_env('request_token', $this->app->get_request_token());

        // write all env variables to client
        $js = $this->framed ? "if(window.parent) {\n" : '';
        $js .= $this->get_js_commands() . ($this->framed ? ' }' : '');
        $this->add_script($js, 'head_top');

        // make sure all <form> tags have a valid request token
        $template = preg_replace_callback('/<form\s+([^>]+)>/Ui', array($this, 'alter_form_tag'), $template);
        $this->footer = preg_replace_callback('/<form\s+([^>]+)>/Ui', array($this, 'alter_form_tag'), $this->footer);

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
        $plugin    = false;
        $realname  = $name;
        $temp      = explode('.', $name, 2);
        $this->plugin_skin_path = null;

        if (count($temp) > 1) {
            $plugin    = $temp[0];
            $name      = $temp[1];
            $skin_dir  = $plugin . '/skins/' . $this->config['skin'];
            $skin_path = $this->plugin_skin_path = $this->app->plugins->dir . $skin_dir;

            // fallback to default skin
            if (!is_dir($skin_path)) {
                $skin_dir = $plugin . '/skins/default';
                $skin_path = $this->plugin_skin_path = $this->app->plugins->dir . $skin_dir;
            }
        }

        $path = "$skin_path/templates/$name.html";

        if (!is_readable($path) && $this->deprecated_templates[$realname]) {
            $path = "$skin_path/templates/".$this->deprecated_templates[$realname].".html";
            if (is_readable($path))
                raise_error(array('code' => 502, 'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Using deprecated template '".$this->deprecated_templates[$realname]
                        ."' in ".$this->config['skin_path']."/templates. Please rename to '".$realname."'"),
                true, false);
        }

        // read template file
        if (($templ = @file_get_contents($path)) === false) {
            raise_error(array(
                'code' => 501,
                'type' => 'php',
                'line' => __LINE__,
                'file' => __FILE__,
                'message' => 'Error loading template for '.$realname
                ), true, true);
            return false;
        }

        // replace all path references to plugins/... with the configured plugins dir
        // and /this/ to the current plugin skin directory
        if ($plugin) {
            $templ = preg_replace(array('/\bplugins\//', '/(["\']?)\/this\//'), array($this->app->plugins->url, '\\1'.$this->app->plugins->url.$skin_dir.'/'), $templ);
        }

        // parse for specialtags
        $output = $this->parse_conditions($templ);
        $output = $this->parse_xml($output);

        // trigger generic hook where plugins can put additional content to the page
        $hook = $this->app->plugins->exec_hook("render_page", array('template' => $realname, 'content' => $output));

        // add debug console
        if ($this->config['debug_level'] & 8) {
            $this->add_footer('<div id="console" style="position:absolute;top:5px;left:5px;width:405px;padding:2px;background:white;z-index:9000;">
                <a href="#toggle" onclick="con=$(\'#dbgconsole\');con[con.is(\':visible\')?\'hide\':\'show\']();return false">console</a>
                <textarea name="console" id="dbgconsole" rows="20" cols="40" wrap="off" style="display:none;width:400px;border:none;font-size:10px" spellcheck="false"></textarea></div>'
            );
        }

        $output = $this->parse_with_globals($hook['content']);
        $this->write(trim($output));
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
                ($parent ? 'if(window.parent && parent.'.JS_OBJECT_NAME.') parent.' : '') . JS_OBJECT_NAME,
                preg_replace('/^parent\./', '', $method),
                implode(',', $args)
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
        if ($str[0] == '/')
            return $this->config['skin_path'] . $str;
        else
            return $str;
    }


    /*****  Template parsing methods  *****/

    /**
     * Replace all strings ($varname)
     * with the content of the according global variable.
     */
    private function parse_with_globals($input)
    {
        $GLOBALS['__version'] = Q(RCMAIL_VERSION);
        $GLOBALS['__comm_path'] = Q($this->app->comm_path);
        return preg_replace_callback('/\$(__[a-z0-9_\-]+)/',
	    array($this, 'globals_callback'), $input);
    }

    /**
     * Callback funtion for preg_replace_callback() in parse_with_globals()
     */
    private function globals_callback($matches)
    {
        return $GLOBALS[$matches[1]];
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
        $matches = preg_split('/<roundcube:(if|elseif|else|endif)\s+([^>]+)>\n?/is', $input, 2, PREG_SPLIT_DELIM_CAPTURE);
        if ($matches && count($matches) == 4) {
            if (preg_match('/^(else|endif)$/i', $matches[1])) {
                return $matches[0] . $this->parse_conditions($matches[3]);
            }
            $attrib = parse_attrib_string($matches[2]);
            if (isset($attrib['condition'])) {
                $condmet = $this->check_condition($attrib['condition']);
                $submatches = preg_split('/<roundcube:(elseif|else|endif)\s+([^>]+)>\n?/is', $matches[3], 2, PREG_SPLIT_DELIM_CAPTURE);
                if ($condmet) {
                    $result = $submatches[0];
                    $result.= ($submatches[1] != 'endif' ? preg_replace('/.*<roundcube:endif\s+[^>]+>\n?/Uis', '', $submatches[3], 1) : $submatches[3]);
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
     * @return boolean True if condition is met, False if not
     */
    private function check_condition($condition)
    {
        return eval("return (".$this->parse_expression($condition).");");
    }


    /**
     * Inserts hidden field with CSRF-prevention-token into POST forms
     */
    private function alter_form_tag($matches)
    {
        $out = $matches[0];
        $attrib  = parse_attrib_string($matches[1]);

        if (strtolower($attrib['method']) == 'post') {
            $hidden = new html_hiddenfield(array('name' => '_token', 'value' => $this->app->get_request_token()));
            $out .= "\n" . $hidden->show();
        }

        return $out;
    }


    /**
     * Parses expression and replaces variables
     *
     * @param  string Expression statement
     * @return string Expression value
     */
    private function parse_expression($expression)
    {
        return preg_replace(
            array(
                '/session:([a-z0-9_]+)/i',
                '/config:([a-z0-9_]+)(:([a-z0-9_]+))?/i',
                '/env:([a-z0-9_]+)/i',
                '/request:([a-z0-9_]+)/i',
                '/cookie:([a-z0-9_]+)/i',
                '/browser:([a-z0-9_]+)/i'
            ),
            array(
                "\$_SESSION['\\1']",
                "\$this->app->config->get('\\1',get_boolean('\\3'))",
                "\$this->env['\\1']",
                "get_input_value('\\1', RCUBE_INPUT_GPC)",
                "\$_COOKIE['\\1']",
                "\$this->browser->{'\\1'}"
            ),
            $expression);
    }


    /**
     * Search for special tags in input and replace them
     * with the appropriate content
     *
     * @param  string Input string to parse
     * @return string Altered input string
     * @todo   Use DOM-parser to traverse template HTML
     * @todo   Maybe a cache.
     */
    private function parse_xml($input)
    {
        return preg_replace_callback('/<roundcube:([-_a-z]+)\s+([^>]+)>/Ui', array($this, 'xml_command'), $input);
    }


    /**
     * Callback function for parsing an xml command tag
     * and turn it into real html content
     *
     * @param  array Matches array of preg_replace_callback
     * @return string Tag/Object content
     */
    private function xml_command($matches)
    {
        $command = strtolower($matches[1]);
        $attrib  = parse_attrib_string($matches[2]);

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
                    $label = rcube_label($attrib + array('vars' => array('product' => $this->config['product_name'])));
                    return !$attrbi['noshow'] ? Q($label) : '';
                }
                break;

            // include a file
            case 'include':
                if (!$this->plugin_skin_path || !is_file($path = realpath($this->plugin_skin_path . $attrib['file'])))
                    $path = realpath(($attrib['skin_path'] ? $attrib['skin_path'] : $this->config['skin_path']).$attrib['file']);
                
                if (is_readable($path)) {
                    if ($this->config['skin_include_php']) {
                        $incl = $this->include_php($path);
                    }
                    else {
                      $incl = file_get_contents($path);
                    }
                    $incl = $this->parse_conditions($incl);
                    return $this->parse_xml($incl);
                }
                break;

            case 'plugin.include':
                $hook = $this->app->plugins->exec_hook("template_plugin_include", $attrib);
                return $hook['content'];
                break;

            // define a container block
            case 'container':
                if ($attrib['name'] && $attrib['id']) {
                    $this->command('gui_container', $attrib['name'], $attrib['id']);
                    // let plugins insert some content here
                    $hook = $this->app->plugins->exec_hook("template_container", $attrib);
                    return $hook['content'];
                }
                break;

            // return code for a specific application object
            case 'object':
                $object = strtolower($attrib['name']);
                $content = '';

                // we are calling a class/method
                if (($handler = $this->object_handlers[$object]) && is_array($handler)) {
                    if ((is_object($handler[0]) && method_exists($handler[0], $handler[1])) ||
                    (is_string($handler[0]) && class_exists($handler[0])))
                    $content = call_user_func($handler, $attrib);
                }
                // execute object handler function
                else if (function_exists($handler)) {
                    $content = call_user_func($handler, $attrib);
                }
                else if ($object == 'logo') {
                    $attrib += array('alt' => $this->xml_command(array('', 'object', 'name="productname"')));
                    if ($this->config['skin_logo'])
                        $attrib['src'] = $this->config['skin_logo'];
                    $content = html::img($attrib);
                }
                else if ($object == 'productname') {
                    $name = !empty($this->config['product_name']) ? $this->config['product_name'] : 'Roundcube Webmail';
                    $content = Q($name);
                }
                else if ($object == 'version') {
                    $ver = (string)RCMAIL_VERSION;
                    if (is_file(INSTALL_PATH . '.svn/entries')) {
                        if (preg_match('/Revision:\s(\d+)/', @shell_exec('svn info'), $regs))
                          $ver .= ' [SVN r'.$regs[1].']';
                    }
                    $content = Q($ver);
                }
                else if ($object == 'steptitle') {
                  $content = Q($this->get_pagetitle());
                }
                else if ($object == 'pagetitle') {
                    $title = !empty($this->config['product_name']) ? $this->config['product_name'].' :: ' : '';
                    $title .= $this->get_pagetitle();
                    $content = Q($title);
                }

                // exec plugin hooks for this template object
                $hook = $this->app->plugins->exec_hook("template_object_$object", $attrib + array('content' => $content));
                return $hook['content'];

            // return code for a specified eval expression
            case 'exp':
                $value = $this->parse_expression($attrib['expression']);
                return eval("return Q($value);");

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
                    case 'cookie':
                        $value = htmlspecialchars($_COOKIE[$name]);
                        break;
                    case 'browser':
                        $value = $this->browser->{$name};
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
    public function button($attrib)
    {
        static $s_button_count = 100;

        // these commands can be called directly via url
        $a_static_commands = array('compose', 'list', 'preferences', 'folders', 'identities');

        if (!($attrib['command'] || $attrib['name'])) {
            return '';
        }

        // try to find out the button type
        if ($attrib['type']) {
            $attrib['type'] = strtolower($attrib['type']);
        }
        else {
            $attrib['type'] = ($attrib['image'] || $attrib['imagepas'] || $attrib['imageact']) ? 'image' : 'link';
        }

        $command = $attrib['command'];

        if ($attrib['task'])
          $command = $attrib['task'] . '.' . $command;

        if (!$attrib['image']) {
            $attrib['image'] = $attrib['imagepas'] ? $attrib['imagepas'] : $attrib['imageact'];
        }

        if (!$attrib['id']) {
            $attrib['id'] =  sprintf('rcmbtn%d', $s_button_count++);
        }
        // get localized text for labels and titles
        if ($attrib['title']) {
            $attrib['title'] = Q(rcube_label($attrib['title'], $attrib['domain']));
        }
        if ($attrib['label']) {
            $attrib['label'] = Q(rcube_label($attrib['label'], $attrib['domain']));
        }
        if ($attrib['alt']) {
            $attrib['alt'] = Q(rcube_label($attrib['alt'], $attrib['domain']));
        }

        // set title to alt attribute for IE browsers
        if ($this->browser->ie && !$attrib['title'] && $attrib['alt']) {
            $attrib['title'] = $attrib['alt'];
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
                $attrib['href'] = rcmail_url(null, null, $attrib['command']);
            }
            else if ($attrib['task'] && in_array($attrib['task'], rcmail::$main_tasks)) {
                $attrib['href'] = rcmail_url($attrib['command'], null, $attrib['task']);
            }
            else if (in_array($attrib['command'], $a_static_commands)) {
                $attrib['href'] = rcmail_url($attrib['command']);
            }
            else if ($attrib['command'] == 'permaurl' && !empty($this->env['permaurl'])) {
              $attrib['href'] = $this->env['permaurl'];
            }
        }

        // overwrite attributes
        if (!$attrib['href']) {
            $attrib['href'] = '#';
        }
        if ($attrib['task']) {
            if ($attrib['classact'])
                $attrib['class'] = $attrib['classact'];
        }
        else if ($command && !$attrib['onclick']) {
            $attrib['onclick'] = sprintf(
                "return %s.command('%s','%s',this)",
                JS_OBJECT_NAME,
                $command,
                $attrib['prop']
            );
        }

        $out = '';

        // generate image tag
        if ($attrib['type']=='image') {
            $attrib_str = html::attrib_string(
                $attrib,
                array(
                    'style', 'class', 'id', 'width', 'height', 'border', 'hspace',
                    'vspace', 'align', 'alt', 'tabindex', 'title'
                )
            );
            $btn_content = sprintf('<img src="%s"%s />', $this->abs_url($attrib['image']), $attrib_str);
            if ($attrib['label']) {
                $btn_content .= ' '.$attrib['label'];
            }
            $link_attrib = array('href', 'onclick', 'onmouseover', 'onmouseout', 'onmousedown', 'onmouseup', 'target');
        }
        else if ($attrib['type']=='link') {
            $btn_content = isset($attrib['content']) ? $attrib['content'] : ($attrib['label'] ? $attrib['label'] : $attrib['command']);
            $link_attrib = array('href', 'onclick', 'title', 'id', 'class', 'style', 'tabindex', 'target');
        }
        else if ($attrib['type']=='input') {
            $attrib['type'] = 'button';

            if ($attrib['label']) {
                $attrib['value'] = $attrib['label'];
            }

            $attrib_str = html::attrib_string(
                $attrib,
                array(
                    'type', 'value', 'onclick', 'id', 'class', 'style', 'tabindex'
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
      if ($this->framed || !empty($_REQUEST['_framed'])) {
        $hiddenfield = new html_hiddenfield(array('name' => '_framed', 'value' => '1'));
        $hidden = $hiddenfield->show();
      }

      if (!$content)
        $attrib['noclose'] = true;

      return html::tag('form',
        $attrib + array('action' => "./", 'method' => "get"),
        $hidden . $content,
        array('id','class','style','name','method','action','enctype','onsubmit'));
    }


    /**
     * Build a form tag with a unique request token
     *
     * @param array Named tag parameters including 'action' and 'task' values which will be put into hidden fields
     * @param string Form content
     * @return string HTML code for the form
     */
    public function request_form($attrib, $content = '')
    {
        $hidden = new html_hiddenfield();
        if ($attrib['task']) {
            $hidden->add(array('name' => '_task', 'value' => $attrib['task']));
        }
        if ($attrib['action']) {
            $hidden->add(array('name' => '_action', 'value' => $attrib['action']));
        }

        unset($attrib['task'], $attrib['request']);
        $attrib['action'] = './';

        // we already have a <form> tag
        if ($attrib['form']) {
            if ($this->framed || !empty($_REQUEST['_framed']))
                $hidden->add(array('name' => '_framed', 'value' => '1'));
            return $hidden->show() . $content;
        }
        else
            return $this->form_tag($attrib, $hidden->show() . $content);
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

        // Current username is an e-mail address
        if (strpos($_SESSION['username'], '@')) {
            $username = $_SESSION['username'];
        }
        // get e-mail address from default identity
        else if ($sql_arr = $this->app->user->get_identity()) {
            $username = $sql_arr['email'];
        }
        else {
            $username = $this->app->user->get_username();
        }

        return rcube_idn_to_utf8($username);
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
        $autocomplete = (int) $this->config['login_autocomplete'];

        $_SESSION['temp'] = true;

        // save original url
        $url = get_input_value('_url', RCUBE_INPUT_POST);
        if (empty($url) && !preg_match('/_(task|action)=logout/', $_SERVER['QUERY_STRING']))
            $url = $_SERVER['QUERY_STRING'];

        // set atocomplete attribute
        $user_attrib = $autocomplete > 0 ? array() : array('autocomplete' => 'off');
        $host_attrib = $autocomplete > 0 ? array() : array('autocomplete' => 'off');
        $pass_attrib = $autocomplete > 1 ? array() : array('autocomplete' => 'off');

        $input_task   = new html_hiddenfield(array('name' => '_task', 'value' => 'login'));
        $input_action = new html_hiddenfield(array('name' => '_action', 'value' => 'login'));
        $input_tzone  = new html_hiddenfield(array('name' => '_timezone', 'id' => 'rcmlogintz', 'value' => '_default_'));
        $input_url    = new html_hiddenfield(array('name' => '_url', 'id' => 'rcmloginurl', 'value' => $url));
        $input_user   = new html_inputfield(array('name' => '_user', 'id' => 'rcmloginuser')
            + $attrib + $user_attrib);
        $input_pass   = new html_passwordfield(array('name' => '_pass', 'id' => 'rcmloginpwd')
            + $attrib + $pass_attrib);
        $input_host   = null;

        if (is_array($default_host) && count($default_host) > 1) {
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
        else if (is_array($default_host) && ($host = array_pop($default_host))) {
            $hide_host = true;
            $input_host = new html_hiddenfield(array(
                'name' => '_host', 'id' => 'rcmloginhost', 'value' => $host) + $attrib);
        }
        else if (empty($default_host)) {
            $input_host = new html_inputfield(array('name' => '_host', 'id' => 'rcmloginhost')
                + $attrib + $host_attrib);
        }

        $form_name  = !empty($attrib['form']) ? $attrib['form'] : 'form';
        $this->add_gui_object('loginform', $form_name);

        // create HTML table with two cols
        $table = new html_table(array('cols' => 2));

        $table->add('title', html::label('rcmloginuser', Q(rcube_label('username'))));
        $table->add(null, $input_user->show(get_input_value('_user', RCUBE_INPUT_GPC)));

        $table->add('title', html::label('rcmloginpwd', Q(rcube_label('password'))));
        $table->add(null, $input_pass->show());

        // add host selection row
        if (is_object($input_host) && !$hide_host) {
            $table->add('title', html::label('rcmloginhost', Q(rcube_label('server'))));
            $table->add(null, $input_host->show(get_input_value('_host', RCUBE_INPUT_GPC)));
        }

        $out  = $input_task->show();
        $out .= $input_action->show();
        $out .= $input_tzone->show();
        $out .= $input_url->show();
        $out .= $table->show();

        if ($hide_host) {
            $out .= $input_host->show();
        }

        // surround html output with a form tag
        if (empty($attrib['form'])) {
            $out = $this->form_tag(array('name' => $form_name, 'method' => 'post'), $out);
        }

        return $out;
    }


    /**
     * GUI object 'preloader'
     * Loads javascript code for images preloading
     *
     * @param array Named parameters
     * @return void
     */
    private function preloader($attrib)
    {
        $images = preg_split('/[\s\t\n,]+/', $attrib['images'], -1, PREG_SPLIT_NO_EMPTY);
        $images = array_map(array($this, 'abs_url'), $images);

        if (empty($images) || $this->app->task == 'logout')
            return;

        $this->add_script('var images = ' . json_serialize($images) .';
            for (var i=0; i<images.length; i++) {
                img = new Image();
                img.src = images[i];
            }', 'docready');
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
        if ($attrib['type'] == 'search' && !$this->browser->khtml) {
            unset($attrib['type'], $attrib['results']);
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
    function charset_selector($attrib)
    {
        // pass the following attributes to the form class
        $field_attrib = array('name' => '_charset');
        foreach ($attrib as $attr => $value) {
            if (in_array($attr, array('id', 'name', 'class', 'style', 'size', 'tabindex'))) {
                $field_attrib[$attr] = $value;
            }
        }

        $charsets = array(
            'UTF-8'        => 'UTF-8 ('.rcube_label('unicode').')',
            'US-ASCII'     => 'ASCII ('.rcube_label('english').')',
            'ISO-8859-1'   => 'ISO-8859-1 ('.rcube_label('westerneuropean').')',
            'ISO-8859-2'   => 'ISO-8859-2 ('.rcube_label('easterneuropean').')',
            'ISO-8859-4'   => 'ISO-8859-4 ('.rcube_label('baltic').')',
            'ISO-8859-5'   => 'ISO-8859-5 ('.rcube_label('cyrillic').')',
            'ISO-8859-6'   => 'ISO-8859-6 ('.rcube_label('arabic').')',
            'ISO-8859-7'   => 'ISO-8859-7 ('.rcube_label('greek').')',
            'ISO-8859-8'   => 'ISO-8859-8 ('.rcube_label('hebrew').')',
            'ISO-8859-9'   => 'ISO-8859-9 ('.rcube_label('turkish').')',
            'ISO-8859-10'   => 'ISO-8859-10 ('.rcube_label('nordic').')',
            'ISO-8859-11'   => 'ISO-8859-11 ('.rcube_label('thai').')',
            'ISO-8859-13'   => 'ISO-8859-13 ('.rcube_label('baltic').')',
            'ISO-8859-14'   => 'ISO-8859-14 ('.rcube_label('celtic').')',
            'ISO-8859-15'   => 'ISO-8859-15 ('.rcube_label('westerneuropean').')',
            'ISO-8859-16'   => 'ISO-8859-16 ('.rcube_label('southeasterneuropean').')',
            'WINDOWS-1250' => 'Windows-1250 ('.rcube_label('easterneuropean').')',
            'WINDOWS-1251' => 'Windows-1251 ('.rcube_label('cyrillic').')',
            'WINDOWS-1252' => 'Windows-1252 ('.rcube_label('westerneuropean').')',
            'WINDOWS-1253' => 'Windows-1253 ('.rcube_label('greek').')',
            'WINDOWS-1254' => 'Windows-1254 ('.rcube_label('turkish').')',
            'WINDOWS-1255' => 'Windows-1255 ('.rcube_label('hebrew').')',
            'WINDOWS-1256' => 'Windows-1256 ('.rcube_label('arabic').')',
            'WINDOWS-1257' => 'Windows-1257 ('.rcube_label('baltic').')',
            'WINDOWS-1258' => 'Windows-1258 ('.rcube_label('vietnamese').')',
            'ISO-2022-JP'  => 'ISO-2022-JP ('.rcube_label('japanese').')',
            'ISO-2022-KR'  => 'ISO-2022-KR ('.rcube_label('korean').')',
            'ISO-2022-CN'  => 'ISO-2022-CN ('.rcube_label('chinese').')',
            'EUC-JP'       => 'EUC-JP ('.rcube_label('japanese').')',
            'EUC-KR'       => 'EUC-KR ('.rcube_label('korean').')',
            'EUC-CN'       => 'EUC-CN ('.rcube_label('chinese').')',
            'BIG5'         => 'BIG5 ('.rcube_label('chinese').')',
            'GB2312'       => 'GB2312 ('.rcube_label('chinese').')',
        );

        if (!empty($_POST['_charset']))
	        $set = $_POST['_charset'];
	    else if (!empty($attrib['selected']))
	        $set = $attrib['selected'];
	    else
	        $set = $this->get_charset();

	    $set = strtoupper($set);
	    if (!isset($charsets[$set]))
	        $charsets[$set] = $set;

        $select = new html_select($field_attrib);
        $select->add(array_values($charsets), array_keys($charsets));

        return $select->show($set);
    }

}  // end class rcube_template



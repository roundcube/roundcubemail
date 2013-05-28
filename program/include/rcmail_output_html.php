<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcmail_output_html.php                                |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2006-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Class to handle HTML page output using a skin template.             |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/


/**
 * Class to create HTML page output using a skin template
 *
 * @package    Core
 * @subpackage View
 */
class rcmail_output_html extends rcmail_output
{
    public $type = 'html';

    protected $message = null;
    protected $js_env = array();
    protected $js_labels = array();
    protected $js_commands = array();
    protected $skin_paths = array();
    protected $template_name;
    protected $scripts_path = '';
    protected $script_files = array();
    protected $css_files = array();
    protected $scripts = array();
    protected $default_template = "<html>\n<head><title></title></head>\n<body></body>\n</html>";
    protected $header = '';
    protected $footer = '';
    protected $body = '';
    protected $base_path = '';

    // deprecated names of templates used before 0.5
    protected $deprecated_templates = array(
        'contact'      => 'showcontact',
        'contactadd'   => 'addcontact',
        'contactedit'  => 'editcontact',
        'identityedit' => 'editidentity',
        'messageprint' => 'printmessage',
    );

    /**
     * Constructor
     *
     * @todo   Replace $this->config with the real rcube_config object
     */
    public function __construct($task = null, $framed = false)
    {
        parent::__construct();

        //$this->framed = $framed;
        $this->set_env('task', $task);
        $this->set_env('x_frame_options', $this->config->get('x_frame_options', 'sameorigin'));

        // add cookie info
        $this->set_env('cookie_domain', ini_get('session.cookie_domain'));
        $this->set_env('cookie_path', ini_get('session.cookie_path'));
        $this->set_env('cookie_secure', ini_get('session.cookie_secure'));

        // load the correct skin (in case user-defined)
        $skin = $this->config->get('skin');
        $this->set_skin($skin);
        $this->set_env('skin', $skin);

        if (!empty($_REQUEST['_extwin']))
          $this->set_env('extwin', 1);
        if ($this->framed || !empty($_REQUEST['_framed']))
          $this->set_env('framed', 1);

        // add common javascripts
        $this->add_script('var '.self::JS_OBJECT_NAME.' = new rcube_webmail();', 'head_top');

        // don't wait for page onload. Call init at the bottom of the page (delayed)
        $this->add_script(self::JS_OBJECT_NAME.'.init();', 'docready');

        $this->scripts_path = 'program/js/';
        $this->include_script('jquery.min.js');
        $this->include_script('common.js');
        $this->include_script('app.js');

        // register common UI objects
        $this->add_handlers(array(
            'loginform'       => array($this, 'login_form'),
            'preloader'       => array($this, 'preloader'),
            'username'        => array($this, 'current_username'),
            'message'         => array($this, 'message_container'),
            'charsetselector' => array($this, 'charset_selector'),
            'aboutcontent'    => array($this, 'about_content'),
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
     * Getter for the current page title
     *
     * @return string The page title
     */
    protected function get_pagetitle()
    {
        if (!empty($this->pagetitle)) {
            $title = $this->pagetitle;
        }
        else if ($this->env['task'] == 'login') {
            $title = $this->app->gettext(array(
                'name' => 'welcome',
                'vars' => array('product' => $this->config->get('product_name')
            )));
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
            $skin_path = $this->config->get('skin_path');
            if (!$skin_path) {
                $skin_path = 'skins/' . rcube_config::DEFAULT_SKIN;
            }
            $valid = !$skin;
        }

        $this->config->set('skin_path', $skin_path);
        $this->base_path = $skin_path;

        // register skin path(s)
        $this->skin_paths = array();
        $this->load_skin($skin_path);

        return $valid;
    }

    /**
     * Helper method to recursively read skin meta files and register search paths
     */
    private function load_skin($skin_path)
    {
        $this->skin_paths[] = $skin_path;

        // read meta file and check for dependecies
        $meta = @json_decode(@file_get_contents($skin_path.'/meta.json'), true);
        if ($meta['extends'] && is_dir('skins/' . $meta['extends'])) {
            $this->load_skin('skins/' . $meta['extends']);
        }
    }


    /**
     * Check if a specific template exists
     *
     * @param string Template name
     * @return boolean True if template exists
     */
    public function template_exists($name)
    {
        $found = false;
        foreach ($this->skin_paths as $skin_path) {
            $filename = $skin_path . '/templates/' . $name . '.html';
            $found = (is_file($filename) && is_readable($filename)) || ($this->deprecated_templates[$name] && $this->template_exists($this->deprecated_templates[$name]));
            if ($found)
                break;
        }
        return $found;
    }


    /**
     * Find the given file in the current skin path stack
     *
     * @param string File name/path to resolve (starting with /)
     * @param string Reference to the base path of the matching skin
     * @param string Additional path to search in
     * @return mixed Relative path to the requested file or False if not found
     */
    public function get_skin_file($file, &$skin_path = null, $add_path = null)
    {
        $skin_paths = $this->skin_paths;
        if ($add_path)
            array_unshift($skin_paths, $add_path);

        foreach ($skin_paths as $skin_path) {
            $path = realpath($skin_path . $file);
            if (is_file($path)) {
                return $skin_path . $file;
            }
        }

        return false;
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
        $this->add_script(self::JS_OBJECT_NAME.".gui_object('$obj', '$id');");
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
        if (strpos($cmd[0], 'plugin.') !== false)
          $this->js_commands[] = array('triggerEvent', $cmd[0], $cmd[1]);
        else
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
            $this->js_labels[$name] = $this->app->gettext($name);
        }
    }


    /**
     * Invoke display_message command
     *
     * @param string  $message  Message to display
     * @param string  $type     Message type [notice|confirm|error]
     * @param array   $vars     Key-value pairs to be replaced in localized text
     * @param boolean $override Override last set message
     * @param int     $timeout  Message display time in seconds
     * @uses self::command()
     */
    public function show_message($message, $type='notice', $vars=null, $override=true, $timeout=0)
    {
        if ($override || !$this->message) {
            if ($this->app->text_exists($message)) {
                if (!empty($vars))
                    $vars = array_map('Q', $vars);
                $msgtext = $this->app->gettext(array('name' => $message, 'vars' => $vars));
            }
            else
                $msgtext = $message;

            $this->message = $message;
            $this->command('display_message', $msgtext, $type, $timeout * 1000);
        }
    }


    /**
     * Delete all stored env variables and commands
     *
     * @param bool $all Reset all env variables (including internal)
     */
    public function reset($all = false)
    {
        $env = $all ? null : array_intersect_key($this->env, array('extwin'=>1, 'framed'=>1));

        parent::reset();

        // let some env variables survive
        $this->env = $this->js_env = $env;
        $this->js_labels    = array();
        $this->js_commands  = array();
        $this->script_files = array();
        $this->scripts      = array();
        $this->header       = '';
        $this->footer       = '';
        $this->body         = '';
    }


    /**
     * Redirect to a certain url
     *
     * @param mixed $p     Either a string with the action or url parameters as key-value pairs
     * @param int   $delay Delay in seconds
     */
    public function redirect($p = array(), $delay = 1)
    {
        if ($this->env['extwin'])
            $p['extwin'] = 1;
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
                rcube::raise_error(array('code' => 505, 'type' => 'php',
                  'file' => __FILE__, 'line' => __LINE__,
                  'message' => 'Recursion alert: ignoring output->send()'), true, false);
                return;
            }
            $this->parse($templ, false);
        }
        else {
            $this->framed = true;
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
     * @param string $template HTML template content
     */
    public function write($template = '')
    {
        // unlock interface after iframe load
        $unlock = preg_replace('/[^a-z0-9]/i', '', $_REQUEST['_unlock']);
        if ($this->framed) {
            array_unshift($this->js_commands, array('iframe_loaded', $unlock));
        }
        else if ($unlock) {
            array_unshift($this->js_commands, array('hide_message', $unlock));
        }

        if (!empty($this->script_files))
          $this->set_env('request_token', $this->app->get_request_token());

        // write all env variables to client
        if ($commands = $this->get_js_commands()) {
            $js = $this->framed ? "if (window.parent) {\n" : '';
            $js .= $commands . ($this->framed ? ' }' : '');
            $this->add_script($js, 'head_top');
        }

        // send clickjacking protection headers
        $iframe = $this->framed || !empty($_REQUEST['_framed']);
        if (!headers_sent() && ($xframe = $this->app->config->get('x_frame_options', 'sameorigin')))
            header('X-Frame-Options: ' . ($iframe && $xframe == 'deny' ? 'sameorigin' : $xframe));

        // call super method
        $this->_write($template, $this->config->get('skin_path'));
    }


    /**
     * Parse a specific skin template and deliver to stdout (or return)
     *
     * @param  string  Template name
     * @param  boolean Exit script
     * @param  boolean Don't write to stdout, return parsed content instead
     *
     * @link   http://php.net/manual/en/function.exit.php
     */
    function parse($name = 'main', $exit = true, $write = true)
    {
        $plugin    = false;
        $realname  = $name;
        $this->template_name = $realname;

        $temp = explode('.', $name, 2);
        if (count($temp) > 1) {
            $plugin    = $temp[0];
            $name      = $temp[1];
            $skin_dir  = $plugin . '/skins/' . $this->config->get('skin');

            // apply skin search escalation list to plugin directory
            $plugin_skin_paths = array();
            foreach ($this->skin_paths as $skin_path) {
                $plugin_skin_paths[] = $this->app->plugins->url . $plugin . '/' . $skin_path;
            }

            // add fallback to default skin
            if (is_dir($this->app->plugins->dir . $plugin . '/skins/default')) {
                $skin_dir = $plugin . '/skins/default';
                $plugin_skin_paths[] = $this->app->plugins->url . $skin_dir;
            }

            // add plugin skin paths to search list
            $this->skin_paths = array_merge($plugin_skin_paths, $this->skin_paths);
        }

        // find skin template
        $path = false;
        foreach ($this->skin_paths as $skin_path) {
            $path = "$skin_path/templates/$name.html";

            // fallback to deprecated template names
            if (!is_readable($path) && $this->deprecated_templates[$realname]) {
                $path = "$skin_path/templates/" . $this->deprecated_templates[$realname] . ".html";

                if (is_readable($path)) {
                    rcube::raise_error(array(
                        'code' => 502, 'type' => 'php',
                        'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Using deprecated template '" . $this->deprecated_templates[$realname]
                            . "' in $skin_path/templates. Please rename to '$realname'"),
                        true, false);
                }
            }

            if (is_readable($path)) {
                $this->config->set('skin_path', $skin_path);
                $this->base_path = preg_replace('!plugins/\w+/!', '', $skin_path);  // set base_path to core skin directory (not plugin's skin)
                $skin_dir = preg_replace('!^plugins/!', '', $skin_path);
                break;
            }
            else {
                $path = false;
            }
        }

        // read template file
        if (!$path || ($templ = @file_get_contents($path)) === false) {
            rcube::raise_error(array(
                'code' => 501,
                'type' => 'php',
                'line' => __LINE__,
                'file' => __FILE__,
                'message' => 'Error loading template for '.$realname
                ), true, $write);
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

        // save some memory
        $output = $hook['content'];
        unset($hook['content']);

        // make sure all <form> tags have a valid request token
        $output = preg_replace_callback('/<form\s+([^>]+)>/Ui', array($this, 'alter_form_tag'), $output);
        $this->footer = preg_replace_callback('/<form\s+([^>]+)>/Ui', array($this, 'alter_form_tag'), $this->footer);

        if ($write) {
            // add debug console
            if ($realname != 'error' && ($this->config->get('debug_level') & 8)) {
                $this->add_footer('<div id="console" style="position:absolute;top:5px;left:5px;width:405px;padding:2px;background:white;z-index:9000;display:none">
                    <a href="#toggle" onclick="con=$(\'#dbgconsole\');con[con.is(\':visible\')?\'hide\':\'show\']();return false">console</a>
                    <textarea name="console" id="dbgconsole" rows="20" cols="40" style="display:none;width:400px;border:none;font-size:10px" spellcheck="false"></textarea></div>'
                );
                $this->add_script(
                    "if (!window.console || !window.console.log) {\n".
                    "  window.console = new rcube_console();\n".
                    "  $('#console').show();\n".
                    "}", 'foot');
            }
            $this->write(trim($output));
        }
        else {
            return $output;
        }

        if ($exit) {
            exit;
        }
    }


    /**
     * Return executable javascript code for all registered commands
     *
     * @return string $out
     */
    protected function get_js_commands()
    {
        $out = '';
        if (!$this->framed && !empty($this->js_env)) {
            $out .= self::JS_OBJECT_NAME . '.set_env('.self::json_serialize($this->js_env).");\n";
        }
        if (!empty($this->js_labels)) {
            $this->command('add_label', $this->js_labels);
        }
        foreach ($this->js_commands as $i => $args) {
            $method = array_shift($args);
            foreach ($args as $i => $arg) {
                $args[$i] = self::json_serialize($arg);
            }
            $parent = $this->framed || preg_match('/^parent\./', $method);
            $out .= sprintf(
                "%s.%s(%s);\n",
                ($parent ? 'if(window.parent && parent.'.self::JS_OBJECT_NAME.') parent.' : '') . self::JS_OBJECT_NAME,
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
     * @param  boolean True if URL should be resolved using the current skin path stack
     * @return string
     */
    public function abs_url($str, $search_path = false)
    {
        if ($str[0] == '/') {
            if ($search_path && ($file_url = $this->get_skin_file($str, $skin_path)))
                return $file_url;

            return $this->base_path . $str;
        }
        else
            return $str;
    }


    /**
     * Show error page and terminate script execution
     *
     * @param int    $code     Error code
     * @param string $message  Error message
     */
    public function raise_error($code, $message)
    {
        global $__page_content, $ERROR_CODE, $ERROR_MESSAGE;

        $ERROR_CODE    = $code;
        $ERROR_MESSAGE = $message;

        include RCUBE_INSTALL_PATH . 'program/steps/utils/error.inc';
        exit;
    }


    /*****  Template parsing methods  *****/

    /**
     * Replace all strings ($varname)
     * with the content of the according global variable.
     */
    protected function parse_with_globals($input)
    {
        $GLOBALS['__version']   = html::quote(RCMAIL_VERSION);
        $GLOBALS['__comm_path'] = html::quote($this->app->comm_path);
        $GLOBALS['__skin_path'] = html::quote($this->base_path);

        return preg_replace_callback('/\$(__[a-z0-9_\-]+)/',
            array($this, 'globals_callback'), $input);
    }


    /**
     * Callback funtion for preg_replace_callback() in parse_with_globals()
     */
    protected function globals_callback($matches)
    {
        return $GLOBALS[$matches[1]];
    }


    /**
     * Correct absolute paths in images and other tags
     * add timestamp to .js and .css filename
     */
    protected function fix_paths($output)
    {
        return preg_replace_callback(
            '!(src|href|background)=(["\']?)([a-z0-9/_.-]+)(["\'\s>])!i',
            array($this, 'file_callback'), $output);
    }


    /**
     * Callback function for preg_replace_callback in write()
     *
     * @return string Parsed string
     */
    protected function file_callback($matches)
    {
        $file = $matches[3];
        $file[0] = preg_replace('!^/this/!', '/', $file[0]);

        // correct absolute paths
        if ($file[0] == '/') {
            $file = $this->base_path . $file;
        }

        // add file modification timestamp
        if (preg_match('/\.(js|css)$/', $file)) {
            if ($fs = @filemtime($file)) {
                $file .= '?s=' . $fs;
            }
        }

        return $matches[1] . '=' . $matches[2] . $file . $matches[4];
    }


    /**
     * Public wrapper to dipp into template parsing.
     *
     * @param  string $input
     * @return string
     * @uses   rcmail_output_html::parse_xml()
     * @since  0.1-rc1
     */
    public function just_parse($input)
    {
        $input = $this->parse_conditions($input);
        $input = $this->parse_xml($input);

        return $input;
    }


    /**
     * Parse for conditional tags
     *
     * @param  string $input
     * @return string
     */
    protected function parse_conditions($input)
    {
        $matches = preg_split('/<roundcube:(if|elseif|else|endif)\s+([^>]+)>\n?/is', $input, 2, PREG_SPLIT_DELIM_CAPTURE);
        if ($matches && count($matches) == 4) {
            if (preg_match('/^(else|endif)$/i', $matches[1])) {
                return $matches[0] . $this->parse_conditions($matches[3]);
            }
            $attrib = html::parse_attrib_string($matches[2]);
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
            rcube::raise_error(array(
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
     * @todo   Extend this to allow real conditions, not just "set"
     * @param  string Condition statement
     * @return boolean True if condition is met, False if not
     */
    protected function check_condition($condition)
    {
        return $this->eval_expression($condition);
    }


    /**
     * Inserts hidden field with CSRF-prevention-token into POST forms
     */
    protected function alter_form_tag($matches)
    {
        $out    = $matches[0];
        $attrib = html::parse_attrib_string($matches[1]);

        if (strtolower($attrib['method']) == 'post') {
            $hidden = new html_hiddenfield(array('name' => '_token', 'value' => $this->app->get_request_token()));
            $out .= "\n" . $hidden->show();
        }

        return $out;
    }


    /**
     * Parse & evaluate a given expression and return its result.
     *
     * @param string Expression statement
     *
     * @return mixed Expression result
     */
    protected function eval_expression ($expression)
    {
        $expression = preg_replace(
            array(
                '/session:([a-z0-9_]+)/i',
                '/config:([a-z0-9_]+)(:([a-z0-9_]+))?/i',
                '/env:([a-z0-9_]+)/i',
                '/request:([a-z0-9_]+)/i',
                '/cookie:([a-z0-9_]+)/i',
                '/browser:([a-z0-9_]+)/i',
                '/template:name/i',
            ),
            array(
                "\$_SESSION['\\1']",
                "\$app->config->get('\\1',rcube_utils::get_boolean('\\3'))",
                "\$env['\\1']",
                "rcube_utils::get_input_value('\\1', rcube_utils::INPUT_GPC)",
                "\$_COOKIE['\\1']",
                "\$browser->{'\\1'}",
                $this->template_name,
            ),
            $expression
        );

        $fn = create_function('$app,$browser,$env', "return ($expression);");
        if (!$fn) {
            rcube::raise_error(array(
                'code' => 505,
                'type' => 'php',
                'file' => __FILE__,
                'line' => __LINE__,
                'message' => "Expression parse error on: ($expression)"), true, false);

            return null;
        }

        return $fn($this->app, $this->browser, $this->env);
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
    protected function parse_xml($input)
    {
        return preg_replace_callback('/<roundcube:([-_a-z]+)\s+((?:[^>]|\\\\>)+)(?<!\\\\)>/Ui', array($this, 'xml_command'), $input);
    }


    /**
     * Callback function for parsing an xml command tag
     * and turn it into real html content
     *
     * @param  array Matches array of preg_replace_callback
     * @return string Tag/Object content
     */
    protected function xml_command($matches)
    {
        $command = strtolower($matches[1]);
        $attrib  = html::parse_attrib_string($matches[2]);

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

            // frame
            case 'frame':
                return $this->frame($attrib);
                break;

            // show a label
            case 'label':
                if ($attrib['expression'])
                    $attrib['name'] = $this->eval_expression($attrib['expression']);

                if ($attrib['name'] || $attrib['command']) {
                    // @FIXME: 'noshow' is useless, remove?
                    if ($attrib['noshow']) {
                        return '';
                    }

                    $vars = $attrib + array('product' => $this->config->get('product_name'));
                    unset($vars['name'], $vars['command']);

                    $label   = $this->app->gettext($attrib + array('vars' => $vars));
                    $quoting = !empty($attrib['quoting']) ? strtolower($attrib['quoting']) : (rcube_utils::get_boolean((string)$attrib['html']) ? 'no' : '');

                    switch ($quoting) {
                        case 'no':
                        case 'raw':
                            break;
                        case 'javascript':
                        case 'js':
                            $label = rcube::JQ($label);
                            break;
                        default:
                            $label = html::quote($label);
                            break;
                    }

                    return $label;
                }
                break;

            // include a file
            case 'include':
                $old_base_path = $this->base_path;
                if (!empty($attrib['skin_path'])) $attrib['skinpath'] = $attrib['skin_path'];
                if ($path = $this->get_skin_file($attrib['file'], $skin_path, $attrib['skinpath'])) {
                    $this->base_path = preg_replace('!plugins/\w+/!', '', $skin_path);  // set base_path to core skin directory (not plugin's skin)
                    $path = realpath($path);
                }

                if (is_readable($path)) {
                    if ($this->config->get('skin_include_php')) {
                        $incl = $this->include_php($path);
                    }
                    else {
                      $incl = file_get_contents($path);
                    }
                    $incl = $this->parse_conditions($incl);
                    $incl = $this->parse_xml($incl);
                    $incl = $this->fix_paths($incl);
                    $this->base_path = $old_base_path;
                    return $incl;
                }
                break;

            case 'plugin.include':
                $hook = $this->app->plugins->exec_hook("template_plugin_include", $attrib);
                return $hook['content'];

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
                else if ($object == 'doctype') {
                    $content = html::doctype($attrib['value']);
                }
                else if ($object == 'logo') {
                    $attrib += array('alt' => $this->xml_command(array('', 'object', 'name="productname"')));
                    if ($logo = $this->config->get('skin_logo'))
                        $attrib['src'] = $logo;
                    $content = html::img($attrib);
                }
                else if ($object == 'productname') {
                    $name = $this->config->get('product_name', 'Roundcube Webmail');
                    $content = html::quote($name);
                }
                else if ($object == 'version') {
                    $ver = (string)RCMAIL_VERSION;
                    if (is_file(RCUBE_INSTALL_PATH . '.svn/entries')) {
                        if (preg_match('/Revision:\s(\d+)/', @shell_exec('svn info'), $regs))
                          $ver .= ' [SVN r'.$regs[1].']';
                    }
                    else if (is_file(RCUBE_INSTALL_PATH . '.git/index')) {
                        if (preg_match('/Date:\s+([^\n]+)/', @shell_exec('git log -1'), $regs)) {
                            if ($date = date('Ymd.Hi', strtotime($regs[1]))) {
                                $ver .= ' [GIT '.$date.']';
                            }
                        }
                    }
                    $content = html::quote($ver);
                }
                else if ($object == 'steptitle') {
                  $content = html::quote($this->get_pagetitle());
                }
                else if ($object == 'pagetitle') {
                    if ($this->config->get('devel_mode') && !empty($_SESSION['username']))
                        $title = $_SESSION['username'].' :: ';
                    else if ($prod_name = $this->config->get('product_name'))
                        $title = $prod_name . ' :: ';
                    else
                        $title = '';
                    $title .= $this->get_pagetitle();
                    $content = html::quote($title);
                }

                // exec plugin hooks for this template object
                $hook = $this->app->plugins->exec_hook("template_object_$object", $attrib + array('content' => $content));
                return $hook['content'];

            // return code for a specified eval expression
            case 'exp':
                return html::quote($this->eval_expression($attrib['expression']));

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
                        $value = $this->config->get($name);
                        if (is_array($value) && $value[$_SESSION['storage_host']]) {
                            $value = $value[$_SESSION['storage_host']];
                        }
                        break;
                    case 'request':
                        $value = rcube_utils::get_input_value($name, rcube_utils::INPUT_GPC);
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

                return html::quote($value);
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
    protected function include_php($file)
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
            $attrib['title'] = html::quote($this->app->gettext($attrib['title'], $attrib['domain']));
        }
        if ($attrib['label']) {
            $attrib['label'] = html::quote($this->app->gettext($attrib['label'], $attrib['domain']));
        }
        if ($attrib['alt']) {
            $attrib['alt'] = html::quote($this->app->gettext($attrib['alt'], $attrib['domain']));
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
                self::JS_OBJECT_NAME,
                $command,
                $attrib['id'],
                $attrib['type'],
                $attrib['imageact'] ? $this->abs_url($attrib['imageact']) : $attrib['classact'],
                $attrib['imagesel'] ? $this->abs_url($attrib['imagesel']) : $attrib['classsel'],
                $attrib['imageover'] ? $this->abs_url($attrib['imageover']) : ''
            ));

            // make valid href to specific buttons
            if (in_array($attrib['command'], rcmail::$main_tasks)) {
                $attrib['href']    = $this->app->url(array('task' => $attrib['command']));
                $attrib['onclick'] = sprintf("return %s.command('switch-task','%s',this,event)", self::JS_OBJECT_NAME, $attrib['command']);
            }
            else if ($attrib['task'] && in_array($attrib['task'], rcmail::$main_tasks)) {
                $attrib['href'] = $this->app->url(array('action' => $attrib['command'], 'task' => $attrib['task']));
            }
            else if (in_array($attrib['command'], $a_static_commands)) {
                $attrib['href'] = $this->app->url(array('action' => $attrib['command']));
            }
            else if (($attrib['command'] == 'permaurl' || $attrib['command'] == 'extwin') && !empty($this->env['permaurl'])) {
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
                "return %s.command('%s','%s',this,event)",
                self::JS_OBJECT_NAME,
                $command,
                $attrib['prop']
            );
        }

        $out = '';

        // generate image tag
        if ($attrib['type'] == 'image') {
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
        else if ($attrib['type'] == 'link') {
            $btn_content = isset($attrib['content']) ? $attrib['content'] : ($attrib['label'] ? $attrib['label'] : $attrib['command']);
            $link_attrib = array('href', 'onclick', 'title', 'id', 'class', 'style', 'tabindex', 'target');
            if ($attrib['innerclass'])
                $btn_content = html::span($attrib['innerclass'], $btn_content);
        }
        else if ($attrib['type'] == 'input') {
            $attrib['type'] = 'button';

            if ($attrib['label']) {
                $attrib['value'] = $attrib['label'];
            }
            if ($attrib['command']) {
              $attrib['disabled'] = 'disabled';
            }

            $out = html::tag('input', $attrib, null, array('type', 'value', 'onclick', 'id', 'class', 'style', 'tabindex', 'disabled'));
        }

        // generate html code for button
        if ($btn_content) {
            $attrib_str = html::attrib_string($attrib, $link_attrib);
            $out = sprintf('<a%s>%s</a>', $attrib_str, $btn_content);
        }

        if ($attrib['wrapper']) {
            $out = html::tag($attrib['wrapper'], null, $out);
        }

        return $out;
    }


    /**
     * Link an external script file
     *
     * @param string File URL
     * @param string Target position [head|foot]
     */
    public function include_script($file, $position='head')
    {
        static $sa_files = array();

        if (!preg_match('|^https?://|i', $file) && $file[0] != '/') {
            $file = $this->scripts_path . $file;
            if ($fs = @filemtime($file)) {
                $file .= '?s=' . $fs;
            }
        }

        if (in_array($file, $sa_files)) {
            return;
        }

        $sa_files[] = $file;

        if (!is_array($this->script_files[$position])) {
            $this->script_files[$position] = array();
        }

        $this->script_files[$position][] = $file;
    }


    /**
     * Add inline javascript code
     *
     * @param string JS code snippet
     * @param string Target position [head|head_top|foot]
     */
    public function add_script($script, $position='head')
    {
        if (!isset($this->scripts[$position])) {
            $this->scripts[$position] = "\n" . rtrim($script);
        }
        else {
            $this->scripts[$position] .= "\n" . rtrim($script);
        }
    }


    /**
     * Link an external css file
     *
     * @param string File URL
     */
    public function include_css($file)
    {
        $this->css_files[] = $file;
    }


    /**
     * Add HTML code to the page header
     *
     * @param string $str HTML code
     */
    public function add_header($str)
    {
        $this->header .= "\n" . $str;
    }


    /**
     * Add HTML code to the page footer
     * To be added right befor </body>
     *
     * @param string $str HTML code
     */
    public function add_footer($str)
    {
        $this->footer .= "\n" . $str;
    }


    /**
     * Process template and write to stdOut
     *
     * @param string HTML template
     * @param string Base for absolute paths
     */
    public function _write($templ = '', $base_path = '')
    {
        $output = empty($templ) ? $this->default_template : trim($templ);

        // set default page title
        if (empty($this->pagetitle)) {
            $this->pagetitle = 'Roundcube Mail';
        }

        // replace specialchars in content
        $page_title  = html::quote($this->pagetitle);
        $page_header = '';
        $page_footer = '';

        // include meta tag with charset
        if (!empty($this->charset)) {
            if (!headers_sent()) {
                header('Content-Type: text/html; charset=' . $this->charset);
            }
            $page_header = '<meta http-equiv="content-type"';
            $page_header.= ' content="text/html; charset=';
            $page_header.= $this->charset . '" />'."\n";
        }

        // definition of the code to be placed in the document header and footer
        if (is_array($this->script_files['head'])) {
            foreach ($this->script_files['head'] as $file) {
                $page_header .= html::script($file);
            }
        }

        $head_script = $this->scripts['head_top'] . $this->scripts['head'];
        if (!empty($head_script)) {
            $page_header .= html::script(array(), $head_script);
        }

        if (!empty($this->header)) {
            $page_header .= $this->header;
        }

        // put docready commands into page footer
        if (!empty($this->scripts['docready'])) {
            $this->add_script('$(document).ready(function(){ ' . $this->scripts['docready'] . "\n});", 'foot');
        }

        if (is_array($this->script_files['foot'])) {
            foreach ($this->script_files['foot'] as $file) {
                $page_footer .= html::script($file);
            }
        }

        if (!empty($this->footer)) {
            $page_footer .= $this->footer . "\n";
        }

        if (!empty($this->scripts['foot'])) {
            $page_footer .= html::script(array(), $this->scripts['foot']);
        }

        // find page header
        if ($hpos = stripos($output, '</head>')) {
            $page_header .= "\n";
        }
        else {
            if (!is_numeric($hpos)) {
                $hpos = stripos($output, '<body');
            }
            if (!is_numeric($hpos) && ($hpos = stripos($output, '<html'))) {
                while ($output[$hpos] != '>') {
                    $hpos++;
                }
                $hpos++;
            }
            $page_header = "<head>\n<title>$page_title</title>\n$page_header\n</head>\n";
        }

        // add page hader
        if ($hpos) {
            $output = substr_replace($output, $page_header, $hpos, 0);
        }
        else {
            $output = $page_header . $output;
        }

        // add page footer
        if (($fpos = strripos($output, '</body>')) || ($fpos = strripos($output, '</html>'))) {
            $output = substr_replace($output, $page_footer."\n", $fpos, 0);
        }
        else {
            $output .= "\n".$page_footer;
        }

        // add css files in head, before scripts, for speed up with parallel downloads
        if (!empty($this->css_files) && 
            (($pos = stripos($output, '<script ')) || ($pos = stripos($output, '</head>')))
        ) {
            $css = '';
            foreach ($this->css_files as $file) {
                $css .= html::tag('link', array('rel' => 'stylesheet',
                    'type' => 'text/css', 'href' => $file, 'nl' => true));
            }
            $output = substr_replace($output, $css, $pos, 0);
        }

        $output = $this->parse_with_globals($this->fix_paths($output));

        // trigger hook with final HTML content to be sent
        $hook = $this->app->plugins->exec_hook("send_page", array('content' => $output));
        if (!$hook['abort']) {
            if ($this->charset != RCUBE_CHARSET) {
                echo rcube_charset::convert($hook['content'], RCUBE_CHARSET, $this->charset);
            }
            else {
                echo $hook['content'];
            }
        }
    }


    /**
     * Returns iframe object, registers some related env variables
     *
     * @param array $attrib HTML attributes
     * @param boolean $is_contentframe Register this iframe as the 'contentframe' gui object
     * @return string IFRAME element
     */
    public function frame($attrib, $is_contentframe = false)
    {
        static $idcount = 0;

        if (!$attrib['id']) {
            $attrib['id'] = 'rcmframe' . ++$idcount;
        }

        $attrib['name'] = $attrib['id'];
        $attrib['src'] = $attrib['src'] ? $this->abs_url($attrib['src'], true) : 'program/resources/blank.gif';

        // register as 'contentframe' object
        if ($is_contentframe || $attrib['contentframe']) {
            $this->set_env('contentframe', $attrib['contentframe'] ? $attrib['contentframe'] : $attrib['name']);
            $this->set_env('blankpage', $attrib['src']);
        }

        return html::iframe($attrib);
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
      if ($this->env['extwin']) {
        $hiddenfield = new html_hiddenfield(array('name' => '_extwin', 'value' => '1'));
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

        return rcube_utils::idn_to_utf8($username);
    }


    /**
     * GUI object 'loginform'
     * Returns code for the webmail login form
     *
     * @param array Named parameters
     * @return string HTML code for the gui object
     */
    protected function login_form($attrib)
    {
        $default_host = $this->config->get('default_host');
        $autocomplete = (int) $this->config->get('login_autocomplete');

        $_SESSION['temp'] = true;

        // save original url
        $url = rcube_utils::get_input_value('_url', rcube_utils::INPUT_POST);
        if (empty($url) && !preg_match('/_(task|action)=logout/', $_SERVER['QUERY_STRING']))
            $url = $_SERVER['QUERY_STRING'];

        // Disable autocapitalization on iPad/iPhone (#1488609)
        $attrib['autocapitalize'] = 'off';

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
        else if (is_array($default_host) && ($host = key($default_host)) !== null) {
            $hide_host = true;
            $input_host = new html_hiddenfield(array(
                'name' => '_host', 'id' => 'rcmloginhost', 'value' => is_numeric($host) ? $default_host[$host] : $host) + $attrib);
        }
        else if (empty($default_host)) {
            $input_host = new html_inputfield(array('name' => '_host', 'id' => 'rcmloginhost')
                + $attrib + $host_attrib);
        }

        $form_name  = !empty($attrib['form']) ? $attrib['form'] : 'form';
        $this->add_gui_object('loginform', $form_name);

        // create HTML table with two cols
        $table = new html_table(array('cols' => 2));

        $table->add('title', html::label('rcmloginuser', html::quote($this->app->gettext('username'))));
        $table->add('input', $input_user->show(rcube_utils::get_input_value('_user', rcube_utils::INPUT_GPC)));

        $table->add('title', html::label('rcmloginpwd', html::quote($this->app->gettext('password'))));
        $table->add('input', $input_pass->show());

        // add host selection row
        if (is_object($input_host) && !$hide_host) {
            $table->add('title', html::label('rcmloginhost', html::quote($this->app->gettext('server'))));
            $table->add('input', $input_host->show(rcube_utils::get_input_value('_host', rcube_utils::INPUT_GPC)));
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

        // include script for timezone detection
        $this->include_script('jstz.min.js');

        return $out;
    }


    /**
     * GUI object 'preloader'
     * Loads javascript code for images preloading
     *
     * @param array Named parameters
     * @return void
     */
    protected function preloader($attrib)
    {
        $images = preg_split('/[\s\t\n,]+/', $attrib['images'], -1, PREG_SPLIT_NO_EMPTY);
        $images = array_map(array($this, 'abs_url'), $images);

        if (empty($images) || $this->app->task == 'logout')
            return;

        $this->add_script('var images = ' . self::json_serialize($images) .';
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
    protected function search_form($attrib)
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
                'onsubmit' => self::JS_OBJECT_NAME . ".command('search'); return false",
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
    protected function message_container($attrib)
    {
        if (isset($attrib['id']) === false) {
            $attrib['id'] = 'rcmMessageContainer';
        }

        $this->add_gui_object('message', $attrib['id']);

        return html::div($attrib, '');
    }


    /**
     * GUI object 'charsetselector'
     *
     * @param array Named parameters for the select tag
     * @return string HTML code for the gui object
     */
    public function charset_selector($attrib)
    {
        // pass the following attributes to the form class
        $field_attrib = array('name' => '_charset');
        foreach ($attrib as $attr => $value) {
            if (in_array($attr, array('id', 'name', 'class', 'style', 'size', 'tabindex'))) {
                $field_attrib[$attr] = $value;
            }
        }

        $charsets = array(
            'UTF-8'        => 'UTF-8 ('.$this->app->gettext('unicode').')',
            'US-ASCII'     => 'ASCII ('.$this->app->gettext('english').')',
            'ISO-8859-1'   => 'ISO-8859-1 ('.$this->app->gettext('westerneuropean').')',
            'ISO-8859-2'   => 'ISO-8859-2 ('.$this->app->gettext('easterneuropean').')',
            'ISO-8859-4'   => 'ISO-8859-4 ('.$this->app->gettext('baltic').')',
            'ISO-8859-5'   => 'ISO-8859-5 ('.$this->app->gettext('cyrillic').')',
            'ISO-8859-6'   => 'ISO-8859-6 ('.$this->app->gettext('arabic').')',
            'ISO-8859-7'   => 'ISO-8859-7 ('.$this->app->gettext('greek').')',
            'ISO-8859-8'   => 'ISO-8859-8 ('.$this->app->gettext('hebrew').')',
            'ISO-8859-9'   => 'ISO-8859-9 ('.$this->app->gettext('turkish').')',
            'ISO-8859-10'   => 'ISO-8859-10 ('.$this->app->gettext('nordic').')',
            'ISO-8859-11'   => 'ISO-8859-11 ('.$this->app->gettext('thai').')',
            'ISO-8859-13'   => 'ISO-8859-13 ('.$this->app->gettext('baltic').')',
            'ISO-8859-14'   => 'ISO-8859-14 ('.$this->app->gettext('celtic').')',
            'ISO-8859-15'   => 'ISO-8859-15 ('.$this->app->gettext('westerneuropean').')',
            'ISO-8859-16'   => 'ISO-8859-16 ('.$this->app->gettext('southeasterneuropean').')',
            'WINDOWS-1250' => 'Windows-1250 ('.$this->app->gettext('easterneuropean').')',
            'WINDOWS-1251' => 'Windows-1251 ('.$this->app->gettext('cyrillic').')',
            'WINDOWS-1252' => 'Windows-1252 ('.$this->app->gettext('westerneuropean').')',
            'WINDOWS-1253' => 'Windows-1253 ('.$this->app->gettext('greek').')',
            'WINDOWS-1254' => 'Windows-1254 ('.$this->app->gettext('turkish').')',
            'WINDOWS-1255' => 'Windows-1255 ('.$this->app->gettext('hebrew').')',
            'WINDOWS-1256' => 'Windows-1256 ('.$this->app->gettext('arabic').')',
            'WINDOWS-1257' => 'Windows-1257 ('.$this->app->gettext('baltic').')',
            'WINDOWS-1258' => 'Windows-1258 ('.$this->app->gettext('vietnamese').')',
            'ISO-2022-JP'  => 'ISO-2022-JP ('.$this->app->gettext('japanese').')',
            'ISO-2022-KR'  => 'ISO-2022-KR ('.$this->app->gettext('korean').')',
            'ISO-2022-CN'  => 'ISO-2022-CN ('.$this->app->gettext('chinese').')',
            'EUC-JP'       => 'EUC-JP ('.$this->app->gettext('japanese').')',
            'EUC-KR'       => 'EUC-KR ('.$this->app->gettext('korean').')',
            'EUC-CN'       => 'EUC-CN ('.$this->app->gettext('chinese').')',
            'BIG5'         => 'BIG5 ('.$this->app->gettext('chinese').')',
            'GB2312'       => 'GB2312 ('.$this->app->gettext('chinese').')',
        );

        if (!empty($_POST['_charset'])) {
            $set = $_POST['_charset'];
        }
        else if (!empty($attrib['selected'])) {
            $set = $attrib['selected'];
        }
        else {
            $set = $this->get_charset();
        }

        $set = strtoupper($set);
        if (!isset($charsets[$set])) {
            $charsets[$set] = $set;
        }

        $select = new html_select($field_attrib);
        $select->add(array_values($charsets), array_keys($charsets));

        return $select->show($set);
    }

    /**
     * Include content from config/about.<LANG>.html if available
     */
    protected function about_content($attrib)
    {
        $content = '';
        $filenames = array(
            'about.' . $_SESSION['language'] . '.html',
            'about.' . substr($_SESSION['language'], 0, 2) . '.html',
            'about.html',
        );
        foreach ($filenames as $file) {
            $fn = RCUBE_CONFIG_DIR . $file;
            if (is_readable($fn)) {
                $content = file_get_contents($fn);
                $content = $this->parse_conditions($content);
                $content = $this->parse_xml($content);
                break;
            }
        }

        return $content;
    }

}

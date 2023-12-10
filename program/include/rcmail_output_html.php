<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Class to handle HTML page output using a skin template.             |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to create HTML page output using a skin template
 *
 * @package    Webmail
 * @subpackage View
 */
class rcmail_output_html extends rcmail_output
{
    public $type = 'html';

    protected $message;
    protected $template_name;
    protected $objects      = [];
    protected $js_env       = [];
    protected $js_labels    = [];
    protected $js_commands  = [];
    protected $skin_paths   = [];
    protected $skin_name    = '';
    protected $scripts_path = '';
    protected $script_files = [];
    protected $css_files    = [];
    protected $scripts      = [];
    protected $meta_tags    = [];
    protected $link_tags    = ['shortcut icon' => ''];
    protected $header       = '';
    protected $footer       = '';
    protected $body         = '';
    protected $base_path    = '';
    protected $assets_path;
    protected $assets_dir   = RCUBE_INSTALL_PATH;
    protected $devel_mode   = false;
    protected $default_template = "<html>\n<head><meta name='generator' content='Roundcube'></head>\n<body></body>\n</html>";

    // deprecated names of templates used before 0.5
    protected $deprecated_templates = [
        'contact'      => 'showcontact',
        'contactadd'   => 'addcontact',
        'contactedit'  => 'editcontact',
        'identityedit' => 'editidentity',
        'messageprint' => 'printmessage',
    ];

    // deprecated names of template objects used before 1.4
    protected $deprecated_template_objects = [
        'addressframe'        => 'contentframe',
        'messagecontentframe' => 'contentframe',
        'prefsframe'          => 'contentframe',
        'folderframe'         => 'contentframe',
        'identityframe'       => 'contentframe',
        'responseframe'       => 'contentframe',
        'keyframe'            => 'contentframe',
        'filterframe'         => 'contentframe',
    ];

    /**
     * Constructor
     */
    public function __construct($task = null, $framed = false)
    {
        parent::__construct();

        $this->devel_mode = $this->config->get('devel_mode');

        $this->set_env('task', $task);
        $this->set_env('standard_windows', (bool) $this->config->get('standard_windows'));
        $this->set_env('locale', !empty($_SESSION['language']) ? $_SESSION['language'] : 'en_US');
        $this->set_env('devel_mode', $this->devel_mode);

        // Version number e.g. 1.4.2 will be 10402
        $version = explode('.', preg_replace('/[^0-9.].*/', '', RCMAIL_VERSION));
        $this->set_env('rcversion', $version[0] * 10000 + $version[1] * 100 + ($version[2] ?? 0));

        // add cookie info
        $this->set_env('cookie_domain', ini_get('session.cookie_domain'));
        $this->set_env('cookie_path', ini_get('session.cookie_path'));
        $this->set_env('cookie_secure', filter_var(ini_get('session.cookie_secure'), FILTER_VALIDATE_BOOLEAN));

        // Easy way to change skin via GET argument, for developers
        if ($this->devel_mode && !empty($_GET['skin']) && preg_match('/^[a-z0-9-_]+$/i', $_GET['skin'])) {
            if ($this->check_skin($_GET['skin'])) {
                $this->set_skin($_GET['skin']);
                $this->app->user->save_prefs(['skin' => $_GET['skin']]);
            }
        }

        // load and setup the skin
        $this->set_skin($this->config->get('skin'));
        $this->set_assets_path($this->config->get('assets_path'), $this->config->get('assets_dir'));

        if (!empty($_REQUEST['_extwin'])) {
            $this->set_env('extwin', 1);
        }

        if ($this->framed || $framed) {
            $this->set_env('framed', 1);
        }

        $lic = <<<EOF
/*
        @licstart  The following is the entire license notice for the 
        JavaScript code in this page.

        Copyright (C) The Roundcube Dev Team

        The JavaScript code in this page is free software: you can redistribute
        it and/or modify it under the terms of the GNU General Public License
        as published by the Free Software Foundation, either version 3 of
        the License, or (at your option) any later version.

        The code is distributed WITHOUT ANY WARRANTY; without even the implied
        warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
        See the GNU GPL for more details.

        @licend  The above is the entire license notice
        for the JavaScript code in this page.
*/
EOF;
        // add common javascripts
        $this->add_script($lic, 'head_top');
        $this->add_script('var '.self::JS_OBJECT_NAME.' = new rcube_webmail();', 'head_top');

        // don't wait for page onload. Call init at the bottom of the page (delayed)
        $this->add_script(self::JS_OBJECT_NAME.'.init();', 'docready');

        $this->scripts_path = 'program/js/';
        $this->include_script('jquery.min.js');
        $this->include_script('common.js');
        $this->include_script('app.js');

        // register common UI objects
        $this->add_handlers([
                'loginform'       => [$this, 'login_form'],
                'preloader'       => [$this, 'preloader'],
                'username'        => [$this, 'current_username'],
                'message'         => [$this, 'message_container'],
                'charsetselector' => [$this, 'charset_selector'],
                'aboutcontent'    => [$this, 'about_content'],
        ]);

        // set blankpage (watermark) url
        $blankpage = $this->config->get('blankpage_url', '/watermark.html');
        $this->set_env('blankpage', $blankpage);
    }

    /**
     * Set environment variable
     *
     * @param string $name    Property name
     * @param mixed  $value   Property value
     * @param bool   $addtojs True if this property should be added
     *                        to client environment
     */
    public function set_env($name, $value, $addtojs = true)
    {
        $this->env[$name] = $value;

        if ($addtojs || isset($this->js_env[$name])) {
            $this->js_env[$name] = $value;
        }
    }

    /**
     * Parse and set assets path
     *
     * @param string $path   Assets path URL (relative or absolute)
     * @param string $fs_dir Assets path in filesystem
     */
    public function set_assets_path($path, $fs_dir = null)
    {
        // set absolute path for assets if /index.php/foo/bar url is used
        if (empty($path) && !empty($_SERVER['PATH_INFO'])) {
            $path = preg_replace('/\?_task=[a-z]+/', '', $this->app->url([], true));
        }

        if (empty($path)) {
            return;
        }

        $path = rtrim($path, '/') . '/';

        // handle relative assets path
        if (!preg_match('|^https?://|', $path) && $path[0] != '/') {
            // save the path to search for asset files later
            $this->assets_dir = $path;

            $base = preg_replace('/[?#&].*$/', '', $_SERVER['REQUEST_URI']);
            $base = rtrim($base, '/');

            // remove url token if exists
            if ($len = intval($this->config->get('use_secure_urls'))) {
                $_base  = explode('/', $base);
                $last   = count($_base) - 1;
                $length = $len > 1 ? $len : 16; // as in rcube::get_secure_url_token()

                // we can't use real token here because it
                // does not exists in unauthenticated state,
                // hope this will not produce false-positive matches
                if ($last > -1 && preg_match('/^[a-f0-9]{' . $length . '}$/', $_base[$last])) {
                    $path = '../' . $path;
                }
            }
        }

        // set filesystem path for assets
        if ($fs_dir) {
            if ($fs_dir[0] != '/') {
                $fs_dir = realpath(RCUBE_INSTALL_PATH . $fs_dir);
            }
            // ensure the path ends with a slash
            $this->assets_dir = rtrim($fs_dir, '/') . '/';
        }

        $this->assets_path = $path;
        $this->set_env('assets_path', $path);
    }

    /**
     * Getter for the current page title
     *
     * @param bool $full Prepend title with product/user name
     *
     * @return string The page title
     */
    protected function get_pagetitle($full = true)
    {
        if (!empty($this->pagetitle)) {
            $title = $this->pagetitle;
        }
        else if (isset($this->env['task'])) {
            if ($this->env['task'] == 'login') {
                $title = $this->app->gettext([
                        'name' => 'welcome',
                        'vars' => ['product' => $this->config->get('product_name')]
                ]);
            }
            else {
                $title = ucfirst($this->env['task']);
            }
        }
        else {
            $title = '';
        }

        if ($full && $title) {
            if ($this->devel_mode && !empty($_SESSION['username'])) {
                $title = $_SESSION['username'] . ' :: ' . $title;
            }
            else if ($prod_name = $this->config->get('product_name')) {
                $title = $prod_name . ' :: ' . $title;
            }
        }

        return $title;
    }

    /**
     * Getter for the current skin path property
     */
    public function get_skin_path()
    {
        return $this->skin_paths[0];
    }

    /**
     * Set skin
     *
     * @param string $skin Skin name
     */
    public function set_skin($skin)
    {
        if (!$this->check_skin($skin)) {
            // If the skin does not exist (could be removed or invalid),
            // fallback to the skin set in the system configuration (#7271)
            $skin = $this->config->system_skin;
        }

        $skin_path = 'skins/' . $skin;

        $this->config->set('skin_path', $skin_path);
        $this->base_path = $skin_path;

        // register skin path(s)
        $this->skin_paths = [];
        $this->skins      = [];
        $this->load_skin($skin_path);

        $this->skin_name = $skin;
        $this->set_env('skin', $skin);
    }

    /**
     * Check skin validity/existence
     *
     * @param string $skin Skin name
     *
     * @return bool True if the skin exist and is readable, False otherwise
     */
    public function check_skin($skin)
    {
        // Sanity check to prevent from path traversal vulnerability (#1490620)
        if (!is_string($skin) || strpos($skin, '/') !== false || strpos($skin, "\\") !== false) {
            rcube::raise_error([
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => 'Invalid skin name'
                ], true, false);

            return false;
        }

        $skins_allowed = $this->config->get('skins_allowed');

        if (!empty($skins_allowed) && !in_array($skin, (array) $skins_allowed)) {
            return false;
        }

        $path = RCUBE_INSTALL_PATH . 'skins/';

        return !empty($skin) && is_dir($path . $skin) && is_readable($path . $skin);
    }

    /**
     * Helper method to recursively read skin meta files and register search paths
     */
    private function load_skin($skin_path)
    {
        $this->skin_paths[] = $skin_path;

        // read meta file and check for dependencies
        $meta = @file_get_contents(RCUBE_INSTALL_PATH . $skin_path . '/meta.json');
        $meta = @json_decode($meta, true);

        $meta['path']  = $skin_path;
        $path_elements = explode('/', $skin_path);
        $skin_id       = end($path_elements);

        if (empty($meta['name'])) {
            $meta['name'] = $skin_id;
        }

        $this->skins[$skin_id] = $meta;

        // Keep skin config for ajax requests (#6613)
        $_SESSION['skin_config'] = [];

        if (!empty($meta['extends'])) {
            $path = RCUBE_INSTALL_PATH . 'skins/';
            if (is_dir($path . $meta['extends']) && is_readable($path . $meta['extends'])) {
                $_SESSION['skin_config'] = $this->load_skin('skins/' . $meta['extends']);
            }
        }

        if (!empty($meta['config'])) {
            foreach ($meta['config'] as $key => $value) {
                $this->config->set($key, $value, true);
                $_SESSION['skin_config'][$key] = $value;
            }

            $value = array_merge((array) $this->config->get('dont_override'), array_keys($meta['config']));
            $this->config->set('dont_override', $value, true);
        }

        if (!empty($meta['localization'])) {
            $locdir = $meta['localization'] === true ? 'localization' : $meta['localization'];
            if ($texts = $this->app->read_localization(RCUBE_INSTALL_PATH . $skin_path . '/' . $locdir)) {
                $this->app->load_language($_SESSION['language'], $texts);
            }
        }

        // Use array_merge() here to allow for global default and extended skins
        if (!empty($meta['meta'])) {
            $this->meta_tags = array_merge($this->meta_tags, (array) $meta['meta']);
        }
        if (!empty($meta['links'])) {
            $this->link_tags = array_merge($this->link_tags, (array) $meta['links']);
        }

        $this->set_env('dark_mode_support', (bool) $this->config->get('dark_mode_support'));

        return $_SESSION['skin_config'];
    }

    /**
     * Check if a specific template exists
     *
     * @param string $name Template name
     *
     * @return bool True if template exists, False otherwise
     */
    public function template_exists($name)
    {
        foreach ($this->skin_paths as $skin_path) {
            $filename = RCUBE_INSTALL_PATH . $skin_path . '/templates/' . $name . '.html';
            if (
                (is_file($filename) && is_readable($filename))
                || (!empty($this->deprecated_templates[$name]) && $this->template_exists($this->deprecated_templates[$name]))
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find the given file in the current skin path stack
     *
     * @param string $file       File name/path to resolve (starting with /)
     * @param string &$skin_path Reference to the base path of the matching skin
     * @param string $add_path   Additional path to search in
     * @param bool   $minified   Fallback to a minified version of the file
     *
     * @return string|false Relative path to the requested file or False if not found
     */
    public function get_skin_file($file, &$skin_path = null, $add_path = null, $minified = false)
    {
        $skin_paths = $this->skin_paths;

        if ($add_path) {
            array_unshift($skin_paths, $add_path);
            $skin_paths = array_unique($skin_paths);
        }

        if ($file[0] != '/') {
            $file = '/' . $file;
        }

        if ($skin_path = $this->find_file_path($file, $skin_paths)) {
            return $skin_path . $file;
        }

        if ($minified && preg_match('/(?<!\.min)\.(js|css)$/', $file)) {
            $file = preg_replace('/\.(js|css)$/', '.min.\\1', $file);

            if ($skin_path = $this->find_file_path($file, $skin_paths)) {
                return $skin_path . $file;
            }
        }

        return false;
    }

    /**
     * Find path of the asset file
     */
    protected function find_file_path($file, $skin_paths)
    {
        foreach ($skin_paths as $skin_path) {
            if ($this->assets_dir != RCUBE_INSTALL_PATH) {
                if (realpath($this->assets_dir . $skin_path . $file)) {
                    return $skin_path;
                }
            }

            if (realpath(RCUBE_INSTALL_PATH . $skin_path . $file)) {
                return $skin_path;
            }
        }
    }

    /**
     * Register a GUI object to the client script
     *
     * @param string $obj Object name
     * @param string $id  Object ID
     */
    public function add_gui_object($obj, $id)
    {
        $this->add_script(self::JS_OBJECT_NAME.".gui_object('$obj', '$id');");
    }

    /**
     * Call a client method
     *
     * @param string $cmd    Method to call
     * @param mixed ...$args Method arguments
     */
    public function command($cmd, ...$args)
    {
        if (strpos($cmd, 'plugin.') !== false) {
            $this->js_commands[] = ['triggerEvent', $cmd, $args[0]];
        }
        else {
            array_unshift($args, $cmd);

            $this->js_commands[] = $args;
        }
    }

    /**
     * Add a localized label to the client environment
     *
     * @param mixed ...$args Labels (an array of strings, or many string arguments)
     */
    public function add_label(...$args)
    {
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }

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
     * @param bool    $override Override last set message
     * @param int     $timeout  Message display time in seconds
     *
     * @uses self::command()
     */
    public function show_message($message, $type = 'notice', $vars = null, $override = true, $timeout = 0)
    {
        if ($override || !$this->message) {
            if ($this->app->text_exists($message)) {
                if (!empty($vars)) {
                    $vars = array_map(['rcube','Q'], $vars);
                }

                $msgtext = $this->app->gettext(['name' => $message, 'vars' => $vars]);
            }
            else {
                $msgtext = $message;
            }

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
        $framed = $this->framed;
        $task   = $this->env['task'] ?? '';
        $env    = $all ? null : array_intersect_key($this->env, ['extwin' => 1, 'framed' => 1]);

        // keep jQuery-UI files
        $css_files = $script_files = [];

        foreach ($this->css_files as $file) {
            if (strpos($file, 'plugins/jqueryui') === 0) {
                $css_files[] = $file;
            }
        }

        foreach ($this->script_files as $position => $files) {
            foreach ($files as $file) {
                if (strpos($file, 'plugins/jqueryui') === 0) {
                    $script_files[$position][] = $file;
                }
            }
        }

        parent::reset();

        // let some env variables survive
        $this->env          = $this->js_env = $env;
        $this->framed       = $framed || !empty($this->env['framed']);
        $this->js_labels    = [];
        $this->js_commands  = [];
        $this->scripts      = [];
        $this->header       = '';
        $this->footer       = '';
        $this->body         = '';
        $this->css_files    = [];
        $this->script_files = [];

        // load defaults
        if (!$all) {
            $this->__construct();
        }

        // Note: we merge jQuery-UI scripts after jQuery...
        $this->css_files    = array_merge($this->css_files, $css_files);
        $this->script_files = array_merge_recursive($this->script_files, $script_files);

        $this->set_env('orig_task', $task);
    }

    /**
     * Redirect to a certain url
     *
     * @param mixed $p      Either a string with the action or url parameters as key-value pairs
     * @param int   $delay  Delay in seconds
     * @param bool  $secure Redirect to secure location (see rcmail::url())
     */
    public function redirect($p = [], $delay = 1, $secure = false)
    {
        if (!empty($this->env['extwin']) && !(is_string($p) && preg_match('#^https?://#', $p))) {
            if (!is_array($p)) {
                $p = ['_action' => $p];
            }

            $p['_extwin'] = 1;
        }

        $location = $this->app->url($p, false, false, $secure);
        $this->header('Location: ' . $location);
        exit;
    }

    /**
     * Send the request output to the client.
     * This will either parse a skin template.
     *
     * @param string $templ Template name
     * @param bool   $exit  True if script should terminate (default)
     */
    public function send($templ = null, $exit = true)
    {
        if ($templ != 'iframe') {
            // prevent from endless loops
            if ($exit != 'recur' && $this->app->plugins->is_processing('render_page')) {
                rcube::raise_error([
                        'code'    => 505,
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => 'Recursion alert: ignoring output->send()'
                    ], true, false
                );

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
        if (!empty($this->script_files)) {
            $this->set_env('request_token', $this->app->get_request_token());
        }

        // Fix assets path on blankpage
        if (!empty($this->js_env['blankpage'])) {
            $this->js_env['blankpage'] = $this->asset_url($this->js_env['blankpage'], true);
        }

        $commands = $this->get_js_commands($framed);

        // if all js commands go to parent window we can ignore all
        // script files and skip rcube_webmail initialization (#1489792)
        // but not on error pages where skins may need jQuery, etc.
        if ($framed && empty($this->js_env['server_error'])) {
            $this->scripts      = [];
            $this->script_files = [];
            $this->header       = '';
            $this->footer       = '';
        }

        // write all javascript commands
        if (!empty($commands)) {
            $this->add_script($commands, 'head_top');
        }

        $this->page_headers();

        // call super method
        $this->_write($template);
    }

    /**
     * Send common page headers
     * For now it only (re)sets X-Frame-Options when needed
     */
    public function page_headers()
    {
        if (headers_sent()) {
            return;
        }

        // allow (legal) iframe content to be loaded
        $framed = $this->framed || !empty($this->env['framed']);
        if ($framed && ($xopt = $this->app->config->get('x_frame_options', 'sameorigin'))) {
            if (strtolower($xopt) === 'deny') {
                $this->header('X-Frame-Options: sameorigin', true);
            }
        }
    }

    /**
     * Parse a specific skin template and deliver to stdout (or return)
     *
     * @param string $name  Template name
     * @param bool   $exit  Exit script
     * @param bool   $write Don't write to stdout, return parsed content instead
     *
     * @link http://php.net/manual/en/function.exit.php
     */
    function parse($name = 'main', $exit = true, $write = true)
    {
        $plugin   = false;
        $realname = $name;
        $skin_dir = '';
        $plugin_skin_paths = [];

        $this->template_name = $realname;

        $temp = explode('.', $name, 2);
        if (count($temp) > 1) {
            $plugin   = $temp[0];
            $name     = $temp[1];
            $skin_dir = $plugin . '/skins/' . $this->config->get('skin');

            // apply skin search escalation list to plugin directory
            foreach ($this->skin_paths as $skin_path) {
                // skin folder in plugin dir
                $plugin_skin_paths[] = $this->app->plugins->url . $plugin . '/' . $skin_path;
                // plugin folder in skin dir
                $plugin_skin_paths[] = $skin_path . '/plugins/' . $plugin;
            }

            // prepend plugin skin paths to search list
            $this->skin_paths = array_merge($plugin_skin_paths, $this->skin_paths);
        }

        // find skin template
        $path = false;
        foreach ($this->skin_paths as $skin_path) {
            // when requesting a plugin template ignore global skin path(s)
            if ($plugin && strpos($skin_path, $this->app->plugins->url) === false) {
                continue;
            }

            $path = RCUBE_INSTALL_PATH . "$skin_path/templates/$name.html";

            // fallback to deprecated template names
            if (!is_readable($path) && !empty($this->deprecated_templates[$realname])) {
                $dname = $this->deprecated_templates[$realname];
                $path  = RCUBE_INSTALL_PATH . "$skin_path/templates/$dname.html";

                if (is_readable($path)) {
                    rcube::raise_error([
                            'code' => 502, 'file' => __FILE__, 'line' => __LINE__,
                            'message' => "Using deprecated template '$dname' in $skin_path/templates. Please rename to '$realname'"
                        ], true, false
                    );
                }
            }

            if (is_readable($path)) {
                $this->config->set('skin_path', $skin_path);
                // set base_path to core skin directory (not plugin's skin)
                $this->base_path = preg_replace('!plugins/\w+/!', '', $skin_path);
                $skin_dir        = preg_replace('!^plugins/!', '', $skin_path);
                break;
            }
            else {
                $path = false;
            }
        }

        // read template file
        if (!$path || ($templ = @file_get_contents($path)) === false) {
            rcube::raise_error([
                    'code' => 404,
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => 'Error loading template for '.$realname
                ], true, $write);

            $this->skin_paths = array_slice($this->skin_paths, count($plugin_skin_paths));
            return false;
        }

        // replace all path references to plugins/... with the configured plugins dir
        // and /this/ to the current plugin skin directory
        if ($plugin) {
            $templ = preg_replace(
                ['/\bplugins\//', '/(["\']?)\/this\//'],
                [$this->app->plugins->url, '\\1' . $this->app->plugins->url . $skin_dir . '/'],
                $templ
            );
        }

        // parse for special tags
        $output = $this->parse_conditions($templ);
        $output = $this->parse_xml($output);

        // trigger generic hook where plugins can put additional content to the page
        $hook = $this->app->plugins->exec_hook("render_page", [
                'template' => $realname,
                'content'  => $output,
                'write'    => $write
        ]);

        // save some memory
        $output = $hook['content'];
        unset($hook['content']);

        // remove plugin skin paths from current context
        $this->skin_paths = array_slice($this->skin_paths, count($plugin_skin_paths));

        if (!$write) {
            return $this->postrender($output);
        }

        $this->write(trim($output));

        if ($exit) {
            exit;
        }
    }

    /**
     * Return executable javascript code for all registered commands
     */
    protected function get_js_commands(&$framed = null)
    {
        $out             = '';
        $parent_commands = 0;
        $parent_prefix   = '';
        $top_commands    = [];

        // these should be always on top,
        // e.g. hide_message() below depends on env.framed
        if (!$this->framed && !empty($this->js_env)) {
            $top_commands[] = ['set_env', $this->js_env];
        }
        if (!empty($this->js_labels)) {
            $top_commands[] = ['add_label', $this->js_labels];
        }

        // unlock interface after iframe load
        $unlock = isset($_REQUEST['_unlock']) ? preg_replace('/[^a-z0-9]/i', '', $_REQUEST['_unlock']) : 0;
        if ($this->framed) {
            $top_commands[] = ['iframe_loaded', $unlock];
        }
        else if ($unlock) {
            $top_commands[] = ['hide_message', $unlock];
        }

        $commands = array_merge($top_commands, $this->js_commands);

        foreach ($commands as $i => $args) {
            $method = array_shift($args);
            $parent = $this->framed || preg_match('/^parent\./', $method);

            foreach ($args as $i => $arg) {
                $args[$i] = self::json_serialize($arg, $this->devel_mode);
            }

            if ($parent) {
                $parent_commands++;
                $method        = preg_replace('/^parent\./', '', $method);
                $parent_prefix = 'if (window.parent && parent.' . self::JS_OBJECT_NAME . ') parent.';
                $method        = $parent_prefix . self::JS_OBJECT_NAME . '.' . $method;
            }
            else {
                $method = self::JS_OBJECT_NAME . '.' . $method;
            }

            $out .= sprintf("%s(%s);\n", $method, implode(',', $args));
        }

        $framed = $parent_prefix && $parent_commands == count($commands);

        // make the output more compact if all commands go to parent window
        if ($framed) {
            $out = "if (window.parent && parent." . self::JS_OBJECT_NAME . ") {\n"
                . str_replace($parent_prefix, "\tparent.", $out)
                . "}\n";
        }

        return $out;
    }

    /**
     * Make URLs starting with a slash point to skin directory
     *
     * @param string $str         Input string
     * @param bool   $search_path True if URL should be resolved using the current skin path stack
     *
     * @return string URL
     */
    public function abs_url($str, $search_path = false)
    {
        if (isset($str[0]) && $str[0] == '/') {
            if ($search_path && ($file_url = $this->get_skin_file($str))) {
                return $file_url;
            }

            return $this->base_path . $str;
        }

        return $str;
    }

    /**
     * Show error page and terminate script execution
     *
     * @param int    $code    Error code
     * @param string $message Error message
     */
    public function raise_error($code, $message)
    {
        $args = [
            'code'    => $code,
            'message' => $message,
        ];

        $page = new rcmail_action_utils_error;
        $page->run($args);
    }

    /**
     * Modify path by adding URL prefix if configured
     *
     * @param string $path    Asset path
     * @param bool   $abs_url Pass to self::abs_url() first
     *
     * @return string Asset path
     */
    public function asset_url($path, $abs_url = false)
    {
        // iframe content can't be in a different domain
        // @TODO: check if assets are on a different domain

        if ($abs_url) {
            $path = $this->abs_url($path, true);
        }

        if (!$this->assets_path || in_array($path[0], ['?', '/', '.']) || strpos($path, '://')) {
            return $path;
        }

        return $this->assets_path . $path;
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

        return preg_replace_callback('/\$(__[a-z0-9_\-]+)/', [$this, 'globals_callback'], $input);
    }

    /**
     * Callback function for preg_replace_callback() in parse_with_globals()
     */
    protected function globals_callback($matches)
    {
        return $GLOBALS[$matches[1]];
    }

    /**
     * Correct absolute paths in images and other tags (add cache busters)
     */
    protected function fix_paths($output)
    {
        $regexp = '!(src|href|background|data-src-[a-z]+)=(["\']?)([a-z0-9/_.-]+)(["\'\s>])!i';

        return preg_replace_callback($regexp, [$this, 'file_callback'], $output);
    }

    /**
     * Callback function for preg_replace_callback in fix_paths()
     *
     * @return string Parsed string
     */
    protected function file_callback($matches)
    {
        $file = $matches[3];
        $file = preg_replace('!^/this/!', '/', $file);

        // correct absolute paths
        if ($file[0] == '/') {
            $this->get_skin_file($file, $skin_path, $this->base_path);
            $file = ($skin_path ?: $this->base_path) . $file;
        }

        // add file modification timestamp
        if (preg_match('/\.(js|css|less|ico|png|svg|jpeg)$/', $file)) {
            $file = $this->file_mod($file);
        }

        return $matches[1] . '=' . $matches[2] . $file . $matches[4];
    }

    /**
     * Correct paths of asset files according to assets_path
     */
    protected function fix_assets_paths($output)
    {
        $regexp = '!(src|href|background)=(["\']?)([a-z0-9/_.?=-]+)(["\'\s>])!i';

        return preg_replace_callback($regexp, [$this, 'assets_callback'], $output);
    }

    /**
     * Callback function for preg_replace_callback in fix_assets_paths()
     *
     * @return string Parsed string
     */
    protected function assets_callback($matches)
    {
        $file = $this->asset_url($matches[3]);

        return $matches[1] . '=' . $matches[2] . $file . $matches[4];
    }

    /**
     * Modify file by adding mtime indicator
     */
    protected function file_mod($file)
    {
        $fs  = false;
        $ext = substr($file, strrpos($file, '.') + 1);

        // use minified file if exists (not in development mode)
        if (!$this->devel_mode && !preg_match('/\.min\.' . $ext . '$/', $file)) {
            $minified_file = substr($file, 0, strlen($ext) * -1) . 'min.' . $ext;
            if ($fs = @filemtime($this->assets_dir . $minified_file)) {
                return $minified_file . '?s=' . $fs;
            }
        }

        if ($fs = @filemtime($this->assets_dir . $file)) {
            $file .= '?s=' . $fs;
        }

        return $file;
    }

    /**
     * Public wrapper to dip into template parsing.
     *
     * @param string $input Template content
     *
     * @return string
     * @uses   rcmail_output_html::parse_xml()
     * @since  0.1-rc1
     */
    public function just_parse($input)
    {
        $input = $this->parse_conditions($input);
        $input = $this->parse_xml($input);
        $input = $this->postrender($input);

        return $input;
    }

    /**
     * Parse for conditional tags
     */
    protected function parse_conditions($input)
    {
        $regexp1 = '/<roundcube:if\s+([^>]+)>/is';
        $regexp2 = '/<roundcube:(if|elseif|else|endif)\s*([^>]*)>/is';

        $pos = 0;

        // Find IF tags and process them
        while ($pos < strlen($input) && preg_match($regexp1, $input, $conditions, PREG_OFFSET_CAPTURE, $pos)) {
            $pos = $start = $conditions[0][1];

            // Process the 'condition' attribute
            $attrib  = html::parse_attrib_string($conditions[1][0]);
            $condmet = isset($attrib['condition']) && $this->check_condition($attrib['condition']);

            // Define start/end position of the content to pass into the output
            $content_start = $condmet ? $pos + strlen($conditions[0][0]) : null;
            $content_end   = null;

            $level = 0;
            $endif = null;
            $n = $pos + 1;

            // Process the code until the closing tag (for the processed IF tag)
            while (preg_match($regexp2, $input, $matches, PREG_OFFSET_CAPTURE, $n)) {
                $tag_start = $matches[0][1];
                $tag_end   = $tag_start + strlen($matches[0][0]);
                $tag_name  = strtolower($matches[1][0]);

                switch ($tag_name) {
                case 'if':
                    $level++;
                    break;

                case 'endif':
                    if (!$level--) {
                        $endif = $tag_end;
                        if ($content_end === null) {
                            $content_end = $tag_start;
                        }
                        break 2;
                    }
                    break;

                case 'elseif':
                    if (!$level) {
                        if ($condmet) {
                            if ($content_end === null) {
                                $content_end = $tag_start;
                            }
                        }
                        else {
                            // Process the 'condition' attribute
                            $attrib  = html::parse_attrib_string($matches[2][0]);
                            $condmet = isset($attrib['condition']) && $this->check_condition($attrib['condition']);

                            if ($condmet) {
                                $content_start = $tag_end;
                            }
                        }
                    }
                    break;

                case 'else':
                    if (!$level) {
                        if ($condmet) {
                            if ($content_end === null) {
                                $content_end = $tag_start;
                            }
                        }
                        else {
                            $content_start = $tag_end;
                        }
                    }
                    break;
                }

                $n = $tag_end;
            }

            // No ending tag found
            if ($endif === null) {
                $pos = strlen($input);
                if ($content_end === null) {
                    $content_end = $pos;
                }
            }

            if ($content_start === null) {
                $content = '';
            }
            else {
                $content = substr($input, $content_start, $content_end - $content_start);
            }

            // Replace the whole IF statement with the output content
            $input = substr_replace($input, $content, $start, max($endif, $content_end, $pos) - $start);
            $pos   = $start;
        }

        return $input;
    }

    /**
     * Determines if a given condition is met
     *
     * @param string $condition Condition statement
     *
     * @return bool True if condition is met, False if not
     * @todo Extend this to allow real conditions, not just "set"
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

        if (!empty($attrib['method']) && strtolower($attrib['method']) == 'post') {
            $hidden = new html_hiddenfield(['name' => '_token', 'value' => $this->app->get_request_token()]);
            $out .= "\n" . $hidden->show();
        }

        return $out;
    }

    /**
     * Parse & evaluate a given expression and return its result.
     *
     * @param string $expression Expression statement
     *
     * @return mixed Expression result
     */
    protected function eval_expression($expression)
    {
        $expression = preg_replace(
            [
                '/session:([a-z0-9_]+)/i',
                '/config:([a-z0-9_]+)(:([a-z0-9_]+))?/i',
                '/env:([a-z0-9_]+)/i',
                '/request:([a-z0-9_]+)/i',
                '/cookie:([a-z0-9_]+)/i',
                '/browser:([a-z0-9_]+)/i',
                '/template:name/i',
            ],
            [
                "(\$_SESSION['\\1'] ?? null)",
                "\$this->app->config->get('\\1',rcube_utils::get_boolean('\\3'))",
                "(\$this->env['\\1'] ?? null)",
                "rcube_utils::get_input_value('\\1', rcube_utils::INPUT_GPC)",
                "(\$_COOKIE['\\1'] ?? null)",
                "(\$this->browser->{'\\1'} ?? null)",
                "'{$this->template_name}'",
            ],
            $expression
        );

        // Note: We used create_function() before but it's deprecated in PHP 7.2
        //       and really it was just a wrapper on eval().
        return eval("return ($expression);");
    }

    /**
     * Parse variable strings
     *
     * @param string $type Variable type (env, config etc)
     * @param string $name Variable name
     *
     * @return mixed Variable value
     */
    protected function parse_variable($type, $name)
    {
        $value = '';

        switch ($type) {
            case 'env':
                $value = $this->env[$name] ?? null;
                break;
            case 'config':
                $value = $this->config->get($name);
                if (is_array($value) && !empty($value[$_SESSION['storage_host']])) {
                    $value = $value[$_SESSION['storage_host']];
                }
                break;
            case 'request':
                $value = rcube_utils::get_input_value($name, rcube_utils::INPUT_GPC);
                break;
            case 'session':
                $value = $_SESSION[$name] ?? '';
                break;
            case 'cookie':
                $value = htmlspecialchars($_COOKIE[$name], ENT_COMPAT | ENT_HTML401, RCUBE_CHARSET);
                break;
            case 'browser':
                $value = $this->browser->{$name} ?? '';
                break;
        }

        return $value;
    }

    /**
     * Search for special tags in input and replace them
     * with the appropriate content
     *
     * @param string $input Input string to parse
     *
     * @return string Altered input string
     * @todo   Use DOM-parser to traverse template HTML
     * @todo   Maybe a cache.
     */
    protected function parse_xml($input)
    {
        $regexp = '/<roundcube:([-_a-z]+)\s+((?:[^>]|\\\\>)+)(?<!\\\\)>/Ui';

        return preg_replace_callback($regexp, [$this, 'xml_command'], $input);
    }

    /**
     * Callback function for parsing an xml command tag
     * and turn it into real html content
     *
     * @param array $matches Matches array of preg_replace_callback
     *
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

        // localize title and summary attributes
        if ($command != 'button' && !empty($attrib['title']) && $this->app->text_exists($attrib['title'])) {
            $attrib['title'] = $this->app->gettext($attrib['title']);
        }
        if ($command != 'button' && !empty($attrib['summary']) && $this->app->text_exists($attrib['summary'])) {
            $attrib['summary'] = $this->app->gettext($attrib['summary']);
        }

        // execute command
        switch ($command) {
            // return a button
            case 'button':
                if (!empty($attrib['name']) || !empty($attrib['command'])) {
                    return $this->button($attrib);
                }
                break;

            // frame
            case 'frame':
                return $this->frame($attrib);
                break;

            // show a label
            case 'label':
                if (!empty($attrib['expression'])) {
                    $attrib['name'] = $this->eval_expression($attrib['expression']);
                }

                if (!empty($attrib['name']) || !empty($attrib['command'])) {
                    $vars = $attrib + ['product' => $this->config->get('product_name')];
                    unset($vars['name'], $vars['command']);

                    $label   = $this->app->gettext($attrib + ['vars' => $vars]);
                    $quoting = null;

                    if (!empty($attrib['quoting'])) {
                        $quoting = strtolower($attrib['quoting']);
                    }
                    else if (isset($attrib['html'])) {
                        $quoting = rcube_utils::get_boolean((string) $attrib['html']) ? 'no' : '';
                    }

                    // 'noshow' can be used in skins to define new labels
                    if (!empty($attrib['noshow'])) {
                        return '';
                    }

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

            case 'add_label':
                $this->add_label($attrib['name']);
                break;

            // include a file
            case 'include':
                if (!empty($attrib['condition']) && !$this->check_condition($attrib['condition'])) {
                    break;
                }

                if ($attrib['file'][0] != '/') {
                    $attrib['file'] = '/templates/' . $attrib['file'];
                }

                $old_base_path   = $this->base_path;
                $include         = '';
                $attr_skin_path = !empty($attrib['skinpath']) ? $attrib['skinpath'] : null;

                if (!empty($attrib['skin_path'])) {
                    $attr_skin_path = $attrib['skin_path'];
                }

                if ($path = $this->get_skin_file($attrib['file'], $skin_path, $attr_skin_path)) {
                    // set base_path to core skin directory (not plugin's skin)
                    $this->base_path = preg_replace('!plugins/\w+/!', '', $skin_path);
                    $path = realpath(RCUBE_INSTALL_PATH . $path);
                }

                if (is_readable($path)) {
                    $allow_php = $this->config->get('skin_include_php');
                    $include   = $allow_php ? $this->include_php($path) : file_get_contents($path);
                    $include   = $this->parse_conditions($include);
                    $include   = $this->parse_xml($include);
                    $include   = $this->fix_paths($include);
                }

                $this->base_path = $old_base_path;

                return $include;

            case 'plugin.include':
                $hook = $this->app->plugins->exec_hook("template_plugin_include", $attrib + ['content' => '']);
                return $hook['content'];

            // define a container block
            case 'container':
                if (!empty($attrib['name']) && !empty($attrib['id'])) {
                    $this->command('gui_container', $attrib['name'], $attrib['id']);
                    // let plugins insert some content here
                    $hook = $this->app->plugins->exec_hook("template_container", $attrib + ['content' => '']);
                    return $hook['content'];
                }
                break;

            // return code for a specific application object
            case 'object':
                $object  = strtolower($attrib['name']);
                $content = '';
                $handler = null;

                // correct deprecated object names
                if (!empty($this->deprecated_template_objects[$object])) {
                    $object = $this->deprecated_template_objects[$object];
                }

                if (!empty($this->object_handlers[$object])) {
                    $handler = $this->object_handlers[$object];
                }

                // execute object handler function
                if (is_callable($handler)) {
                    $this->prepare_object_attribs($attrib);

                    // We assume that objects with src attribute are internal (in most
                    // cases this is a watermark frame). We need this to make sure assets_path
                    // is added to the internal assets paths
                    $external = empty($attrib['src']);
                    $content  = call_user_func($handler, $attrib);
                }
                else if ($object == 'doctype') {
                    $content = html::doctype($attrib['value']);
                }
                else if ($object == 'logo') {
                    $attrib += ['alt' => $this->xml_command(['', 'object', 'name="productname"'])];

                    // 'type' attribute added in 1.4 was renamed 'logo-type' in 1.5
                    // check both for backwards compatibility
                    $logo_type  = !empty($attrib['logo-type']) ? $attrib['logo-type'] : null;
                    $logo_match = !empty($attrib['logo-match']) ? $attrib['logo-match'] : null;
                    if (!empty($attrib['type']) && empty($logo_type)) {
                        $logo_type = $attrib['type'];
                    }

                    if (($template_logo = $this->get_template_logo($logo_type, $logo_match)) !== null) {
                        $attrib['src'] = $template_logo;
                    }

                    if (($link = $this->get_template_logo('link')) !== null) {
                        $attrib['onclick'] = "location.href='$link';";
                        $attrib['style'] = 'cursor:pointer;';
                    }

                    $additional_logos = [];
                    $logo_types       = (array) $this->config->get('additional_logo_types');

                    foreach ($logo_types as $type) {
                        if (($template_logo = $this->get_template_logo($type)) !== null) {
                            $additional_logos[$type] = $this->abs_url($template_logo);
                        }
                        else if (!empty($attrib['data-src-' . $type])) {
                            $additional_logos[$type] = $this->abs_url($attrib['data-src-' . $type]);
                        }
                    }

                    if (!empty($additional_logos)) {
                        $this->set_env('additional_logos', $additional_logos);
                    }

                    if (!empty($attrib['src'])) {
                        $content = html::img($attrib);
                    }
                }
                else if ($object == 'productname') {
                    $name    = $this->config->get('product_name', 'Roundcube Webmail');
                    $content = html::quote($name);
                }
                else if ($object == 'version') {
                    $ver = (string) RCMAIL_VERSION;
                    if (is_file(RCUBE_INSTALL_PATH . '.svn/entries')) {
                        if (preg_match('/Revision:\s(\d+)/', (string) @shell_exec('svn info'), $regs))
                          $ver .= ' [SVN r'.$regs[1].']';
                    }
                    else if (is_file(RCUBE_INSTALL_PATH . '.git/index')) {
                        if (preg_match('/Date:\s+([^\n]+)/', (string) @shell_exec('git log -1'), $regs)) {
                            if ($date = date('Ymd.Hi', strtotime($regs[1]))) {
                                $ver .= ' [GIT '.$date.']';
                            }
                        }
                    }
                    $content = html::quote($ver);
                }
                else if ($object == 'steptitle') {
                    $content = html::quote($this->get_pagetitle(false));
                }
                else if ($object == 'pagetitle') {
                    // Deprecated, <title> will be added automatically
                    $content = html::quote($this->get_pagetitle());
                }
                else if ($object == 'contentframe') {
                    if (empty($attrib['id'])) {
                        $attrib['id'] = 'rcm' . $this->env['task'] . 'frame';
                    }

                    // parse variables
                    if (preg_match('/^(config|env):([a-z0-9_]+)$/i', $attrib['src'], $matches)) {
                        $attrib['src'] = $this->parse_variable($matches[1], $matches[2]);
                    }

                    $content = $this->frame($attrib, true);
                }
                else if ($object == 'meta' || $object == 'links') {
                    if ($object == 'meta') {
                        $source = 'meta_tags';
                        $tag    = 'meta';
                        $key    = 'name';
                        $param  = 'content';
                    }
                    else {
                        $source = 'link_tags';
                        $tag    = 'link';
                        $key    = 'rel';
                        $param  = 'href';
                    }

                    foreach ($this->$source as $name => $vars) {
                        // $vars can be in many forms:
                        // - string
                        // - ['key' => 'val']
                        // - [string, string]
                        // - [[], string]
                        // - [['key' => 'val'], ['key' => 'val']]
                        // normalise this for processing by checking for string array keys
                        $vars = is_array($vars) ? (count(array_filter(array_keys($vars), 'is_string')) > 0 ? [$vars] : $vars) : [$vars];

                        foreach ($vars as $args) {
                            // skip unset headers e.g. when extending a skin and removing a header defined in the parent
                            if ($args === false) {
                                continue;
                            }

                            $args = is_array($args) ? $args : [$param => $args];

                            // special handling for favicon
                            if ($object == 'links' && $name == 'shortcut icon' && empty($args[$param])) {
                                if ($href = $this->get_template_logo('favicon')) {
                                    $args[$param] = $href;
                                }
                                else if ($href = $this->config->get('favicon', '/images/favicon.ico')) {
                                    $args[$param] = $href;
                                }
                            }

                            $content .= html::tag($tag, [$key => $name, 'nl' => true] + $args);
                        }
                    }
                }

                // exec plugin hooks for this template object
                $hook = $this->app->plugins->exec_hook("template_object_$object", $attrib + ['content' => (string) $content]);

                if (strlen($hook['content']) && !empty($external)) {
                    $object_id                 = uniqid('TEMPLOBJECT:', true);
                    $this->objects[$object_id] = $hook['content'];
                    $hook['content']           = $object_id;
                }

                return $hook['content'];

            // return <link> element
            case 'link':
                if ($attrib['condition'] && !$this->check_condition($attrib['condition'])) {
                    break;
                }

                unset($attrib['condition']);

                return html::tag('link', $attrib);


            // return code for a specified eval expression
            case 'exp':
                return html::quote($this->eval_expression($attrib['expression']));

            // return variable
            case 'var':
                $var = explode(':', $attrib['name']);
                $value = $this->parse_variable($var[0], $var[1]);

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                return html::quote($value);

            case 'form':
                return $this->form_tag($attrib);
        }

        return '';
    }

    /**
     * Prepares template object attributes
     *
     * @param array &$attribs Attributes
     */
    protected function prepare_object_attribs(&$attribs)
    {
        foreach ($attribs as $key => &$value) {
            if (strpos($key, 'data-label-') === 0) {
                // Localize data-label-* attributes
                $value = $this->app->gettext($value);
            }
            elseif ($key[0] == ':') {
                // Evaluate attributes with expressions and remove special character from attribute name
                $attribs[substr($key, 1)] = $this->eval_expression($value);
                unset($attribs[$key]);
            }
        }
    }

    /**
     * Include a specific file and return it's contents
     *
     * @param string $file File path
     *
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
     * Put objects' content back into template output
     */
    protected function postrender($output)
    {
        // insert objects' contents
        foreach ($this->objects as $key => $val) {
            $output = str_replace($key, (string) $val, $output, $count);
            if ($count) {
                $this->objects[$key] = null;
            }
        }

        // make sure all <form> tags have a valid request token
        $output = preg_replace_callback('/<form\s+([^>]+)>/Ui', [$this, 'alter_form_tag'], $output);

        return $output;
    }

    /**
     * Create and register a button
     *
     * @param array $attrib Named button attributes
     *
     * @return string HTML button
     * @todo   Remove all inline JS calls and use jQuery instead.
     * @todo   Remove all sprintf()'s - they are pretty, but also slow.
     */
    public function button($attrib)
    {
        static $s_button_count = 100;

        // these commands can be called directly via url
        $a_static_commands = ['compose', 'list', 'preferences', 'folders', 'identities'];

        if (empty($attrib['command']) && empty($attrib['name']) && empty($attrib['href'])) {
            return '';
        }

        $command = !empty($attrib['command']) ? $attrib['command'] : null;
        $action  = $command ?: (!empty($attrib['name']) ? $attrib['name'] : null);

        if (!empty($attrib['task'])) {
            $command = $attrib['task'] . '.' . $command;
            $element = $attrib['task'] . '.' . $action;
        }
        else {
            $element = (!empty($this->env['task']) ? $this->env['task'] . '.' : '') . $action;
        }

        $disabled_actions = (array) $this->config->get('disabled_actions');

        // remove buttons for disabled actions
        if (in_array($element, $disabled_actions) || in_array($action, $disabled_actions)) {
            return '';
        }

        // try to find out the button type
        if (!empty($attrib['type'])) {
            $attrib['type'] = strtolower($attrib['type']);
            if (strpos($attrib['type'], '-menuitem')) {
                $attrib['type'] = substr($attrib['type'], 0, -9);
                $menuitem = true;
            }
        }
        else if (!empty($attrib['image']) || !empty($attrib['imagepas']) || !empty($attrib['imageact'])) {
            $attrib['type'] = 'image';
        }
        else {
            $attrib['type'] = 'button';
        }

        if (empty($attrib['image'])) {
            if (!empty($attrib['imagepas'])) {
                $attrib['image'] = $attrib['imagepas'];
            }
            else if (!empty($attrib['imageact'])) {
                $attrib['image'] = $attrib['imageact'];
            }
        }

        if (empty($attrib['id'])) {
            // ensure auto generated IDs are unique between main window and content frame
            // Elastic skin duplicates buttons between the two on smaller screens (#7618)
            $prefix       = ($this->framed || !empty($this->env['framed'])) ? 'frm' : '';
            $attrib['id'] = sprintf('rcmbtn%s%d', $prefix, $s_button_count++);
        }

        // get localized text for labels and titles
        $domain = !empty($attrib['domain']) ? $attrib['domain'] : null;
        if (!empty($attrib['title'])) {
            $attrib['title'] = html::quote($this->app->gettext($attrib['title'], $domain));
        }
        if (!empty($attrib['label'])) {
            $attrib['label'] = html::quote($this->app->gettext($attrib['label'], $domain));
        }
        if (!empty($attrib['alt'])) {
            $attrib['alt'] = html::quote($this->app->gettext($attrib['alt'], $domain));
        }

        // set accessibility attributes
        if (empty($attrib['role'])) {
            $attrib['role'] = 'button';
        }

        if (!empty($attrib['class']) && !empty($attrib['classact']) || !empty($attrib['imagepas']) && !empty($attrib['imageact'])) {
            if (array_key_exists('tabindex', $attrib)) {
                $attrib['data-tabindex'] = $attrib['tabindex'];
            }
            $attrib['tabindex']      = '-1';  // disable button by default
            $attrib['aria-disabled'] = 'true';
        }

        // set title to alt attribute for IE browsers
        if ($this->browser->ie && empty($attrib['title']) && !empty($attrib['alt'])) {
            $attrib['title'] = $attrib['alt'];
        }

        // add empty alt attribute for XHTML compatibility
        if (!isset($attrib['alt'])) {
            $attrib['alt'] = '';
        }

        // register button in the system
        if (!empty($attrib['command'])) {
            $this->add_script(sprintf(
                "%s.register_button('%s', '%s', '%s', '%s', '%s', '%s');",
                self::JS_OBJECT_NAME,
                $command,
                $attrib['id'],
                $attrib['type'],
                !empty($attrib['imageact']) ? $this->abs_url($attrib['imageact']) : (!empty($attrib['classact']) ? $attrib['classact'] : ''),
                !empty($attrib['imagesel']) ? $this->abs_url($attrib['imagesel']) : (!empty($attrib['classsel']) ? $attrib['classsel'] : ''),
                !empty($attrib['imageover']) ? $this->abs_url($attrib['imageover']) : ''
            ));

            // make valid href to specific buttons
            if (in_array($attrib['command'], rcmail::$main_tasks)) {
                $attrib['href']    = $this->app->url(['task' => $attrib['command']]);
                $attrib['onclick'] = sprintf("return %s.command('switch-task','%s',this,event)", self::JS_OBJECT_NAME, $attrib['command']);
            }
            else if (!empty($attrib['task']) && in_array($attrib['task'], rcmail::$main_tasks)) {
                $attrib['href'] = $this->app->url(['action' => $attrib['command'], 'task' => $attrib['task']]);
            }
            else if (in_array($attrib['command'], $a_static_commands)) {
                $attrib['href'] = $this->app->url(['action' => $attrib['command']]);
            }
            else if (($attrib['command'] == 'permaurl' || $attrib['command'] == 'extwin') && !empty($this->env['permaurl'])) {
              $attrib['href'] = $this->env['permaurl'];
            }
        }

        // overwrite attributes
        if (empty($attrib['href'])) {
            $attrib['href'] = '#';
        }

        if (!empty($attrib['task'])) {
            if (!empty($attrib['classact'])) {
                $attrib['class'] = $attrib['classact'];
            }
        }
        else if ($command && empty($attrib['onclick'])) {
            $attrib['onclick'] = sprintf(
                "return %s.command('%s','%s',this,event)",
                self::JS_OBJECT_NAME,
                $command,
                !empty($attrib['prop']) ? $attrib['prop'] : ''
            );
        }

        $out         = '';
        $btn_content = null;
        $link_attrib = [];

        // generate image tag
        if ($attrib['type'] == 'image') {
            $attrib_str = html::attrib_string(
                $attrib,
                [
                    'style', 'class', 'id', 'width', 'height', 'border', 'hspace',
                    'vspace', 'align', 'alt', 'tabindex', 'title'
                ]
            );
            $btn_content = sprintf('<img src="%s"%s />', $this->abs_url($attrib['image']), $attrib_str);
            if (!empty($attrib['label'])) {
                $btn_content .= ' '.$attrib['label'];
            }
            $link_attrib = ['href', 'onclick', 'onmouseover', 'onmouseout', 'onmousedown', 'onmouseup', 'target'];
        }
        else if ($attrib['type'] == 'link') {
            $btn_content = $attrib['content'] ?? (!empty($attrib['label']) ? $attrib['label'] : $attrib['command']);
            $link_attrib = array_merge(html::$common_attrib, ['href', 'onclick', 'tabindex', 'target', 'rel']);
            if (!empty($attrib['innerclass'])) {
                $btn_content = html::span($attrib['innerclass'], $btn_content);
            }
        }
        else if ($attrib['type'] == 'input') {
            $attrib['type'] = 'button';

            if (!empty($attrib['label'])) {
                $attrib['value'] = $attrib['label'];
            }
            if (!empty($attrib['command'])) {
                $attrib['disabled'] = 'disabled';
            }

            $out = html::tag('input', $attrib, null, ['type', 'value', 'onclick', 'id', 'class', 'style', 'tabindex', 'disabled']);
        }
        else {
            if (!empty($attrib['label'])) {
                $attrib['value'] = $attrib['label'];
            }
            if (!empty($attrib['command'])) {
                $attrib['disabled'] = 'disabled';
            }

            $content = $attrib['content'] ?? $attrib['label'];
            $out = html::tag('button', $attrib, $content, ['type', 'value', 'onclick', 'id', 'class', 'style', 'tabindex', 'disabled']);
        }

        // generate html code for button
        if ($btn_content) {
            $attrib_str = html::attrib_string($attrib, $link_attrib);
            $out = sprintf('<a%s>%s</a>', $attrib_str, $btn_content);
        }

        if (!empty($attrib['wrapper'])) {
            $out = html::tag($attrib['wrapper'], null, $out);
        }

        if (!empty($menuitem)) {
            $class = !empty($attrib['menuitem-class']) ? ' class="' . $attrib['menuitem-class'] . '"' : '';
            $out   = '<li role="menuitem"' . $class . '>' . $out . '</li>';
        }

        return $out;
    }

    /**
     * Link an external script file
     *
     * @param string $file     File URL
     * @param string $position Target position [head|head_bottom|foot]
     */
    public function include_script($file, $position = 'head', $add_path = true)
    {
        if ($add_path && !preg_match('|^https?://|i', $file) && $file[0] != '/') {
            $file = $this->file_mod($this->scripts_path . $file);
        }

        if (!isset($this->script_files[$position]) || !is_array($this->script_files[$position])) {
            $this->script_files[$position] = [];
        }

        if (!in_array($file, $this->script_files[$position])) {
            $this->script_files[$position][] = $file;
        }
    }

    /**
     * Add inline javascript code
     *
     * @param string $script   JS code snippet
     * @param string $position Target position [head|head_top|foot|docready]
     */
    public function add_script($script, $position = 'head')
    {
        if (!isset($this->scripts[$position])) {
            $this->scripts[$position] = rtrim($script);
        }
        else {
            $this->scripts[$position] .= "\n" . rtrim($script);
        }
    }

    /**
     * Link an external css file
     *
     * @param string $file File URL
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
     * To be added right before </body>
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
     * @param string $output HTML output
     */
    protected function _write($output = '')
    {
        $output = trim($output);

        if (empty($output)) {
            $output   = html::doctype('html5') . "\n" . $this->default_template;
            $is_empty = true;
        }

        $merge_script_files = function($output, $script) {
            return $output . html::script($script);
        };

        $merge_scripts = function($output, $script) {
            return $output . html::script([], $script);
        };

        // put docready commands into page footer
        if (!empty($this->scripts['docready'])) {
            $this->add_script("\$(function() {\n" . $this->scripts['docready'] . "\n});", 'foot');
        }

        $page_header = '';
        $page_footer = '';
        $meta        = '';

        // declare page language
        if (!empty($_SESSION['language'])) {
            $lang   = substr($_SESSION['language'], 0, 2);
            $output = preg_replace('/<html/', '<html lang="' . html::quote($lang) . '"', $output, 1);

            if (!headers_sent()) {
                $this->header('Content-Language: ' . $lang);
            }
        }

        // include meta tag with charset
        if (!empty($this->charset)) {
            if (!headers_sent()) {
                $this->header('Content-Type: text/html; charset=' . $this->charset);
            }

            $meta .= html::tag('meta', [
                    'http-equiv' => 'content-type',
                    'content'    => "text/html; charset={$this->charset}",
                    'nl'         => true
            ]);
        }

        // include page title (after charset specification)
        $meta .= '<title>' . html::quote($this->get_pagetitle()) . "</title>\n";

        $output = preg_replace('/(<head[^>]*>)\n*/i', "\\1\n{$meta}", $output, 1, $count);
        if (!$count) {
            $page_header .= $meta;
        }

        // include scripts into header/footer
        if (!empty($this->script_files['head'])) {
            $page_header .= array_reduce((array) $this->script_files['head'], $merge_script_files);
        }

        $head  = $this->scripts['head_top'] ?? '';
        $head .= $this->scripts['head'] ?? '';

        $page_header .= array_reduce((array) $head, $merge_scripts);
        $page_header .= $this->header . "\n";

        if (!empty($this->script_files['head_bottom'])) {
            $page_header .= array_reduce((array) $this->script_files['head_bottom'], $merge_script_files);
        }

        if (!empty($this->script_files['foot'])) {
            $page_footer .= array_reduce((array) $this->script_files['foot'], $merge_script_files);
        }

        $page_footer .= $this->footer . "\n";

        if (!empty($this->scripts['foot'])) {
            $page_footer .= array_reduce((array) $this->scripts['foot'], $merge_scripts);
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
            $page_header = "<head>\n$page_header\n</head>\n";
        }

        // add page header
        if ($hpos) {
            $output = substr_replace($output, $page_header, $hpos, 0);
        }
        else {
            $output = $page_header . $output;
        }

        // add page footer
        if (($fpos = strripos($output, '</body>')) || ($fpos = strripos($output, '</html>'))) {
            // for Elastic: put footer content before "footer scripts"
            while (($npos = strripos($output, "\n", -strlen($output) + $fpos - 1))
                && $npos != $fpos
                && ($chunk = substr($output, $npos, $fpos - $npos)) !== ''
                && (trim($chunk) === '' || preg_match('/\s*<script[^>]+><\/script>\s*/', $chunk))
            ) {
                $fpos = $npos;
            }

            $output = substr_replace($output, $page_footer."\n", $fpos, 0);
        }
        else {
            $output .= "\n".$page_footer;
        }

        // add css files in head, before scripts, for speed up with parallel downloads
        if (!empty($this->css_files) && empty($is_empty)
            && (($pos = stripos($output, '<script ')) || ($pos = stripos($output, '</head>')))
        ) {
            $css = '';
            foreach ($this->css_files as $file) {
                $is_less = substr_compare($file, '.less', -5, 5, true) === 0;
                $css    .= html::tag('link', [
                        'rel'  => $is_less ? 'stylesheet/less' : 'stylesheet',
                        'type' => 'text/css',
                        'href' => $file,
                        'nl'   => true,
                ]);
            }
            $output = substr_replace($output, $css, $pos, 0);
        }

        $output = $this->parse_with_globals($this->fix_paths($output));

        if ($this->assets_path) {
            $output = $this->fix_assets_paths($output);
        }

        $output = $this->postrender($output);

        // trigger hook with final HTML content to be sent
        $hook = $this->app->plugins->exec_hook("send_page", ['content' => $output]);
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
     * @param array $attrib          HTML attributes
     * @param bool  $is_contentframe Register this iframe as the 'contentframe' gui object
     *
     * @return string IFRAME element
     */
    public function frame($attrib, $is_contentframe = false)
    {
        static $idcount = 0;

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmframe' . ++$idcount;
        }

        $attrib['name'] = $attrib['id'];
        $attrib['src']  = !empty($attrib['src']) ? $this->abs_url($attrib['src'], true) : 'javascript:false;';

        // register as 'contentframe' object
        if ($is_contentframe || !empty($attrib['contentframe'])) {
            $this->set_env('contentframe', !empty($attrib['contentframe']) ? $attrib['contentframe'] : $attrib['name']);
        }

        return html::iframe($attrib);
    }


    /*  ************* common functions delivering gui objects **************  */

    /**
     * Create a form tag with the necessary hidden fields
     *
     * @param array  $attrib  Named tag parameters
     * @param string $content HTML content of the form
     *
     * @return string HTML code for the form
     */
    public function form_tag($attrib, $content = null)
    {
        $hidden = '';

        if (!empty($this->env['extwin'])) {
            $hiddenfield = new html_hiddenfield(['name' => '_extwin', 'value' => '1']);
            $hidden = $hiddenfield->show();
        }
        else if ($this->framed || !empty($this->env['framed'])) {
            $hiddenfield = new html_hiddenfield(['name' => '_framed', 'value' => '1']);
            $hidden = $hiddenfield->show();
        }

        if (!$content) {
            $attrib['noclose'] = true;
        }

        return html::tag('form',
            $attrib + ['action' => $this->app->comm_path, 'method' => 'get'],
            $hidden . $content,
            ['id', 'class', 'style', 'name', 'method', 'action', 'enctype', 'onsubmit']
        );
    }

    /**
     * Build a form tag with a unique request token
     *
     * @param array  $attrib  Named tag parameters including 'action' and 'task' values
     *                        which will be put into hidden fields
     * @param string $content Form content
     *
     * @return string HTML code for the form
     */
    public function request_form($attrib, $content = '')
    {
        $hidden = new html_hiddenfield();

        if (!empty($attrib['task'])) {
            $hidden->add(['name' => '_task', 'value' => $attrib['task']]);
        }

        if (!empty($attrib['action'])) {
            $hidden->add(['name' => '_action', 'value' => $attrib['action']]);
        }

        // we already have a <form> tag
        if (!empty($attrib['form'])) {
            if ($this->framed || !empty($this->env['framed'])) {
                $hidden->add(['name' => '_framed', 'value' => '1']);
            }

            return $hidden->show() . $content;
        }

        unset($attrib['task'], $attrib['request']);
        $attrib['action'] = './';

        return $this->form_tag($attrib, $hidden->show() . $content);
    }

    /**
     * GUI object 'username'
     * Showing IMAP username of the current session
     *
     * @param array $attrib Named tag parameters (currently not used)
     *
     * @return string HTML code for the gui object
     */
    public function current_username($attrib)
    {
        static $username;

        // already fetched
        if (!empty($username)) {
            return $username;
        }

        // Current username is an e-mail address
        if (isset($_SESSION['username']) && strpos($_SESSION['username'], '@')) {
            $username = $_SESSION['username'];
        }
        // get e-mail address from default identity
        else if ($sql_arr = $this->app->user->get_identity()) {
            $username = $sql_arr['email'];
        }
        else {
            $username = $this->app->user->get_username();
        }

        $username = rcube_utils::idn_to_utf8($username);

        return html::quote($username);
    }

    /**
     * GUI object 'loginform'
     * Returns code for the webmail login form
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML code for the gui object
     */
    protected function login_form($attrib)
    {
        $default_host     = $this->config->get('imap_host');
        $autocomplete     = (int) $this->config->get('login_autocomplete');
        $username_filter  = $this->config->get('login_username_filter');
        $_SESSION['temp'] = true;

        // save original url
        $url = rcube_utils::get_input_string('_url', rcube_utils::INPUT_POST);
        if (
            empty($url)
            && !empty($_SERVER['QUERY_STRING'])
            && !preg_match('/_(task|action)=logout/', $_SERVER['QUERY_STRING'])
        ) {
            $url = $_SERVER['QUERY_STRING'];
        }

        // Disable autocapitalization on iPad/iPhone (#1488609)
        $attrib['autocapitalize'] = 'off';

        $form_name = !empty($attrib['form']) ? $attrib['form'] : 'form';

        // set autocomplete attribute
        $user_attrib = $autocomplete > 0 ? [] : ['autocomplete' => 'off'];
        $host_attrib = $autocomplete > 0 ? [] : ['autocomplete' => 'off'];
        $pass_attrib = $autocomplete > 1 ? [] : ['autocomplete' => 'off'];

        if ($username_filter && strtolower($username_filter) == 'email') {
            $user_attrib['type'] = 'email';
        }

        $input_task   = new html_hiddenfield(['name' => '_task', 'value' => 'login']);
        $input_action = new html_hiddenfield(['name' => '_action', 'value' => 'login']);
        $input_tzone  = new html_hiddenfield(['name' => '_timezone', 'id' => 'rcmlogintz', 'value' => '_default_']);
        $input_url    = new html_hiddenfield(['name' => '_url', 'id' => 'rcmloginurl', 'value' => $url]);
        $input_user   = new html_inputfield(['name' => '_user', 'id' => 'rcmloginuser', 'required' => 'required']
            + $attrib + $user_attrib);
        $input_pass   = new html_passwordfield(['name' => '_pass', 'id' => 'rcmloginpwd', 'required' => 'required']
            + $attrib + $pass_attrib);
        $input_host   = null;

        $form_content = [
            'hidden' => [
                'task'   => $input_task->show(),
                'action' => $input_action->show(),
                'tzone'  => $input_tzone->show(),
                'url'    => $input_url->show(),
            ],
            'inputs' => [
                'user' => [
                    'title'   => html::label('rcmloginuser', html::quote($this->app->gettext('username'))),
                    'content' => $input_user->show(rcube_utils::get_input_string('_user', rcube_utils::INPUT_GPC))
                ],
                'password' => [
                    'title'   => html::label('rcmloginpwd', html::quote($this->app->gettext('password'))),
                    'content' => $input_pass->show()
                ],
            ],
            'buttons' => []
        ];

        if (is_array($default_host) && count($default_host) > 1) {
            $input_host = new html_select(['name' => '_host', 'id' => 'rcmloginhost', 'class' => 'custom-select']);

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
            $val = is_numeric($host) ? $default_host[$host] : $host;
            $input_host = new html_hiddenfield(['name' => '_host', 'id' => 'rcmloginhost', 'value' => $val] + $attrib);

            $form_content['hidden']['host'] = $input_host->show();
            $input_host = null;
        }
        else if (empty($default_host)) {
            $input_host = new html_inputfield(['name' => '_host', 'id' => 'rcmloginhost', 'class' => 'form-control']
                + $attrib + $host_attrib);
        }

        // add host selection row
        if (is_object($input_host)) {
            $form_content['inputs']['host'] = [
                'title'   => html::label('rcmloginhost', html::quote($this->app->gettext('server'))),
                'content' => $input_host->show(rcube_utils::get_input_string('_host', rcube_utils::INPUT_GPC))
            ];
        }

        if (rcube_utils::get_boolean($attrib['submit'])) {
            $button_attr = ['type' => 'submit', 'id' => 'rcmloginsubmit', 'class' => 'button mainaction submit'];
            $button      = html::tag('button', $button_attr, $this->app->gettext('login'));

            $form_content['buttons']['submit'] = ['outterclass' => 'formbuttons', 'content' => $button];
        }

        // add oauth login button
        if ($this->config->get('oauth_auth_uri') && $this->config->get('oauth_provider')) {
            // hide login form fields when `oauth_login_redirect` is configured
            if ($this->config->get('oauth_login_redirect')) {
                $form_content['hidden']  = [];
                $form_content['inputs']  = [];
                $form_content['buttons'] = [];
            }

            $link_attr = [
                'href'  => $this->app->url(['action' => 'oauth']),
                'id'    => 'rcmloginoauth',
                'class' => 'button oauth ' . $this->config->get('oauth_provider')
            ];

            $provider = $this->config->get('oauth_provider_name', 'OAuth');
            $button   = html::a($link_attr, $this->app->gettext(['name' => 'oauthlogin', 'vars' => ['provider' => $provider]]));

            $form_content['buttons']['oauthlogin'] = ['outterclass' => 'oauthlogin', 'content' => $button];
        }

        $data = $this->app->plugins->exec_hook('loginform_content', $form_content);

        $this->add_gui_object('loginform', $form_name);

        // output login form contents
        $out = implode('', $data['hidden']);

        if (count($data['inputs']) > 0) {
            // create HTML table with two cols
            $table = new html_table(['cols' => 2]);

            foreach ($data['inputs'] as $input) {
                if (isset($input['title'])) {
                    $table->add('title', $input['title']);
                    $table->add('input', $input['content']);
                }
                else {
                    $table->add(['colspan' => 2, 'class' => 'input'], $input['content']);
                }
            }

            $out .= $table->show();
        }

        foreach ($data['buttons'] as $button) {
            $out .= html::p($button['outterclass'], $button['content']);
        }

        // surround html output with a form tag
        if (empty($attrib['form'])) {
            $out = $this->form_tag(['name' => $form_name, 'method' => 'post'], $out);
        }

        // include script for timezone detection
        $this->include_script('jstz.min.js');

        return $out;
    }

    /**
     * GUI object 'preloader'
     * Loads javascript code for images preloading
     *
     * @param array $attrib Named parameters
     * @return void
     */
    protected function preloader($attrib)
    {
        $images = preg_split('/[\s\t\n,]+/', $attrib['images'], -1, PREG_SPLIT_NO_EMPTY);
        $images = array_map([$this, 'abs_url'], $images);
        $images = array_map([$this, 'asset_url'], $images);

        if (empty($images) || (isset($_REQUEST['_task']) && $_REQUEST['_task'] == 'logout')) {
            return;
        }

        $this->add_script('var images = ' . self::json_serialize($images, $this->devel_mode) .';
            for (var i=0; i<images.length; i++) {
                img = new Image();
                img.src = images[i];
            }', 'docready');
    }

    /**
     * GUI object 'searchform'
     * Returns code for search function
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML code for the gui object
     */
    public function search_form($attrib)
    {
        // add some labels to client
        $this->add_label('searching');

        $attrib['name']  = '_q';
        $attrib['class'] = trim((!empty($attrib['class']) ? $attrib['class'] : '') . ' no-bs');

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmqsearchbox';
        }
        if (isset($attrib['type']) && $attrib['type'] == 'search' && !$this->browser->khtml) {
            unset($attrib['type'], $attrib['results']);
        }
        if (empty($attrib['placeholder'])) {
            $attrib['placeholder'] = $this->app->gettext('searchplaceholder');
        }

        $label   = html::label(['for' => $attrib['id'], 'class' => 'voice'], rcube::Q($this->app->gettext('arialabelsearchterms')));
        $input_q = new html_inputfield($attrib);
        $out     = $label . $input_q->show();
        $name    = 'qsearchbox';

        // Support for multiple searchforms on the same page
        if (isset($attrib['gui-object']) && $attrib['gui-object'] !== false && $attrib['gui-object'] !== 'false') {
            $name = $attrib['gui-object'];
        }

        $this->add_gui_object($name, $attrib['id']);

        // add form tag around text field
        if (empty($attrib['form']) && empty($attrib['no-form'])) {
            $out = $this->form_tag([
                    'name'     => !empty($attrib['form-name']) ? $attrib['form-name'] : 'rcmqsearchform',
                    'onsubmit' => sprintf(
                        "%s.command('%s'); return false",
                        self::JS_OBJECT_NAME,
                        !empty($attrib['command']) ? $attrib['command'] : 'search'
                    ),
                    // 'style'    => "display:inline"
                ], $out);
        }

        if (!empty($attrib['wrapper'])) {
            $options_button = '';

            $ariatag = !empty($attrib['ariatag']) ? $attrib['ariatag'] : 'h2';
            $domain  = !empty($attrib['label-domain']) ? $attrib['label-domain'] : null;
            $options = !empty($attrib['options']) ? $attrib['options'] : null;

            $header_label = $this->app->gettext('arialabel' . $attrib['label'], $domain);
            $header_attrs = [
                'id'    => 'aria-label-' . $attrib['label'],
                'class' => 'voice'
            ];

            $header = html::tag($ariatag, $header_attrs, rcube::Q($header_label));

            if (!empty($attrib['options'])) {
                $options_button = $this->button([
                        'type'       => 'link',
                        'href'       => '#search-filter',
                        'class'      => 'button options',
                        'label'      => 'options',
                        'title'      => 'options',
                        'tabindex'   => '0',
                        'innerclass' => 'inner',
                        'data-target' => $options
                ]);
            }

            $search_button = $this->button([
                    'type'       => 'link',
                    'href'       => '#search',
                    'class'      => 'button search',
                    'label'      => $attrib['buttontitle'],
                    'title'      => $attrib['buttontitle'],
                    'tabindex'   => '0',
                    'innerclass' => 'inner',
            ]);

            $reset_button = $this->button([
                    'type'       => 'link',
                    'command'    => !empty($attrib['reset-command']) ? $attrib['reset-command'] : 'reset-search',
                    'class'      => 'button reset',
                    'label'      => 'resetsearch',
                    'title'      => 'resetsearch',
                    'tabindex'   => '0',
                    'innerclass' => 'inner',
            ]);

            $out = html::div([
                    'role'            => 'search',
                    'aria-labelledby' => !empty($attrib['label']) ? 'aria-label-' . $attrib['label'] : null,
                    'class'           => $attrib['wrapper'],
                ],
                "$header$out\n$reset_button\n$options_button\n$search_button"
            );
        }

        return $out;
    }

    /**
     * Builder for GUI object 'message'
     *
     * @param array $attrib Named tag parameters
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
     * @param array $attrib Named parameters for the select tag
     *
     * @return string HTML code for the gui object
     */
    public function charset_selector($attrib)
    {
        // pass the following attributes to the form class
        $field_attrib = ['name' => '_charset'];
        foreach ($attrib as $attr => $value) {
            if (in_array($attr, ['id', 'name', 'class', 'style', 'size', 'tabindex'])) {
                $field_attrib[$attr] = $value;
            }
        }

        $charsets = [
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
            'ISO-8859-10'  => 'ISO-8859-10 ('.$this->app->gettext('nordic').')',
            'ISO-8859-11'  => 'ISO-8859-11 ('.$this->app->gettext('thai').')',
            'ISO-8859-13'  => 'ISO-8859-13 ('.$this->app->gettext('baltic').')',
            'ISO-8859-14'  => 'ISO-8859-14 ('.$this->app->gettext('celtic').')',
            'ISO-8859-15'  => 'ISO-8859-15 ('.$this->app->gettext('westerneuropean').')',
            'ISO-8859-16'  => 'ISO-8859-16 ('.$this->app->gettext('southeasterneuropean').')',
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
            'KOI8-R'       => 'KOI8-R ('.$this->app->gettext('cyrillic').')',
        ];

        if ($post = rcube_utils::get_input_string('_charset', rcube_utils::INPUT_POST)) {
            $set = $post;
        }
        else if (!empty($attrib['selected'])) {
            $set = $attrib['selected'];
        }
        else {
            $set = $this->get_charset();
        }

        $set = strtoupper($set);
        if (!isset($charsets[$set]) && preg_match('/^[A-Z0-9-]+$/', $set)) {
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
        $filenames = [
            'about.' . $_SESSION['language'] . '.html',
            'about.' . substr($_SESSION['language'], 0, 2) . '.html',
            'about.html',
        ];

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

    /**
     * Get logo URL for current template based on skin_logo config option
     *
     * @param string $type   Type of the logo to check for (e.g. 'print' or 'small')
     *                       default is null (no special type)
     * @param string $match  (optional) 'all' = type, template or wildcard, 'template' = type or template
     *                       Note: when type is specified matches are limited to type only unless $match is defined
     *
     * @return string image URL
     */
    protected function get_template_logo($type = null, $match = null)
    {
        $template_logo = null;

        if ($logo = $this->config->get('skin_logo')) {
            if (is_array($logo)) {
                $template_names = [
                    $this->skin_name . ':' . $this->template_name . '[' . $type . ']',
                    $this->skin_name . ':' . $this->template_name,
                    $this->skin_name . ':*[' . $type . ']',
                    $this->skin_name . ':[' . $type . ']',
                    $this->skin_name . ':*',
                    '*:' . $this->template_name . '[' . $type . ']',
                    '*:' . $this->template_name,
                    '*:*[' . $type . ']',
                    '*:[' . $type . ']',
                    $this->template_name . '[' . $type . ']',
                    $this->template_name,
                    '*[' . $type . ']',
                    '[' . $type . ']',
                    '*',
                ];

                if (empty($type)) {
                    // If no type provided then remove those options from the list
                    $template_names = preg_grep("/\]$/", $template_names, PREG_GREP_INVERT);
                }
                elseif ($match === null) {
                    // Type specified with no special matching requirements so remove all none type specific options from the list
                    $template_names = preg_grep("/\]$/", $template_names);
                }

                if ($match == 'template') {
                    // Match only specific type or template name
                    $template_names = preg_grep("/\*$/", $template_names, PREG_GREP_INVERT);
                }

                foreach ($template_names as $key) {
                    if (isset($logo[$key])) {
                        $template_logo = $logo[$key];
                        break;
                    }
                }
            }
            else if ($type != 'link') {
                $template_logo = $logo;
            }
        }

        return $template_logo;
    }
}

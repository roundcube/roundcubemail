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
 |   Plugins repository                                                  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

// location where plugins are loaded from
if (!defined('RCUBE_PLUGINS_DIR')) {
    define('RCUBE_PLUGINS_DIR', RCUBE_INSTALL_PATH . 'plugins/');
}

/**
 * The plugin loader and global API
 *
 * @package    Framework
 * @subpackage PluginAPI
 */
class rcube_plugin_api
{
    static protected $instance;

    /** @var string */
    public $dir;
    /** @var string */
    public $url = 'plugins/';
    /** @var string */
    public $task = '';
    /** @var bool */
    public $initialized = false;

    public $output;
    public $handlers              = [];
    public $allowed_prefs         = [];
    public $allowed_session_prefs = [];
    public $active_plugins        = [];

    protected $plugins           = [];
    protected $plugins_initialized = [];
    protected $tasks             = [];
    protected $actions           = [];
    protected $actionmap         = [];
    protected $objectsmap        = [];
    protected $template_contents = [];
    protected $exec_stack        = [];
    protected $deprecated_hooks  = [];


    /**
     * This implements the 'singleton' design pattern
     *
     * @return rcube_plugin_api The one and only instance if this class
     */
    static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new rcube_plugin_api();
        }

        return self::$instance;
    }

    /**
     * Private constructor
     */
    protected function __construct()
    {
        $this->dir = slashify(RCUBE_PLUGINS_DIR);
    }

    /**
     * Initialize plugin engine
     *
     * This has to be done after rcmail::load_gui() or rcmail::json_init()
     * was called because plugins need to have access to rcmail->output
     *
     * @param rcube  $app  Instance of the rcube base class
     * @param string $task Current application task (used for conditional plugin loading)
     */
    public function init($app, $task = '')
    {
        $this->task   = $task;
        $this->output = $app->output;

        // register an internal hook
        $this->register_hook('template_container', [$this, 'template_container_hook']);
        // maybe also register a shutdown function which triggers
        // shutdown functions of all plugin objects

        foreach ($this->plugins as $plugin) {
            // ... task, request type and framed mode
            if (empty($this->plugins_initialized[$plugin->ID]) && !$this->filter($plugin)) {
                $plugin->init();
                $this->plugins_initialized[$plugin->ID] = $plugin;
            }
        }

        // we have finished initializing all plugins
        $this->initialized = true;
    }

    /**
     * Load and init all enabled plugins
     *
     * This has to be done after rcmail::load_gui() or rcmail::json_init()
     * was called because plugins need to have access to rcmail->output
     *
     * @param array $plugins_enabled  List of configured plugins to load
     * @param array $plugins_required List of plugins required by the application
     */
    public function load_plugins($plugins_enabled, $plugins_required = [])
    {
        foreach ($plugins_enabled as $plugin_name) {
            $this->load_plugin($plugin_name);
        }

        // check existence of all required core plugins
        foreach ($plugins_required as $plugin_name) {
            $loaded = false;
            foreach ($this->plugins as $plugin) {
                if ($plugin instanceof $plugin_name) {
                    $loaded = true;
                    break;
                }
            }

            // load required core plugin if no derivate was found
            if (!$loaded) {
                $loaded = $this->load_plugin($plugin_name);
            }

            // trigger fatal error if still not loaded
            if (!$loaded) {
                rcube::raise_error([
                        'code' => 520, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Required plugin $plugin_name was not loaded"
                    ],
                    true, true
                );
            }
        }
    }

    /**
     * Load the specified plugin
     *
     * @param string $plugin_name Plugin name
     * @param bool   $force        Force loading of the plugin even if it doesn't match the filter
     * @param bool   $require      Require loading of the plugin, error if it doesn't exist
     *
     * @return bool True on success, false if not loaded or failure
     */
    public function load_plugin($plugin_name, $force = false, $require = true)
    {
        static $plugins_dir;

        if (!$plugins_dir) {
            $dir         = dir($this->dir);
            $plugins_dir = unslashify($dir->path);
        }

        // Validate the plugin name to prevent from path traversal
        if (preg_match('/[^a-zA-Z0-9_-]/', $plugin_name)) {
            rcube::raise_error([
                    'code' => 520, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Invalid plugin name: $plugin_name"
                ],
                true, false
            );

            return false;
        }

        // plugin already loaded?
        if (!isset($this->plugins[$plugin_name])) {
            $fn = "$plugins_dir/$plugin_name/$plugin_name.php";

            if (!is_readable($fn)) {
                if ($require) {
                    rcube::raise_error([
                            'code' => 520, 'file' => __FILE__, 'line' => __LINE__,
                            'message' => "Failed to load plugin file $fn"
                        ],
                        true, false
                    );
                }

                return false;
            }

            if (!class_exists($plugin_name, false)) {
                include $fn;
            }

            // instantiate class if exists
            if (!class_exists($plugin_name, false)) {
                rcube::raise_error([
                        'code' => 520, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "No plugin class $plugin_name found in $fn"
                    ],
                    true, false
                );

                return false;
            }

            $plugin = new $plugin_name($this);
            $this->active_plugins[] = $plugin_name;

            // check inheritance...
            if (is_subclass_of($plugin, 'rcube_plugin')) {
                // call onload method on plugin if it exists.
                // this is useful if you want to be called early in the boot process
                if (method_exists($plugin, 'onload')) {
                    $plugin->onload();
                }

                if (!empty($plugin->allowed_prefs)) {
                    $this->allowed_prefs = array_merge($this->allowed_prefs, $plugin->allowed_prefs);
                }

                $this->plugins[$plugin_name] = $plugin;
            }
        }

        if (!empty($this->plugins[$plugin_name])) {
            $plugin = $this->plugins[$plugin_name];

            // init a plugin only if $force is set or if we're called after initialization
            if (
                ($force || $this->initialized)
                && empty($this->plugins_initialized[$plugin_name])
                && ($force || !$this->filter($plugin))
            ) {
                $plugin->init();
                $this->plugins_initialized[$plugin_name] = $plugin;
            }
        }

        return true;
    }

    /**
     * Check if we should prevent this plugin from initializing
     *
     * @param rcube_plugin $plugin Plugin object
     *
     * @return bool
     */
    private function filter($plugin)
    {
        return ($plugin->noajax  && !(is_object($this->output) && $this->output->type == 'html'))
             || ($plugin->task && !preg_match('/^('.$plugin->task.')$/i', $this->task))
             || ($plugin->noframe && !empty($_REQUEST['_framed']));
    }

    /**
     * Get information about a specific plugin.
     * This is either provided by a plugin's info() method or extracted from a package.xml or a composer.json file
     *
     * @param string $plugin_name Plugin name
     *
     * @return array Meta information about a plugin or False if plugin was not found
     */
    public function get_info($plugin_name)
    {
        static $composer_lock, $license_uris = [
            'Apache' => 'https://www.apache.org/licenses/LICENSE-2.0.html',
            'Apache-2' => 'https://www.apache.org/licenses/LICENSE-2.0.html',
            'Apache-1' => 'https://www.apache.org/licenses/LICENSE-1.0',
            'Apache-1.1' => 'https://www.apache.org/licenses/LICENSE-1.1',
            'GPL' => 'https://www.gnu.org/licenses/gpl.html',
            'GPL-2.0' => 'https://www.gnu.org/licenses/gpl-2.0.html',
            'GPL-2.0+' => 'https://www.gnu.org/licenses/gpl.html',
            'GPL-3.0' => 'https://www.gnu.org/licenses/gpl-3.0.html',
            'GPL-3.0+' => 'https://www.gnu.org/licenses/gpl.html',
            'AGPL-3.0' => 'https://www.gnu.org/licenses/agpl.html',
            'AGPL-3.0+' => 'https://www.gnu.org/licenses/agpl.html',
            'LGPL' => 'https://www.gnu.org/licenses/lgpl.html',
            'LGPL-2.0' => 'https://www.gnu.org/licenses/lgpl-2.0.html',
            'LGPL-2.1' => 'https://www.gnu.org/licenses/lgpl-2.1.html',
            'LGPL-3.0' => 'https://www.gnu.org/licenses/lgpl.html',
            'LGPL-3.0+' => 'https://www.gnu.org/licenses/lgpl.html',
            'BSD' => 'https://opensource.org/licenses/bsd-license.html',
            'BSD-2-Clause' => 'https://opensource.org/licenses/BSD-2-Clause',
            'BSD-3-Clause' => 'https://opensource.org/licenses/BSD-3-Clause',
            'FreeBSD' => 'https://opensource.org/licenses/BSD-2-Clause',
            'MIT' => 'https://www.opensource.org/licenses/mit-license.php',
            'PHP' => 'https://opensource.org/licenses/PHP-3.0',
            'PHP-3' => 'https://www.php.net/license/3_01.txt',
            'PHP-3.0' => 'https://www.php.net/license/3_0.txt',
            'PHP-3.01' => 'https://www.php.net/license/3_01.txt',
        ];

        $dir  = dir($this->dir);
        $fn   = unslashify($dir->path) . "/$plugin_name/$plugin_name.php";
        $info = false;

        // Validate the plugin name to prevent from path traversal
        if (preg_match('/[^a-zA-Z0-9_-]/', $plugin_name)) {
            rcube::raise_error([
                    'code' => 520, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Invalid plugin name: $plugin_name"
                ],
                true, false
            );

            return false;
        }

        if (!class_exists($plugin_name, false)) {
            if (is_readable($fn)) {
                include($fn);
            }
            else {
                return false;
            }
        }

        if (class_exists($plugin_name)) {
            $info = $plugin_name::info();
        }

        // fall back to composer.json file
        if (empty($info)) {
            $info = [];
            $composer = INSTALL_PATH . "/plugins/$plugin_name/composer.json";

            if (is_readable($composer) && ($json = json_decode(file_get_contents($composer), true))) {
                // Build list of plugins required
                $require = [];
                if (!empty($json['require'])) {
                    foreach (array_keys((array) $json['require']) as $dname) {
                        if (!preg_match('|^([^/]+)/([a-zA-Z0-9_-]+)$|', $dname, $m)) {
                            continue;
                        }

                        $vendor = $m[1];
                        $name   = $m[2];

                        if ($name != 'plugin-installer' && $vendor != 'pear' && $vendor != 'pear-pear') {
                            $dpath = unslashify($dir->path) . "/$name/$name.php";
                            if (is_readable($dpath)) {
                                $require[] = $name;
                            }
                        }
                    }
                }

                if (!empty($json['name']) && is_string($json['name']) && strpos($json['name'], '/') !== false) {
                    list($info['vendor'], $info['name']) = explode('/', $json['name'], 2);
                }

                $info['version'] = isset($json['version']) ? $json['version'] : null;
                $info['license'] = isset($json['license']) ? $json['license'] : null;
                $info['require'] = $require;

                if (!empty($json['homepage'])) {
                    $info['uri'] = $json['homepage'];
                }
            }

            // read local composer.lock file (once)
            if (!isset($composer_lock)) {
                $composer_lock = @json_decode(@file_get_contents(INSTALL_PATH . "/composer.lock"), true);
                if ($composer_lock && !empty($composer_lock['packages'])) {
                    foreach ($composer_lock['packages'] as $i => $package) {
                        $composer_lock['installed'][$package['name']] = $package;
                    }
                }
            }

            // load additional information from local composer.lock file
            if (!empty($json['name']) && $composer_lock && !empty($composer_lock['installed'])
                && !empty($composer_lock['installed'][$json['name']])
            ) {
                $lock            = $composer_lock['installed'][$json['name']];
                $info['version'] = $lock['version'];
                $info['uri']     = !empty($lock['homepage']) ? $lock['homepage'] : $lock['source']['url'];
                $info['src_uri'] = !empty($lock['dist']['url']) ? $lock['dist']['url'] : $lock['source']['url'];
            }
        }

        // fall back to package.xml file
        if (empty($info)) {
            $package = INSTALL_PATH . "/plugins/$plugin_name/package.xml";
            if (is_readable($package) && ($file = file_get_contents($package))) {
                $doc = new DOMDocument();
                $doc->loadXML($file);
                $xpath = new DOMXPath($doc);
                $xpath->registerNamespace('rc', "http://pear.php.net/dtd/package-2.0");

                // XPaths of plugin metadata elements
                $metadata = [
                    'name'        => 'string(//rc:package/rc:name)',
                    'version'     => 'string(//rc:package/rc:version/rc:release)',
                    'license'     => 'string(//rc:package/rc:license)',
                    'license_uri' => 'string(//rc:package/rc:license/@uri)',
                    'src_uri'     => 'string(//rc:package/rc:srcuri)',
                    'uri'         => 'string(//rc:package/rc:uri)',
                ];

                foreach ($metadata as $key => $path) {
                    $info[$key] = $xpath->evaluate($path);
                }

                // dependent required plugins (can be used, but not included in config)
                $deps = $xpath->evaluate('//rc:package/rc:dependencies/rc:required/rc:package/rc:name');
                for ($i = 0; $i < $deps->length; $i++) {
                    $dn = $deps->item($i)->nodeValue;
                    $info['require'][] = $dn;
                }
            }
        }

        // At least provide the name
        if (!$info && class_exists($plugin_name)) {
            $info = ['name' => $plugin_name, 'version' => '--'];
        }
        else if (!empty($info['license'])) {
            // Convert license identifier to something shorter
            if (preg_match('/^([ALGP]+)[-v]([0-9.]+)(\+|-or-later)?/', $info['license'], $matches)) {
                $info['license'] = $matches[1] . '-' . sprintf('%.1f', $matches[2])
                    . (!empty($matches[3]) ? '+' : '');
            }

            if (empty($info['license_uri']) && !empty($license_uris[$info['license']])) {
                $info['license_uri'] = $license_uris[$info['license']];
            }
        }

        return $info;
    }

    /**
     * Allows a plugin object to register a callback for a certain hook
     *
     * @param string   $hook     Hook name
     * @param callable $callback A callback function
     */
    public function register_hook($hook, $callback)
    {
        if (is_callable($callback)) {
            if (isset($this->deprecated_hooks[$hook])) {
                rcube::raise_error([
                        'code' => 522, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Deprecated hook name. "
                            . $hook . ' -> ' . $this->deprecated_hooks[$hook]
                    ], true, false
                );
                $hook = $this->deprecated_hooks[$hook];
            }
            $this->handlers[$hook][] = $callback;
        }
        else {
            rcube::raise_error([
                    'code' => 521, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Invalid callback function for $hook"
                ],
                true, false
            );
        }
    }

    /**
     * Allow a plugin object to unregister a callback.
     *
     * @param string   $hook     Hook name
     * @param callable $callback A callback function
     */
    public function unregister_hook($hook, $callback)
    {
        if (empty($this->handlers[$hook])) {
            return;
        }

        $callback_id = array_search($callback, (array) $this->handlers[$hook]);
        if ($callback_id !== false) {
            // array_splice() removes the element and re-indexes keys
            // that is required by the 'for' loop in exec_hook() below
            array_splice($this->handlers[$hook], $callback_id, 1);
        }
    }

    /**
     * Triggers a plugin hook.
     * This is called from the application and executes all registered handlers
     *
     * @param string $hook Hook name
     * @param array  $args Named arguments (key->value pairs)
     *
     * @return array The (probably) altered hook arguments
     */
    public function exec_hook($hook, $args = [])
    {
        if (!is_array($args)) {
            $args = ['arg' => $args];
        }

        // TODO: avoid recursion by checking in_array($hook, $this->exec_stack) ?

        $args += ['abort' => false];
        array_push($this->exec_stack, $hook);

        // Use for loop here, so handlers added in the hook will be executed too
        if (!empty($this->handlers[$hook])) {
            for ($i = 0; $i < count($this->handlers[$hook]); $i++) {
                $ret = call_user_func($this->handlers[$hook][$i], $args);
                if ($ret && is_array($ret)) {
                    $args = $ret + $args;
                }

                if (!empty($args['break'])) {
                    break;
                }
            }
        }

        array_pop($this->exec_stack);
        return $args;
    }

    /**
     * Let a plugin register a handler for a specific request
     *
     * @param string   $action   Action name (_task=mail&_action=plugin.foo)
     * @param string   $owner    Plugin name that registers this action
     * @param callable $callback A callback function
     * @param string   $task     Task name registered by this plugin
     */
    public function register_action($action, $owner, $callback, $task = null)
    {
        // check action name
        if ($task) {
            $action = $task.'.'.$action;
        }
        else if (strpos($action, 'plugin.') !== 0) {
            $action = 'plugin.'.$action;
        }

        // can register action only if it's not taken or registered by myself
        if (!isset($this->actionmap[$action]) || $this->actionmap[$action] == $owner) {
            $this->actions[$action] = $callback;
            $this->actionmap[$action] = $owner;
        }
        else {
            rcube::raise_error([
                    'code' => 523, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Cannot register action $action; already taken by another plugin"
                ],
                true, false
            );
        }
    }

    /**
     * This method handles requests like _task=mail&_action=plugin.foo
     * It executes the callback function that was registered with the given action.
     *
     * @param string $action Action name
     */
    public function exec_action($action)
    {
        if (isset($this->actions[$action])) {
            call_user_func($this->actions[$action]);
        }
        else if (rcube::get_instance()->action != 'refresh') {
            rcube::raise_error([
                    'code' => 524, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "No handler found for action $action"
                ],
                true, true
            );
        }
    }

    /**
     * Register a handler function for template objects
     *
     * @param string   $name     Object name
     * @param string   $owner    Plugin name that registers this action
     * @param callable $callback A callback function
     */
    public function register_handler($name, $owner, $callback)
    {
        // check name
        if (strpos($name, 'plugin.') !== 0) {
            $name = 'plugin.' . $name;
        }

        // can register handler only if it's not taken or registered by myself
        if (is_object($this->output)
            && (!isset($this->objectsmap[$name]) || $this->objectsmap[$name] == $owner)
        ) {
            $this->output->add_handler($name, $callback);
            $this->objectsmap[$name] = $owner;
        }
        else {
            rcube::raise_error([
                    'code' => 525, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Cannot register template handler $name;"
                        ." already taken by another plugin or no output object available"
                ],
                true, false
            );
        }
    }

    /**
     * Register this plugin to be responsible for a specific task
     *
     * @param string $task  Task name (only characters [a-z0-9_-] are allowed)
     * @param string $owner Plugin name that registers this action
     */
    public function register_task($task, $owner)
    {
        // tasks are irrelevant in framework mode
        if (!class_exists('rcmail', false)) {
            return true;
        }

        if ($task != asciiwords($task, true)) {
            rcube::raise_error([
                    'code' => 526, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Invalid task name: $task."
                        ." Only characters [a-z0-9_.-] are allowed"
                ],
                true, false
            );
        }
        else if (in_array($task, rcmail::$main_tasks)) {
            rcube::raise_error([
                    'code' => 526, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Cannot register task $task;"
                        ." already taken by another plugin or the application itself"
                ],
                true, false
            );
        }
        else {
            $this->tasks[$task] = $owner;
            rcmail::$main_tasks[] = $task;
            return true;
        }

        return false;
    }

    /**
     * Checks whether the given task is registered by a plugin
     *
     * @param string $task Task name
     *
     * @return bool True if registered, otherwise false
     */
    public function is_plugin_task($task)
    {
        return !empty($this->tasks[$task]);
    }

    /**
     * Check if a plugin hook is currently processing.
     * Mainly used to prevent loops and recursion.
     *
     * @param string $hook Hook to check (optional)
     *
     * @return bool True if any/the given hook is currently processed, otherwise false
     */
    public function is_processing($hook = null)
    {
        return count($this->exec_stack) > 0 && (!$hook || in_array($hook, $this->exec_stack));
    }

    /**
     * Include a plugin script file in the current HTML page
     *
     * @param string $fn Path to script
     */
    public function include_script($fn)
    {
        if (is_object($this->output) && $this->output->type == 'html') {
            $src = $this->resource_url($fn);
            $this->output->include_script($src, 'head_bottom', false);
        }
    }

    /**
     * Include a plugin stylesheet in the current HTML page
     *
     * @param string $fn Path to stylesheet
     */
    public function include_stylesheet($fn)
    {
        if (is_object($this->output) && $this->output->type == 'html') {
            if ($fn[0] != '/' && !preg_match('|^https?://|i', $fn)) {
                $rcube      = rcube::get_instance();
                $devel_mode = $rcube->config->get('devel_mode');
                $assets_dir = $rcube->config->get('assets_dir');
                $path       = unslashify($assets_dir ?: RCUBE_INSTALL_PATH);
                $dir        = $path . (strpos($fn, "plugins/") === false ? '/plugins' : '');

                // Prefer .less files in devel_mode (assume less.js is loaded)
                if ($devel_mode) {
                    $less = preg_replace('/\.css$/i', '.less', $fn);
                    if ($less != $fn && is_file("$dir/$less")) {
                        $fn = $less;
                    }
                }
                else if (!preg_match('/\.min\.css$/', $fn)) {
                    $min = preg_replace('/\.css$/i', '.min.css', $fn);
                    if (is_file("$dir/$min")) {
                        $fn = $min;
                    }
                }

                if (!is_file("$dir/$fn")) {
                    return;
                }
            }

            $src = $this->resource_url($fn);
            $this->output->include_css($src);
        }
    }

    /**
     * Save the given HTML content to be added to a template container
     *
     * @param string $html      HTML content
     * @param string $container Template container identifier
     */
    public function add_content($html, $container)
    {
        if (!isset($this->template_contents[$container])) {
            $this->template_contents[$container] = '';
        }

        $this->template_contents[$container] .= $html . "\n";
    }

    /**
     * Returns list of loaded plugins names
     *
     * @return array List of plugin names
     */
    public function loaded_plugins()
    {
        return array_keys($this->plugins);
    }

    /**
     * Returns loaded plugin
     *
     * @return rcube_plugin|null Plugin instance
     */
    public function get_plugin($name)
    {
        return !empty($this->plugins[$name]) ? $this->plugins[$name] : null;
    }

    /**
     * Callback for template_container hooks
     *
     * @param array $attrib Container attributes
     *
     * @return array
     */
    protected function template_container_hook($attrib)
    {
        $container     = $attrib['name'];
        $content       = $attrib['content'] ?? '';

        if (isset($this->template_contents[$container])) {
            $content .= $this->template_contents[$container];
        }

        return ['content' => $content];
    }

    /**
     * Make the given file name link into the plugins directory
     *
     * @param string $fn Filename
     *
     * @return string
     */
    protected function resource_url($fn)
    {
        // pattern "skins/" used to identify plugin resources loaded from the core skin folder
        if ($fn[0] != '/' && !preg_match('#^(https?://|skins/)#i', $fn)) {
            return $this->url . $fn;
        }
        else {
            return $fn;
        }
    }
}

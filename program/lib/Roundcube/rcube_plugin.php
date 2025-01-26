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
 |  Abstract plugins interface/class                                     |
 |  All plugins need to extend this class                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Plugin interface class
 *
 * @package    Framework
 * @subpackage PluginAPI
 */
abstract class rcube_plugin
{
    /**
     * Class name of the plugin instance
     *
     * @var string
     */
    public $ID;

    /**
     * Instance of Plugin API
     *
     * @var rcube_plugin_api
     */
    public $api;

    /**
     * Regular expression defining task(s) to bind with
     *
     * @var string
     */
    public $task;

    /**
     * Disables plugin in AJAX requests
     *
     * @var bool
     */
    public $noajax = false;

    /**
     * Disables plugin in framed mode
     *
     * @var bool
     */
    public $noframe = false;

    /**
     * A list of config option names that can be modified
     * by the user via user interface (with save-prefs command)
     *
     * @var array
     */
    public $allowed_prefs;

    /** @var string Plugin directory location */
    protected $home;

    /** @var string Base URL to the plugin directory */
    protected $urlbase;

    /** @var string Plugin task name (if registered) */
    private $mytask;

    /** @var array List of plugin configuration files already loaded */
    private $loaded_config = [];


    /**
     * Default constructor.
     *
     * @param rcube_plugin_api $api Plugin API
     */
    public function __construct($api)
    {
        $this->ID      = get_class($this);
        $this->api     = $api;
        $this->home    = $api->dir . $this->ID;
        $this->urlbase = $api->url . $this->ID . '/';
    }

    /**
     * Initialization method, needs to be implemented by the plugin itself
     */
    abstract function init();

    /**
     * Provide information about this
     *
     * @return array Meta information about a plugin or false if not implemented.
     * As hash array with the following keys:
     *      name: The plugin name
     *    vendor: Name of the plugin developer
     *   version: Plugin version name
     *   license: License name (short form according to https://spdx.org/licenses/)
     *       uri: The URL to the plugin homepage or source repository
     *   src_uri: Direct download URL to the source code of this plugin
     *   require: List of plugins required for this one (as array of plugin names)
     */
    public static function info()
    {
        return false;
    }

    /**
     * Attempt to load the given plugin which is required for the current plugin
     *
     * @param string $plugin_name Plugin name
     *
     * @return bool True on success, false on failure
     */
    public function require_plugin($plugin_name)
    {
        return $this->api->load_plugin($plugin_name, true);
    }

    /**
     * Attempt to load the given plugin which is optional for the current plugin
     *
     * @param string $plugin_name Plugin name
     *
     * @return bool True on success, false on failure
     */
    public function include_plugin($plugin_name)
    {
        return $this->api->load_plugin($plugin_name, true, false);
    }

    /**
     * Load local config file from plugins directory.
     * The loaded values are patched over the global configuration.
     *
     * @param string $fname Config file name relative to the plugin's folder
     *
     * @return bool True on success, false on failure
     */
    public function load_config($fname = 'config.inc.php')
    {
        if (in_array($fname, $this->loaded_config)) {
            return true;
        }

        $this->loaded_config[] = $fname;

        $fpath = slashify($this->home) . $fname;
        $rcube = rcube::get_instance();

        if (($is_local = is_file($fpath)) && !$rcube->config->load_from_file($fpath)) {
            rcube::raise_error([
                    'code' => 527, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Failed to load config from $fpath"
                ], true, false
            );
            return false;
        }
        else if (!$is_local) {
            // Search plugin_name.inc.php file in any configured path
            return $rcube->config->load_from_file($this->ID . '.inc.php');
        }

        return true;
    }

    /**
     * Register a callback function for a specific (server-side) hook
     *
     * @param string $hook     Hook name
     * @param mixed  $callback Callback function as string or array
     *                         with object reference and method name
     */
    public function add_hook($hook, $callback)
    {
        $this->api->register_hook($hook, $callback);
    }

    /**
     * Unregister a callback function for a specific (server-side) hook.
     *
     * @param string $hook     Hook name
     * @param mixed  $callback Callback function as string or array
     *                         with object reference and method name
     */
    public function remove_hook($hook, $callback)
    {
        $this->api->unregister_hook($hook, $callback);
    }

    /**
     * Load localized texts from the plugins dir
     *
     * @param string $dir        Directory to search in
     * @param mixed  $add2client Make texts also available on the client
     *                           (array with list or true for all)
     */
    public function add_texts($dir, $add2client = false)
    {
        $rcube = rcube::get_instance();
        $texts = $rcube->read_localization(realpath(slashify($this->home) . $dir));

        // prepend domain to text keys and add to the application texts repository
        if (!empty($texts)) {
            $domain = $this->ID;
            $add    = [];

            foreach ($texts as $key => $value) {
                $add[$domain.'.'.$key] = $value;
            }

            $rcube->load_language($_SESSION['language'], $add);

            // add labels to client
            if ($add2client && method_exists($rcube->output, 'add_label')) {
                if (is_array($add2client)) {
                    $js_labels = array_map([$this, 'label_map_callback'], $add2client);
                }
                else {
                    $js_labels = array_keys($add);
                }

                $rcube->output->add_label($js_labels);
            }
        }
    }

    /**
     * Wrapper for add_label() adding the plugin ID as domain
     */
    public function add_label(...$args)
    {
        $rcube = rcube::get_instance();

        if (method_exists($rcube->output, 'add_label')) {
            if (count($args) == 1 && is_array($args[0])) {
                $args = $args[0];
            }

            $args = array_map([$this, 'label_map_callback'], $args);
            $rcube->output->add_label($args);
        }
    }

    /**
     * Wrapper for rcube::gettext() adding the plugin ID as domain
     *
     * @param string|array $p Named parameters array or label name
     *
     * @return string Localized text
     * @see rcube::gettext()
     */
    public function gettext($p)
    {
        return rcube::get_instance()->gettext($p, $this->ID);
    }

    /**
     * Register this plugin to be responsible for a specific task
     *
     * @param string $task Task name (only characters [a-z0-9_-] are allowed)
     */
    public function register_task($task)
    {
        if ($this->api->register_task($task, $this->ID)) {
            $this->mytask = $task;
        }
    }

    /**
     * Register a handler for a specific client-request action
     *
     * The callback will be executed upon a request like /?_task=mail&_action=plugin.myaction
     *
     * @param string $action   Action name (should be unique)
     * @param mixed  $callback Callback function as string
     *                         or array with object reference and method name
     */
    public function register_action($action, $callback)
    {
        $this->api->register_action($action, $this->ID, $callback, $this->mytask);
    }

    /**
     * Register a handler function for a template object
     *
     * When parsing a template for display, tags like <roundcube:object name="plugin.myobject" />
     * will be replaced by the return value if the registered callback function.
     *
     * @param string $name     Object name (should be unique and start with 'plugin.')
     * @param mixed  $callback Callback function as string or array with object reference
     *                         and method name
     */
    public function register_handler($name, $callback)
    {
        $this->api->register_handler($name, $this->ID, $callback);
    }

    /**
     * Make this javascript file available on the client
     *
     * @param string $fn File path; absolute or relative to the plugin directory
     */
    public function include_script($fn)
    {
        $this->api->include_script($this->resource_url($fn));
    }

    /**
     * Make this stylesheet available on the client
     *
     * @param string $fn File path; absolute or relative to the plugin directory
     */
    public function include_stylesheet($fn)
    {
        $this->api->include_stylesheet($this->resource_url($fn));
    }

    /**
     * Append a button to a certain container
     *
     * @param array  $p         Hash array with named parameters (as used in skin templates)
     * @param string $container Container name where the buttons should be added to
     *
     * @see rcube_template::button()
     */
    public function add_button($p, $container)
    {
        if ($this->api->output->type == 'html') {
            // fix relative paths
            foreach (['imagepas', 'imageact', 'imagesel'] as $key) {
                if (!empty($p[$key])) {
                    $p[$key] = $this->api->url . $this->resource_url($p[$key]);
                }
            }

            $this->api->add_content($this->api->output->button($p), $container);
        }
    }

    /**
     * Generate an absolute URL to the given resource within the current
     * plugin directory
     *
     * @param string $fn The file name
     *
     * @return string Absolute URL to the given resource
     */
    public function url($fn)
    {
        return $this->api->url . $this->resource_url($fn);
    }

    /**
     * Make the given file name link into the plugin directory
     *
     * @param string $fn Filename
     */
    private function resource_url($fn)
    {
        // pattern "skins/[a-z0-9-_]+/plugins/$this->ID/" used to identify plugin resources loaded from the core skin folder
        if ($fn[0] != '/' && !preg_match("#^(https?://|skins/[a-z0-9-_]+/plugins/$this->ID/)#i", $fn)) {
            return $this->ID . '/' . $fn;
        }
        else {
            return $fn;
        }
    }

    /**
     * Provide path to the currently selected skin folder within the plugin directory
     * with a fallback to the default skin folder.
     *
     * @param  string $extra_dir Additional directory to search in (optional)
     * @param  mixed  $skin_name Specific skin name(s) to look for, string or array (optional)
     * @return string            Skin path relative to plugins directory
     */
    public function local_skin_path($extra_dir = null, $skin_name = null)
    {
        $rcube     = rcube::get_instance();
        $skins     = array_keys((array)$rcube->output->skins);
        $skin_path = '';

        if (empty($skins)) {
            $skins = (array) $rcube->config->get('skin');
        }

        $dirs = ['skins'];
        if (!empty($extra_dir)) {
            array_unshift($dirs, $extra_dir);
        }

        if (!empty($skin_name)) {
            $skins = (array) $skin_name;
        }

        foreach ($skins as $skin) {
            foreach ($dirs as $dir) {
                // skins folder in the plugins dir
                $skin_path = $dir . '/' . $skin;

                if (!is_dir(realpath(slashify($this->home) . $skin_path))) {
                    // plugins folder in the skins dir
                    $skin_path .= '/plugins/' . $this->ID;
                    if (is_dir(realpath(slashify(RCUBE_INSTALL_PATH) . $skin_path))) {
                        break 2;
                    }
                }
                else {
                    break 2;
                }
            }
        }

        return $skin_path;
    }

    /**
     * Callback function for array_map
     *
     * @param string $key Array key.
     *
     * @return string
     */
    private function label_map_callback($key)
    {
        if (strpos($key, $this->ID.'.') === 0) {
            return $key;
        }

        return $this->ID.'.'.$key;
    }
}

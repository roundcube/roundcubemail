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
 |   Class to handle JSON (AJAX) output                                  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * View class to produce JSON responses
 *
 * @package    Webmail
 * @subpackage View
 */
class rcmail_output_json extends rcmail_output
{
    protected $texts     = [];
    protected $commands  = [];
    protected $callbacks = [];
    protected $message   = null;
    protected $header_sent = false;

    public $type      = 'js';
    public $ajax_call = true;


    /**
     * Object constructor
     */
    public function __construct()
    {
        parent::__construct();

        if (!empty($_SESSION['skin_config'])) {
            foreach ($_SESSION['skin_config'] as $key => $value) {
                $this->config->set($key, $value, true);
            }

            $value = array_merge((array) $this->config->get('dont_override'), array_keys($_SESSION['skin_config']));
            $this->config->set('dont_override', $value, true);
        }
    }

    /**
     * Issue command to set page title
     *
     * @param string $title New page title
     */
    public function set_pagetitle($title)
    {
        if ($this->config->get('devel_mode') && !empty($_SESSION['username'])) {
            $name = $_SESSION['username'];
        }
        else {
            $name = $this->config->get('product_name');
        }

        $this->command('set_pagetitle', empty($name) ? $title : $name . ' :: ' . $title);
    }

    /**
     * Register a template object handler
     *
     * @param string $obj  Object name
     * @param callable $func Function name to call
     */
    public function add_handler($obj, $func)
    {
        // ignore
    }

    /**
     * Register a list of template object handlers
     *
     * @param array $arr Hash array with object=>handler pairs
     */
    public function add_handlers($arr)
    {
        // ignore
    }

    /**
     * Call a client method
     *
     * @param string $cmd    Method to call
     * @param mixed ...$args Additional arguments
     */
    public function command($cmd, ...$args)
    {
        array_unshift($args, $cmd);

        if (strpos($args[0], 'plugin.') === 0) {
            $this->callbacks[] = $args;
        }
        else {
            $this->commands[] = $args;
        }
    }

    /**
     * Add a localized label(s) to the client environment
     *
     * @param mixed ...$args Labels (an array of strings, or many string arguments)
     */
    public function add_label(...$args)
    {
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }

        foreach ($args as $name) {
            $this->texts[$name] = $this->app->gettext($name);
        }
    }

    /**
     * Invoke display_message command
     *
     * @param string $message  Message to display
     * @param string $type     Message type [notice|confirm|error]
     * @param array  $vars     Key-value pairs to be replaced in localized text
     * @param bool   $override Override last set message
     * @param int    $timeout  Message displaying time in seconds
     *
     * @uses self::command()
     */
    public function show_message($message, $type = 'notice', $vars = null, $override = true, $timeout = 0)
    {
        if ($override || !$this->message) {
            if ($this->app->text_exists($message)) {
                if (!empty($vars)) {
                    $vars = array_map(['rcmail', 'Q'], $vars);
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
     */
    public function reset()
    {
        parent::reset();
        $this->texts    = [];
        $this->commands = [];
    }

    /**
     * Redirect to a certain url
     *
     * @param mixed $p     Either a string with the action or url parameters as key-value pairs
     * @param int   $delay Delay in seconds
     *
     * @see rcmail::url()
     */
    public function redirect($p = [], $delay = 1)
    {
        $location = $this->app->url($p);
        $this->remote_response(sprintf("window.setTimeout(function(){ %s.redirect('%s',true); }, %d);",
            self::JS_OBJECT_NAME, $location, $delay));
        exit;
    }

    /**
     * Send an AJAX response to the client.
     */
    public function send()
    {
        $this->remote_response();
        exit;
    }

    /**
     * Show error page and terminate script execution
     *
     * @param int    $code    Error code
     * @param string $message Error message
     */
    public function raise_error($code, $message)
    {
        if ($code == 403) {
            $this->header('HTTP/1.1 403 Forbidden');
            die("Invalid Request");
        }

        $this->show_message("Application Error ($code): $message", 'error');
        $this->remote_response();
        exit;
    }

    /**
     * Send an AJAX response with executable JS code
     *
     * @param string $add Additional JS code
     */
    protected function remote_response($add = '')
    {
        if (!$this->header_sent) {
            $this->header_sent = true;
            $this->nocacheing_headers();
            $this->header('Content-Type: application/json; charset=' . $this->get_charset());
        }

        // unset default env vars
        unset($this->env['task'], $this->env['action'], $this->env['comm_path']);

        $rcmail = rcmail::get_instance();
        $response['action'] = $rcmail->action;

        if ($unlock = rcube_utils::get_input_string('_unlock', rcube_utils::INPUT_GPC)) {
            $response['unlock'] = $unlock;
        }

        if (!empty($this->env)) {
            $response['env'] = $this->env;
        }

        if (!empty($this->texts)) {
            $response['texts'] = $this->texts;
        }

        // send function calls
        $response['exec'] = $this->get_js_commands() . $add;

        if (!empty($this->callbacks)) {
            $response['callbacks'] = $this->callbacks;
        }

        // trigger generic hook where plugins can put additional content to the response
        $hook = $this->app->plugins->exec_hook("render_response", ['response' => $response]);

        // save some memory
        $response = $hook['response'];
        unset($hook['response']);

        echo self::json_serialize($response, $this->devel_mode, false);
    }

    /**
     * Return executable javascript code for all registered commands
     */
    protected function get_js_commands()
    {
        $out = '';

        foreach ($this->commands as $i => $args) {
            $method = array_shift($args);
            foreach ($args as $i => $arg) {
                $args[$i] = self::json_serialize($arg, $this->devel_mode, false);
            }

            $out .= sprintf(
                "this.%s(%s);\n",
                preg_replace('/^parent\./', '', $method),
                implode(',', $args)
            );
        }

        return $out;
    }
}

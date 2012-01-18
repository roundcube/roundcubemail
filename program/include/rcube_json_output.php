<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_json_output.php                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2010, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
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
 * View class to produce JSON responses
 *
 * @package View
 */
class rcube_json_output
{
    /**
     * Stores configuration object.
     *
     * @var rcube_config
     */
    private $config;
    private $charset = RCMAIL_CHARSET;
    private $texts = array();
    private $commands = array();
    private $callbacks = array();
    private $message = null;

    public $browser;
    public $env = array();
    public $type = 'js';
    public $ajax_call = true;


    /**
     * Constructor
     */
    public function __construct($task=null)
    {
        $this->config  = rcmail::get_instance()->config;
        $this->browser = new rcube_browser();
    }


    /**
     * Set environment variable
     *
     * @param string $name Property name
     * @param mixed $value Property value
     */
    public function set_env($name, $value)
    {
        $this->env[$name] = $value;
    }


    /**
     * Issue command to set page title
     *
     * @param string $title New page title
     */
    public function set_pagetitle($title)
    {
        if ($this->config->get('devel_mode') && !empty($_SESSION['username']))
            $name = $_SESSION['username'];
        else
            $name = $this->config->get('product_name');

        $this->command('set_pagetitle', empty($name) ? $title : $name.' :: '.$title);
    }


    /**
     * @ignore
     */
    function set_charset($charset)
    {
        // ignore: $this->charset = $charset;
    }


    /**
     * Get charset for output
     *
     * @return string Output charset
     */
    function get_charset()
    {
        return $this->charset;
    }


    /**
     * Register a template object handler
     *
     * @param  string $obj Object name
     * @param  string $func Function name to call
     * @return void
     */
    public function add_handler($obj, $func)
    {
        // ignore
    }


    /**
     * Register a list of template object handlers
     *
     * @param  array $arr Hash array with object=>handler pairs
     * @return void
     */
    public function add_handlers($arr)
    {
        // ignore
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

        if (strpos($cmd[0], 'plugin.') === 0)
          $this->callbacks[] = $cmd;
        else
          $this->commands[] = $cmd;
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
            $this->texts[$name] = rcube_label($name);
        }
    }


    /**
     * Invoke display_message command
     *
     * @param string  $message  Message to display
     * @param string  $type     Message type [notice|confirm|error]
     * @param array   $vars     Key-value pairs to be replaced in localized text
     * @param boolean $override Override last set message
     * @param int     $timeout  Message displaying time in seconds
     * @uses self::command()
     */
    public function show_message($message, $type='notice', $vars=null, $override=true, $timeout=0)
    {
        if ($override || !$this->message) {
            if (rcube_label_exists($message)) {
                if (!empty($vars))
                    $vars = array_map('Q', $vars);
                $msgtext = rcube_label(array('name' => $message, 'vars' => $vars));
            }
            else
                $msgtext = $message;

            $this->message = $message;
            $this->command('display_message', $msgtext, $type, $timeout * 1000);
        }
    }


    /**
     * Delete all stored env variables and commands
     */
    public function reset()
    {
        $this->env = array();
        $this->texts = array();
        $this->commands = array();
    }


    /**
     * Redirect to a certain url
     *
     * @param mixed $p Either a string with the action or url parameters as key-value pairs
     * @param int $delay Delay in seconds
     * @see rcmail::url()
     */
    public function redirect($p = array(), $delay = 1)
    {
        $location = rcmail::get_instance()->url($p);
        $this->remote_response("window.setTimeout(\"location.href='{$location}'\", $delay);");
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
     * Send an AJAX response with executable JS code
     *
     * @param  string  $add Additional JS code
     * @param  boolean True if output buffer should be flushed
     * @return void
     * @deprecated
     */
    public function remote_response($add='')
    {
        static $s_header_sent = false;

        if (!$s_header_sent) {
            $s_header_sent = true;
            send_nocacheing_headers();
            header('Content-Type: text/plain; charset=' . $this->get_charset());
        }

        // unset default env vars
        unset($this->env['task'], $this->env['action'], $this->env['comm_path']);

        $rcmail = rcmail::get_instance();
        $response['action'] = $rcmail->action;

        if ($unlock = get_input_value('_unlock', RCUBE_INPUT_GPC)) {
            $response['unlock'] = $unlock;
        }

        if (!empty($this->env))
            $response['env'] = $this->env;

        if (!empty($this->texts))
            $response['texts'] = $this->texts;

        // send function calls
        $response['exec'] = $this->get_js_commands() . $add;

        if (!empty($this->callbacks))
            $response['callbacks'] = $this->callbacks;

        echo json_serialize($response);
    }


    /**
     * Return executable javascript code for all registered commands
     *
     * @return string $out
     */
    private function get_js_commands()
    {
        $out = '';

        foreach ($this->commands as $i => $args) {
            $method = array_shift($args);
            foreach ($args as $i => $arg) {
                $args[$i] = json_serialize($arg);
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

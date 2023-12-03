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
 | CONTENTS:                                                             |
 |   Abstract class for output generation                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Class for output generation
 *
 * @package    Webmail
 * @subpackage View
 */
abstract class rcmail_output extends rcube_output
{
    const JS_OBJECT_NAME = 'rcmail';
    const BLANK_GIF      = 'R0lGODlhDwAPAIAAAMDAwAAAACH5BAEAAAAALAAAAAAPAA8AQAINhI+py+0Po5y02otnAQA7';

    public $type      = 'html';
    public $ajax_call = false;
    public $framed    = false;

    protected $pagetitle       = '';
    protected $object_handlers = [];
    protected $devel_mode      = false;


    /**
     * Object constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->devel_mode = (bool) $this->config->get('devel_mode');
    }

    /**
     * Setter for page title
     *
     * @param string $title Page title
     */
    public function set_pagetitle($title)
    {
        $this->pagetitle = $title;
    }

    /**
     * Getter for the current skin path property
     */
    public function get_skin_path()
    {
        return $this->config->get('skin_path');
    }

    /**
     * Delete all stored env variables and commands
     */
    public function reset()
    {
        parent::reset();

        $this->object_handlers = [];
        $this->pagetitle = '';
    }

    /**
     * Call a client method
     *
     * @param string $cmd     Method to call
     * @param mixed  ...$args Method arguments
     */
    abstract function command($cmd, ...$args);

    /**
     * Add a localized label(s) to the client environment
     *
     * @param mixed ...$args Labels (an array of strings, or many string arguments)
     */
    abstract function add_label(...$args);

    /**
     * Register a template object handler
     *
     * @param string $name Object name
     * @param callable $func Function name to call
     *
     * @return void
     */
    public function add_handler($name, $func)
    {
        $this->object_handlers[$name] = $func;
    }

    /**
     * Register a list of template object handlers
     *
     * @param array $handlers Hash array with object=>handler pairs
     *
     * @return void
     */
    public function add_handlers($handlers)
    {
        $this->object_handlers = array_merge($this->object_handlers, $handlers);
    }

    /**
     * A wrapper for header() function, so it can be replaced for automated tests
     *
     * @param string $header  The header string
     * @param bool   $replace Replace previously set header?
     *
     * @return void
     */
    public function header($header, $replace = true)
    {
        header($header, $replace);
    }

    /**
     * A helper to send output to the browser and exit
     *
     * @param string $body    The output body
     * @param array  $headers Headers
     *
     * @return void
     */
    public function sendExit($body = '', $headers = [])
    {
        foreach ($headers as $header) {
            header($header);
        }

        print $body;
        exit;
    }

    /**
     * A helper to send HTTP error code and message to the browser, and exit.
     *
     * @param int    $code    The HTTP error code
     * @param string $message The HTTP error message
     *
     * @return void
     */
    public function sendExitError($code, $message = '')
    {
        http_response_code($code);
        exit($message);
    }
}

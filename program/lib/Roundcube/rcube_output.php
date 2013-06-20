<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube PHP suite                          |
 | Copyright (C) 2005-2012 The Roundcube Dev Team                        |
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
 * @package    Framework
 * @subpackage View
 */
abstract class rcube_output
{
    public $browser;

    protected $app;
    protected $config;
    protected $charset = RCUBE_CHARSET;
    protected $env = array();


    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->app     = rcube::get_instance();
        $this->config  = $this->app->config;
        $this->browser = new rcube_browser();
    }


    /**
     * Magic getter
     */
    public function __get($var)
    {
        // allow read-only access to $env
        if ($var == 'env')
            return $this->env;

        return null;
    }


    /**
     * Setter for output charset.
     * To be specified in a meta tag and sent as http-header
     *
     * @param string $charset Charset name
     */
    public function set_charset($charset)
    {
        $this->charset = $charset;
    }


    /**
     * Getter for output charset
     *
     * @return string Output charset name
     */
    public function get_charset()
    {
        return $this->charset;
    }


    /**
     * Set environment variable
     *
     * @param string $name   Property name
     * @param mixed  $value  Property value
     */
    public function set_env($name, $value)
    {
        $this->env[$name] = $value;
    }


    /**
     * Environment variable getter.
     *
     * @param string $name  Property name
     *
     * @return mixed Property value
     */
    public function get_env($name)
    {
        return $this->env[$name];
    }


    /**
     * Delete all stored env variables and commands
     */
    public function reset()
    {
        $this->env = array();
    }


    /**
     * Invoke display_message command
     *
     * @param string  $message  Message to display
     * @param string  $type     Message type [notice|confirm|error]
     * @param array   $vars     Key-value pairs to be replaced in localized text
     * @param boolean $override Override last set message
     * @param int     $timeout  Message displaying time in seconds
     */
    abstract function show_message($message, $type = 'notice', $vars = null, $override = true, $timeout = 0);


    /**
     * Redirect to a certain url.
     *
     * @param mixed $p     Either a string with the action or url parameters as key-value pairs
     * @param int   $delay Delay in seconds
     */
    abstract function redirect($p = array(), $delay = 1);


    /**
     * Send output to the client.
     */
    abstract function send();


    /**
     * Send HTTP headers to prevent caching a page
     */
    public function nocacheing_headers()
    {
        if (headers_sent()) {
            return;
        }

        header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

        // Request browser to disable DNS prefetching (CVE-2010-0464)
        header("X-DNS-Prefetch-Control: off");

        // We need to set the following headers to make downloads work using IE in HTTPS mode.
        if ($this->browser->ie && rcube_utils::https_check()) {
            header('Pragma: private');
            header("Cache-Control: private, must-revalidate");
        }
        else {
            header("Cache-Control: private, no-cache, no-store, must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
        }
    }

    /**
     * Send header with expire date 30 days in future
     *
     * @param int Expiration time in seconds
     */
    public function future_expire_header($offset = 2600000)
    {
        if (headers_sent())
            return;

        header("Expires: " . gmdate("D, d M Y H:i:s", time()+$offset) . " GMT");
        header("Cache-Control: max-age=$offset");
        header("Pragma: ");
    }


    /**
     * Show error page and terminate script execution
     *
     * @param int    $code     Error code
     * @param string $message  Error message
     */
    public function raise_error($code, $message)
    {
        // STUB: to be overloaded by specific output classes
        fputs(STDERR, "Error $code: $message\n");
        exit(-1);
    }


    /**
     * Create an edit field for inclusion on a form
     *
     * @param string col field name
     * @param string value field value
     * @param array attrib HTML element attributes for field
     * @param string type HTML element type (default 'text')
     *
     * @return string HTML field definition
     */
    public static function get_edit_field($col, $value, $attrib, $type = 'text')
    {
        static $colcounts = array();

        $fname = '_'.$col;
        $attrib['name']  = $fname . ($attrib['array'] ? '[]' : '');
        $attrib['class'] = trim($attrib['class'] . ' ff_' . $col);

        if ($type == 'checkbox') {
            $attrib['value'] = '1';
            $input = new html_checkbox($attrib);
        }
        else if ($type == 'textarea') {
            $attrib['cols'] = $attrib['size'];
            $input = new html_textarea($attrib);
        }
        else if ($type == 'select') {
            $input = new html_select($attrib);
            $input->add('---', '');
            $input->add(array_values($attrib['options']), array_keys($attrib['options']));
        }
        else if ($attrib['type'] == 'password') {
            $input = new html_passwordfield($attrib);
        }
        else {
            if ($attrib['type'] != 'text' && $attrib['type'] != 'hidden') {
                $attrib['type'] = 'text';
            }
            $input = new html_inputfield($attrib);
        }

        // use value from post
        if (isset($_POST[$fname])) {
            $postvalue = rcube_utils::get_input_value($fname, rcube_utils::INPUT_POST, true);
            $value = $attrib['array'] ? $postvalue[intval($colcounts[$col]++)] : $postvalue;
        }

        $out = $input->show($value);

        return $out;
    }


    /**
     * Convert a variable into a javascript object notation
     *
     * @param mixed Input value
     *
     * @return string Serialized JSON string
     */
    public static function json_serialize($input)
    {
        $input = rcube_charset::clean($input);

        // sometimes even using rcube_charset::clean() the input contains invalid UTF-8 sequences
        // that's why we have @ here
        return @json_encode($input);
    }

}

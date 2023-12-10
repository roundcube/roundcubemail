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
 |   A class for easier testing of code that uses rcmail_output classes  |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * A class for easier testing of code that uses rcmail_output classes
 *
 * @package Tests
 */
class OutputHtmlMock extends rcmail_output_html
{
    const E_EXIT     = 101;
    const E_REDIRECT = 102;

    public $output;
    public $headers  = [];
    public $errorCode;
    public $errorMessage;
    public $template = '';

    /**
     * Redirect to a certain url
     *
     * @param mixed $p      Either a string with the action or url parameters as key-value pairs
     * @param int   $delay  Delay in seconds
     * @param bool  $secure Redirect to secure location (see rcmail::url())
     */
    public function redirect($p = [], $delay = 1, $secure = false)
    {
        if (!empty($this->env['extwin'])) {
            $p['extwin'] = 1;
        }

        $location = $this->app->url($p, false, false, $secure);

        // header('Location: ' . $location);
        throw new ExitException("Location: $location", self::E_REDIRECT);
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
        $this->template = $templ;

        parent::send($templ, false);

        if ($exit) {
            throw new ExitException("Output sent", self::E_EXIT);
        }
    }

    /**
     * A helper to send output to the browser and exit
     *
     * @param string $body    The output body
     * @param array  $headers Headers
     */
    public function sendExit($body = '', $headers = [])
    {
        foreach ($headers as $header) {
            $this->header($header);
        }

        $this->output = $body;

        throw new ExitException("Output sent", self::E_EXIT);
    }

    /**
     * A helper to send HTTP error code and message to the browser, and exit.
     *
     * @param int    $code    The HTTP error code
     * @param string $message The HTTP error message
     */
    public function sendExitError($code, $message = '')
    {
        $this->errorCode = $code;
        $this->errorMessage = $message;

        throw new ExitException("Output sent (error)", self::E_EXIT);
    }

    /**
     * Process template and write to stdOut
     *
     * @param string $template HTML template content
     */
    public function write($template = '')
    {
        ob_start();
        parent::write($template);
        $this->output = ob_get_contents();
        ob_end_clean();
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
        //ob_start();
        parent::parse($name, false, $write);
        //$this->output = ob_get_contents();
        //ob_end_clean();

        if ($exit) {
            throw new ExitException("Output sent", self::E_EXIT);
        }
    }

    /**
     * Delete all stored env variables and commands
     */
    public function reset($all = false)
    {
        parent::reset($all);

        $this->headers  = [];
        $this->output   = null;
        $this->template = null;

        $this->errorCode    = null;
        $this->errorMessage = null;
    }

    /**
     * A wrapper for header() function, so it can be replaced for automated tests
     *
     * @param string $header  The header string
     * @param bool   $replace Replace previously set header?
     */
    public function header($header, $replace = true)
    {
        $this->headers[] = $header;
    }

    /**
     * Return the output
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Return private/protected property
     */
    public function getProperty($name)
    {
        return $this->{$name};
    }
}

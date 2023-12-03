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
class OutputJsonMock extends rcmail_output_json
{
    const E_EXIT     = 101;
    const E_REDIRECT = 102;

    public $output;
    public $headers = [];
    public $errorCode;
    public $errorMessage;

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
        ob_start();
        $this->remote_response(sprintf("window.setTimeout(function(){ %s.redirect('%s',true); }, %d);",
            self::JS_OBJECT_NAME, $location, $delay));
        $this->output = ob_get_contents();
        ob_end_clean();

        throw new ExitException("Location: $location", self::E_REDIRECT);
    }

    /**
     * Send an AJAX response to the client.
     */
    public function send()
    {
        ob_start();
        $this->remote_response();
        $this->output = ob_get_contents();
        ob_end_clean();

        throw new ExitException("Output sent", self::E_EXIT);
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
     * Show error page and terminate script execution
     *
     * @param int    $code    Error code
     * @param string $message Error message
     */
    public function raise_error($code, $message)
    {
        if ($code == 403) {
            throw new ExitException("403 Forbidden", self::E_EXIT);
        }

        $this->show_message("Application Error ($code): $message", 'error');

        ob_start();
        $this->remote_response();
        $this->output = ob_get_contents();
        ob_end_clean();

        throw new ExitException("Error $code raised", self::E_EXIT);
    }

    /**
     * Delete all stored env variables and commands
     */
    public function reset()
    {
        parent::reset();

        $this->headers     = [];
        $this->output      = null;
        $this->header_sent = false;

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
     * Return the JSON output as an array
     */
    public function getOutput()
    {
        return $this->output ? json_decode($this->output, true) : null;
    }

    /**
     * Return private/protected property
     */
    public function getProperty($name)
    {
        return $this->{$name};
    }
}

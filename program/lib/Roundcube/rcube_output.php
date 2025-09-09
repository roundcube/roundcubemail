<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube webmail client                     |
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
 */
abstract class rcube_output
{
    public $browser;
    public $skins = [];
    public $charset = RCUBE_CHARSET;

    protected $app;
    protected $config;
    protected $env = [];

    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->app = rcube::get_instance();
        $this->config = $this->app->config;
        $this->browser = new rcube_browser();
    }

    /**
     * Magic getter
     */
    public function __get($var)
    {
        // allow read-only access to some members
        switch ($var) {
            case 'env':     return $this->env;
            case 'skins':   return $this->skins;
            case 'charset': return $this->charset;
        }
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
     * @param string $name  Property name
     * @param mixed  $value Property value
     */
    public function set_env($name, $value)
    {
        $this->env[$name] = $value;
    }

    /**
     * Environment variable getter.
     *
     * @param string $name Property name
     *
     * @return mixed Property value
     */
    public function get_env($name)
    {
        return $this->env[$name] ?? null;
    }

    /**
     * Delete all stored env variables and commands
     */
    public function reset()
    {
        $this->env = [];
    }

    /**
     * Invoke display_message command
     *
     * @param string $message  Message to display
     * @param string $type     Message type [notice|confirm|error]
     * @param array  $vars     Key-value pairs to be replaced in localized text
     * @param bool   $override Override last set message
     * @param int    $timeout  Message displaying time in seconds
     */
    abstract public function show_message($message, $type = 'notice', $vars = null, $override = true, $timeout = 0);

    /**
     * Redirect to a certain url.
     *
     * @param array|string $p     Either a string with the action or url parameters as key-value pairs
     * @param int          $delay Delay in seconds
     */
    abstract public function redirect($p = [], $delay = 1);

    /**
     * Send output to the client.
     */
    abstract public function send();

    /**
     * Send HTTP headers to prevent caching a page
     */
    public function nocacheing_headers()
    {
        if (headers_sent()) {
            return;
        }

        $this->header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        $this->header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        // We need to set the following headers to make downloads work using IE in HTTPS mode.
        if ($this->browser->ie && rcube_utils::https_check()) {
            $this->header('Pragma: private');
            $this->header('Cache-Control: private, must-revalidate');
        } else {
            $this->header('Cache-Control: private, no-cache, no-store, must-revalidate, post-check=0, pre-check=0');
            $this->header('Pragma: no-cache');
        }
    }

    /**
     * Send header with expire date 30 days in future
     *
     * @param int $offset Expiration time in seconds
     */
    public function future_expire_header($offset = 2600000)
    {
        if (headers_sent()) {
            return;
        }

        $this->header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $offset) . ' GMT');
        $this->header("Cache-Control: max-age={$offset}");
        $this->header('Pragma: ');
    }

    /**
     * Send browser compatibility/security/privacy headers
     *
     * @param bool $privacy Enable privacy headers
     */
    public function common_headers($privacy = true)
    {
        if (headers_sent()) {
            return;
        }

        $headers = [];

        // Unlock IE compatibility mode
        if ($this->browser->ie) {
            $headers['X-UA-Compatible'] = 'IE=edge';
        }

        if ($privacy) {
            // Request browser to disable DNS prefetching (CVE-2010-0464)
            $headers['X-DNS-Prefetch-Control'] = 'off';

            // Request browser disable Referer (sic) header
            $headers['Referrer-Policy'] = 'same-origin';
        }

        // send CSRF and clickjacking protection headers
        if ($xframe = $this->app->config->get('x_frame_options', 'sameorigin')) {
            $headers['X-Frame-Options'] = $xframe;
        }

        $plugin = $this->app->plugins->exec_hook('common_headers', ['headers' => $headers, 'privacy' => $privacy]);

        foreach ($plugin['headers'] as $header => $value) {
            $this->header("{$header}: {$value}");
        }
    }

    /**
     * Send headers related to file downloads.
     *
     * @param string $filename File name
     * @param array  $params   Optional parameters:
     *                         type         - File content type (default: 'application/octet-stream')
     *                         disposition  - Download type: 'inline' or 'attachment' (default)
     *                         length       - Content length
     *                         charset      - File name character set
     *                         type_charset - Content character set
     *                         time_limit   - Script execution limit (default: 3600)
     */
    public function download_headers($filename, $params = [])
    {
        // For security reasons we validate type, filename and charset params.
        // Some HTTP servers might drop a header that is malformed or very long, this then
        // can lead to web browsers unintentionally executing javascript code in the body.

        if (empty($params['disposition'])) {
            $params['disposition'] = 'attachment';
        }

        $ctype = 'application/octet-stream';
        $disposition = $params['disposition'];

        if (!empty($params['type']) && is_string($params['type']) && strlen($params['type']) < 256
            && preg_match('/^[a-z0-9!#$&.+^_-]+\/[a-z0-9!#$&.+^_-]+$/i', $params['type'])
        ) {
            $ctype = strtolower($params['type']);
        }

        // Send unsafe content as plain text
        if ($disposition == 'inline') {
            if ($ctype != 'image/svg+xml' && preg_match('~(javascript|jscript|ecmascript|xml|html|text/)~', $ctype)) {
                $ctype = 'text/plain';
            }

            if (str_starts_with($ctype, 'text')) {
                $charset = $this->charset;
                if (!empty($params['type_charset']) && rcube_charset::is_valid($params['type_charset'])) {
                    $charset = $params['type_charset'];
                }

                $ctype .= "; charset={$charset}";
            }
        }

        // @phpstan-ignore-next-line
        if (is_string($filename) && $filename !== '' && strlen($filename) <= 1024) {
            // For non-ascii characters we'll use RFC2231 syntax
            $fallback_filename = preg_replace('/[^a-zA-Z0-9_.(),;@+ -]/', '_', $filename);
            $disposition .= "; filename=\"{$fallback_filename}\"";

            if ($fallback_filename != $filename) {
                $filename = rawurlencode($filename);
                $charset = $this->charset;
                if (!empty($params['charset']) && rcube_charset::is_valid($params['charset'])) {
                    $charset = $params['charset'];
                }
                $disposition .= "; filename*={$charset}''{$filename}";
            }
        }

        $this->header("Content-Disposition: {$disposition}");
        $this->header("Content-Type: {$ctype}");

        if ($params['disposition'] == 'attachment' && $this->browser->ie) {
            $this->header('Content-Type: application/force-download');
        }

        if (isset($params['length'])) {
            $this->header('Content-Length: ' . $params['length']);
        }

        // Use strict security policy to make sure no javascript content is executed
        // img-src is needed to be able to print attachment preview page
        $this->header("Content-Security-Policy: default-src 'none'; img-src 'self'");

        // don't kill the connection if download takes more than 30 sec.
        if (!array_key_exists('time_limit', $params)) {
            $params['time_limit'] = 3600;
        }

        if (is_numeric($params['time_limit'])) {
            @set_time_limit($params['time_limit']);
        }
    }

    /**
     * Show error page and terminate script execution
     *
     * @param int    $code    Error code
     * @param string $message Error message
     */
    public function raise_error($code, $message)
    {
        // STUB: to be overloaded by specific output classes
        fwrite(\STDERR, "Error {$code}: {$message}\n");
        exit(-1);
    }

    /**
     * Create an edit field for inclusion on a form
     *
     * @param string $name   Field name
     * @param string $value  Field value
     * @param array  $attrib HTML element attributes for the field
     * @param string $type   HTML element type (default 'text')
     *
     * @return string HTML field definition
     */
    public static function get_edit_field($name, $value, $attrib = [], $type = 'text')
    {
        static $colcounts = [];

        $fname = '_' . $name;
        $attrib['name'] = $fname . (!empty($attrib['array']) ? '[]' : '');
        $attrib['class'] = trim((!empty($attrib['class']) ? $attrib['class'] : '') . ' ff_' . $name);

        if ($type == 'checkbox') {
            $attrib['value'] = '1';
            $input = new html_checkbox($attrib);
        } elseif ($type == 'textarea') {
            if (!empty($attrib['size'])) {
                $attrib['cols'] = $attrib['size'];
            }
            $input = new html_textarea($attrib);
        } elseif ($type == 'select') {
            $input = new html_select($attrib);
            if (empty($attrib['skip-empty'])) {
                $input->add('---', '');
            }
            if (!empty($attrib['options'])) {
                $input->add(array_values($attrib['options']), array_keys($attrib['options']));
            }
        } elseif ($type == 'password' || (isset($attrib['type']) && $attrib['type'] == 'password')) {
            $input = new html_passwordfield($attrib);
        } else {
            if (!isset($attrib['type']) || ($attrib['type'] != 'text' && $attrib['type'] != 'hidden')) {
                $attrib['type'] = 'text';
            }
            $input = new html_inputfield($attrib);
        }

        // use value from post
        if (isset($_POST[$fname])) {
            $postvalue = rcube_utils::get_input_value($fname, rcube_utils::INPUT_POST, true);
            if (!empty($attrib['array'])) {
                if (!isset($colcounts[$name])) {
                    $colcounts[$name] = 0;
                }
                $idx = intval($colcounts[$name]++);
                $value = $postvalue[$idx] ?? null;
            } else {
                $value = $postvalue;
            }
        }

        return $input->show($value);
    }

    /**
     * Convert a variable into a javascript object notation
     *
     * @param mixed $input  Input value
     * @param bool  $pretty Enable JSON formatting
     * @param bool  $inline Enable inline mode (generates output safe for use inside HTML)
     *
     * @return string Serialized JSON string
     */
    public static function json_serialize($input, $pretty = false, $inline = true)
    {
        $options = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_IGNORE;

        // JSON_HEX_TAG is needed for inlining JSON inside of the <script> tag
        // if input contains a html tag it will cause issues (#6207)
        if ($inline) {
            $options |= \JSON_HEX_TAG;
        }

        if ($pretty) {
            $options |= \JSON_PRETTY_PRINT;
        }

        return json_encode($input, $options);
    }

    /**
     * A wrapper for header() function, so it can be replaced for automated tests
     *
     * @param string $header  The header string
     * @param bool   $replace Replace previously set header?
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
     * @return never
     */
    public function sendExit($body = '', $headers = [])
    {
        foreach ($headers as $header) {
            $this->header($header);
        }

        echo $body;
        exit;
    }

    /**
     * A helper to send HTTP error code and message to the browser, and exit.
     *
     * @param int    $code    The HTTP error code
     * @param string $message The HTTP error message
     *
     * @return never
     */
    public function sendExitError($code, $message = '')
    {
        http_response_code($code);
        exit($message);
    }
}

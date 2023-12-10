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
 |   Provide SMTP functionality using socket connections                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 |         Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to provide SMTP functionality using PEAR Net_SMTP
 *
 * @package    Framework
 * @subpackage Mail
 */
class rcube_smtp
{
    private $conn;
    private $response;
    private $error;
    private $anonymize_log = 0;

    // define headers delimiter
    const SMTP_MIME_CRLF = "\r\n";

    const DEBUG_LINE_LENGTH = 4098; // 4KB + 2B for \r\n


    /**
     * SMTP Connection and authentication
     *
     * @param string $host Server host
     * @param string $port Server port
     * @param string $user User name
     * @param string $pass Password
     *
     * @return bool True on success, or False on error
     */
    public function connect($host = null, $port = null, $user = null, $pass = null)
    {
        $rcube = rcube::get_instance();

        // disconnect/destroy $this->conn
        $this->disconnect();

        // reset error/response var
        $this->error = $this->response = null;

        if (!$host) {
            $host = $rcube->config->get('smtp_host', 'localhost:587');
            if (is_array($host)) {
                if (array_key_exists($_SESSION['storage_host'], $host)) {
                    $host = $host[$_SESSION['storage_host']];
                }
                else {
                    $this->response[] = "Connection failed: No SMTP server found for IMAP host " . $_SESSION['storage_host'];
                    $this->error = ['label' => 'smtpconnerror', 'vars' => ['code' => '500']];
                    return false;
                }
            }
        }
        else if (!empty($port) && !empty($host) && !preg_match('/:\d+$/', $host)) {
            $host = "{$host}:{$port}";
        }

        $host = rcube_utils::parse_host($host);

        // let plugins alter smtp connection config
        $CONFIG = $rcube->plugins->exec_hook('smtp_connect', [
            'smtp_host'      => $host,
            'smtp_user'      => $user !== null ? $user : $rcube->config->get('smtp_user', '%u'),
            'smtp_pass'      => $pass !== null ? $pass : $rcube->config->get('smtp_pass', '%p'),
            'smtp_auth_cid'  => $rcube->config->get('smtp_auth_cid'),
            'smtp_auth_pw'   => $rcube->config->get('smtp_auth_pw'),
            'smtp_auth_type' => $rcube->config->get('smtp_auth_type'),
            'smtp_helo_host' => $rcube->config->get('smtp_helo_host'),
            'smtp_timeout'   => $rcube->config->get('smtp_timeout'),
            'smtp_conn_options'   => $rcube->config->get('smtp_conn_options'),
            'smtp_auth_callbacks' => [],
            'gssapi_context'      => null,
            'gssapi_cn'           => null,
        ]);

        $smtp_host = $CONFIG['smtp_host'] ?: 'localhost';

        list($smtp_host, $scheme, $smtp_port) = rcube_utils::parse_host_uri($smtp_host, 587, 465);

        $use_tls = $scheme === 'tls';

        // re-add the ssl:// prefix
        if ($scheme === 'ssl') {
            $smtp_host = "ssl://{$smtp_host}";
        }

        // Handle per-host socket options
        rcube_utils::parse_socket_options($CONFIG['smtp_conn_options'], $smtp_host);

        // Use valid EHLO/HELO host (#6408)
        $helo_host = $CONFIG['smtp_helo_host'] ?: rcube_utils::server_name();
        $helo_host = rcube_utils::idn_to_ascii($helo_host);
        if (!preg_match('/^[a-zA-Z0-9.:-]+$/', $helo_host)) {
            $helo_host = 'localhost';
        }

        // IDNA Support
        $smtp_host = rcube_utils::idn_to_ascii($smtp_host);

        $this->conn = new Net_SMTP($smtp_host, $smtp_port, $helo_host, false, 0, $CONFIG['smtp_conn_options'],
            $CONFIG['gssapi_context'], $CONFIG['gssapi_cn']);

        if ($rcube->config->get('smtp_debug')) {
            $this->conn->setDebug(true, [$this, 'debug_handler']);
            $this->anonymize_log = 0;

            $_host = ($use_tls ? 'tls://' : '') . $smtp_host . ':' . $smtp_port;
            $this->debug_handler($this->conn, "Connecting to $_host...");
        }

        // register authentication methods
        if (!empty($CONFIG['smtp_auth_callbacks']) && method_exists($this->conn, 'setAuthMethod')) {
            foreach ($CONFIG['smtp_auth_callbacks'] as $callback) {
                $this->conn->setAuthMethod($callback['name'], $callback['function'],
                    $callback['prepend'] ?? true);
            }
        }

        // try to connect to server and exit on failure
        $result = $this->conn->connect($CONFIG['smtp_timeout']);

        if (is_a($result, 'PEAR_Error')) {
            $this->_conn_error('smtpconnerror', "Connection failed", [], $result);
            $this->conn = null;
            return false;
        }

        // workaround for timeout bug in Net_SMTP 1.5.[0-1] (#1487843)
        if (method_exists($this->conn, 'setTimeout')
            && ($timeout = ini_get('default_socket_timeout'))
        ) {
            $this->conn->setTimeout($timeout);
        }

        // XCLIENT extension
        $result = $this->_process_xclient($use_tls, $helo_host);

        if (is_a($result, 'PEAR_Error')) {
            $this->_conn_error('smtpconnerror', "XCLIENT failed", [], $result);
            $this->disconnect();
            return false;
        }

        if ($use_tls) {
            $result = $this->conn->starttls();

            if (is_a($result, 'PEAR_Error')) {
                $this->_conn_error('smtpconnerror', "STARTTLS failed", [], $result);
                $this->disconnect();
                return false;
            }
        }

        if ($CONFIG['smtp_pass'] == '%p') {
            $smtp_pass = (string) $rcube->get_user_password();
        } else {
            $smtp_pass = $CONFIG['smtp_pass'];
        }

        $smtp_user      = str_replace('%u', (string) $rcube->get_user_name(), $CONFIG['smtp_user']);
        $smtp_auth_type = $CONFIG['smtp_auth_type'] ?: null;
        $smtp_authz     = null;

        if (!empty($CONFIG['smtp_auth_cid'])) {
            $smtp_authz = $smtp_user;
            $smtp_user  = $CONFIG['smtp_auth_cid'];
            $smtp_pass  = $CONFIG['smtp_auth_pw'];
        }

        // attempt to authenticate to the SMTP server
        if (($smtp_user && $smtp_pass) || ($smtp_auth_type == 'GSSAPI')) {
            // IDNA Support
            if (strpos($smtp_user, '@')) {
                $smtp_user = rcube_utils::idn_to_ascii($smtp_user);
            }

            $result = $this->conn->auth($smtp_user, $smtp_pass, $smtp_auth_type, false, $smtp_authz);

            if (is_a($result, 'PEAR_Error')) {
                $this->_conn_error('smtpautherror', "Authentication failure", [], $result);
                $this->disconnect();
                return false;
            }
        }

        return true;
    }

    /**
     * Function for sending mail
     *
     * @param string $from       Sender e-Mail address
     *
     * @param mixed  $recipients Either a comma-separated list of recipients
     *                           (RFC822 compliant), or an array of recipients,
     *                           each RFC822 valid. This may contain recipients not
     *                           specified in the headers, for Bcc:, resending
     *                           messages, etc.
     * @param mixed  $headers    The message headers to send with the mail
     *                           Either as an associative array or a finally
     *                           formatted string
     * @param mixed  $body       The full text of the message body, including any Mime parts
     *                           or file handle
     * @param array  $opts       Delivery options (e.g. DSN request)
     *
     * @return bool True on success, or False on error
     */
    public function send_mail($from, $recipients, $headers, $body, $opts = [])
    {
        if (!is_object($this->conn)) {
            return false;
        }

        // prepare message headers as string
        $text_headers = null;
        if (is_array($headers)) {
            if (!($headerElements = $this->_prepare_headers($headers))) {
                $this->reset();
                return false;
            }

            list($from, $text_headers) = $headerElements;
        }
        else if (is_string($headers)) {
            $text_headers = $headers;
        }

        // exit if no from address is given
        if (!isset($from)) {
            $this->reset();
            $this->response[] = "No From address has been provided";
            return false;
        }

        // prepare list of recipients
        $recipients = $this->_parse_rfc822($recipients);
        if (is_a($recipients, 'PEAR_Error')) {
            $this->error = ['label' => 'smtprecipientserror'];
            $this->reset();
            return false;
        }

        $exts             = $this->conn->getServiceExtensions();
        $from_params      = null;
        $recipient_params = null;

        // RFC3461: Delivery Status Notification
        if (!empty($opts['dsn'])) {
            if (isset($exts['DSN'])) {
                $from_params      = 'RET=HDRS';
                $recipient_params = 'NOTIFY=SUCCESS,FAILURE';
            }
        }

        // RFC6531: request SMTPUTF8 if needed
        if (preg_match('/[^\x00-\x7F]/', $from . implode('', $recipients))) {
            if (isset($exts['SMTPUTF8'])) {
                $from_params = ltrim($from_params . ' SMTPUTF8');
            }
            else {
                $this->_conn_error('smtputf8error', "SMTP server does not support unicode in email addresses");
                $this->reset();
                return false;
            }
        }

        // RFC2298.3: remove envelope sender address
        if (empty($opts['mdn_use_from'])
            && preg_match('/Content-Type: multipart\/report/', $text_headers)
            && preg_match('/report-type=disposition-notification/', $text_headers)
        ) {
            $from = '';
        }

        // set From: address
        $result = $this->conn->mailFrom($from, $from_params);
        if (is_a($result, 'PEAR_Error')) {
            $this->_conn_error('smtpfromerror', "Failed to set sender '$from'", ['from' => $from]);
            $this->reset();
            return false;
        }

        // set mail recipients
        foreach ($recipients as $recipient) {
            $result = $this->conn->rcptTo($recipient, $recipient_params);
            if (is_a($result, 'PEAR_Error')) {
                $this->_conn_error('smtptoerror', "Failed to add recipient '$recipient'", ['to' => $recipient]);
                $this->reset();
                return false;
            }
        }

        if (is_resource($body)) {
            if ($text_headers) {
                $text_headers = preg_replace('/[\r\n]+$/', '', $text_headers);
            }
        }
        else {
            if ($text_headers) {
                $body = $text_headers . "\r\n" . $body;
            }

            $text_headers = null;
        }

        // Send the message's headers and the body as SMTP data.
        $result = $this->conn->data($body, $text_headers);
        if (is_a($result, 'PEAR_Error')) {
            $err       = $this->conn->getResponse();
            $err_label = 'smtperror';
            $err_vars  = [];

            if (!in_array($err[0], [354, 250, 221])) {
                $msg = sprintf('[%d] %s', $err[0], $err[1]);
            }
            else {
                $msg = $result->getMessage();

                if (strpos($msg, 'size exceeds')) {
                    $err_label = 'smtpsizeerror';
                    $exts      = $this->conn->getServiceExtensions();

                    if (!empty($exts['SIZE'])) {
                        $limit = $exts['SIZE'];
                        $msg .= " (Limit: $limit)";
                        if (class_exists('rcmail_action')) {
                            $limit = rcmail_action::show_bytes($limit);
                        }

                        $err_vars['limit'] = $limit;
                        $err_label         = 'smtpsizeerror';
                    }
                }
            }

            $err_vars['msg'] = $msg;

            $this->error = ['label' => $err_label, 'vars' => $err_vars];
            $this->response[] = "Failed to send data. " . $msg;
            $this->reset();
            return false;
        }

        $this->response[] = implode(': ', $this->conn->getResponse());
        return true;
    }

    /**
     * Reset the global SMTP connection
     */
    public function reset()
    {
        if (is_object($this->conn)) {
            $this->conn->rset();
        }
    }

    /**
     * Disconnect the global SMTP connection
     */
    public function disconnect()
    {
        if (is_object($this->conn)) {
            $this->conn->disconnect();
            $this->conn = null;
        }
    }

    /**
     * This is our own debug handler for the SMTP connection
     */
    public function debug_handler($smtp, $message)
    {
        // catch AUTH commands and set anonymization flag for subsequent sends
        if (preg_match('/^Send: AUTH ([A-Z]+)/', $message, $m)) {
            $this->anonymize_log = $m[1] == 'LOGIN' ? 2 : 1;
        }
        // anonymize this log entry
        else if ($this->anonymize_log > 0 && strpos($message, 'Send:') === 0 && --$this->anonymize_log == 0) {
            $message = sprintf('Send: ****** [%d]', strlen($message) - 8);
        }

        if (($len = strlen($message)) > self::DEBUG_LINE_LENGTH) {
            $diff    = $len - self::DEBUG_LINE_LENGTH;
            $message = substr($message, 0, self::DEBUG_LINE_LENGTH)
                . "... [truncated $diff bytes]";
        }

        rcube::write_log('smtp', preg_replace('/\r\n$/', '', $message));
    }

    /**
     * Get error message
     */
    public function get_error()
    {
        return $this->error;
    }

    /**
     * Get server response messages array
     */
    public function get_response()
    {
         return $this->response;
    }

    /**
     * Take an array of mail headers and return a string containing
     * text usable in sending a message.
     *
     * @param array $headers The array of headers to prepare, in an associative
     *              array, where the array key is the header name (ie,
     *              'Subject'), and the array value is the header
     *              value (ie, 'test'). The header produced from those
     *              values would be 'Subject: test'.
     *
     * @return mixed Returns false if it encounters a bad address,
     *               otherwise returns an array containing two
     *               elements: Any From: address found in the headers,
     *               and the plain text version of the headers.
     */
    private function _prepare_headers($headers)
    {
        $lines = [];
        $from  = null;

        foreach ($headers as $key => $value) {
            if (strcasecmp($key, 'From') === 0) {
                $addresses = $this->_parse_rfc822($value);

                if (is_array($addresses)) {
                    $from = $addresses[0];
                }

                // Reject envelope From: addresses with spaces.
                if (strpos($from, ' ') !== false) {
                    return false;
                }

                $lines[] = $key . ': ' . $value;
            }
            else if (strcasecmp($key, 'Received') === 0) {
                $received = [];
                if (is_array($value)) {
                    foreach ($value as $line) {
                        $received[] = $key . ': ' . $line;
                    }
                }
                else {
                    $received[] = $key . ': ' . $value;
                }

                // Put Received: headers at the top.  Spam detectors often
                // flag messages with Received: headers after the Subject:
                // as spam.
                $lines = array_merge($received, $lines);
            }
            else {
                // If $value is an array (i.e., a list of addresses), convert
                // it to a comma-delimited string of its elements (addresses).
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $lines[] = $key . ': ' . $value;
            }
        }

        return [$from, implode(self::SMTP_MIME_CRLF, $lines) . self::SMTP_MIME_CRLF];
    }

    /**
     * Take a set of recipients and parse them, returning an array of
     * bare addresses (forward paths) that can be passed to sendmail
     * or an smtp server with the rcpt to: command.
     *
     * @param mixed $recipients Either a comma-separated list of recipients
     *                          (RFC822 compliant), or an array of recipients,
     *                          each RFC822 valid.
     *
     * @return array An array of forward paths (bare addresses).
     */
    private function _parse_rfc822($recipients)
    {
        // if we're passed an array, assume addresses are valid and implode them before parsing.
        if (is_array($recipients)) {
            $recipients = implode(', ', $recipients);
        }

        $addresses  = [];
        $recipients = preg_replace('/[\s\t]*\r?\n/', '', $recipients);
        $recipients = rcube_utils::explode_quoted_string(',', $recipients);

        reset($recipients);
        foreach ($recipients as $recipient) {
            $a = rcube_utils::explode_quoted_string(' ', $recipient);
            foreach ($a as $word) {
                $word = trim($word);
                $len  = strlen($word);

                if ($len && strpos($word, "@") > 0 && $word[$len-1] != '"') {
                    $word = preg_replace('/^<|>$/', '', $word);
                    if (!in_array($word, $addresses)) {
                        array_push($addresses, $word);
                    }
                }
            }
        }

        return $addresses;
    }

    /**
     * Send XCLIENT command if configured and supported
     */
    private function _process_xclient($use_tls, $helo_host)
    {
        $rcube = rcube::get_instance();

        if (!is_object($this->conn)) {
            return false;
        }

        $exts = $this->conn->getServiceExtensions();

        if (!isset($exts['XCLIENT'])) {
            return true;
        }

        $opts = explode(' ', $exts['XCLIENT']);
        $cmd = '';

        if ($rcube->config->get('smtp_xclient_login') && in_array_nocase('login', $opts)) {
            $cmd .= " LOGIN=" . $rcube->get_user_name();
        }

        if ($rcube->config->get('smtp_xclient_addr') && in_array_nocase('addr', $opts)) {
            $ip = rcube_utils::remote_addr();

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $r = $ip;
            }
            elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $r = "IPV6:{$ip}";
            }
            else {
                $r = "[UNAVAILABLE]";
            }

            $cmd .= " ADDR={$r}";
        }

        if ($cmd) {
            $result = $this->conn->command("XCLIENT" . $cmd, [220]);

            if ($result !== true) {
                return $result;
            }

            if (!$use_tls) {
                return $this->conn->helo($helo_host);
            }
        }

        return true;
    }

    /**
     * Handle connection error
     */
    private function _conn_error($label, $message, $vars = [], $result = null)
    {
        $err = $this->conn->getResponse();

        $vars['code'] = $result ? $result->getCode() : $err[0];
        $vars['msg']  = $result ? $result->getMessage() : $err[1];

        $this->error = ['label' => $label, 'vars' => $vars];
        $this->response[] = "{$message}: {$err[1]} (Code: {$err[0]})";
    }
}

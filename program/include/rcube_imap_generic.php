<?php

/**
 +-----------------------------------------------------------------------+
 | program/include/rcube_imap_generic.php                                |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide alternative IMAP library that doesn't rely on the standard  |
 |   C-Client based version. This allows to function regardless          |
 |   of whether or not the PHP build it's running on has IMAP            |
 |   functionality built-in.                                             |
 |                                                                       |
 |   Based on Iloha IMAP Library. See http://ilohamail.org/ for details  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 | Author: Ryo Chijiiwa <Ryo@IlohaMail.org>                              |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Struct representing an e-mail message header
 *
 * @package Mail
 * @author  Aleksander Machniak <alec@alec.pl>
 */
class rcube_mail_header
{
    public $id;
    public $uid;
    public $subject;
    public $from;
    public $to;
    public $cc;
    public $replyto;
    public $in_reply_to;
    public $date;
    public $messageID;
    public $size;
    public $encoding;
    public $charset;
    public $ctype;
    public $flags;
    public $timestamp;
    public $body_structure;
    public $internaldate;
    public $references;
    public $priority;
    public $mdn_to;
    public $mdn_sent = false;
    public $is_draft = false;
    public $seen = false;
    public $deleted = false;
    public $recent = false;
    public $answered = false;
    public $forwarded = false;
    public $junk = false;
    public $flagged = false;
    public $has_children = false;
    public $depth = 0;
    public $unread_children = 0;
    public $others = array();
}

// For backward compatibility with cached messages (#1486602)
class iilBasicHeader extends rcube_mail_header
{
}

/**
 * PHP based wrapper class to connect to an IMAP server
 *
 * @package Mail
 * @author  Aleksander Machniak <alec@alec.pl>
 */
class rcube_imap_generic
{
    public $error;
    public $errornum;
    public $result;
    public $resultcode;
    public $data = array();
    public $flags = array(
        'SEEN'     => '\\Seen',
        'DELETED'  => '\\Deleted',
        'RECENT'   => '\\Recent',
        'ANSWERED' => '\\Answered',
        'DRAFT'    => '\\Draft',
        'FLAGGED'  => '\\Flagged',
        'FORWARDED' => '$Forwarded',
        'MDNSENT'  => '$MDNSent',
        '*'        => '\\*',
    );

    private $selected;
    private $fp;
    private $host;
    private $logged = false;
    private $capability = array();
    private $capability_readed = false;
    private $prefs;
    private $cmd_tag;
    private $cmd_num = 0;
    private $_debug = false;
    private $_debug_handler = false;

    const ERROR_OK = 0;
    const ERROR_NO = -1;
    const ERROR_BAD = -2;
    const ERROR_BYE = -3;
    const ERROR_UNKNOWN = -4;
    const ERROR_COMMAND = -5;
    const ERROR_READONLY = -6;

    const COMMAND_NORESPONSE = 1;
    const COMMAND_CAPABILITY = 2;
    const COMMAND_LASTLINE   = 4;

    /**
     * Object constructor
     */
    function __construct()
    {
    }

    /**
     * Send simple (one line) command to the connection stream
     *
     * @param string $string Command string
     * @param bool   $endln  True if CRLF need to be added at the end of command
     *
     * @param int Number of bytes sent, False on error
     */
    function putLine($string, $endln=true)
    {
        if (!$this->fp)
            return false;

        if ($this->_debug) {
            $this->debug('C: '. rtrim($string));
        }

        $res = fwrite($this->fp, $string . ($endln ? "\r\n" : ''));

        if ($res === false) {
            @fclose($this->fp);
            $this->fp = null;
        }

        return $res;
    }

    /**
     * Send command to the connection stream with Command Continuation
     * Requests (RFC3501 7.5) and LITERAL+ (RFC2088) support
     *
     * @param string $string Command string
     * @param bool   $endln  True if CRLF need to be added at the end of command
     *
     * @param int Number of bytes sent, False on error
     */
    function putLineC($string, $endln=true)
    {
        if (!$this->fp)
            return false;

        if ($endln)
            $string .= "\r\n";

        $res = 0;
        if ($parts = preg_split('/(\{[0-9]+\}\r\n)/m', $string, -1, PREG_SPLIT_DELIM_CAPTURE)) {
            for ($i=0, $cnt=count($parts); $i<$cnt; $i++) {
                if (preg_match('/^\{[0-9]+\}\r\n$/', $parts[$i+1])) {
                    // LITERAL+ support
                    if ($this->prefs['literal+'])
                        $parts[$i+1] = preg_replace('/([0-9]+)/', '\\1+', $parts[$i+1]);

                    $bytes = $this->putLine($parts[$i].$parts[$i+1], false);
                    if ($bytes === false)
                        return false;
                    $res += $bytes;

                    // don't wait if server supports LITERAL+ capability
                    if (!$this->prefs['literal+']) {
                        $line = $this->readLine(1000);
                        // handle error in command
                        if ($line[0] != '+')
                            return false;
                    }
                    $i++;
                }
                else {
                    $bytes = $this->putLine($parts[$i], false);
                    if ($bytes === false)
                        return false;
                    $res += $bytes;
                }
            }
        }

        return $res;
    }

    function readLine($size=1024)
    {
        $line = '';

        if (!$this->fp) {
            return NULL;
        }

        if (!$size) {
            $size = 1024;
        }

        do {
            if (feof($this->fp)) {
                return $line ? $line : NULL;
            }

            $buffer = fgets($this->fp, $size);

            if ($buffer === false) {
                @fclose($this->fp);
                $this->fp = null;
                break;
            }
            if ($this->_debug) {
                $this->debug('S: '. rtrim($buffer));
            }
            $line .= $buffer;
        } while ($buffer[strlen($buffer)-1] != "\n");

        return $line;
    }

    function multLine($line, $escape=false)
    {
        $line = rtrim($line);
        if (preg_match('/\{[0-9]+\}$/', $line)) {
            $out = '';

            preg_match_all('/(.*)\{([0-9]+)\}$/', $line, $a);
            $bytes = $a[2][0];
            while (strlen($out) < $bytes) {
                $line = $this->readBytes($bytes);
                if ($line === NULL)
                    break;
                $out .= $line;
            }

            $line = $a[1][0] . ($escape ? $this->escape($out) : $out);
        }

        return $line;
    }

    function readBytes($bytes)
    {
        $data = '';
        $len  = 0;
        while ($len < $bytes && !feof($this->fp))
        {
            $d = fread($this->fp, $bytes-$len);
            if ($this->_debug) {
                $this->debug('S: '. $d);
            }
            $data .= $d;
            $data_len = strlen($data);
            if ($len == $data_len) {
                break; // nothing was read -> exit to avoid apache lockups
            }
            $len = $data_len;
        }

        return $data;
    }

    function readReply(&$untagged=null)
    {
        do {
            $line = trim($this->readLine(1024));
            // store untagged response lines
            if ($line[0] == '*')
                $untagged[] = $line;
        } while ($line[0] == '*');

        if ($untagged)
            $untagged = join("\n", $untagged);

        return $line;
    }

    function parseResult($string, $err_prefix='')
    {
        if (preg_match('/^[a-z0-9*]+ (OK|NO|BAD|BYE)(.*)$/i', trim($string), $matches)) {
            $res = strtoupper($matches[1]);
            $str = trim($matches[2]);

            if ($res == 'OK') {
                $this->errornum = self::ERROR_OK;
            } else if ($res == 'NO') {
                $this->errornum = self::ERROR_NO;
            } else if ($res == 'BAD') {
                $this->errornum = self::ERROR_BAD;
            } else if ($res == 'BYE') {
                @fclose($this->fp);
                $this->fp = null;
                $this->errornum = self::ERROR_BYE;
            }

            if ($str) {
                $str = trim($str);
                // get response string and code (RFC5530)
                if (preg_match("/^\[([a-z-]+)\]/i", $str, $m)) {
                    $this->resultcode = strtoupper($m[1]);
                    $str = trim(substr($str, strlen($m[1]) + 2));
                }
                else {
                    $this->resultcode = null;
                }
                $this->result = $str;

                if ($this->errornum != self::ERROR_OK) {
                    $this->error = $err_prefix ? $err_prefix.$str : $str;
                }
            }

            return $this->errornum;
        }
        return self::ERROR_UNKNOWN;
    }

    function setError($code, $msg='')
    {
        $this->errornum = $code;
        $this->error    = $msg;
    }

    // check if $string starts with $match (or * BYE/BAD)
    function startsWith($string, $match, $error=false, $nonempty=false)
    {
        $len = strlen($match);
        if ($len == 0) {
            return false;
        }
        if (!$this->fp) {
            return true;
        }
        if (strncmp($string, $match, $len) == 0) {
            return true;
        }
        if ($error && preg_match('/^\* (BYE|BAD) /i', $string, $m)) {
            if (strtoupper($m[1]) == 'BYE') {
                @fclose($this->fp);
                $this->fp = null;
            }
            return true;
        }
        if ($nonempty && !strlen($string)) {
            return true;
        }
        return false;
    }

    private function hasCapability($name)
    {
        if (empty($this->capability) || $name == '') {
            return false;
        }

        if (in_array($name, $this->capability)) {
            return true;
        }
        else if (strpos($name, '=')) {
            return false;
        }

        $result = array();
        foreach ($this->capability as $cap) {
            $entry = explode('=', $cap);
            if ($entry[0] == $name) {
                $result[] = $entry[1];
            }
        }

        return !empty($result) ? $result : false;
    }

    /**
     * Capabilities checker
     *
     * @param string $name Capability name
     *
     * @return mixed Capability values array for key=value pairs, true/false for others
     */
    function getCapability($name)
    {
        $result = $this->hasCapability($name);

        if (!empty($result)) {
            return $result;
        }
        else if ($this->capability_readed) {
            return false;
        }

        // get capabilities (only once) because initial
        // optional CAPABILITY response may differ
        $result = $this->execute('CAPABILITY');

        if ($result[0] == self::ERROR_OK) {
            $this->parseCapability($result[1]);
        }

        $this->capability_readed = true;

        return $this->hasCapability($name);
    }

    function clearCapability()
    {
        $this->capability = array();
        $this->capability_readed = false;
    }

    /**
     * DIGEST-MD5/CRAM-MD5/PLAIN Authentication
     *
     * @param string $user
     * @param string $pass
     * @param string $type Authentication type (PLAIN/CRAM-MD5/DIGEST-MD5)
     *
     * @return resource Connection resourse on success, error code on error
     */
    function authenticate($user, $pass, $type='PLAIN')
    {
        if ($type == 'CRAM-MD5' || $type == 'DIGEST-MD5') {
            if ($type == 'DIGEST-MD5' && !class_exists('Auth_SASL')) {
                $this->setError(self::ERROR_BYE,
                    "The Auth_SASL package is required for DIGEST-MD5 authentication");
                return self::ERROR_BAD;
            }

            $this->putLine($this->nextTag() . " AUTHENTICATE $type");
            $line = trim($this->readReply());

            if ($line[0] == '+') {
                $challenge = substr($line, 2);
            }
            else {
                return $this->parseResult($line);
            }

            if ($type == 'CRAM-MD5') {
                // RFC2195: CRAM-MD5
                $ipad = '';
                $opad = '';

                // initialize ipad, opad
                for ($i=0; $i<64; $i++) {
                    $ipad .= chr(0x36);
                    $opad .= chr(0x5C);
                }

                // pad $pass so it's 64 bytes
                $padLen = 64 - strlen($pass);
                for ($i=0; $i<$padLen; $i++) {
                    $pass .= chr(0);
                }

                // generate hash
                $hash  = md5($this->_xor($pass, $opad) . pack("H*",
                    md5($this->_xor($pass, $ipad) . base64_decode($challenge))));
                $reply = base64_encode($user . ' ' . $hash);

                // send result
                $this->putLine($reply);
            }
            else {
                // RFC2831: DIGEST-MD5
                // proxy authorization
                if (!empty($this->prefs['auth_cid'])) {
                    $authc = $this->prefs['auth_cid'];
                    $pass  = $this->prefs['auth_pw'];
                }
                else {
                    $authc = $user;
                }
                $auth_sasl = Auth_SASL::factory('digestmd5');
                $reply = base64_encode($auth_sasl->getResponse($authc, $pass,
                    base64_decode($challenge), $this->host, 'imap', $user));

                // send result
                $this->putLine($reply);
                $line = trim($this->readReply());

                if ($line[0] == '+') {
                    $challenge = substr($line, 2);
                }
                else {
                    return $this->parseResult($line);
                }

                // check response
                $challenge = base64_decode($challenge);
                if (strpos($challenge, 'rspauth=') === false) {
                    $this->setError(self::ERROR_BAD,
                        "Unexpected response from server to DIGEST-MD5 response");
                    return self::ERROR_BAD;
                }

                $this->putLine('');
            }

            $line = $this->readReply();
            $result = $this->parseResult($line);
        }
        else { // PLAIN
            // proxy authorization
            if (!empty($this->prefs['auth_cid'])) {
                $authc = $this->prefs['auth_cid'];
                $pass  = $this->prefs['auth_pw'];
            }
            else {
                $authc = $user;
            }

            $reply = base64_encode($user . chr(0) . $authc . chr(0) . $pass);

            // RFC 4959 (SASL-IR): save one round trip
            if ($this->getCapability('SASL-IR')) {
                list($result, $line) = $this->execute("AUTHENTICATE PLAIN", array($reply),
                    self::COMMAND_LASTLINE | self::COMMAND_CAPABILITY);
            }
            else {
                $this->putLine($this->nextTag() . " AUTHENTICATE PLAIN");
                $line = trim($this->readReply());

                if ($line[0] != '+') {
                    return $this->parseResult($line);
                }

                // send result, get reply and process it
                $this->putLine($reply);
                $line = $this->readReply();
                $result = $this->parseResult($line);
            }
        }

        if ($result == self::ERROR_OK) {
            // optional CAPABILITY response
            if ($line && preg_match('/\[CAPABILITY ([^]]+)\]/i', $line, $matches)) {
                $this->parseCapability($matches[1], true);
            }
            return $this->fp;
        }
        else {
            $this->setError($result, "AUTHENTICATE $type: $line");
        }

        return $result;
    }

    /**
     * LOGIN Authentication
     *
     * @param string $user
     * @param string $pass
     *
     * @return resource Connection resourse on success, error code on error
     */
    function login($user, $password)
    {
        list($code, $response) = $this->execute('LOGIN', array(
            $this->escape($user), $this->escape($password)), self::COMMAND_CAPABILITY);

        // re-set capabilities list if untagged CAPABILITY response provided
        if (preg_match('/\* CAPABILITY (.+)/i', $response, $matches)) {
            $this->parseCapability($matches[1], true);
        }

        if ($code == self::ERROR_OK) {
            return $this->fp;
        }

        return $code;
    }

    /**
     * Gets the delimiter
     *
     * @return string The delimiter
     */
    function getHierarchyDelimiter()
    {
        if ($this->prefs['delimiter']) {
            return $this->prefs['delimiter'];
        }

        // try (LIST "" ""), should return delimiter (RFC2060 Sec 6.3.8)
        list($code, $response) = $this->execute('LIST',
            array($this->escape(''), $this->escape('')));

        if ($code == self::ERROR_OK) {
            $args = $this->tokenizeResponse($response, 4);
            $delimiter = $args[3];

            if (strlen($delimiter) > 0) {
                return ($this->prefs['delimiter'] = $delimiter);
            }
        }

        return NULL;
    }

    /**
     * NAMESPACE handler (RFC 2342)
     *
     * @return array Namespace data hash (personal, other, shared)
     */
    function getNamespace()
    {
        if (array_key_exists('namespace', $this->prefs)) {
            return $this->prefs['namespace'];
        }

        if (!$this->getCapability('NAMESPACE')) {
            return self::ERROR_BAD;
        }

        list($code, $response) = $this->execute('NAMESPACE');

        if ($code == self::ERROR_OK && preg_match('/^\* NAMESPACE /', $response)) {
            $data = $this->tokenizeResponse(substr($response, 11));
        }

        if (!is_array($data)) {
            return $code;
        }

        $this->prefs['namespace'] = array(
            'personal' => $data[0],
            'other'    => $data[1],
            'shared'   => $data[2],
        );

        return $this->prefs['namespace'];
    }

    function connect($host, $user, $password, $options=null)
    {
        // set options
        if (is_array($options)) {
            $this->prefs = $options;
        }
        // set auth method
        if (!empty($this->prefs['auth_method'])) {
            $auth_method = strtoupper($this->prefs['auth_method']);
        } else {
            $auth_method = 'CHECK';
        }

        $result = false;

        // initialize connection
        $this->error    = '';
        $this->errornum = self::ERROR_OK;
        $this->selected = '';
        $this->user     = $user;
        $this->host     = $host;
        $this->logged   = false;

        // check input
        if (empty($host)) {
            $this->setError(self::ERROR_BAD, "Empty host");
            return false;
        }
        if (empty($user)) {
            $this->setError(self::ERROR_NO, "Empty user");
            return false;
        }
        if (empty($password)) {
            $this->setError(self::ERROR_NO, "Empty password");
            return false;
        }

        if (!$this->prefs['port']) {
            $this->prefs['port'] = 143;
        }
        // check for SSL
        if ($this->prefs['ssl_mode'] && $this->prefs['ssl_mode'] != 'tls') {
            $host = $this->prefs['ssl_mode'] . '://' . $host;
        }

        // Connect
        if ($this->prefs['timeout'] > 0)
            $this->fp = @fsockopen($host, $this->prefs['port'], $errno, $errstr, $this->prefs['timeout']);
        else
            $this->fp = @fsockopen($host, $this->prefs['port'], $errno, $errstr);

        if (!$this->fp) {
            $this->setError(self::ERROR_BAD, sprintf("Could not connect to %s:%d: %s", $host, $this->prefs['port'], $errstr));
            return false;
        }

        if ($this->prefs['timeout'] > 0)
            stream_set_timeout($this->fp, $this->prefs['timeout']);

        $line = trim(fgets($this->fp, 8192));

        if ($this->_debug && $line) {
            $this->debug('S: '. $line);
        }

        // Connected to wrong port or connection error?
        if (!preg_match('/^\* (OK|PREAUTH)/i', $line)) {
            if ($line)
                $error = sprintf("Wrong startup greeting (%s:%d): %s", $host, $this->prefs['port'], $line);
            else
                $error = sprintf("Empty startup greeting (%s:%d)", $host, $this->prefs['port']);

            $this->setError(self::ERROR_BAD, $error);
            $this->closeConnection();
            return false;
        }

        // RFC3501 [7.1] optional CAPABILITY response
        if (preg_match('/\[CAPABILITY ([^]]+)\]/i', $line, $matches)) {
            $this->parseCapability($matches[1], true);
        }

        // TLS connection
        if ($this->prefs['ssl_mode'] == 'tls' && $this->getCapability('STARTTLS')) {
            if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
                $res = $this->execute('STARTTLS');

                if ($res[0] != self::ERROR_OK) {
                    $this->closeConnection();
                    return false;
                }

                if (!stream_socket_enable_crypto($this->fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    $this->setError(self::ERROR_BAD, "Unable to negotiate TLS");
                    $this->closeConnection();
                    return false;
                }

                // Now we're secure, capabilities need to be reread
                $this->clearCapability();
            }
        }

        $auth_methods = array();
        $result       = null;

        // check for supported auth methods
        if ($auth_method == 'CHECK') {
            if ($auth_caps = $this->getCapability('AUTH')) {
                $auth_methods = $auth_caps;
            }
            // RFC 2595 (LOGINDISABLED) LOGIN disabled when connection is not secure
            $login_disabled = $this->getCapability('LOGINDISABLED');
            if (($key = array_search('LOGIN', $auth_methods)) !== false) {
                if ($login_disabled) {
                    unset($auth_methods[$key]);
                }
            }
            else if (!$login_disabled) {
                $auth_methods[] = 'LOGIN';
            }
        }
        else {
            // Prevent from sending credentials in plain text when connection is not secure
            if ($auth_method == 'LOGIN' && $this->getCapability('LOGINDISABLED')) {
                $this->setError(self::ERROR_BAD, "Login disabled by IMAP server");
                $this->closeConnection();
                return false;
            }
            // replace AUTH with CRAM-MD5 for backward compat.
            $auth_methods[] = $auth_method == 'AUTH' ? 'CRAM-MD5' : $auth_method;
        }

        // pre-login capabilities can be not complete
        $this->capability_readed = false;

        // Authenticate
        foreach ($auth_methods as $method) {
            switch ($method) {
            case 'CRAM_MD5':
                $method = 'CRAM-MD5';
            case 'CRAM-MD5':
            case 'DIGEST-MD5':
            case 'PLAIN':
                $result = $this->authenticate($user, $password, $method);
                break;
            case 'LOGIN':
                $result = $this->login($user, $password);
                break;
            default:
                $this->setError(self::ERROR_BAD, "Configuration error. Unknown auth method: $method");
            }

            if (is_resource($result)) {
                break;
            }
        }

        // Connected and authenticated
        if (is_resource($result)) {
            if ($this->prefs['force_caps']) {
                $this->clearCapability();
            }
            $this->logged = true;

            return true;
        }

        $this->closeConnection();

        return false;
    }

    function connected()
    {
        return ($this->fp && $this->logged) ? true : false;
    }

    function closeConnection()
    {
        if ($this->putLine($this->nextTag() . ' LOGOUT')) {
            $this->readReply();
        }

        @fclose($this->fp);
        $this->fp = false;
    }

    /**
     * Executes SELECT command (if mailbox is already not in selected state)
     *
     * @param string $mailbox Mailbox name
     *
     * @return boolean True on success, false on error
     * @access public
     */
    function select($mailbox)
    {
        if (!strlen($mailbox)) {
            return false;
        }

        if ($this->selected == $mailbox) {
            return true;
        }
/*
    Temporary commented out because Courier returns \Noselect for INBOX
    Requires more investigation

        if (is_array($this->data['LIST']) && is_array($opts = $this->data['LIST'][$mailbox])) {
            if (in_array('\\Noselect', $opts)) {
                return false;
            }
        }
*/
        list($code, $response) = $this->execute('SELECT', array($this->escape($mailbox)));

        if ($code == self::ERROR_OK) {
            $response = explode("\r\n", $response);
            foreach ($response as $line) {
                if (preg_match('/^\* ([0-9]+) (EXISTS|RECENT)$/i', $line, $m)) {
                    $this->data[strtoupper($m[2])] = (int) $m[1];
                }
                else if (preg_match('/^\* OK \[(UIDNEXT|UIDVALIDITY|UNSEEN) ([0-9]+)\]/i', $line, $match)) {
                    $this->data[strtoupper($match[1])] = (int) $match[2];
                }
                else if (preg_match('/^\* OK \[PERMANENTFLAGS \(([^\)]+)\)\]/iU', $line, $match)) {
                    $this->data['PERMANENTFLAGS'] = explode(' ', $match[1]);
                }
            }

            $this->data['READ-WRITE'] = $this->resultcode != 'READ-ONLY';

            $this->selected = $mailbox;
            return true;
        }

        return false;
    }

    /**
     * Executes STATUS command
     *
     * @param string $mailbox Mailbox name
     * @param array  $items   Additional requested item names. By default
     *                        MESSAGES and UNSEEN are requested. Other defined
     *                        in RFC3501: UIDNEXT, UIDVALIDITY, RECENT
     *
     * @return array Status item-value hash
     * @access public
     * @since 0.5-beta
     */
    function status($mailbox, $items=array())
    {
        if (!strlen($mailbox)) {
            return false;
        }

        if (!in_array('MESSAGES', $items)) {
            $items[] = 'MESSAGES';
        }
        if (!in_array('UNSEEN', $items)) {
            $items[] = 'UNSEEN';
        }

        list($code, $response) = $this->execute('STATUS', array($this->escape($mailbox),
            '(' . implode(' ', (array) $items) . ')'));

        if ($code == self::ERROR_OK && preg_match('/\* STATUS /i', $response)) {
            $result   = array();
            $response = substr($response, 9); // remove prefix "* STATUS "

            list($mbox, $items) = $this->tokenizeResponse($response, 2);

            for ($i=0, $len=count($items); $i<$len; $i += 2) {
                $result[$items[$i]] = (int) $items[$i+1];
            }

            $this->data['STATUS:'.$mailbox] = $result;

            return $result;
        }

        return false;
    }

    /**
     * Executes EXPUNGE command
     *
     * @param string $mailbox  Mailbox name
     * @param string $messages Message UIDs to expunge
     *
     * @return boolean True on success, False on error
     * @access public
     */
    function expunge($mailbox, $messages=NULL)
    {
        if (!$this->select($mailbox)) {
            return false;
        }

        if (!$this->data['READ-WRITE']) {
            $this->setError(self::ERROR_READONLY, "Mailbox is read-only", 'EXPUNGE');
            return false;
        }

        // Clear internal status cache
        unset($this->data['STATUS:'.$mailbox]);

        if ($messages)
            $result = $this->execute('UID EXPUNGE', array($messages), self::COMMAND_NORESPONSE);
        else
            $result = $this->execute('EXPUNGE', null, self::COMMAND_NORESPONSE);

        if ($result == self::ERROR_OK) {
            $this->selected = ''; // state has changed, need to reselect
            return true;
        }

        return false;
    }

    /**
     * Executes CLOSE command
     *
     * @return boolean True on success, False on error
     * @access public
     * @since 0.5
     */
    function close()
    {
        $result = $this->execute('CLOSE', NULL, self::COMMAND_NORESPONSE);

        if ($result == self::ERROR_OK) {
            $this->selected = '';
            return true;
        }

        return false;
    }

    /**
     * Executes SUBSCRIBE command
     *
     * @param string $mailbox Mailbox name
     *
     * @return boolean True on success, False on error
     * @access public
     */
    function subscribe($mailbox)
    {
        $result = $this->execute('SUBSCRIBE', array($this->escape($mailbox)),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    /**
     * Executes UNSUBSCRIBE command
     *
     * @param string $mailbox Mailbox name
     *
     * @return boolean True on success, False on error
     * @access public
     */
    function unsubscribe($mailbox)
    {
        $result = $this->execute('UNSUBSCRIBE', array($this->escape($mailbox)),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    /**
     * Executes DELETE command
     *
     * @param string $mailbox Mailbox name
     *
     * @return boolean True on success, False on error
     * @access public
     */
    function deleteFolder($mailbox)
    {
        $result = $this->execute('DELETE', array($this->escape($mailbox)),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    /**
     * Removes all messages in a folder
     *
     * @param string $mailbox Mailbox name
     *
     * @return boolean True on success, False on error
     * @access public
     */
    function clearFolder($mailbox)
    {
        $num_in_trash = $this->countMessages($mailbox);
        if ($num_in_trash > 0) {
            $res = $this->delete($mailbox, '1:*');
        }

        if ($res) {
            if ($this->selected == $mailbox)
                $res = $this->close();
            else
                $res = $this->expunge($mailbox);
        }

        return $res;
    }

    /**
     * Returns count of all messages in a folder
     *
     * @param string $mailbox Mailbox name
     *
     * @return int Number of messages, False on error
     * @access public
     */
    function countMessages($mailbox, $refresh = false)
    {
        if ($refresh) {
            $this->selected = '';
        }

        if ($this->selected == $mailbox) {
            return $this->data['EXISTS'];
        }

        // Check internal cache
        $cache = $this->data['STATUS:'.$mailbox];
        if (!empty($cache) && isset($cache['MESSAGES'])) {
            return (int) $cache['MESSAGES'];
        }

        // Try STATUS (should be faster than SELECT)
        $counts = $this->status($mailbox);
        if (is_array($counts)) {
            return (int) $counts['MESSAGES'];
        }

        return false;
    }

    /**
     * Returns count of messages with \Recent flag in a folder
     *
     * @param string $mailbox Mailbox name
     *
     * @return int Number of messages, False on error
     * @access public
     */
    function countRecent($mailbox)
    {
        if (!strlen($mailbox)) {
            $mailbox = 'INBOX';
        }

        $this->select($mailbox);

        if ($this->selected == $mailbox) {
            return $this->data['RECENT'];
        }

        return false;
    }

    /**
     * Returns count of messages without \Seen flag in a specified folder
     *
     * @param string $mailbox Mailbox name
     *
     * @return int Number of messages, False on error
     * @access public
     */
    function countUnseen($mailbox)
    {
        // Check internal cache
        $cache = $this->data['STATUS:'.$mailbox];
        if (!empty($cache) && isset($cache['UNSEEN'])) {
            return (int) $cache['UNSEEN'];
        }

        // Try STATUS (should be faster than SELECT+SEARCH)
        $counts = $this->status($mailbox);
        if (is_array($counts)) {
            return (int) $counts['UNSEEN'];
        }

        // Invoke SEARCH as a fallback
        $index = $this->search($mailbox, 'ALL UNSEEN', false, array('COUNT'));
        if (is_array($index)) {
            return (int) $index['COUNT'];
        }

        return false;
    }

    function sort($mailbox, $field, $add='', $is_uid=FALSE, $encoding = 'US-ASCII')
    {
        $field = strtoupper($field);
        if ($field == 'INTERNALDATE') {
            $field = 'ARRIVAL';
        }

        $fields = array('ARRIVAL' => 1,'CC' => 1,'DATE' => 1,
            'FROM' => 1, 'SIZE' => 1, 'SUBJECT' => 1, 'TO' => 1);

        if (!$fields[$field]) {
            return false;
        }

        if (!$this->select($mailbox)) {
            return false;
        }

        // message IDs
        if (!empty($add))
            $add = $this->compressMessageSet($add);

        list($code, $response) = $this->execute($is_uid ? 'UID SORT' : 'SORT',
            array("($field)", $encoding, 'ALL' . (!empty($add) ? ' '.$add : '')));

        if ($code == self::ERROR_OK) {
            // remove prefix and unilateral untagged server responses
            $response = substr($response, stripos($response, '* SORT') + 7);
            if ($pos = strpos($response, '*')) {
                $response = substr($response, 0, $pos);
            }
            return preg_split('/[\s\r\n]+/', $response, -1, PREG_SPLIT_NO_EMPTY);
        }

        return false;
    }

    function fetchHeaderIndex($mailbox, $message_set, $index_field='', $skip_deleted=true, $uidfetch=false)
    {
        if (is_array($message_set)) {
            if (!($message_set = $this->compressMessageSet($message_set)))
                return false;
        } else {
            list($from_idx, $to_idx) = explode(':', $message_set);
            if (empty($message_set) ||
                (isset($to_idx) && $to_idx != '*' && (int)$from_idx > (int)$to_idx)) {
                return false;
            }
        }

        $index_field = empty($index_field) ? 'DATE' : strtoupper($index_field);

        $fields_a['DATE']         = 1;
        $fields_a['INTERNALDATE'] = 4;
        $fields_a['ARRIVAL']      = 4;
        $fields_a['FROM']         = 1;
        $fields_a['REPLY-TO']     = 1;
        $fields_a['SENDER']       = 1;
        $fields_a['TO']           = 1;
        $fields_a['CC']           = 1;
        $fields_a['SUBJECT']      = 1;
        $fields_a['UID']          = 2;
        $fields_a['SIZE']         = 2;
        $fields_a['SEEN']         = 3;
        $fields_a['RECENT']       = 3;
        $fields_a['DELETED']      = 3;

        if (!($mode = $fields_a[$index_field])) {
            return false;
        }

        /*  Do "SELECT" command */
        if (!$this->select($mailbox)) {
            return false;
        }

        // build FETCH command string
        $key     = $this->nextTag();
        $cmd     = $uidfetch ? 'UID FETCH' : 'FETCH';
        $deleted = $skip_deleted ? ' FLAGS' : '';

        if ($mode == 1 && $index_field == 'DATE')
            $request = " $cmd $message_set (INTERNALDATE BODY.PEEK[HEADER.FIELDS (DATE)]$deleted)";
        else if ($mode == 1)
            $request = " $cmd $message_set (BODY.PEEK[HEADER.FIELDS ($index_field)]$deleted)";
        else if ($mode == 2) {
            if ($index_field == 'SIZE')
                $request = " $cmd $message_set (RFC822.SIZE$deleted)";
            else
                $request = " $cmd $message_set ($index_field$deleted)";
        } else if ($mode == 3)
            $request = " $cmd $message_set (FLAGS)";
        else // 4
            $request = " $cmd $message_set (INTERNALDATE$deleted)";

        $request = $key . $request;

        if (!$this->putLine($request)) {
            $this->setError(self::ERROR_COMMAND, "Unable to send command: $request");
            return false;
        }

        $result = array();

        do {
            $line = rtrim($this->readLine(200));
            $line = $this->multLine($line);

            if (preg_match('/^\* ([0-9]+) FETCH/', $line, $m)) {
                $id     = $m[1];
                $flags  = NULL;

                if ($skip_deleted && preg_match('/FLAGS \(([^)]+)\)/', $line, $matches)) {
                    $flags = explode(' ', strtoupper($matches[1]));
                    if (in_array('\\DELETED', $flags)) {
                        $deleted[$id] = $id;
                        continue;
                    }
                }

                if ($mode == 1 && $index_field == 'DATE') {
                    if (preg_match('/BODY\[HEADER\.FIELDS \("*DATE"*\)\] (.*)/', $line, $matches)) {
                        $value = preg_replace(array('/^"*[a-z]+:/i'), '', $matches[1]);
                        $value = trim($value);
                        $result[$id] = $this->strToTime($value);
                    }
                    // non-existent/empty Date: header, use INTERNALDATE
                    if (empty($result[$id])) {
                        if (preg_match('/INTERNALDATE "([^"]+)"/', $line, $matches))
                            $result[$id] = $this->strToTime($matches[1]);
                        else
                            $result[$id] = 0;
                    }
                } else if ($mode == 1) {
                    if (preg_match('/BODY\[HEADER\.FIELDS \("?(FROM|REPLY-TO|SENDER|TO|SUBJECT)"?\)\] (.*)/', $line, $matches)) {
                        $value = preg_replace(array('/^"*[a-z]+:/i', '/\s+$/sm'), array('', ''), $matches[2]);
                        $result[$id] = trim($value);
                    } else {
                        $result[$id] = '';
                    }
                } else if ($mode == 2) {
                    if (preg_match('/\((UID|RFC822\.SIZE) ([0-9]+)/', $line, $matches)) {
                        $result[$id] = trim($matches[2]);
                    } else {
                        $result[$id] = 0;
                    }
                } else if ($mode == 3) {
                    if (!$flags && preg_match('/FLAGS \(([^)]+)\)/', $line, $matches)) {
                        $flags = explode(' ', $matches[1]);
                    }
                    $result[$id] = in_array('\\'.$index_field, $flags) ? 1 : 0;
                } else if ($mode == 4) {
                    if (preg_match('/INTERNALDATE "([^"]+)"/', $line, $matches)) {
                        $result[$id] = $this->strToTime($matches[1]);
                    } else {
                        $result[$id] = 0;
                    }
                }
            }
        } while (!$this->startsWith($line, $key, true, true));

        return $result;
    }

    static function compressMessageSet($messages, $force=false)
    {
        // given a comma delimited list of independent mid's,
        // compresses by grouping sequences together

        if (!is_array($messages)) {
            // if less than 255 bytes long, let's not bother
            if (!$force && strlen($messages)<255) {
                return $messages;
           }

            // see if it's already been compressed
            if (strpos($messages, ':') !== false) {
                return $messages;
            }

            // separate, then sort
            $messages = explode(',', $messages);
        }

        sort($messages);

        $result = array();
        $start  = $prev = $messages[0];

        foreach ($messages as $id) {
            $incr = $id - $prev;
            if ($incr > 1) { // found a gap
                if ($start == $prev) {
                    $result[] = $prev; // push single id
                } else {
                    $result[] = $start . ':' . $prev; // push sequence as start_id:end_id
                }
                $start = $id; // start of new sequence
            }
            $prev = $id;
        }

        // handle the last sequence/id
        if ($start == $prev) {
            $result[] = $prev;
        } else {
            $result[] = $start.':'.$prev;
        }

        // return as comma separated string
        return implode(',', $result);
    }

    static function uncompressMessageSet($messages)
    {
        $result   = array();
        $messages = explode(',', $messages);

        foreach ($messages as $part) {
            $items = explode(':', $part);
            $max   = max($items[0], $items[1]);

            for ($x=$items[0]; $x<=$max; $x++) {
                $result[] = $x;
            }
        }

        return $result;
    }

    /**
     * Returns message sequence identifier
     *
     * @param string $mailbox Mailbox name
     * @param int    $uid     Message unique identifier (UID)
     *
     * @return int Message sequence identifier
     * @access public
     */
    function UID2ID($mailbox, $uid)
    {
        if ($uid > 0) {
            $id_a = $this->search($mailbox, "UID $uid");
            if (is_array($id_a) && count($id_a) == 1) {
                return (int) $id_a[0];
            }
        }
        return null;
    }

    /**
     * Returns message unique identifier (UID)
     *
     * @param string $mailbox Mailbox name
     * @param int    $uid     Message sequence identifier
     *
     * @return int Message unique identifier
     * @access public
     */
    function ID2UID($mailbox, $id)
    {
        if (empty($id) || $id < 0) {
            return 	null;
        }

        if (!$this->select($mailbox)) {
            return null;
        }

        list($code, $response) = $this->execute('FETCH', array($id, '(UID)'));

        if ($code == self::ERROR_OK && preg_match("/^\* $id FETCH \(UID (.*)\)/i", $response, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    function fetchUIDs($mailbox, $message_set=null)
    {
        if (is_array($message_set))
            $message_set = join(',', $message_set);
        else if (empty($message_set))
            $message_set = '1:*';

        return $this->fetchHeaderIndex($mailbox, $message_set, 'UID', false);
    }

    function fetchHeaders($mailbox, $message_set, $uidfetch=false, $bodystr=false, $add='')
    {
        $result = array();

        if (!$this->select($mailbox)) {
            return false;
        }

        $message_set = $this->compressMessageSet($message_set);

        if ($add)
            $add = ' '.trim($add);

        /* FETCH uid, size, flags and headers */
        $key      = $this->nextTag();
        $request  = $key . ($uidfetch ? ' UID' : '') . " FETCH $message_set ";
        $request .= "(UID RFC822.SIZE FLAGS INTERNALDATE ";
        if ($bodystr)
            $request .= "BODYSTRUCTURE ";
        $request .= "BODY.PEEK[HEADER.FIELDS (DATE FROM TO SUBJECT CONTENT-TYPE ";
        $request .= "LIST-POST DISPOSITION-NOTIFICATION-TO".$add.")])";

        if (!$this->putLine($request)) {
            $this->setError(self::ERROR_COMMAND, "Unable to send command: $request");
            return false;
        }
        do {
            $line = $this->readLine(4096);
            $line = $this->multLine($line);

            if (!$line)
                break;

            if (preg_match('/^\* ([0-9]+) FETCH/', $line, $m)) {
                $id = intval($m[1]);

                $result[$id]            = new rcube_mail_header;
                $result[$id]->id        = $id;
                $result[$id]->subject   = '';
                $result[$id]->messageID = 'mid:' . $id;

                $lines = array();
                $ln = 0;

                // Sample reply line:
                // * 321 FETCH (UID 2417 RFC822.SIZE 2730 FLAGS (\Seen)
                // INTERNALDATE "16-Nov-2008 21:08:46 +0100" BODYSTRUCTURE (...)
                // BODY[HEADER.FIELDS ...

                if (preg_match('/^\* [0-9]+ FETCH \((.*) BODY/sU', $line, $matches)) {
                    $str = $matches[1];

                    // swap parents with quotes, then explode
                    $str = preg_replace('/[()]/', '"', $str);
                    $a = rcube_explode_quoted_string(' ', $str);

                    // did we get the right number of replies?
                    $parts_count = count($a);
                    if ($parts_count>=6) {
                        for ($i=0; $i<$parts_count; $i=$i+2) {
                            if ($a[$i] == 'UID') {
                                $result[$id]->uid = intval($a[$i+1]);
                            }
                            else if ($a[$i] == 'RFC822.SIZE') {
                                $result[$id]->size = intval($a[$i+1]);
                            }
                            else if ($a[$i] == 'INTERNALDATE') {
                                $time_str = $a[$i+1];
                            }
                            else if ($a[$i] == 'FLAGS') {
                                $flags_str = $a[$i+1];
                            }
                        }

                        $time_str = str_replace('"', '', $time_str);

                        // if time is gmt...
                        $time_str = str_replace('GMT','+0000',$time_str);

                        $result[$id]->internaldate = $time_str;
                        $result[$id]->timestamp    = $this->StrToTime($time_str);
                        $result[$id]->date         = $time_str;
                    }

                    // BODYSTRUCTURE
                    if ($bodystr) {
                        while (!preg_match('/ BODYSTRUCTURE (.*) BODY\[HEADER.FIELDS/sU', $line, $m)) {
                            $line2 = $this->readLine(1024);
                            $line .= $this->multLine($line2, true);
                        }
                        $result[$id]->body_structure = $m[1];
                    }

                    // the rest of the result
                    if (preg_match('/ BODY\[HEADER.FIELDS \(.*?\)\]\s*(.*)$/s', $line, $m)) {
                        $reslines = explode("\n", trim($m[1], '"'));
                        // re-parse (see below)
                        foreach ($reslines as $resln) {
                            if (ord($resln[0])<=32) {
                                $lines[$ln] .= (empty($lines[$ln])?'':"\n").trim($resln);
                            } else {
                                $lines[++$ln] = trim($resln);
                            }
                        }
                    }
                }

                // Start parsing headers.  The problem is, some header "lines" take up multiple lines.
                // So, we'll read ahead, and if the one we're reading now is a valid header, we'll
                // process the previous line.  Otherwise, we'll keep adding the strings until we come
                // to the next valid header line.

                do {
                    $line = rtrim($this->readLine(300), "\r\n");

                    // The preg_match below works around communigate imap, which outputs " UID <number>)".
                    // Without this, the while statement continues on and gets the "FH0 OK completed" message.
                    // If this loop gets the ending message, then the outer loop does not receive it from radline on line 1249.
                    // This in causes the if statement on line 1278 to never be true, which causes the headers to end up missing
                    // If the if statement was changed to pick up the fh0 from this loop, then it causes the outer loop to spin
                    // An alternative might be:
                    // if (!preg_match("/:/",$line) && preg_match("/\)$/",$line)) break;
                    // however, unsure how well this would work with all imap clients.
                    if (preg_match("/^\s*UID [0-9]+\)$/", $line)) {
                        break;
                    }

                    // handle FLAGS reply after headers (AOL, Zimbra?)
                    if (preg_match('/\s+FLAGS \((.*)\)\)$/', $line, $matches)) {
                        $flags_str = $matches[1];
                        break;
                    }

                    if (ord($line[0])<=32) {
                        $lines[$ln] .= (empty($lines[$ln])?'':"\n").trim($line);
                    } else {
                        $lines[++$ln] = trim($line);
                    }
                // patch from "Maksim Rubis" <siburny@hotmail.com>
                } while ($line[0] != ')' && !$this->startsWith($line, $key, true));

                if (strncmp($line, $key, strlen($key))) {
                    // process header, fill rcube_mail_header obj.
                    // initialize
                    if (is_array($headers)) {
                        reset($headers);
                        while (list($k, $bar) = each($headers)) {
                            $headers[$k] = '';
                        }
                    }

                    // create array with header field:data
                    while (list($lines_key, $str) = each($lines)) {
                        list($field, $string) = $this->splitHeaderLine($str);

                        $field  = strtolower($field);
                        $string = preg_replace('/\n\s*/', ' ', $string);

                        switch ($field) {
                        case 'date';
                            $result[$id]->date = $string;
                            $result[$id]->timestamp = $this->strToTime($string);
                            break;
                        case 'from':
                            $result[$id]->from = $string;
                            break;
                        case 'to':
                            $result[$id]->to = preg_replace('/undisclosed-recipients:[;,]*/', '', $string);
                            break;
                        case 'subject':
                            $result[$id]->subject = $string;
                            break;
                        case 'reply-to':
                            $result[$id]->replyto = $string;
                            break;
                        case 'cc':
                            $result[$id]->cc = $string;
                            break;
                        case 'bcc':
                            $result[$id]->bcc = $string;
                            break;
                        case 'content-transfer-encoding':
                            $result[$id]->encoding = $string;
                        break;
                        case 'content-type':
                            $ctype_parts = preg_split('/[; ]/', $string);
                            $result[$id]->ctype = array_shift($ctype_parts);
                            if (preg_match('/charset\s*=\s*"?([a-z0-9\-\.\_]+)"?/i', $string, $regs)) {
                                $result[$id]->charset = $regs[1];
                            }
                            break;
                        case 'in-reply-to':
                            $result[$id]->in_reply_to = str_replace(array("\n", '<', '>'), '', $string);
                            break;
                        case 'references':
                            $result[$id]->references = $string;
                            break;
                        case 'return-receipt-to':
                        case 'disposition-notification-to':
                        case 'x-confirm-reading-to':
                            $result[$id]->mdn_to = $string;
                            break;
                        case 'message-id':
                            $result[$id]->messageID = $string;
                            break;
                        case 'x-priority':
                            if (preg_match('/^(\d+)/', $string, $matches)) {
                                $result[$id]->priority = intval($matches[1]);
                            }
                            break;
                        default:
                            if (strlen($field) > 2) {
                                $result[$id]->others[$field] = $string;
                            }
                            break;
                        } // end switch ()
                    } // end while ()
                }

                // process flags
                if (!empty($flags_str)) {
                    $flags_str = preg_replace('/[\\\"]/', '', $flags_str);
                    $flags_a   = explode(' ', $flags_str);

                    if (is_array($flags_a)) {
                        foreach($flags_a as $flag) {
                            $flag = strtoupper($flag);
                            if ($flag == 'SEEN') {
                                $result[$id]->seen = true;
                            } else if ($flag == 'DELETED') {
                                $result[$id]->deleted = true;
                            } else if ($flag == 'RECENT') {
                                $result[$id]->recent = true;
                            } else if ($flag == 'ANSWERED') {
                                $result[$id]->answered = true;
                            } else if ($flag == '$FORWARDED') {
                                $result[$id]->forwarded = true;
                            } else if ($flag == 'DRAFT') {
                                $result[$id]->is_draft = true;
                            } else if ($flag == '$MDNSENT') {
                                $result[$id]->mdn_sent = true;
                            } else if ($flag == 'FLAGGED') {
                                 $result[$id]->flagged = true;
                            }
                        }
                        $result[$id]->flags = $flags_a;
                    }
                }
            }
        } while (!$this->startsWith($line, $key, true));

        return $result;
    }

    function fetchHeader($mailbox, $id, $uidfetch=false, $bodystr=false, $add='')
    {
        $a  = $this->fetchHeaders($mailbox, $id, $uidfetch, $bodystr, $add);
        if (is_array($a)) {
            return array_shift($a);
        }
        return false;
    }

    function sortHeaders($a, $field, $flag)
    {
        if (empty($field)) {
            $field = 'uid';
        }
        else {
            $field = strtolower($field);
        }

        if ($field == 'date' || $field == 'internaldate') {
            $field = 'timestamp';
        }

        if (empty($flag)) {
            $flag = 'ASC';
        } else {
            $flag = strtoupper($flag);
        }

        $c = count($a);
        if ($c > 0) {
            // Strategy:
            // First, we'll create an "index" array.
            // Then, we'll use sort() on that array,
            // and use that to sort the main array.

            // create "index" array
            $index = array();
            reset($a);
            while (list($key, $val) = each($a)) {
                if ($field == 'timestamp') {
                    $data = $this->strToTime($val->date);
                    if (!$data) {
                        $data = $val->timestamp;
                    }
                } else {
                    $data = $val->$field;
                    if (is_string($data)) {
                        $data = str_replace('"', '', $data);
                        if ($field == 'subject') {
                            $data = preg_replace('/^(Re: \s*|Fwd:\s*|Fw:\s*)+/i', '', $data);
                        }
                        $data = strtoupper($data);
                    }
                }
                $index[$key] = $data;
            }

            // sort index
            if ($flag == 'ASC') {
                asort($index);
            } else {
                arsort($index);
            }

            // form new array based on index
            $result = array();
            reset($index);
            while (list($key, $val) = each($index)) {
                $result[$key] = $a[$key];
            }
        }

        return $result;
    }


    function modFlag($mailbox, $messages, $flag, $mod)
    {
        if ($mod != '+' && $mod != '-') {
            $mod = '+';
        }

        if (!$this->select($mailbox)) {
            return false;
        }

        if (!$this->data['READ-WRITE']) {
            $this->setError(self::ERROR_READONLY, "Mailbox is read-only", 'STORE');
            return false;
        }

        // Clear internal status cache
        if ($flag == 'SEEN') {
            unset($this->data['STATUS:'.$mailbox]['UNSEEN']);
        }

        $flag   = $this->flags[strtoupper($flag)];
        $result = $this->execute('UID STORE', array(
            $this->compressMessageSet($messages), $mod . 'FLAGS.SILENT', "($flag)"),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    function flag($mailbox, $messages, $flag) {
        return $this->modFlag($mailbox, $messages, $flag, '+');
    }

    function unflag($mailbox, $messages, $flag) {
        return $this->modFlag($mailbox, $messages, $flag, '-');
    }

    function delete($mailbox, $messages) {
        return $this->modFlag($mailbox, $messages, 'DELETED', '+');
    }

    function copy($messages, $from, $to)
    {
        if (!$this->select($from)) {
            return false;
        }

        // Clear internal status cache
        unset($this->data['STATUS:'.$to]);

        $result = $this->execute('UID COPY', array(
            $this->compressMessageSet($messages), $this->escape($to)),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    function move($messages, $from, $to)
    {
        if (!$this->select($from)) {
            return false;
        }

        if (!$this->data['READ-WRITE']) {
            $this->setError(self::ERROR_READONLY, "Mailbox is read-only", 'STORE');
            return false;
        }

        $r = $this->copy($messages, $from, $to);

        if ($r) {
            // Clear internal status cache
            unset($this->data['STATUS:'.$from]);

            return $this->delete($from, $messages);
        }
        return $r;
    }

    // Don't be tempted to change $str to pass by reference to speed this up - it will slow it down by about
    // 7 times instead :-) See comments on http://uk2.php.net/references and this article:
    // http://derickrethans.nl/files/phparch-php-variables-article.pdf
    private function parseThread($str, $begin, $end, $root, $parent, $depth, &$depthmap, &$haschildren)
    {
        $node = array();
        if ($str[$begin] != '(') {
            $stop = $begin + strspn($str, '1234567890', $begin, $end - $begin);
            $msg = substr($str, $begin, $stop - $begin);
            if ($msg == 0)
                return $node;
            if (is_null($root))
                $root = $msg;
            $depthmap[$msg] = $depth;
            $haschildren[$msg] = false;
            if (!is_null($parent))
                $haschildren[$parent] = true;
            if ($stop + 1 < $end)
                $node[$msg] = $this->parseThread($str, $stop + 1, $end, $root, $msg, $depth + 1, $depthmap, $haschildren);
            else
                $node[$msg] = array();
        } else {
            $off = $begin;
            while ($off < $end) {
                $start = $off;
                $off++;
                $n = 1;
                while ($n > 0) {
                    $p = strpos($str, ')', $off);
                    if ($p === false) {
                        error_log("Mismatched brackets parsing IMAP THREAD response:");
                        error_log(substr($str, ($begin < 10) ? 0 : ($begin - 10), $end - $begin + 20));
                        error_log(str_repeat(' ', $off - (($begin < 10) ? 0 : ($begin - 10))));
                        return $node;
                    }
                    $p1 = strpos($str, '(', $off);
                    if ($p1 !== false && $p1 < $p) {
                        $off = $p1 + 1;
                        $n++;
                    } else {
                        $off = $p + 1;
                        $n--;
                    }
                }
                $node += $this->parseThread($str, $start + 1, $off - 1, $root, $parent, $depth, $depthmap, $haschildren);
            }
        }

        return $node;
    }

    function thread($mailbox, $algorithm='REFERENCES', $criteria='', $encoding='US-ASCII')
    {
        $old_sel = $this->selected;

        if (!$this->select($mailbox)) {
            return false;
        }

        // return empty result when folder is empty and we're just after SELECT
        if ($old_sel != $mailbox && !$this->data['EXISTS']) {
            return array(array(), array(), array());
        }

        $encoding  = $encoding ? trim($encoding) : 'US-ASCII';
        $algorithm = $algorithm ? trim($algorithm) : 'REFERENCES';
        $criteria  = $criteria ? 'ALL '.trim($criteria) : 'ALL';
        $data      = '';

        list($code, $response) = $this->execute('THREAD', array(
            $algorithm, $encoding, $criteria));

        if ($code == self::ERROR_OK) {
            // remove prefix...
            $response = substr($response, stripos($response, '* THREAD') + 9);
            // ...unilateral untagged server responses
            if ($pos = strpos($response, '*')) {
                $response = substr($response, 0, $pos);
            }

            $response    = str_replace("\r\n", '', $response);
            $depthmap    = array();
            $haschildren = array();

            $tree = $this->parseThread($response, 0, strlen($response),
                null, null, 0, $depthmap, $haschildren);

            return array($tree, $depthmap, $haschildren);
        }

        return false;
    }

    /**
     * Executes SEARCH command
     *
     * @param string $mailbox    Mailbox name
     * @param string $criteria   Searching criteria
     * @param bool   $return_uid Enable UID in result instead of sequence ID
     * @param array  $items      Return items (MIN, MAX, COUNT, ALL)
     *
     * @return array Message identifiers or item-value hash 
     */
    function search($mailbox, $criteria, $return_uid=false, $items=array())
    {
        $old_sel = $this->selected;

        if (!$this->select($mailbox)) {
            return false;
        }

        // return empty result when folder is empty and we're just after SELECT
        if ($old_sel != $mailbox && !$this->data['EXISTS']) {
            if (!empty($items))
                return array_combine($items, array_fill(0, count($items), 0));
            else
                return array();
        }

        $esearch  = empty($items) ? false : $this->getCapability('ESEARCH');
        $criteria = trim($criteria);
        $params   = '';

        // RFC4731: ESEARCH
        if (!empty($items) && $esearch) {
            $params .= 'RETURN (' . implode(' ', $items) . ')';
        }
        if (!empty($criteria)) {
            $params .= ($params ? ' ' : '') . $criteria;
        }
        else {
            $params .= 'ALL';
        }

        list($code, $response) = $this->execute($return_uid ? 'UID SEARCH' : 'SEARCH',
            array($params));

        if ($code == self::ERROR_OK) {
            // remove prefix...
            $response = substr($response, stripos($response, 
                $esearch ? '* ESEARCH' : '* SEARCH') + ($esearch ? 10 : 9));
            // ...and unilateral untagged server responses
            if ($pos = strpos($response, '*')) {
                $response = rtrim(substr($response, 0, $pos));
            }

            if ($esearch) {
                // Skip prefix: ... (TAG "A285") UID ...
                $this->tokenizeResponse($response, $return_uid ? 2 : 1);

                $result = array();
                for ($i=0; $i<count($items); $i++) {
                    // If the SEARCH results in no matches, the server MUST NOT
                    // include the item result option in the ESEARCH response
                    if ($ret = $this->tokenizeResponse($response, 2)) {
                        list ($name, $value) = $ret;
                        $result[$name] = $value;
                    }
                }

                return $result;
            }
            else {
                $response = preg_split('/[\s\r\n]+/', $response, -1, PREG_SPLIT_NO_EMPTY);

                if (!empty($items)) {
                    $result = array();
                    if (in_array('COUNT', $items)) {
                        $result['COUNT'] = count($response);
                    }
                    if (in_array('MIN', $items)) {
                        $result['MIN'] = !empty($response) ? min($response) : 0;
                    }
                    if (in_array('MAX', $items)) {
                        $result['MAX'] = !empty($response) ? max($response) : 0;
                    }
                    if (in_array('ALL', $items)) {
                        $result['ALL'] = $this->compressMessageSet($response, true);
                    }

                    return $result;
                }
                else {
                    return $response;
                }
            }
        }

        return false;
    }

    /**
     * Returns list of mailboxes
     *
     * @param string $ref         Reference name
     * @param string $mailbox     Mailbox name
     * @param array  $status_opts (see self::_listMailboxes)
     * @param array  $select_opts (see self::_listMailboxes)
     *
     * @return array List of mailboxes or hash of options if $status_opts argument
     *               is non-empty.
     * @access public
     */
    function listMailboxes($ref, $mailbox, $status_opts=array(), $select_opts=array())
    {
        return $this->_listMailboxes($ref, $mailbox, false, $status_opts, $select_opts);
    }

    /**
     * Returns list of subscribed mailboxes
     *
     * @param string $ref         Reference name
     * @param string $mailbox     Mailbox name
     * @param array  $status_opts (see self::_listMailboxes)
     *
     * @return array List of mailboxes or hash of options if $status_opts argument
     *               is non-empty.
     * @access public
     */
    function listSubscribed($ref, $mailbox, $status_opts=array())
    {
        return $this->_listMailboxes($ref, $mailbox, true, $status_opts, NULL);
    }

    /**
     * IMAP LIST/LSUB command
     *
     * @param string $ref         Reference name
     * @param string $mailbox     Mailbox name
     * @param bool   $subscribed  Enables returning subscribed mailboxes only
     * @param array  $status_opts List of STATUS options (RFC5819: LIST-STATUS)
     *                            Possible: MESSAGES, RECENT, UIDNEXT, UIDVALIDITY, UNSEEN
     * @param array  $select_opts List of selection options (RFC5258: LIST-EXTENDED)
     *                            Possible: SUBSCRIBED, RECURSIVEMATCH, REMOTE
     *
     * @return array List of mailboxes or hash of options if $status_ops argument
     *               is non-empty.
     * @access private
     */
    private function _listMailboxes($ref, $mailbox, $subscribed=false,
        $status_opts=array(), $select_opts=array())
    {
        if (!strlen($mailbox)) {
            $mailbox = '*';
        }

        $args = array();

        if (!empty($select_opts) && $this->getCapability('LIST-EXTENDED')) {
            $select_opts = (array) $select_opts;

            $args[] = '(' . implode(' ', $select_opts) . ')';
        }

        $args[] = $this->escape($ref);
        $args[] = $this->escape($mailbox);

        if (!empty($status_opts) && $this->getCapability('LIST-STATUS')) {
            $status_opts = (array) $status_opts;
            $lstatus = true;

            $args[] = 'RETURN (STATUS (' . implode(' ', $status_opts) . '))';
        }

        list($code, $response) = $this->execute($subscribed ? 'LSUB' : 'LIST', $args);

        if ($code == self::ERROR_OK) {
            $folders = array();
            while ($this->tokenizeResponse($response, 1) == '*') {
                $cmd = strtoupper($this->tokenizeResponse($response, 1));
                // * LIST (<options>) <delimiter> <mailbox>
                if (!$lstatus || $cmd == 'LIST' || $cmd == 'LSUB') {
                    list($opts, $delim, $mailbox) = $this->tokenizeResponse($response, 3);

                    // Add to result array
                    if (!$lstatus) {
                        $folders[] = $mailbox;
                    }
                    else {
                        $folders[$mailbox] = array();
                    }

                    // Add to options array
                    if (!empty($opts)) {
                        if (empty($this->data['LIST'][$mailbox]))
                            $this->data['LIST'][$mailbox] = $opts;
                        else
                            $this->data['LIST'][$mailbox] = array_unique(array_merge(
                                $this->data['LIST'][$mailbox], $opts));
                    }
                }
                // * STATUS <mailbox> (<result>)
                else if ($cmd == 'STATUS') {
                    list($mailbox, $status) = $this->tokenizeResponse($response, 2);

                    for ($i=0, $len=count($status); $i<$len; $i += 2) {
                        list($name, $value) = $this->tokenizeResponse($status, 2);
                        $folders[$mailbox][$name] = $value;
                    }
                }
            }

            return $folders;
        }

        return false;
    }

    function fetchMIMEHeaders($mailbox, $id, $parts, $mime=true)
    {
        if (!$this->select($mailbox)) {
            return false;
        }

        $result = false;
        $parts  = (array) $parts;
        $key    = $this->nextTag();
        $peeks  = '';
        $idx    = 0;
        $type   = $mime ? 'MIME' : 'HEADER';

        // format request
        foreach($parts as $part) {
            $peeks[] = "BODY.PEEK[$part.$type]";
        }

        $request = "$key FETCH $id (" . implode(' ', $peeks) . ')';

        // send request
        if (!$this->putLine($request)) {
            $this->setError(self::ERROR_COMMAND, "Unable to send command: $request");
            return false;
        }

        do {
            $line = $this->readLine(1024);
            $line = $this->multLine($line);

            if (preg_match('/BODY\[([0-9\.]+)\.'.$type.'\]/', $line, $matches)) {
                $idx = $matches[1];
                $result[$idx] = preg_replace('/^(\* '.$id.' FETCH \()?\s*BODY\['.$idx.'\.'.$type.'\]\s+/', '', $line);
                $result[$idx] = trim($result[$idx], '"');
                $result[$idx] = rtrim($result[$idx], "\t\r\n\0\x0B");
            }
        } while (!$this->startsWith($line, $key, true));

        return $result;
    }

    function fetchPartHeader($mailbox, $id, $is_uid=false, $part=NULL)
    {
        $part = empty($part) ? 'HEADER' : $part.'.MIME';

        return $this->handlePartBody($mailbox, $id, $is_uid, $part);
    }

    function handlePartBody($mailbox, $id, $is_uid=false, $part='', $encoding=NULL, $print=NULL, $file=NULL)
    {
        if (!$this->select($mailbox)) {
            return false;
        }

        switch ($encoding) {
        case 'base64':
            $mode = 1;
            break;
        case 'quoted-printable':
            $mode = 2;
            break;
        case 'x-uuencode':
        case 'x-uue':
        case 'uue':
        case 'uuencode':
            $mode = 3;
            break;
        default:
            $mode = 0;
        }

        // format request
        $reply_key = '* ' . $id;
        $key       = $this->nextTag();
        $request   = $key . ($is_uid ? ' UID' : '') . " FETCH $id (BODY.PEEK[$part])";

        // send request
        if (!$this->putLine($request)) {
            $this->setError(self::ERROR_COMMAND, "Unable to send command: $request");
            return false;
        }

        // receive reply line
        do {
            $line = rtrim($this->readLine(1024));
            $a    = explode(' ', $line);
        } while (!($end = $this->startsWith($line, $key, true)) && $a[2] != 'FETCH');

        $len    = strlen($line);
        $result = false;

        // handle empty "* X FETCH ()" response
        if ($line[$len-1] == ')' && $line[$len-2] != '(') {
            // one line response, get everything between first and last quotes
            if (substr($line, -4, 3) == 'NIL') {
                // NIL response
                $result = '';
            } else {
                $from = strpos($line, '"') + 1;
                $to   = strrpos($line, '"');
                $len  = $to - $from;
                $result = substr($line, $from, $len);
            }

            if ($mode == 1) {
                $result = base64_decode($result);
            }
            else if ($mode == 2) {
                $result = quoted_printable_decode($result);
            }
            else if ($mode == 3) {
                $result = convert_uudecode($result);
            }

        } else if ($line[$len-1] == '}') {
            // multi-line request, find sizes of content and receive that many bytes
            $from     = strpos($line, '{') + 1;
            $to       = strrpos($line, '}');
            $len      = $to - $from;
            $sizeStr  = substr($line, $from, $len);
            $bytes    = (int)$sizeStr;
            $prev     = '';

            while ($bytes > 0) {
                $line = $this->readLine(4096);

                if ($line === NULL) {
                    break;
                }

                $len  = strlen($line);

                if ($len > $bytes) {
                    $line = substr($line, 0, $bytes);
                    $len = strlen($line);
                }
                $bytes -= $len;

                // BASE64
                if ($mode == 1) {
                    $line = rtrim($line, "\t\r\n\0\x0B");
                    // create chunks with proper length for base64 decoding
                    $line = $prev.$line;
                    $length = strlen($line);
                    if ($length % 4) {
                        $length = floor($length / 4) * 4;
                        $prev = substr($line, $length);
                        $line = substr($line, 0, $length);
                    }
                    else
                        $prev = '';
                    $line = base64_decode($line);
                // QUOTED-PRINTABLE
                } else if ($mode == 2) {
                    $line = rtrim($line, "\t\r\0\x0B");
                    $line = quoted_printable_decode($line);
                    // Remove NULL characters (#1486189)
                    $line = str_replace("\x00", '', $line);
                // UUENCODE
                } else if ($mode == 3) {
                    $line = rtrim($line, "\t\r\n\0\x0B");
                    if ($line == 'end' || preg_match('/^begin\s+[0-7]+\s+.+$/', $line))
                        continue;
                    $line = convert_uudecode($line);
                // default
                } else {
                    $line = rtrim($line, "\t\r\n\0\x0B") . "\n";
                }

                if ($file)
                    fwrite($file, $line);
                else if ($print)
                    echo $line;
                else
                    $result .= $line;
            }
        }

        // read in anything up until last line
        if (!$end)
            do {
                $line = $this->readLine(1024);
            } while (!$this->startsWith($line, $key, true));

        if ($result !== false) {
            if ($file) {
                fwrite($file, $result);
            } else if ($print) {
                echo $result;
            } else
                return $result;
            return true;
        }

        return false;
    }

    function createFolder($mailbox)
    {
        $result = $this->execute('CREATE', array($this->escape($mailbox)),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    function renameFolder($from, $to)
    {
        $result = $this->execute('RENAME', array($this->escape($from), $this->escape($to)),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    function append($mailbox, &$message)
    {
        if (!$mailbox) {
            return false;
        }

        $message = str_replace("\r", '', $message);
        $message = str_replace("\n", "\r\n", $message);

        $len = strlen($message);
        if (!$len) {
            return false;
        }

        $key = $this->nextTag();
        $request = sprintf("$key APPEND %s (\\Seen) {%d%s}", $this->escape($mailbox),
            $len, ($this->prefs['literal+'] ? '+' : ''));

        if ($this->putLine($request)) {
            // Don't wait when LITERAL+ is supported
            if (!$this->prefs['literal+']) {
                $line = $this->readReply();

                if ($line[0] != '+') {
                    $this->parseResult($line, 'APPEND: ');
                    return false;
                }
            }

            if (!$this->putLine($message)) {
                return false;
            }

            do {
                $line = $this->readLine();
            } while (!$this->startsWith($line, $key, true, true));

            // Clear internal status cache
            unset($this->data['STATUS:'.$mailbox]);

            return ($this->parseResult($line, 'APPEND: ') == self::ERROR_OK);
        }
        else {
            $this->setError(self::ERROR_COMMAND, "Unable to send command: $request");
        }

        return false;
    }

    function appendFromFile($mailbox, $path, $headers=null)
    {
        if (!$mailbox) {
            return false;
        }

        // open message file
        $in_fp = false;
        if (file_exists(realpath($path))) {
            $in_fp = fopen($path, 'r');
        }
        if (!$in_fp) {
            $this->setError(self::ERROR_UNKNOWN, "Couldn't open $path for reading");
            return false;
        }

        $body_separator = "\r\n\r\n";
        $len = filesize($path);

        if (!$len) {
            return false;
        }

        if ($headers) {
            $headers = preg_replace('/[\r\n]+$/', '', $headers);
            $len += strlen($headers) + strlen($body_separator);
        }

        // send APPEND command
        $key = $this->nextTag();
        $request = sprintf("$key APPEND %s (\\Seen) {%d%s}", $this->escape($mailbox),
            $len, ($this->prefs['literal+'] ? '+' : ''));

        if ($this->putLine($request)) {
            // Don't wait when LITERAL+ is supported
            if (!$this->prefs['literal+']) {
                $line = $this->readReply();

                if ($line[0] != '+') {
                    $this->parseResult($line, 'APPEND: ');
                    return false;
                }
            }

            // send headers with body separator
            if ($headers) {
                $this->putLine($headers . $body_separator, false);
            }

            // send file
            while (!feof($in_fp) && $this->fp) {
                $buffer = fgets($in_fp, 4096);
                $this->putLine($buffer, false);
            }
            fclose($in_fp);

            if (!$this->putLine('')) { // \r\n
                return false;
            }

            // read response
            do {
                $line = $this->readLine();
            } while (!$this->startsWith($line, $key, true, true));

            // Clear internal status cache
            unset($this->data['STATUS:'.$mailbox]);

            return ($this->parseResult($line, 'APPEND: ') == self::ERROR_OK);
        }
        else {
            $this->setError(self::ERROR_COMMAND, "Unable to send command: $request");
        }

        return false;
    }

    function fetchStructureString($mailbox, $id, $is_uid=false)
    {
        if (!$this->select($mailbox)) {
            return false;
        }

        $key = $this->nextTag();
        $result = false;
        $command = $key . ($is_uid ? ' UID' : '') ." FETCH $id (BODYSTRUCTURE)";

        if ($this->putLine($command)) {
            do {
                $line = $this->readLine(5000);
                $line = $this->multLine($line, true);
                if (!preg_match("/^$key /", $line))
                    $result .= $line;
            } while (!$this->startsWith($line, $key, true, true));

            $result = trim(substr($result, strpos($result, 'BODYSTRUCTURE')+13, -1));
        }
        else {
            $this->setError(self::ERROR_COMMAND, "Unable to send command: $command");
        }

        return $result;
    }

    function getQuota()
    {
        /*
         * GETQUOTAROOT "INBOX"
         * QUOTAROOT INBOX user/rchijiiwa1
         * QUOTA user/rchijiiwa1 (STORAGE 654 9765)
         * OK Completed
         */
        $result      = false;
        $quota_lines = array();
        $key         = $this->nextTag();
        $command     = $key . ' GETQUOTAROOT INBOX';

        // get line(s) containing quota info
        if ($this->putLine($command)) {
            do {
                $line = rtrim($this->readLine(5000));
                if (preg_match('/^\* QUOTA /', $line)) {
                    $quota_lines[] = $line;
                }
            } while (!$this->startsWith($line, $key, true, true));
        }
        else {
            $this->setError(self::ERROR_COMMAND, "Unable to send command: $command");
        }

        // return false if not found, parse if found
        $min_free = PHP_INT_MAX;
        foreach ($quota_lines as $key => $quota_line) {
            $quota_line   = str_replace(array('(', ')'), '', $quota_line);
            $parts        = explode(' ', $quota_line);
            $storage_part = array_search('STORAGE', $parts);

            if (!$storage_part) {
                continue;
            }

            $used  = intval($parts[$storage_part+1]);
            $total = intval($parts[$storage_part+2]);
            $free  = $total - $used;

            // return lowest available space from all quotas
            if ($free < $min_free) {
                $min_free          = $free;
                $result['used']    = $used;
                $result['total']   = $total;
                $result['percent'] = min(100, round(($used/max(1,$total))*100));
                $result['free']    = 100 - $result['percent'];
            }
        }

        return $result;
    }

    /**
     * Send the SETACL command (RFC4314)
     *
     * @param string $mailbox Mailbox name
     * @param string $user    User name
     * @param mixed  $acl     ACL string or array
     *
     * @return boolean True on success, False on failure
     *
     * @access public
     * @since 0.5-beta
     */
    function setACL($mailbox, $user, $acl)
    {
        if (is_array($acl)) {
            $acl = implode('', $acl);
        }

        $result = $this->execute('SETACL', array(
            $this->escape($mailbox), $this->escape($user), strtolower($acl)),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    /**
     * Send the DELETEACL command (RFC4314)
     *
     * @param string $mailbox Mailbox name
     * @param string $user    User name
     *
     * @return boolean True on success, False on failure
     *
     * @access public
     * @since 0.5-beta
     */
    function deleteACL($mailbox, $user)
    {
        $result = $this->execute('DELETEACL', array(
            $this->escape($mailbox), $this->escape($user)),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    /**
     * Send the GETACL command (RFC4314)
     *
     * @param string $mailbox Mailbox name
     *
     * @return array User-rights array on success, NULL on error
     * @access public
     * @since 0.5-beta
     */
    function getACL($mailbox)
    {
        list($code, $response) = $this->execute('GETACL', array($this->escape($mailbox)));

        if ($code == self::ERROR_OK && preg_match('/^\* ACL /i', $response)) {
            // Parse server response (remove "* ACL ")
            $response = substr($response, 6);
            $ret  = $this->tokenizeResponse($response);
            $mbox = array_shift($ret);
            $size = count($ret);

            // Create user-rights hash array
            // @TODO: consider implementing fixACL() method according to RFC4314.2.1.1
            // so we could return only standard rights defined in RFC4314,
            // excluding 'c' and 'd' defined in RFC2086.
            if ($size % 2 == 0) {
                for ($i=0; $i<$size; $i++) {
                    $ret[$ret[$i]] = str_split($ret[++$i]);
                    unset($ret[$i-1]);
                    unset($ret[$i]);
                }
                return $ret;
            }

            $this->setError(self::ERROR_COMMAND, "Incomplete ACL response");
            return NULL;
        }

        return NULL;
    }

    /**
     * Send the LISTRIGHTS command (RFC4314)
     *
     * @param string $mailbox Mailbox name
     * @param string $user    User name
     *
     * @return array List of user rights
     * @access public
     * @since 0.5-beta
     */
    function listRights($mailbox, $user)
    {
        list($code, $response) = $this->execute('LISTRIGHTS', array(
            $this->escape($mailbox), $this->escape($user)));

        if ($code == self::ERROR_OK && preg_match('/^\* LISTRIGHTS /i', $response)) {
            // Parse server response (remove "* LISTRIGHTS ")
            $response = substr($response, 13);

            $ret_mbox = $this->tokenizeResponse($response, 1);
            $ret_user = $this->tokenizeResponse($response, 1);
            $granted  = $this->tokenizeResponse($response, 1);
            $optional = trim($response);

            return array(
                'granted'  => str_split($granted),
                'optional' => explode(' ', $optional),
            );
        }

        return NULL;
    }

    /**
     * Send the MYRIGHTS command (RFC4314)
     *
     * @param string $mailbox Mailbox name
     *
     * @return array MYRIGHTS response on success, NULL on error
     * @access public
     * @since 0.5-beta
     */
    function myRights($mailbox)
    {
        list($code, $response) = $this->execute('MYRIGHTS', array($this->escape($mailbox)));

        if ($code == self::ERROR_OK && preg_match('/^\* MYRIGHTS /i', $response)) {
            // Parse server response (remove "* MYRIGHTS ")
            $response = substr($response, 11);

            $ret_mbox = $this->tokenizeResponse($response, 1);
            $rights   = $this->tokenizeResponse($response, 1);

            return str_split($rights);
        }

        return NULL;
    }

    /**
     * Send the SETMETADATA command (RFC5464)
     *
     * @param string $mailbox Mailbox name
     * @param array  $entries Entry-value array (use NULL value as NIL)
     *
     * @return boolean True on success, False on failure
     * @access public
     * @since 0.5-beta
     */
    function setMetadata($mailbox, $entries)
    {
        if (!is_array($entries) || empty($entries)) {
            $this->setError(self::ERROR_COMMAND, "Wrong argument for SETMETADATA command");
            return false;
        }

        foreach ($entries as $name => $value) {
            if ($value === null) {
                $value = 'NIL';
            }
            else {
                $value = sprintf("{%d}\r\n%s", strlen($value), $value);
            }
            $entries[$name] = $this->escape($name) . ' ' . $value;
        }

        $entries = implode(' ', $entries);
        $result = $this->execute('SETMETADATA', array(
            $this->escape($mailbox), '(' . $entries . ')'),
            self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    /**
     * Send the SETMETADATA command with NIL values (RFC5464)
     *
     * @param string $mailbox Mailbox name
     * @param array  $entries Entry names array
     *
     * @return boolean True on success, False on failure
     *
     * @access public
     * @since 0.5-beta
     */
    function deleteMetadata($mailbox, $entries)
    {
        if (!is_array($entries) && !empty($entries)) {
            $entries = explode(' ', $entries);
        }

        if (empty($entries)) {
            $this->setError(self::ERROR_COMMAND, "Wrong argument for SETMETADATA command");
            return false;
        }

        foreach ($entries as $entry) {
            $data[$entry] = NULL;
        }

        return $this->setMetadata($mailbox, $data);
    }

    /**
     * Send the GETMETADATA command (RFC5464)
     *
     * @param string $mailbox Mailbox name
     * @param array  $entries Entries
     * @param array  $options Command options (with MAXSIZE and DEPTH keys)
     *
     * @return array GETMETADATA result on success, NULL on error
     *
     * @access public
     * @since 0.5-beta
     */
    function getMetadata($mailbox, $entries, $options=array())
    {
        if (!is_array($entries)) {
            $entries = array($entries);
        }

        // create entries string
        foreach ($entries as $idx => $name) {
            $entries[$idx] = $this->escape($name);
        }

        $optlist = '';
        $entlist = '(' . implode(' ', $entries) . ')';

        // create options string
        if (is_array($options)) {
            $options = array_change_key_case($options, CASE_UPPER);
            $opts = array();

            if (!empty($options['MAXSIZE'])) {
                $opts[] = 'MAXSIZE '.intval($options['MAXSIZE']);
            }
            if (!empty($options['DEPTH'])) {
                $opts[] = 'DEPTH '.intval($options['DEPTH']);
            }

            if ($opts) {
                $optlist = '(' . implode(' ', $opts) . ')';
            }
        }

        $optlist .= ($optlist ? ' ' : '') . $entlist;

        list($code, $response) = $this->execute('GETMETADATA', array(
            $this->escape($mailbox), $optlist));

        if ($code == self::ERROR_OK) {
            $result = array();
            $data   = $this->tokenizeResponse($response);

            // The METADATA response can contain multiple entries in a single
            // response or multiple responses for each entry or group of entries
            if (!empty($data) && ($size = count($data))) {
                for ($i=0; $i<$size; $i++) {
                    if (isset($mbox) && is_array($data[$i])) {
                        $size_sub = count($data[$i]);
                        for ($x=0; $x<$size_sub; $x++) {
                            $result[$mbox][$data[$i][$x]] = $data[$i][++$x];
                        }
                        unset($data[$i]);
                    }
                    else if ($data[$i] == '*') {
                        if ($data[$i+1] == 'METADATA') {
                            $mbox = $data[$i+2];
                            unset($data[$i]);   // "*"
                            unset($data[++$i]); // "METADATA"
                            unset($data[++$i]); // Mailbox
                        }
                        // get rid of other untagged responses
                        else {
                            unset($mbox);
                            unset($data[$i]);
                        }
                    }
                    else if (isset($mbox)) {
                        $result[$mbox][$data[$i]] = $data[++$i];
                        unset($data[$i]);
                        unset($data[$i-1]);
                    }
                    else {
                        unset($data[$i]);
                    }
                }
            }

            return $result;
        }

        return NULL;
    }

    /**
     * Send the SETANNOTATION command (draft-daboo-imap-annotatemore)
     *
     * @param string $mailbox Mailbox name
     * @param array  $data    Data array where each item is an array with
     *                        three elements: entry name, attribute name, value
     *
     * @return boolean True on success, False on failure
     * @access public
     * @since 0.5-beta
     */
    function setAnnotation($mailbox, $data)
    {
        if (!is_array($data) || empty($data)) {
            $this->setError(self::ERROR_COMMAND, "Wrong argument for SETANNOTATION command");
            return false;
        }

        foreach ($data as $entry) {
            $name  = $entry[0];
            $attr  = $entry[1];
            $value = $entry[2];

            if ($value === null) {
                $value = 'NIL';
            }
            else {
                $value = sprintf("{%d}\r\n%s", strlen($value), $value);
            }

            // ANNOTATEMORE drafts before version 08 require quoted parameters
            $entries[] = sprintf('%s (%s %s)',
                $this->escape($name, true), $this->escape($attr, true), $value);
        }

        $entries = implode(' ', $entries);
        $result  = $this->execute('SETANNOTATION', array(
            $this->escape($mailbox), $entries), self::COMMAND_NORESPONSE);

        return ($result == self::ERROR_OK);
    }

    /**
     * Send the SETANNOTATION command with NIL values (draft-daboo-imap-annotatemore)
     *
     * @param string $mailbox Mailbox name
     * @param array  $data    Data array where each item is an array with
     *                        two elements: entry name and attribute name
     *
     * @return boolean True on success, False on failure
     *
     * @access public
     * @since 0.5-beta
     */
    function deleteAnnotation($mailbox, $data)
    {
        if (!is_array($data) || empty($data)) {
            $this->setError(self::ERROR_COMMAND, "Wrong argument for SETANNOTATION command");
            return false;
        }

        return $this->setAnnotation($mailbox, $data);
    }

    /**
     * Send the GETANNOTATION command (draft-daboo-imap-annotatemore)
     *
     * @param string $mailbox Mailbox name
     * @param array  $entries Entries names
     * @param array  $attribs Attribs names
     *
     * @return array Annotations result on success, NULL on error
     *
     * @access public
     * @since 0.5-beta
     */
    function getAnnotation($mailbox, $entries, $attribs)
    {
        if (!is_array($entries)) {
            $entries = array($entries);
        }
        // create entries string
        // ANNOTATEMORE drafts before version 08 require quoted parameters
        foreach ($entries as $idx => $name) {
            $entries[$idx] = $this->escape($name, true);
        }
        $entries = '(' . implode(' ', $entries) . ')';

        if (!is_array($attribs)) {
            $attribs = array($attribs);
        }
        // create entries string
        foreach ($attribs as $idx => $name) {
            $attribs[$idx] = $this->escape($name, true);
        }
        $attribs = '(' . implode(' ', $attribs) . ')';

        list($code, $response) = $this->execute('GETANNOTATION', array(
            $this->escape($mailbox), $entries, $attribs));

        if ($code == self::ERROR_OK) {
            $result = array();
            $data   = $this->tokenizeResponse($response);

            // Here we returns only data compatible with METADATA result format
            if (!empty($data) && ($size = count($data))) {
                for ($i=0; $i<$size; $i++) {
                    $entry = $data[$i];
                    if (isset($mbox) && is_array($entry)) {
                        $attribs = $entry;
                        $entry   = $last_entry;
                    }
                    else if ($entry == '*') {
                        if ($data[$i+1] == 'ANNOTATION') {
                            $mbox = $data[$i+2];
                            unset($data[$i]);   // "*"
                            unset($data[++$i]); // "ANNOTATION"
                            unset($data[++$i]); // Mailbox
                        }
                        // get rid of other untagged responses
                        else {
                            unset($mbox);
                            unset($data[$i]);
                        }
                        continue;
                    }
                    else if (isset($mbox)) {
                        $attribs = $data[++$i];
                    }
                    else {
                        unset($data[$i]);
                        continue;
                    }

                    if (!empty($attribs)) {
                        for ($x=0, $len=count($attribs); $x<$len;) {
                            $attr  = $attribs[$x++];
                            $value = $attribs[$x++];
                            if ($attr == 'value.priv') {
                                $result[$mbox]['/private' . $entry] = $value;
                            }
                            else if ($attr == 'value.shared') {
                                $result[$mbox]['/shared' . $entry] = $value;
                            }
                        }
                    }
                    $last_entry = $entry;
                    unset($data[$i]);
                }
            }

            return $result;
        }

        return NULL;
    }

    /**
     * Creates next command identifier (tag)
     *
     * @return string Command identifier
     * @access public
     * @since 0.5-beta
     */
    function nextTag()
    {
        $this->cmd_num++;
        $this->cmd_tag = sprintf('A%04d', $this->cmd_num);

        return $this->cmd_tag;
    }

    /**
     * Sends IMAP command and parses result
     *
     * @param string $command   IMAP command
     * @param array  $arguments Command arguments
     * @param int    $options   Execution options
     *
     * @return mixed Response code or list of response code and data
     * @access public
     * @since 0.5-beta
     */
    function execute($command, $arguments=array(), $options=0)
    {
        $tag      = $this->nextTag();
        $query    = $tag . ' ' . $command;
        $noresp   = ($options & self::COMMAND_NORESPONSE);
        $response = $noresp ? null : '';

        if (!empty($arguments)) {
            $query .= ' ' . implode(' ', $arguments);
        }

        // Send command
        if (!$this->putLineC($query)) {
            $this->setError(self::ERROR_COMMAND, "Unable to send command: $query");
            return $noresp ? self::ERROR_COMMAND : array(self::ERROR_COMMAND, '');
        }

        // Parse response
        do {
            $line = $this->readLine(4096);
            if ($response !== null) {
                $response .= $line;
            }
        } while (!$this->startsWith($line, $tag . ' ', true, true));

        $code = $this->parseResult($line, $command . ': ');

        // Remove last line from response
        if ($response) {
            $line_len = min(strlen($response), strlen($line) + 2);
            $response = substr($response, 0, -$line_len);
        }

        // optional CAPABILITY response
        if (($options & self::COMMAND_CAPABILITY) && $code == self::ERROR_OK
            && preg_match('/\[CAPABILITY ([^]]+)\]/i', $line, $matches)
        ) {
            $this->parseCapability($matches[1], true);
        }

        // return last line only (without command tag, result and response code)
        if ($line && ($options & self::COMMAND_LASTLINE)) {
            $response = preg_replace("/^$tag (OK|NO|BAD|BYE|PREAUTH)?\s*(\[[a-z-]+\])?\s*/i", '', trim($line));
        }

        return $noresp ? $code : array($code, $response);
    }

    /**
     * Splits IMAP response into string tokens
     *
     * @param string &$str The IMAP's server response
     * @param int    $num  Number of tokens to return
     *
     * @return mixed Tokens array or string if $num=1
     * @access public
     * @since 0.5-beta
     */
    static function tokenizeResponse(&$str, $num=0)
    {
        $result = array();

        while (!$num || count($result) < $num) {
            // remove spaces from the beginning of the string
            $str = ltrim($str);

            switch ($str[0]) {

            // String literal
            case '{':
                if (($epos = strpos($str, "}\r\n", 1)) == false) {
                    // error
                }
                if (!is_numeric(($bytes = substr($str, 1, $epos - 1)))) {
                    // error
                }
                $result[] = substr($str, $epos + 3, $bytes);
                // Advance the string
                $str = substr($str, $epos + 3 + $bytes);
                break;

            // Quoted string
            case '"':
                $len = strlen($str);

                for ($pos=1; $pos<$len; $pos++) {
                    if ($str[$pos] == '"') {
                        break;
                    }
                    if ($str[$pos] == "\\") {
                        if ($str[$pos + 1] == '"' || $str[$pos + 1] == "\\") {
                            $pos++;
                        }
                    }
                }
                if ($str[$pos] != '"') {
                    // error
                }
                // we need to strip slashes for a quoted string
                $result[] = stripslashes(substr($str, 1, $pos - 1));
                $str      = substr($str, $pos + 1);
                break;

            // Parenthesized list
            case '(':
                $str = substr($str, 1);
                $result[] = self::tokenizeResponse($str);
                break;
            case ')':
                $str = substr($str, 1);
                return $result;
                break;

            // String atom, number, NIL, *, %
            default:
                // empty or one character
                if ($str === '') {
                    break 2;
                }
                if (strlen($str) < 2) {
                    $result[] = $str;
                    $str = '';
                    break;
                }

                // excluded chars: SP, CTL, (, ), {, ", ], %
                if (preg_match('/^([\x21\x23\x24\x26\x27\x2A-\x5C\x5E-\x7A\x7C-\x7E]+)/', $str, $m)) {
                    $result[] = $m[1] == 'NIL' ? NULL : $m[1];
                    $str = substr($str, strlen($m[1]));
                }
                break;
            }
        }

        return $num == 1 ? $result[0] : $result;
    }

    private function _xor($string, $string2)
    {
        $result = '';
        $size   = strlen($string);

        for ($i=0; $i<$size; $i++) {
            $result .= chr(ord($string[$i]) ^ ord($string2[$i]));
        }

        return $result;
    }

    /**
     * Converts datetime string into unix timestamp
     *
     * @param string $date Date string
     *
     * @return int Unix timestamp
     */
    private function strToTime($date)
    {
        $ts = (int) rcube_strtotime($date);
        return $ts < 0 ? 0 : $ts;
    }

    private function splitHeaderLine($string)
    {
        $pos = strpos($string, ':');
        if ($pos>0) {
            $res[0] = substr($string, 0, $pos);
            $res[1] = trim(substr($string, $pos+1));
            return $res;
        }
        return $string;
    }

    private function parseCapability($str, $trusted=false)
    {
        $str = preg_replace('/^\* CAPABILITY /i', '', $str);

        $this->capability = explode(' ', strtoupper($str));

        if (!isset($this->prefs['literal+']) && in_array('LITERAL+', $this->capability)) {
            $this->prefs['literal+'] = true;
        }

        if ($trusted) {
            $this->capability_readed = true;
        }
    }

    /**
     * Escapes a string when it contains special characters (RFC3501)
     *
     * @param string  $string       IMAP string
     * @param boolean $force_quotes Forces string quoting
     *
     * @return string Escaped string
     * @todo String literals, lists
     */
    static function escape($string, $force_quotes=false)
    {
        if ($string === null) {
            return 'NIL';
        }
        else if ($string === '') {
            return '""';
        }
        else if ($force_quotes ||
            preg_match('/([\x00-\x20\x28-\x29\x7B\x25\x2A\x22\x5C\x5D\x7F]+)/', $string)
        ) {
            // string: special chars: SP, CTL, (, ), {, %, *, ", \, ]
            return '"' . strtr($string, array('"'=>'\\"', '\\' => '\\\\')) . '"';
        }

        // atom
        return $string;
    }

    static function unEscape($string)
    {
        return strtr($string, array('\\"'=>'"', '\\\\' => '\\'));
    }

    /**
     * Set the value of the debugging flag.
     *
     * @param   boolean $debug      New value for the debugging flag.
     *
     * @access  public
     * @since   0.5-stable
     */
    function setDebug($debug, $handler = null)
    {
        $this->_debug = $debug;
        $this->_debug_handler = $handler;
    }

    /**
     * Write the given debug text to the current debug output handler.
     *
     * @param   string  $message    Debug mesage text.
     *
     * @access  private
     * @since   0.5-stable
     */
    private function debug($message)
    {
        if ($this->_debug_handler) {
            call_user_func_array($this->_debug_handler, array(&$this, $message));
        } else {
            echo "DEBUG: $message\n";
        }
    }

}

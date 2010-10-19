<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_imap_generic.php                                |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
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
 * @package    Mail
 * @author     Aleksander Machniak <alec@alec.pl>
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
	public $f;
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
 * @package    Mail
 * @author     Aleksander Machniak <alec@alec.pl>
 */
class rcube_imap_generic
{
    public $error;
    public $errornum;
	public $message;
	public $rootdir;
	public $delimiter;
	public $permanentflags = array();
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

	private $exists;
	private $recent;
    private $selected;
	private $fp;
	private $host;
	private $logged = false;
	private $capability = array();
	private $capability_readed = false;
    private $prefs;

    const ERROR_OK = 0;
    const ERROR_NO = -1;
    const ERROR_BAD = -2;
    const ERROR_BYE = -3;
    const ERROR_COMMAND = -5;
    const ERROR_UNKNOWN = -4;

    /**
     * Object constructor
     */
    function __construct()
    {
    }

    function putLine($string, $endln=true)
    {
        if (!$this->fp)
            return false;

		if (!empty($this->prefs['debug_mode'])) {
    		write_log('imap', 'C: '. rtrim($string));
	    }

        $res = fwrite($this->fp, $string . ($endln ? "\r\n" : ''));

   		if ($res === false) {
           	@fclose($this->fp);
           	$this->fp = null;
   		}

        return $res;
    }

    // $this->putLine replacement with Command Continuation Requests (RFC3501 7.5) support
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
		    if (!empty($this->prefs['debug_mode'])) {
			    write_log('imap', 'S: '. rtrim($buffer));
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
		    if (!empty($this->prefs['debug_mode'])) {
			    write_log('imap', 'S: '. $d);
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

    // don't use it in loops, until you exactly know what you're doing
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
			    return $this->errornum = self::ERROR_OK;
		    } else if ($res == 'NO') {
                $this->errornum = self::ERROR_NO;
		    } else if ($res == 'BAD') {
			    $this->errornum = self::ERROR_BAD;
		    } else if ($res == 'BYE') {
                @fclose($this->fp);
                $this->fp = null;
			    $this->errornum = self::ERROR_BYE;
		    }

            if ($str)
                $this->error = $err_prefix ? $err_prefix.$str : $str;

	        return $this->errornum;
	    }
	    return self::ERROR_UNKNOWN;
    }

    private function set_error($code, $msg='')
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

    function getCapability($name)
    {
	    if (in_array($name, $this->capability)) {
		    return true;
	    }
	    else if ($this->capability_readed) {
		    return false;
	    }

	    // get capabilities (only once) because initial
	    // optional CAPABILITY response may differ
	    $this->capability = array();

	    if (!$this->putLine("cp01 CAPABILITY")) {
            return false;
        }
	    do {
		    $line = trim($this->readLine(1024));
	        if (preg_match('/^\* CAPABILITY (.+)/i', $line, $matches)) {
		        $this->parseCapability($matches[1]);
	        }
	    } while (!$this->startsWith($line, 'cp01', true));

	    $this->capability_readed = true;

	    if (in_array($name, $this->capability)) {
		    return true;
	    }

	    return false;
    }

    function clearCapability()
    {
	    $this->capability = array();
	    $this->capability_readed = false;
    }

    function authenticate($user, $pass, $encChallenge)
    {
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
        $hash  = md5($this->_xor($pass,$opad) . pack("H*", md5($this->_xor($pass, $ipad) . base64_decode($encChallenge))));

        // generate reply
        $reply = base64_encode($user . ' ' . $hash);

        // send result, get reply
        $this->putLine($reply);
        $line = $this->readLine(1024);

        // process result
        $result = $this->parseResult($line);
        if ($result == self::ERROR_OK) {
            return $this->fp;
        }

        $this->error = "Authentication for $user failed (AUTH): $line";

        return $result;
    }

    function login($user, $password)
    {
        $this->putLine(sprintf("a001 LOGIN %s %s",
            $this->escape($user), $this->escape($password)));

        $line = $this->readReply($untagged);

        // re-set capabilities list if untagged CAPABILITY response provided
	    if (preg_match('/\* CAPABILITY (.+)/i', $untagged, $matches)) {
		    $this->parseCapability($matches[1]);
	    }

        // process result
        $result = $this->parseResult($line);

        if ($result == self::ERROR_OK) {
            return $this->fp;
        }

        @fclose($this->fp);
        $this->fp    = false;
        $this->error = "Authentication for $user failed (LOGIN): $line";

        return $result;
    }

    function getNamespace()
    {
	    if (isset($this->prefs['rootdir']) && is_string($this->prefs['rootdir'])) {
    		$this->rootdir = $this->prefs['rootdir'];
		    return true;
	    }

	    if (!is_array($data = $this->_namespace())) {
	        return false;
	    }

	    $user_space_data = $data[0];
	    if (!is_array($user_space_data)) {
	        return false;
	    }

	    $first_userspace = $user_space_data[0];
	    if (count($first_userspace)!=2) {
	        return false;
	    }

	    $this->rootdir            = $first_userspace[0];
	    $this->delimiter          = $first_userspace[1];
	    $this->prefs['rootdir']   = substr($this->rootdir, 0, -1);
	    $this->prefs['delimiter'] = $this->delimiter;

	    return true;
    }


    /**
     * Gets the delimiter, for example:
     * INBOX.foo -> .
     * INBOX/foo -> /
     * INBOX\foo -> \
     *
     * @return mixed A delimiter (string), or false.
     * @see connect()
     */
    function getHierarchyDelimiter()
    {
	    if ($this->delimiter) {
    		return $this->delimiter;
	    }
	    if (!empty($this->prefs['delimiter'])) {
    	    return ($this->delimiter = $this->prefs['delimiter']);
	    }

	    $delimiter = false;

	    // try (LIST "" ""), should return delimiter (RFC2060 Sec 6.3.8)
	    if (!$this->putLine('ghd LIST "" ""')) {
	        return false;
	    }

	    do {
		    $line = $this->readLine(1024);
		    if (preg_match('/^\* LIST \([^\)]*\) "*([^"]+)"* ""/', $line, $m)) {
    	        $delimiter = $this->unEscape($m[1]);
		    }
	    } while (!$this->startsWith($line, 'ghd', true, true));

	    if (strlen($delimiter)>0) {
	        return $delimiter;
	    }

	    // if that fails, try namespace extension
	    // try to fetch namespace data
	    if (!is_array($data = $this->_namespace())) {
            return false;
        }

	    // extract user space data (opposed to global/shared space)
	    $user_space_data = $data[0];
	    if (!is_array($user_space_data)) {
	        return false;
	    }

	    // get first element
	    $first_userspace = $user_space_data[0];
	    if (!is_array($first_userspace)) {
	        return false;
	    }

	    // extract delimiter
	    $delimiter = $first_userspace[1];

	    return $delimiter;
    }

    function _namespace()
    {
        if (!$this->getCapability('NAMESPACE')) {
	        return false;
	    }

	    if (!$this->putLine("ns1 NAMESPACE")) {
            return false;
        }

	    do {
		    $line = $this->readLine(1024);
		    if (preg_match('/^\* NAMESPACE/', $line)) {
			    $i = 0;
			    $data = $this->parseNamespace(substr($line,11), $i, 0, 0);
		    }
	    } while (!$this->startsWith($line, 'ns1', true, true));

	    if (!is_array($data)) {
	        return false;
	    }

        return $data;
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

	    $message = "INITIAL: $auth_method\n";

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
		    $this->set_error(self::ERROR_BAD, "Empty host");
		    return false;
	    }
        if (empty($user)) {
    		$this->set_error(self::ERROR_NO, "Empty user");
	    	return false;
	    }
	    if (empty($password)) {
	    	$this->set_error(self::ERROR_NO, "Empty password");
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
    		$this->set_error(self::ERROR_BAD, sprintf("Could not connect to %s:%d: %s", $host, $this->prefs['port'], $errstr));
		    return false;
	    }

        if ($this->prefs['timeout'] > 0)
	        stream_set_timeout($this->fp, $this->prefs['timeout']);

	    $line = trim(fgets($this->fp, 8192));

	    if ($this->prefs['debug_mode'] && $line) {
		    write_log('imap', 'S: '. $line);
        }

	    // Connected to wrong port or connection error?
	    if (!preg_match('/^\* (OK|PREAUTH)/i', $line)) {
		    if ($line)
			    $this->error = sprintf("Wrong startup greeting (%s:%d): %s", $host, $this->prefs['port'], $line);
		    else
			    $this->error = sprintf("Empty startup greeting (%s:%d)", $host, $this->prefs['port']);
	        $this->errornum = self::ERROR_BAD;
	        return false;
	    }

	    // RFC3501 [7.1] optional CAPABILITY response
	    if (preg_match('/\[CAPABILITY ([^]]+)\]/i', $line, $matches)) {
		    $this->parseCapability($matches[1]);
	    }

	    $this->message .= $line;

	    // TLS connection
	    if ($this->prefs['ssl_mode'] == 'tls' && $this->getCapability('STARTTLS')) {
        	if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
               	$this->putLine("tls0 STARTTLS");

			    $line = $this->readLine(4096);
                if (!preg_match('/^tls0 OK/', $line)) {
				    $this->set_error(self::ERROR_BAD, "Server responded to STARTTLS with: $line");
                    return false;
                }

			    if (!stream_socket_enable_crypto($this->fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
				    $this->set_error(self::ERROR_BAD, "Unable to negotiate TLS");
				    return false;
			    }

			    // Now we're authenticated, capabilities need to be reread
			    $this->clearCapability();
        	}
	    }

	    $orig_method = $auth_method;

	    if ($auth_method == 'CHECK') {
		    // check for supported auth methods
		    if ($this->getCapability('AUTH=CRAM-MD5') || $this->getCapability('AUTH=CRAM_MD5')) {
			    $auth_method = 'AUTH';
		    }
		    else {
			    // default to plain text auth
			    $auth_method = 'PLAIN';
		    }
	    }

	    if ($auth_method == 'AUTH') {
		    // do CRAM-MD5 authentication
		    $this->putLine("a000 AUTHENTICATE CRAM-MD5");
		    $line = trim($this->readLine(1024));

		    if ($line[0] == '+') {
			    // got a challenge string, try CRAM-MD5
			    $result = $this->authenticate($user, $password, substr($line,2));

			    // stop if server sent BYE response
			    if ($result == self::ERROR_BYE) {
				    return false;
			    }
		    }

		    if (!is_resource($result) && $orig_method == 'CHECK') {
			    $auth_method = 'PLAIN';
		    }
	    }

	    if ($auth_method == 'PLAIN') {
		    // do plain text auth
		    $result = $this->login($user, $password);
	    }

	    if (is_resource($result)) {
            if ($this->prefs['force_caps']) {
			    $this->clearCapability();
            }
		    $this->getNamespace();
            $this->logged = true;

		    return true;
	    } else {
		    return false;
	    }
    }

    function connected()
    {
		return ($this->fp && $this->logged) ? true : false;
    }

    function close()
    {
	    if ($this->logged && $this->putLine("I LOGOUT")) {
		    if (!feof($this->fp))
			    fgets($this->fp, 1024);
	    }
		@fclose($this->fp);
		$this->fp = false;
    }

    function select($mailbox)
    {
	    if (empty($mailbox)) {
		    return false;
	    }
	    if ($this->selected == $mailbox) {
		    return true;
	    }

        $command = "sel1 SELECT " . $this->escape($mailbox);

	    if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }

		do {
			$line = rtrim($this->readLine(512));

			if (preg_match('/^\* ([0-9]+) (EXISTS|RECENT)$/', $line, $m)) {
			    $token = strtolower($m[2]);
			    $this->$token = (int) $m[1];
			}
			else if (preg_match('/\[?PERMANENTFLAGS\s+\(([^\)]+)\)\]/U', $line, $match)) {
			    $this->permanentflags = explode(' ', $match[1]);
			}
		} while (!$this->startsWith($line, 'sel1', true, true));

        if ($this->parseResult($line, 'SELECT: ') == self::ERROR_OK) {
		    $this->selected = $mailbox;
			return true;
		}

        return false;
    }

    function checkForRecent($mailbox)
    {
	    if (empty($mailbox)) {
		    $mailbox = 'INBOX';
	    }

	    $this->select($mailbox);
	    if ($this->selected == $mailbox) {
		    return $this->recent;
	    }

	    return false;
    }

    function countMessages($mailbox, $refresh = false)
    {
	    if ($refresh) {
		    $this->selected = '';
	    }

	    $this->select($mailbox);
	    if ($this->selected == $mailbox) {
		    return $this->exists;
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

	    /*  Do "SELECT" command */
	    if (!$this->select($mailbox)) {
	        return false;
	    }

	    $is_uid = $is_uid ? 'UID ' : '';

	    // message IDs
	    if (is_array($add))
		    $add = $this->compressMessageSet(join(',', $add));

	    $command  = "s ".$is_uid."SORT ($field) $encoding ALL";
	    $line     = '';
	    $data     = '';

	    if (!empty($add))
	        $command .= ' '.$add;

	    if (!$this->putLineC($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
	        return false;
	    }
	    do {
		    $line = rtrim($this->readLine());
		    if (!$data && preg_match('/^\* SORT/', $line)) {
			    $data .= substr($line, 7);
    		} else if (preg_match('/^[0-9 ]+$/', $line)) {
			    $data .= $line;
		    }
	    } while (!$this->startsWith($line, 's ', true, true));

	    $result_code = $this->parseResult($line, 'SORT: ');
	    if ($result_code != self::ERROR_OK) {
            return false;
	    }

	    return preg_split('/\s+/', $data, -1, PREG_SPLIT_NO_EMPTY);
    }

    function fetchHeaderIndex($mailbox, $message_set, $index_field='', $skip_deleted=true, $uidfetch=false)
    {
	    if (is_array($message_set)) {
		    if (!($message_set = $this->compressMessageSet(join(',', $message_set))))
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
    	$fields_a['ARRIVAL'] 	  = 4;
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
	    $key     = 'fhi0';
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
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $request");
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

    private function compressMessageSet($message_set)
    {
	    // given a comma delimited list of independent mid's,
	    // compresses by grouping sequences together

	    // if less than 255 bytes long, let's not bother
	    if (strlen($message_set)<255) {
	        return $message_set;
	    }

	    // see if it's already been compress
	    if (strpos($message_set, ':') !== false) {
	        return $message_set;
	    }

	    // separate, then sort
	    $ids = explode(',', $message_set);
	    sort($ids);

	    $result = array();
	    $start  = $prev = $ids[0];

	    foreach ($ids as $id) {
		    $incr = $id - $prev;
		    if ($incr > 1) {			//found a gap
			    if ($start == $prev) {
			        $result[] = $prev;	//push single id
			    } else {
			        $result[] = $start . ':' . $prev;   //push sequence as start_id:end_id
			    }
        		$start = $id;			//start of new sequence
		    }
		    $prev = $id;
	    }

	    // handle the last sequence/id
	    if ($start==$prev) {
	        $result[] = $prev;
	    } else {
    	    $result[] = $start.':'.$prev;
	    }

	    // return as comma separated string
	    return implode(',', $result);
    }

    function UID2ID($folder, $uid)
    {
	    if ($uid > 0) {
    		$id_a = $this->search($folder, "UID $uid");
	    	if (is_array($id_a) && count($id_a) == 1) {
		    	return $id_a[0];
		    }
	    }
	    return false;
    }

    function ID2UID($folder, $id)
    {
	    if (empty($id)) {
	        return 	-1;
	    }

    	if (!$this->select($folder)) {
            return -1;
        }

	    $result = -1;
        $command = "fuid FETCH $id (UID)";

		if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return -1;
        }

	    do {
		    $line = rtrim($this->readLine(1024));
			if (preg_match("/^\* $id FETCH \(UID (.*)\)/i", $line, $r)) {
				$result = $r[1];
			}
		} while (!$this->startsWith($line, 'fuid', true, true));

    	return $result;
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

	    if (is_array($message_set))
		    $message_set = join(',', $message_set);

	    $message_set = $this->compressMessageSet($message_set);

	    if ($add)
		    $add = ' '.trim($add);

	    /* FETCH uid, size, flags and headers */
	    $key  	  = 'FH12';
	    $request  = $key . ($uidfetch ? ' UID' : '') . " FETCH $message_set ";
	    $request .= "(UID RFC822.SIZE FLAGS INTERNALDATE ";
	    if ($bodystr)
		    $request .= "BODYSTRUCTURE ";
	    $request .= "BODY.PEEK[HEADER.FIELDS (DATE FROM TO SUBJECT CONTENT-TYPE ";
	    $request .= "LIST-POST DISPOSITION-NOTIFICATION-TO".$add.")])";

	    if (!$this->putLine($request)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $request");
		    return false;
	    }
	    do {
		    $line = $this->readLine(1024);
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

			    if (preg_match('/^\* [0-9]+ FETCH \((.*) BODY/s', $line, $matches)) {
				    $str = $matches[1];

				    // swap parents with quotes, then explode
				    $str = preg_replace('/[()]/', '"', $str);
				    $a = rcube_explode_quoted_string(' ', $str);

				    // did we get the right number of replies?
				    $parts_count = count($a);
				    if ($parts_count>=6) {
					    for ($i=0; $i<$parts_count; $i=$i+2) {
						    if ($a[$i] == 'UID')
							    $result[$id]->uid = intval($a[$i+1]);
						    else if ($a[$i] == 'RFC822.SIZE')
							    $result[$id]->size = intval($a[$i+1]);
    						else if ($a[$i] == 'INTERNALDATE')
	    						$time_str = $a[$i+1];
		    				else if ($a[$i] == 'FLAGS')
			    				$flags_str = $a[$i+1];
				    	}

					    $time_str = str_replace('"', '', $time_str);

					    // if time is gmt...
		                $time_str = str_replace('GMT','+0000',$time_str);

					    $result[$id]->internaldate = $time_str;
    					$result[$id]->timestamp = $this->StrToTime($time_str);
	    				$result[$id]->date = $time_str;
		    		}

			    	// BODYSTRUCTURE
				    if($bodystr) {
					    while (!preg_match('/ BODYSTRUCTURE (.*) BODY\[HEADER.FIELDS/s', $line, $m)) {
						    $line2 = $this->readLine(1024);
    						$line .= $this->multLine($line2, true);
	    				}
		    			$result[$id]->body_structure = $m[1];
			    	}

    				// the rest of the result
	    			preg_match('/ BODY\[HEADER.FIELDS \(.*?\)\]\s*(.*)$/s', $line, $m);
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
    				while ( list($lines_key, $str) = each($lines) ) {
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
    						if (preg_match('/^(\d+)/', $string, $matches))
	    						$result[$id]->priority = intval($matches[1]);
		    				break;
			    		default:
				    		if (strlen($field) > 2)
					    		$result[$id]->others[$field] = $string;
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

	    $stripArr = ($field=='subject') ? array('Re: ','Fwd: ','Fw: ','"') : array('"');

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
					    $data = strtoupper(str_replace($stripArr, '', $data));
            		}
			    }
    			$index[$key]=$data;
	    	}

		    // sort index
    		$i = 0;
	    	if ($flag == 'ASC') {
		    	asort($index);
    		} else {
        	    arsort($index);
		    }

    		// form new array based on index
	    	$result = array();
		    reset($index);
    		while (list($key, $val) = each($index)) {
	    		$result[$key]=$a[$key];
		    	$i++;
		    }
	    }

	    return $result;
    }

    function expunge($mailbox, $messages=NULL)
    {
	    if (!$this->select($mailbox)) {
            return -1;
        }

        $c = 0;
		$command = $messages ? "exp1 UID EXPUNGE $messages" : "exp1 EXPUNGE";

		if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return -1;
        }

		do {
			$line = $this->readLine(100);
			if ($line[0] == '*') {
            	$c++;
        	}
		} while (!$this->startsWith($line, 'exp1', true, true));

		if ($this->parseResult($line, 'EXPUNGE: ') == self::ERROR_OK) {
			$this->selected = ''; // state has changed, need to reselect
			return $c;
		}

	    return -1;
    }

    function modFlag($mailbox, $messages, $flag, $mod)
    {
	    if ($mod != '+' && $mod != '-') {
	        return -1;
	    }

	    $flag = $this->flags[strtoupper($flag)];

	    if (!$this->select($mailbox)) {
	        return -1;
	    }

        $c = 0;
        $command = "flg UID STORE $messages {$mod}FLAGS ($flag)";
	    if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }

	    do {
		    $line = $this->readLine();
		    if ($line[0] == '*') {
		        $c++;
            }
	    } while (!$this->startsWith($line, 'flg', true, true));

	    if ($this->parseResult($line, 'STORE: ') == self::ERROR_OK) {
		    return $c;
	    }

	    return -1;
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
	    if (empty($from) || empty($to)) {
	        return -1;
	    }

	    if (!$this->select($from)) {
            return -1;
	    }

        $command = "cpy1 UID COPY $messages ".$this->escape($to);

        if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
        }

        $line = $this->readReply();
	    return $this->parseResult($line, 'COPY: ');
    }

    function countUnseen($folder)
    {
        $index = $this->search($folder, 'ALL UNSEEN');
        if (is_array($index))
            return count($index);
        return false;
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
					    error_log('Mismatched brackets parsing IMAP THREAD response:');
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

    function thread($folder, $algorithm='REFERENCES', $criteria='', $encoding='US-ASCII')
    {
        $old_sel = $this->selected;

	    if (!$this->select($folder)) {
    		return false;
	    }

        // return empty result when folder is empty and we're just after SELECT
        if ($old_sel != $folder && !$this->exists) {
            return array(array(), array(), array());
	    }

    	$encoding  = $encoding ? trim($encoding) : 'US-ASCII';
	    $algorithm = $algorithm ? trim($algorithm) : 'REFERENCES';
	    $criteria  = $criteria ? 'ALL '.trim($criteria) : 'ALL';
        $data      = '';

        $command = "thrd1 THREAD $algorithm $encoding $criteria";

	    if (!$this->putLineC($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
		    return false;
	    }
	    do {
		    $line = trim($this->readLine());
		    if (!$data && preg_match('/^\* THREAD/', $line)) {
    			$data .= substr($line, 9);
	    	} else if (preg_match('/^[0-9() ]+$/', $line)) {
		    	$data .= $line;
		    }
	    } while (!$this->startsWith($line, 'thrd1', true, true));

    	$result_code = $this->parseResult($line, 'THREAD: ');
	    if ($result_code == self::ERROR_OK) {
            $depthmap    = array();
            $haschildren = array();
            $tree = $this->parseThread($data, 0, strlen($data), null, null, 0, $depthmap, $haschildren);
            return array($tree, $depthmap, $haschildren);
	    }

	    return false;
    }

    function search($folder, $criteria, $return_uid=false)
    {
        $old_sel = $this->selected;

	    if (!$this->select($folder)) {
    		return false;
	    }

        // return empty result when folder is empty and we're just after SELECT
        if ($old_sel != $folder && !$this->exists) {
            return array();
	    }

    	$data = '';
	    $query = 'srch1 ' . ($return_uid ? 'UID ' : '') . 'SEARCH ' . trim($criteria);

	    if (!$this->putLineC($query)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $query");
		    return false;
	    }

    	do {
	    	$line = trim($this->readLine());
		    if (!$data && preg_match('/^\* SEARCH/', $line)) {
    			$data .= substr($line, 8);
	    	} else if (preg_match('/^[0-9 ]+$/', $line)) {
		    	$data .= $line;
		    }
    	} while (!$this->startsWith($line, 'srch1', true, true));

	    $result_code = $this->parseResult($line, 'SEARCH: ');
	    if ($result_code == self::ERROR_OK) {
		    return preg_split('/\s+/', $data, -1, PREG_SPLIT_NO_EMPTY);
	    }

	    return false;
    }

    function move($messages, $from, $to)
    {
        if (!$from || !$to) {
            return -1;
        }

        $r = $this->copy($messages, $from, $to);

        if ($r==0) {
            return $this->delete($from, $messages);
        }
        return $r;
    }

    function listMailboxes($ref, $mailbox)
    {
        return $this->_listMailboxes($ref, $mailbox, false);
    }

    function listSubscribed($ref, $mailbox)
    {
        return $this->_listMailboxes($ref, $mailbox, true);
    }

    private function _listMailboxes($ref, $mailbox, $subscribed=false)
    {
		if (empty($mailbox)) {
	        $mailbox = '*';
	    }

	    if (empty($ref) && $this->rootdir) {
	        $ref = $this->rootdir;
	    }

        if ($subscribed) {
            $key     = 'lsb';
            $command = 'LSUB';
        }
        else {
            $key     = 'lmb';
            $command = 'LIST';
        }

        $query = sprintf("%s %s %s %s", $key, $command, $this->escape($ref), $this->escape($mailbox));

    	// send command
	    if (!$this->putLine($query)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $query");
	        return false;
	    }

	    // get folder list
	    do {
		    $line = $this->readLine(500);
		    $line = $this->multLine($line, true);
		    $line = trim($line);

		    if (preg_match('/^\* '.$command.' \(([^\)]*)\) "*([^"]+)"* (.*)$/', $line, $m)) {
        		// folder name
   			    $folders[] = preg_replace(array('/^"/', '/"$/'), '', $this->unEscape($m[3]));
		        // attributes
//        		$attrib = explode(' ', $this->unEscape($m[1]));
		        // delimiter
//        		$delim = $this->unEscape($m[2]);
		    }
	    } while (!$this->startsWith($line, $key, true));

	    if (is_array($folders)) {
    	    return $folders;
	    } else if ($this->parseResult($line, $command.': ') == self::ERROR_OK) {
            return array();
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
    	$key    = 'fmh0';
	    $peeks  = '';
    	$idx    = 0;
        $type   = $mime ? 'MIME' : 'HEADER';

	    // format request
	    foreach($parts as $part)
		    $peeks[] = "BODY.PEEK[$part.$type]";

	    $request = "$key FETCH $id (" . implode(' ', $peeks) . ')';

	    // send request
	    if (!$this->putLine($request)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $request");
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
		$key       = 'ftch0';
		$request   = $key . ($is_uid ? ' UID' : '') . " FETCH $id (BODY.PEEK[$part])";

    	// send request
		if (!$this->putLine($request)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $request");
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

        	if ($mode == 1)
				$result = base64_decode($result);
			else if ($mode == 2)
				$result = quoted_printable_decode($result);
			else if ($mode == 3)
				$result = convert_uudecode($result);

    	} else if ($line[$len-1] == '}') {
	        // multi-line request, find sizes of content and receive that many bytes
        	$from     = strpos($line, '{') + 1;
	        $to       = strrpos($line, '}');
        	$len      = $to - $from;
	        $sizeStr  = substr($line, $from, $len);
        	$bytes    = (int)$sizeStr;
			$prev	  = '';

        	while ($bytes > 0) {
    		    $line = $this->readLine(1024);

    		    if ($line === NULL)
    		        break;

            	$len  = strlen($line);

		        if ($len > $bytes) {
            		$line = substr($line, 0, $bytes);
					$len = strlen($line);
		        }
            	$bytes -= $len;

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

					if ($file)
						fwrite($file, base64_decode($line));
            		else if ($print)
						echo base64_decode($line);
					else
						$result .= base64_decode($line);
				} else if ($mode == 2) {
					$line = rtrim($line, "\t\r\0\x0B");
					if ($file)
						fwrite($file, quoted_printable_decode($line));
            		else if ($print)
						echo quoted_printable_decode($line);
					else
						$result .= quoted_printable_decode($line);
				} else if ($mode == 3) {
					$line = rtrim($line, "\t\r\n\0\x0B");
					if ($line == 'end' || preg_match('/^begin\s+[0-7]+\s+.+$/', $line))
						continue;
					if ($file)
						fwrite($file, convert_uudecode($line));
            		else if ($print)
						echo convert_uudecode($line);
					else
						$result .= convert_uudecode($line);
				} else {
					$line = rtrim($line, "\t\r\n\0\x0B");
					if ($file)
						fwrite($file, $line . "\n");
            		else if ($print)
						echo $line . "\n";
					else
						$result .= $line . "\n";
				}
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

    function createFolder($folder)
    {
        $command = sprintf("c CREATE %s", $this->escape($folder));

	    if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }

	    do {
		    $line = $this->readLine(300);
	    } while (!$this->startsWith($line, 'c ', true, true));

	    return ($this->parseResult($line, 'CREATE: ') == self::ERROR_OK);
    }

    function renameFolder($from, $to)
    {
        $command = sprintf("r RENAME %s %s", $this->escape($from), $this->escape($to));

	    if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }
		do {
		    $line = $this->readLine(300);
		} while (!$this->startsWith($line, 'r ', true, true));

		return ($this->parseResult($line, 'RENAME: ') == self::ERROR_OK);
    }

    function deleteFolder($folder)
    {
        $command = sprintf("d DELETE %s", $this->escape($folder));

	    if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }
	    do {
		    $line = $this->readLine(300);
	    } while (!$this->startsWith($line, 'd ', true, true));

	    return ($this->parseResult($line, 'DELETE: ') == self::ERROR_OK);
    }

    function clearFolder($folder)
    {
	    $num_in_trash = $this->countMessages($folder);
	    if ($num_in_trash > 0) {
		    $this->delete($folder, '1:*');
	    }
	    return ($this->expunge($folder) >= 0);
    }

    function subscribe($folder)
    {
	    $command = sprintf("sub1 SUBSCRIBE %s", $this->escape($folder));

	    if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }

    	$line = trim($this->readLine(512));
	    return ($this->parseResult($line, 'SUBSCRIBE: ') == self::ERROR_OK);
    }

    function unsubscribe($folder)
    {
        $command = sprintf("usub1 UNSUBSCRIBE %s", $this->escape($folder));

	    if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }

	    $line = trim($this->readLine(512));
	    return ($this->parseResult($line, 'UNSUBSCRIBE: ') == self::ERROR_OK);
    }

    function append($folder, &$message)
    {
	    if (!$folder) {
		    return false;
	    }

    	$message = str_replace("\r", '', $message);
	    $message = str_replace("\n", "\r\n", $message);

    	$len = strlen($message);
	    if (!$len) {
		    return false;
	    }

	    $request = sprintf("a APPEND %s (\\Seen) {%d%s}", $this->escape($folder),
            $len, ($this->prefs['literal+'] ? '+' : ''));

	    if ($this->putLine($request)) {
            // Don't wait when LITERAL+ is supported
            if (!$this->prefs['literal+']) {
                $line = $this->readLine(512);

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
    		} while (!$this->startsWith($line, 'a ', true, true));

    		return ($this->parseResult($line, 'APPEND: ') == self::ERROR_OK);
	    }
        else {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $request");
        }

	    return false;
    }

    function appendFromFile($folder, $path, $headers=null)
    {
	    if (!$folder) {
	        return false;
	    }

	    // open message file
	    $in_fp = false;
	    if (file_exists(realpath($path))) {
		    $in_fp = fopen($path, 'r');
	    }
	    if (!$in_fp) {
		    $this->set_error(self::ERROR_UNKNOWN, "Couldn't open $path for reading");
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
	    $request = sprintf("a APPEND %s (\\Seen) {%d%s}", $this->escape($folder),
            $len, ($this->prefs['literal+'] ? '+' : ''));

	    if ($this->putLine($request)) {
            // Don't wait when LITERAL+ is supported
            if (!$this->prefs['literal+']) {
    		    $line = $this->readLine(512);

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
		    } while (!$this->startsWith($line, 'a ', true, true));


		    return ($this->parseResult($line, 'APPEND: ') == self::ERROR_OK);
	    }
        else {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $request");
        }

	    return false;
    }

    function fetchStructureString($folder, $id, $is_uid=false)
    {
	    if (!$this->select($folder)) {
            return false;
        }

		$key = 'F1247';
    	$result = false;
        $command = $key . ($is_uid ? ' UID' : '') ." FETCH $id (BODYSTRUCTURE)";

		if ($this->putLine($command)) {
			do {
				$line = $this->readLine(5000);
				$line = $this->multLine($line, true);
				if (!preg_match("/^$key/", $line))
					$result .= $line;
			} while (!$this->startsWith($line, $key, true, true));

			$result = trim(substr($result, strpos($result, 'BODYSTRUCTURE')+13, -1));
		}
        else {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
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
        $command     = 'QUOT1 GETQUOTAROOT "INBOX"';

	    // get line(s) containing quota info
	    if ($this->putLine($command)) {
		    do {
			    $line = rtrim($this->readLine(5000));
			    if (preg_match('/^\* QUOTA /', $line)) {
				    $quota_lines[] = $line;
        		}
		    } while (!$this->startsWith($line, 'QUOT1', true, true));
	    }
        else {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
        }

	    // return false if not found, parse if found
	    $min_free = PHP_INT_MAX;
	    foreach ($quota_lines as $key => $quota_line) {
		    $quota_line   = str_replace(array('(', ')'), '', $quota_line);
		    $parts        = explode(' ', $quota_line);
		    $storage_part = array_search('STORAGE', $parts);

		    if (!$storage_part)
                continue;

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

        $key     = 'acl1';
        $command = sprintf("%s SETACL %s %s %s",
            $key, $this->escape($mailbox), $this->escape($user), strtolower($acl));

		if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }

		$line = $this->readReply();
	    return ($this->parseResult($line, 'SETACL: ') == self::ERROR_OK);
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
        $key     = 'acl2';
        $command = sprintf("%s DELETEACL %s %s",
            $key, $this->escape($mailbox), $this->escape($user));

		if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }

		$line = $this->readReply();
	    return ($this->parseResult($line, 'DELETEACL: ') == self::ERROR_OK);
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
        $key     = 'acl3';
        $command = sprintf("%s GETACL %s", $key, $this->escape($mailbox));

		if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return NULL;
        }

        $response = '';

		do {
		    $line = $this->readLine(4096);
            $response .= $line;
        } while (!$this->startsWith($line, $key, true, true));

        if ($this->parseResult($line, 'GETACL: ') == self::ERROR_OK) {
            // Parse server response (remove "* ACL " and "\r\nacl3 OK...")
            $response = substr($response, 6, -(strlen($line)+2));
            $ret  = $this->tokenizeResponse($response);
            $mbox = array_unshift($ret);
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

            $this->set_error(self::ERROR_COMMAND, "Incomplete ACL response");
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
        $key     = 'acl4';
        $command = sprintf("%s LISTRIGHTS %s %s",
            $key, $this->escape($mailbox), $this->escape($user));

		if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return NULL;
        }

        $response = '';

		do {
		    $line = $this->readLine(4096);
            $response .= $line;
        } while (!$this->startsWith($line, $key, true, true));

        if ($this->parseResult($line, 'LISTRIGHTS: ') == self::ERROR_OK) {
            // Parse server response (remove "* LISTRIGHTS " and "\r\nacl3 OK...")
            $response = substr($response, 13, -(strlen($line)+2));

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
        $key = 'acl5';
        $command = sprintf("%s MYRIGHTS %s", $key, $this->escape(mailbox));

		if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return NULL;
        }

        $response = '';

		do {
            $line = $this->readLine(1024);
            $response .= $line;
        } while (!$this->startsWith($line, $key, true, true));

        if ($this->parseResult($line, 'MYRIGHTS: ') == self::ERROR_OK) {
            // Parse server response (remove "* MYRIGHTS " and "\r\nacl5 OK...")
            $response = substr($response, 11, -(strlen($line)+2));

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
            $this->set_error(self::ERROR_COMMAND, "Wrong argument for SETMETADATA command");
            return false;
        }

        foreach ($entries as $name => $value) {
            if ($value === null)
                $value = 'NIL';
            else
                $value = sprintf("{%d}\r\n%s", strlen($value), $value);

            $entries[$name] = $this->escape($name) . ' ' . $value;
        }

        $entries = implode(' ', $entries);
        $key     = 'md1';
        $command = sprintf("%s SETMETADATA %s (%s)",
            $key, $this->escape($mailbox), $entries);

		if (!$this->putLineC($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }

        $line = $this->readReply();
        return ($this->parseResult($line, 'SETMETADATA: ') == self::ERROR_OK);
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
        if (!is_array($entries) && !empty($entries))
            $entries = explode(' ', $entries);

        if (empty($entries)) {
            $this->set_error(self::ERROR_COMMAND, "Wrong argument for SETMETADATA command");
            return false;
        }

        foreach ($entries as $entry)
            $data[$entry] = NULL;

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

            if (!empty($options['MAXSIZE']))
                $opts[] = 'MAXSIZE '.intval($options['MAXSIZE']);
            if (!empty($options['DEPTH']))
                $opts[] = 'DEPTH '.intval($options['DEPTH']);

            if ($opts)
                $optlist = '(' . implode(' ', $opts) . ')';
        }

        $optlist .= ($optlist ? ' ' : '') . $entlist;

        $key     = 'md2';
        $command = sprintf("%s GETMETADATA %s %s",
            $key, $this->escape($mailbox), $optlist);

		if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return NULL;
        }

        $response = '';

		do {
		    $line = $this->readLine(4096);
            $response .= $line;
        } while (!$this->startsWith($line, $key, true, true));

        if ($this->parseResult($line, 'GETMETADATA: ') == self::ERROR_OK) {
            // Parse server response (remove "* METADATA " and "\r\nmd2 OK...")
            $response = substr($response, 11, -(strlen($line)+2));
            $ret_mbox = $this->tokenizeResponse($response, 1);
            $data     = $this->tokenizeResponse($response);

            // The METADATA response can contain multiple entries in a single
            // response or multiple responses for each entry or group of entries
            if (!empty($data) && ($size = count($data))) {
                for ($i=0; $i<$size; $i++) {
                    if (is_array($data[$i])) {
                        $size_sub = count($data[$i]);
                        for ($x=0; $x<$size_sub; $x++) {
                            $data[$data[$i][$x]] = $data[$i][++$x];
                        }
                        unset($data[$i]);
                    }
                    else if ($data[$i] == '*' && $data[$i+1] == 'METADATA') {
                        unset($data[$i]);   // "*"
                        unset($data[++$i]); // "METADATA"
                        unset($data[++$i]); // Mailbox
                    }
                    else {
                        $data[$data[$i]] = $data[++$i];
                        unset($data[$i]);
                        unset($data[$i-1]);
                    }
                }
            }

            return $data;
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
            $this->set_error(self::ERROR_COMMAND, "Wrong argument for SETANNOTATION command");
            return false;
        }

        foreach ($data as $entry) {
            $name  = $entry[0];
            $attr  = $entry[1];
            $value = $entry[2];

            if ($value === null)
                $value = 'NIL';
            else
                $value = sprintf("{%d}\r\n%s", strlen($value), $value);

            $entries[] = sprintf('%s (%s %s)',
                $this->escape($name), $this->escape($attr), $value);
        }

        $entries = implode(' ', $entries);
        $key     = 'an1';
        $command = sprintf("%s SETANNOTATION %s %s",
            $key, $this->escape($mailbox), $entries);

		if (!$this->putLineC($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return false;
        }

        $line = $this->readReply();
        return ($this->parseResult($line, 'SETANNOTATION: ') == self::ERROR_OK);
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
            $this->set_error(self::ERROR_COMMAND, "Wrong argument for SETANNOTATION command");
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
        foreach ($entries as $idx => $name) {
            $entries[$idx] = $this->escape($name);
        }
        $entries = '(' . implode(' ', $entries) . ')';

        if (!is_array($attribs)) {
            $attribs = array($attribs);
        }
        // create entries string
        foreach ($attribs as $idx => $name) {
            $attribs[$idx] = $this->escape($name);
        }
        $attribs = '(' . implode(' ', $attribs) . ')';

        $key     = 'an2';
        $command = sprintf("%s GETANNOTATION %s %s %s",
            $key, $this->escape($mailbox), $entries, $attribs);

		if (!$this->putLine($command)) {
            $this->set_error(self::ERROR_COMMAND, "Unable to send command: $command");
            return NULL;
        }

        $response = '';

		do {
		    $line = $this->readLine(4096);
            $response .= $line;
        } while (!$this->startsWith($line, $key, true, true));

        if ($this->parseResult($line, 'GETANNOTATION: ') == self::ERROR_OK) {
            // Parse server response (remove "* ANNOTATION " and "\r\nan2 OK...")
            $response = substr($response, 13, -(strlen($line)+2));
            $ret_mbox = $this->tokenizeResponse($response, 1);
            $data     = $this->tokenizeResponse($response);
            $res      = array();

            // Here we returns only data compatible with METADATA result format
            if (!empty($data) && ($size = count($data))) {
                for ($i=0; $i<$size; $i++) {
                    $entry = $data[$i++];
                    if (is_array($entry)) {
                        $attribs = $entry;
                        $entry   = $last_entry;
                    }
                    else
                        $attribs = $data[$i++];

                    if (!empty($attribs)) {
                        for ($x=0, $len=count($attribs); $x<$len;) {
                            $attr  = $attribs[$x++];
                            $value = $attribs[$x++];
                            if ($attr == 'value.priv')
                                $res['/private' . $entry] = $value;
                            else if ($attr == 'value.shared')
                                $res['/shared' . $entry] = $value;
                        }
                    }
                    $last_entry = $entry;
                    unset($data[$i-1]);
                    unset($data[$i-2]);
                }
            }

            return $res;
        }

        return NULL;
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
	    $size = strlen($string);
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
	    // support non-standard "GMTXXXX" literal
	    $date = preg_replace('/GMT\s*([+-][0-9]+)/', '\\1', $date);
        // if date parsing fails, we have a date in non-rfc format.
	    // remove token from the end and try again
	    while ((($ts = @strtotime($date))===false) || ($ts < 0)) {
	        $d = explode(' ', $date);
		    array_pop($d);
		    if (!$d) break;
		    $date = implode(' ', $d);
	    }

	    $ts = (int) $ts;

	    return $ts < 0 ? 0 : $ts;
    }

    private function SplitHeaderLine($string)
    {
	    $pos = strpos($string, ':');
	    if ($pos>0) {
		    $res[0] = substr($string, 0, $pos);
		    $res[1] = trim(substr($string, $pos+1));
		    return $res;
	    }
    	return $string;
    }

    private function parseNamespace($str, &$i, $len=0, $l)
    {
	    if (!$l) {
	        $str = str_replace('NIL', '()', $str);
	    }
	    if (!$len) {
	        $len = strlen($str);
	    }
	    $data      = array();
	    $in_quotes = false;
	    $elem      = 0;

        for ($i; $i<$len; $i++) {
		    $c = (string)$str[$i];
		    if ($c == '(' && !$in_quotes) {
			    $i++;
			    $data[$elem] = $this->parseNamespace($str, $i, $len, $l++);
			    $elem++;
		    } else if ($c == ')' && !$in_quotes) {
			    return $data;
    		} else if ($c == '\\') {
			    $i++;
			    if ($in_quotes) {
				    $data[$elem] .= $str[$i];
        		}
		    } else if ($c == '"') {
			    $in_quotes = !$in_quotes;
			    if (!$in_quotes) {
				    $elem++;
        		}
		    } else if ($in_quotes) {
			    $data[$elem].=$c;
		    }
	    }

        return $data;
    }

    private function parseCapability($str)
    {
        $this->capability = explode(' ', strtoupper($str));

        if (!isset($this->prefs['literal+']) && in_array('LITERAL+', $this->capability)) {
            $this->prefs['literal+'] = true;
        }
    }

    /**
     * Escapes a string when it contains special characters (RFC3501)
     *
     * @param string $string IMAP string
     *
     * @return string Escaped string
     * @todo String literals, lists
     */
    static function escape($string)
    {
        // NIL
        if ($string === null) {
            return 'NIL';
        }
        // empty string
        else if ($string === '') {
            return '""';
        }
        // string: special chars: SP, CTL, (, ), {, %, *, ", \, ]
        else if (preg_match('/([\x00-\x20\x28-\x29\x7B\x25\x2A\x22\x5C\x5D\x7F]+)/', $string)) {
	        return '"' . strtr($string, array('"'=>'\\"', '\\' => '\\\\')) . '"';
        }

        // atom
        return $string;
    }

    static function unEscape($string)
    {
	    return strtr($string, array('\\"'=>'"', '\\\\' => '\\'));
    }

}

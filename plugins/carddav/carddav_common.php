<?php
/*
    RCM CardDAV Plugin
    Copyright (C) 2011-2016 Benjamin Schieder <rcmcarddav@wegwerf.anderdonau.de>,
                            Michael Stilkerich <ms@mike2k.de>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

if (file_exists(__DIR__ . '/vendor/autoload.php'))
	require_once __DIR__ . '/vendor/autoload.php';

\Httpful\Bootstrap::init();

class carddav_common
{
	const DEBUG      = false; // set to true for basic debugging
	const DEBUG_HTTP = false; // set to true for debugging raw http stream

	const NSDAV     = 'DAV:';
	const NSCARDDAV = 'urn:ietf:params:xml:ns:carddav';

	// admin settings from config.inc.php
	private static $admin_settings;
	// encryption scheme
	public static $pwstore_scheme = 'base64';

	private $module_prefix = '';

	public function __construct($module_prefix = '')
	{{{
	$this->module_prefix = $module_prefix;
	}}}

	public static function concaturl($str, $cat)
	{{{
	preg_match(";(^https?://[^/]+)(.*);", $str, $match);
	$hostpart = $match[1];
	$urlpart  = $match[2];

	// is $cat already a full URL?
	if(strpos($cat, '://') !== FALSE) {
		return $cat;
	}

	// is $cat a simple filename?
	// then attach it to the URL
	if (substr($cat, 0, 1) != "/"){
		$urlpart .= "/$cat";

		// $cat is a full path, the append it to the
		// hostpart only
	} else {
		$urlpart = $cat;
	}

	// remove // in the path
	$urlpart = preg_replace(';//+;','/',$urlpart);
	return $hostpart.$urlpart;
	}}}

	// log helpers
	private function getCaller()
	{{{
	// determine calling function for debug output
	if (version_compare(PHP_VERSION, "5.4", ">=")){
		$caller=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
	} else {
		$caller=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	}
	$caller=$caller[2]['function'];
	return $caller;
	}}}

	public function warn()
	{{{
	$caller=self::getCaller();
	rcmail::write_log("carddav.warn", $this->module_prefix . "($caller) " . implode(' ', func_get_args()));
	}}}

	public function debug()
	{{{
	if(self::DEBUG) {
		$caller=self::getCaller();
		rcmail::write_log("carddav", $this->module_prefix . "($caller) " . implode(' ', func_get_args()));
	}
	}}}

	public function debug_http()
	{{{
	if(self::DEBUG_HTTP) {
		$caller=self::getCaller();
		rcmail::write_log("carddav", $this->module_prefix . "($caller) " . implode(' ', func_get_args()));
	}
	}}}

	// XML helpers
	public function checkAndParseXML($reply) {
		if(!is_array($reply))
			return false;

		if(!self::check_contenttype($reply['headers']['content-type'], ';(text|application)/xml;'))
			return false;

		$xml = new SimpleXMLElement($reply['body']);
		$this->registerNamespaces($xml);
		return $xml;
	}

	public function registerNamespaces($xml) {
		// Use slightly complex prefixes to avoid conflicts
		$xml->registerXPathNamespace('RCMCC', self::NSCARDDAV);
		$xml->registerXPathNamespace('RCMCD', self::NSDAV);
	}

	// HTTP helpers
	/**
	 * @param $url: url of the requested resource
	 *
	 * @param $http_opts: Options for the HTTP request, keys:
	 *             - method: request method (GET, PROPFIND, etc.)
	 *             - content: request body
	 *             - header: array of HTTP request headers as simple strings
	 *
	 * @param $carddav: config array containing at least the keys
	 *             - url: base url, used if $url is a relative url
	 *             - username
	 *             - password: password (encoded/encrypted form as stored in DB)
	 */
	public function cdfopen($url, $http_opts, $carddav)
	{{{
	$redirect_limit = 5;
	$rcmail = rcmail::get_instance();

	$username=$carddav['username'];
	$password = self::decrypt_password($carddav['password']);
	$baseurl=$carddav['url'];

	// determine calling function for debug output
	$caller=self::getCaller();

	$local = $rcmail->user->get_username('local');
	$domain = $rcmail->user->get_username('domain');

	// Substitute Placeholders
	$username = str_replace( '%u', $_SESSION['username'], $username);
	$username = str_replace( '%V' ,str_replace('@','_', str_replace('.','_',$_SESSION['username'])), $username);
	$username = str_replace( '%l', $local, $username);
	$username = str_replace( '%d', $domain, $username);
	if($password == '%p')
		$password = $rcmail->decrypt($_SESSION['password']);
	$baseurl = str_replace("%u", $username, $carddav['url']);
	$url = str_replace("%u", $username, $url);
	$baseurl = str_replace("%l", $local, $baseurl);
	$url = str_replace("%l", $local, $url);
	$baseurl = str_replace("%d", $domain, $baseurl);
	$url = str_replace("%d", $domain, $url);

	// if $url is relative, prepend the base url
	$url = self::concaturl($baseurl, $url);

	do {
		$isRedirect = false;
		if (self::DEBUG){ $this->debug("$caller requesting $url as user $username [RL $redirect_limit]"); }

		$httpful = \Httpful\Request::init();
		$scheme = strtolower($carddav['authentication_scheme']);
		if ($scheme != "basic" && $scheme != "digest" && $scheme != "negotiate"){
				/* figure out authentication */
				$httpful->addHeader("User-Agent", "RCM CardDAV plugin/3.0.3");
				$httpful->uri($url);
				$httpful->method($http_opts['method']);
				$error = $httpful->send();

				$httpful = \Httpful\Request::init();
				$scheme = "unknown";
				// Using raw_headers since there might be multiple www-authenticate headers
				if (preg_match("/^(.*\n)*WWW-Authenticate:\s+Negotiate\b/i", $error->raw_headers) && !empty($_SERVER['KRB5CCNAME'])){
					$httpful->negotiateAuth($username, $password);
					$scheme = "negotiate";
				} else if (preg_match("/\bDigest\b/i", $error->headers["www-authenticate"])){
					$httpful->digestAuth($username, $password);
					$scheme = "digest";
				} else if (preg_match("/\bBasic\b/i", $error->headers["www-authenticate"])){
					$httpful->basicAuth($username, $password);
					$scheme = "basic";
				}

				if ($scheme != "unknown")
					carddav_backend::update_addressbook($carddav['abookid'], array("authentication_scheme"), array($scheme));
		} else {
			// if we have KRB5CCNAME, use negotiate even if current scheme is basic
			if ((strtolower($scheme) == "negotiate" || strtolower($scheme) == "basic") && !empty($_SERVER['KRB5CCNAME'])) {
				$httpful->negotiateAuth($username, $password);
			} else if (strtolower($scheme) == "digest"){
				$httpful->digestAuth($username, $password);
			// if we don't have KRB5CCNAME, use basic even if current scheme is negotiate
			} else if (strtolower($scheme) == "negotiate" || strtolower($scheme) == "basic"){
				$httpful->basicAuth($username, $password);
			}
		}

		$httpful->addHeader("User-Agent", "RCM CardDAV plugin/3.0.3");
		$httpful->uri($url);

		$httpful->method($http_opts['method']);
		if (array_key_exists('content',$http_opts) && strlen($http_opts['content'])>0 && $http_opts['method'] != "GET"){
			$httpful->body($http_opts['content']);
		}

		if(array_key_exists('header',$http_opts)) {
			foreach ($http_opts['header'] as $header){
				$h = explode(": ", $header);
				if (strlen($h[0]) > 0 && strlen($h[1]) > 0){
					// Only append headers with key AND value
					$httpful->addHeader($h[0], $h[1]);
				}
			}
		}

		$reply = $httpful->send();
		$scode = $reply->code;
		if (self::DEBUG){ $this->debug("Code: $scode"); }

		$isRedirect = ($scode>300 && $scode<304) || $scode==307;
		if($isRedirect && strlen($reply->headers['location'])>0) {
			$url = self::concaturl($baseurl, $reply->headers['location']);
		} else {
			$retVal["status"] = $scode;
			$retVal["headers"] = $reply->headers;
			$retVal["body"] = $reply->raw_body;
			if (self::DEBUG_HTTP){ $this->debug_http("success: ".var_export($retVal, true)); }
			return $retVal;
		}
	} while($redirect_limit-->0 && $isRedirect);

	return $reply->code;
	}}}

	public function check_contenttype($ctheader, $expectedct)
	{{{
	if(!is_array($ctheader)) {
		$ctheader = array($ctheader);
	}

	foreach($ctheader as $ct) {
		if(preg_match($expectedct, $ct))
			return true;
	}

	return false;
	}}}

	// password helpers
	private function carddav_des_key()
	{{{
	$rcmail = rcmail::get_instance();
	$imap_password = $rcmail->decrypt($_SESSION['password']);
	while(strlen($imap_password)<24) {
		$imap_password .= $imap_password;
	}
	return substr($imap_password, 0, 24);
	}}}

	public function encrypt_password($clear)
	{{{
	if(strcasecmp(self::$pwstore_scheme, 'plain')===0)
		return $clear;

	if(strcasecmp(self::$pwstore_scheme, 'encrypted')===0) {

		// return {IGNORE} scheme if session password is empty (krb_authentication plugin)
		if(empty($_SESSION['password'])) return '{IGNORE}';

		// encrypted with IMAP password
		$rcmail = rcmail::get_instance();

		$imap_password = self::carddav_des_key();
		$deskey_backup = $rcmail->config->set('carddav_des_key', $imap_password);

		$crypted = $rcmail->encrypt($clear, 'carddav_des_key');

		// there seems to be no way to unset a preference
		$deskey_backup = $rcmail->config->set('carddav_des_key', '');

		return '{ENCRYPTED}'.$crypted;
	}

	if(strcasecmp(self::$pwstore_scheme, 'des_key')===0) {

		// encrypted with global des_key
		$rcmail = rcmail::get_instance();
		$crypted = $rcmail->encrypt($clear);

		return '{DES_KEY}'.$crypted;
	}

	// default: base64-coded password
	return '{BASE64}'.base64_encode($clear);
	}}}

	public function password_scheme($crypt)
	{{{
	if(strpos($crypt, '{IGNORE}') === 0)
		return 'ignore';

	if(strpos($crypt, '{ENCRYPTED}') === 0)
		return 'encrypted';

	if(strpos($crypt, '{DES_KEY}') === 0)
		return 'des_key';

	if(strpos($crypt, '{BASE64}') === 0)
		return 'base64';

	// unknown scheme, assume cleartext
	return 'plain';
	}}}

	public function decrypt_password($crypt)
	{{{
	if(strpos($crypt, '{ENCRYPTED}') === 0) {
		// return {IGNORE} scheme if session password is empty (krb_authentication plugin)
		if (empty($_SESSION['password'])) return '{IGNORE}';

		$crypt = substr($crypt, strlen('{ENCRYPTED}'));
		$rcmail = rcmail::get_instance();

		$imap_password = self::carddav_des_key();
		$deskey_backup = $rcmail->config->set('carddav_des_key', $imap_password);

		$clear = $rcmail->decrypt($crypt, 'carddav_des_key');

		// there seems to be no way to unset a preference
		$deskey_backup = $rcmail->config->set('carddav_des_key', '');

		return $clear;
	}

	if(strpos($crypt, '{DES_KEY}') === 0) {
		$crypt = substr($crypt, strlen('{DES_KEY}'));
		$rcmail = rcmail::get_instance();

		return $rcmail->decrypt($crypt);
	}

	if(strpos($crypt, '{BASE64}') === 0) {
		$crypt = substr($crypt, strlen('{BASE64}'));
		return base64_decode($crypt);
	}

	// unknown scheme, assume cleartext
	return $crypt;
	}}}

	// admin settings from config.inc.php
	public static function get_adminsettings()
	{{{
	if(is_array(self::$admin_settings))
		return self::$admin_settings;

	$rcmail = rcmail::get_instance();
	$prefs = array();
	$configfile = dirname(__FILE__)."/config.inc.php";
	if (file_exists($configfile)){
		require("$configfile");
	}
	self::$admin_settings = $prefs;

	if(is_array($prefs['_GLOBAL'])) {
		$scheme = $prefs['_GLOBAL']['pwstore_scheme'];
		if(preg_match("/^(plain|base64|encrypted|des_key)$/", $scheme))
			self::$pwstore_scheme = $scheme;
	}
	return $prefs;
	}}}

	// short form for deprecated Q helper function
	public function Q($str, $mode='strict', $newlines=true)
	{{{
		return rcube_utils::rep_specialchars_output($str, 'html', $mode, $newlines);
	}}}
}

?>

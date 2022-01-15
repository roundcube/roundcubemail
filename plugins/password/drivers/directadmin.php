<?php

/**
 * DirectAdmin Password Driver
 *
 * Driver to change passwords via DirectAdmin Control Panel
 *
 * @version 2.2
 * @author Victor Benincasa <vbenincasa @ gmail.com>
 *
 * Copyright (C) The Roundcube Dev Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

class rcube_directadmin_password
{
    public function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();
        $Socket = new HTTPSocket;

        $da_user    = $_SESSION['username'];
        $da_curpass = $curpass;
        $da_newpass = $passwd;
        $da_host    = $rcmail->config->get('password_directadmin_host');
        $da_port    = $rcmail->config->get('password_directadmin_port');

        if (strpos($da_user, '@') === false) {
            return ['code' => PASSWORD_ERROR, 'message' => 'Change the SYSTEM user password through control panel!'];
        }

        $da_host = str_replace('%h', $_SESSION['imap_host'], $da_host);
        $da_host = str_replace('%d', $rcmail->user->get_username('domain'), $da_host);

        $Socket->connect($da_host,$da_port); 
        $Socket->set_method('POST');
        $Socket->query('/CMD_CHANGE_EMAIL_PASSWORD', [
                'email'         => $da_user,
                'oldpassword'   => $da_curpass,
                'password1'     => $da_newpass,
                'password2'     => $da_newpass,
                'api'           => '1'
        ]);

        $response = $Socket->fetch_parsed_body();

        //DEBUG
        //rcube::console("Password Plugin: [USER: $da_user] [HOST: $da_host] - Response: [SOCKET: ".$Socket->result_status_code."] [DA ERROR: ".strip_tags($response['error'])."] [TEXT: ".$response[text]."]");

        if ($Socket->result_status_code != 200) {
            return ['code' => PASSWORD_CONNECT_ERROR, 'message' => $Socket->error[0]];
        }

        if ($response['error'] == 1) {
            return ['code' => PASSWORD_ERROR, 'message' => strip_tags($response['text'])];
        }

        return PASSWORD_SUCCESS;
    }
}


/**
 * Socket communication class.
 *
 * Originally designed for use with DirectAdmin's API, this class will fill any HTTP socket need.
 *
 * Very, very basic usage:
 *   $Socket = new HTTPSocket;
 *   echo $Socket->get('http://user:pass@somesite.com/somedir/some.file?query=string&this=that');
 *
 * @author Phi1 'l0rdphi1' Stier <l0rdphi1@liquenox.net>
 * @package HTTPSocket
 * @version 3.0.2
 */
class HTTPSocket
{
    var $version = '3.0.2';

    // all vars are private except $error, $query_cache, and $doFollowLocationHeader

    var $method = 'GET';

    var $remote_host;
    var $remote_port;
    var $remote_uname;
    var $remote_passwd;

    var $result;
    var $result_header;
    var $result_body;
    var $result_status_code;

    var $lastTransferSpeed;
    var $bind_host;
    var $error       = [];
    var $warn        = [];
    var $query_cache = [];
    var $doFollowLocationHeader = true;
    var $redirectURL;
    var $max_redirects = 5;
    var $ssl_setting_message = 'DirectAdmin appears to be using SSL. Change your script to connect to ssl://';
    var $extra_headers = [];

    /**
     * Create server "connection".
     *
     */
    function connect($host, $port = '')
    {
        if (!is_numeric($port)) {
            $port = 2222;
        }

        $this->remote_host = $host;
        $this->remote_port = $port;
    }

    function bind($ip = '')
    {
        if ($ip == '') {
            $ip = $_SERVER['SERVER_ADDR'];
        }

        $this->bind_host = $ip;
    }

    /**
     * Change the method being used to communicate.
     *
     * @param string|null request method. supports GET, POST, and HEAD. default is GET
     */
    function set_method($method = 'GET')
    {
        $this->method = strtoupper($method);
    }

    /**
     * Specify a username and password.
     *
     * @param string|null username. default is null
     * @param string|null password. default is null
     */
    function set_login($uname = '', $passwd = '')
    {
        if (strlen($uname) > 0) {
            $this->remote_uname = $uname;
        }

        if (strlen($passwd) > 0) {
            $this->remote_passwd = $passwd;
        }
    }

    /**
     * Query the server
     *
     * @param string containing properly formatted server API. See DA API docs and examples. Http:// URLs O.K. too.
     * @param string|array query to pass to url
     */
    function query($request, $content = '')
    {
        $this->error = $this->warn = [];
        $this->result_status_code  = null;

        $is_ssl = false;

        // is our request a http:// ... ?
        if (preg_match('!^http://!i',$request) || preg_match('!^https://!i',$request)) {
            $location = parse_url($request);
            if (preg_match('!^https://!i',$request)) {
                $this->connect('https://'.$location['host'],$location['port']);
            }
            else {
                $this->connect('http://'.$location['host'],$location['port']);
            }

            $this->set_login($location['user'], $location['pass']);

            $request = $location['path'];
            
            if ($content == '') {
                $content = $location['query'];
            }

            if (strlen($request) < 1) {
                $request = '/';
            }
        }

        if (preg_match('!^ssl://!i', $this->remote_host)) {
            $this->remote_host = 'https://'.substr($this->remote_host, 6);
        }

        if (preg_match('!^tcp://!i', $this->remote_host)) {
            $this->remote_host = 'http://'.substr($this->remote_host, 6);
        }

        if (preg_match('!^https://!i', $this->remote_host)) {
            $is_ssl = true;
        }

        $array_headers = [
            'Host'       => $this->remote_port == 80 ? $this->remote_host : "$this->remote_host:$this->remote_port",
            'Accept'     => '*/*',
            'Connection' => 'Close'
        ];

        foreach ($this->extra_headers as $key => $value) {
            $array_headers[$key] = $value;
        }

        $this->result = $this->result_header = $this->result_body = '';

        // was content sent as an array? if so, turn it into a string
        if (is_array($content)) {
            $pairs = [];

            foreach ($content as $key => $value) {
                $pairs[] = "$key=".urlencode($value);
            }

            $content = join('&',$pairs);
            unset($pairs);
        }

        $OK = true;

        if ($this->method == 'GET') {
            $request .= '?'.$content;
        }

        $ch = curl_init($this->remote_host.':'.$this->remote_port.$request);

        if ($is_ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //1
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //2
            //curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_USERAGENT, "HTTPSocket/$this->version");
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 512);
        curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 120);

        // instance connection
        if ($this->bind_host) {
            curl_setopt($ch, CURLOPT_INTERFACE, $this->bind_host);
        }

        // if we have a username and password, add the header
        if (isset($this->remote_uname) && isset($this->remote_passwd)) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->remote_uname.':'.$this->remote_passwd);
        }

        // for DA skins: if $this->remote_passwd is NULL, try to use the login key system
        if (isset($this->remote_uname) && $this->remote_passwd == NULL) {
            $array_headers['Cookie'] = "session={$_SERVER['SESSION_ID']}; key={$_SERVER['SESSION_KEY']}";
        }

        // if method is POST, add content length & type headers
        if ($this->method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);

            //$array_headers['Content-type'] = 'application/x-www-form-urlencoded';
            $array_headers['Content-length'] = strlen($content);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $array_headers);

        if (!($this->result = curl_exec($ch))) {
            $this->error[] = curl_error($ch);
            $OK = false;
        }

        $header_size              = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->result_header      = substr($this->result, 0, $header_size);
        $this->result_body        = substr($this->result, $header_size);
        $this->result_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastTransferSpeed  = curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD) / 1024;

        curl_close($ch);

        $this->query_cache[] = $this->remote_host.':'.$this->remote_port.$request;

        $headers = $this->fetch_header();

        // did we get the full file?
        if (!empty($headers['content-length']) && $headers['content-length'] != strlen($this->result_body)) {
            $this->result_status_code = 206;
        }

        // now, if we're being passed a location header, should we follow it?
        if ($this->doFollowLocationHeader) {
            //dont bother if we didn't even setup the script correctly
            if (isset($headers['x-use-https']) && $headers['x-use-https'] == 'yes') {
                die($this->ssl_setting_message);
            }

            if (isset($headers['location'])) {
                if ($this->max_redirects <= 0) {
                    die("Too many redirects on: ".$headers['location']);
                }

                $this->max_redirects--;
                $this->redirectURL = $headers['location'];
                $this->query($headers['location'], $content);
            }
        }
    }

    function getTransferSpeed()
    {
        return $this->lastTransferSpeed;
    }

    /**
     * The quick way to get a URL's content :)
     *
     * @param string $location URL
     * @param bool   $asArray  return as array? (like PHP's file() command)
     *
     * @return string result body
     */
    function get($location, $asArray = false)
    {
        $this->query($location);

        if ($this->get_status_code() == 200) {
            if ($asArray) {
                return preg_split("/\n/", $this->fetch_body());
            }

            return $this->fetch_body();
        }

        return false;
    }

    /**
     * Returns the last status code.
     * 200 = OK;
     * 403 = FORBIDDEN;
     * etc.
     *
     * @return int status code
     */
    function get_status_code()
    {
        return $this->result_status_code;
    }

    /**
     * Adds a header, sent with the next query.
     *
     * @param string header name
     * @param string header value
     */
    function add_header($key, $value)
    {
        $this->extra_headers[$key] = $value;
    }

    /**
     * Clears any extra headers.
     *
     */
    function clear_headers()
    {
        $this->extra_headers = [];
    }

    /**
     * Return the result of a query.
     *
     * @return string result
     */
    function fetch_result()
    {
        return $this->result;
    }

    /**
     * Return the header of result (stuff before body).
     *
     * @param string (optional) header to return
     * @return array result header
     */
    function fetch_header($header = '')
    {
        $array_headers = preg_split("/\r\n/", $this->result_header);

        $array_return = [0 => $array_headers[0]];
        unset($array_headers[0]);

        foreach ($array_headers as $pair) {
            if ($pair == '' || $pair == "\r\n") continue;
            list($key,$value) = preg_split("/: /", $pair, 2);
            $array_return[strtolower($key)] = $value;
        }

        if ($header != '') {
            return $array_return[strtolower($header)];
        }

        return $array_return;
    }

    /**
     * Return the body of result (stuff after header).
     *
     * @return string result body
     */
    function fetch_body()
    {
        return $this->result_body;
    }

    /**
     * Return parsed body in array format.
     *
     * @return array result parsed
     */
    function fetch_parsed_body()
    {
        parse_str($this->result_body, $x);
        return $x;
    }

    /**
     * Set a specific message on how to change the SSL setting, in the event that it's not set correctly.
     */
    function set_ssl_setting_message($str)
    {
        $this->ssl_setting_message = $str;
    }
}

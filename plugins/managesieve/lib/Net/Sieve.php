<?php
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002-2003, Richard Heyes                                |
// | Copyright (c) 2006, Anish Mistry                                      |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard@phpguru.org>                           |
// | Co-Author: Damian Fernandez Sosa <damlists@cnba.uba.ar>               |
// | Co-Author: Anish Mistry <amistry@am-productions.biz>                  |
// +-----------------------------------------------------------------------+

require_once('Net/Socket.php');

/**
* TODO
*
* o supportsAuthMech()
*/

/**
* Disconnected state
* @const NET_SIEVE_STATE_DISCONNECTED
*/
define('NET_SIEVE_STATE_DISCONNECTED',  1, true);

/**
* Authorisation state
* @const NET_SIEVE_STATE_AUTHORISATION
*/
define('NET_SIEVE_STATE_AUTHORISATION', 2, true);

/**
* Transaction state
* @const NET_SIEVE_STATE_TRANSACTION
*/
define('NET_SIEVE_STATE_TRANSACTION',   3, true);

/**
* A class for talking to the timsieved server which
* comes with Cyrus IMAP.
*
* SIEVE: RFC3028 http://www.ietf.org/rfc/rfc3028.txt
* MANAGE-SIEVE: http://www.ietf.org/internet-drafts/draft-martin-managesieve-07.txt
*
* @author  Richard Heyes <richard@php.net>
* @author  Damian Fernandez Sosa <damlists@cnba.uba.ar>
* @author  Anish Mistry <amistry@am-productions.biz>
* @access  public
* @version 1.2.0
* @package Net_Sieve
*/

class Net_Sieve
{
    /**
    * The socket object
    * @var object
    */
    var $_sock;

    /**
    * Info about the connect
    * @var array
    */
    var $_data;

    /**
    * Current state of the connection
    * @var integer
    */
    var $_state;

    /**
    * Constructor error is any
    * @var object
    */
    var $_error;

    /**
    * To allow class debuging
    * @var boolean
    */
    var $_debug = false;

    /**
    * Allows picking up of an already established connection
    * @var boolean
    */
    var $_bypassAuth = false;

    /**
    * Whether to use TLS if available
    * @var boolean
    */
    var $_useTLS = true;

    /**
    * The auth methods this class support
    * @var array
    */
    var $supportedAuthMethods=array('DIGEST-MD5', 'CRAM-MD5', 'PLAIN' , 'LOGIN');
    //if you have problems using DIGEST-MD5 authentication  please comment the line above and uncomment the following line
    //var $supportedAuthMethods=array( 'CRAM-MD5', 'PLAIN' , 'LOGIN');

    //var $supportedAuthMethods=array( 'PLAIN' , 'LOGIN');

    /**
    * The auth methods this class support
    * @var array
    */
    var $supportedSASLAuthMethods=array('DIGEST-MD5', 'CRAM-MD5');

    /**
    * Handles posible referral loops
    * @var array
    */
    var $_maxReferralCount = 15;

    /**
    * Constructor
    * Sets up the object, connects to the server and logs in. stores
    * any generated error in $this->_error, which can be retrieved
    * using the getError() method.
    *
    * @param  string $user      Login username
    * @param  string $pass      Login password
    * @param  string $host      Hostname of server
    * @param  string $port      Port of server
    * @param  string $logintype Type of login to perform
    * @param  string $euser     Effective User (if $user=admin, login as $euser)
    * @param  string $bypassAuth Skip the authentication phase.  Useful if the socket
                                  is already open.
    * @param  boolean $useTLS Use TLS if available
    */
    function Net_Sieve($user = null , $pass  = null , $host = 'localhost', $port = 2000, $logintype = '', $euser = '', $debug = false, $bypassAuth = false, $useTLS = true)
    {
        $this->_state = NET_SIEVE_STATE_DISCONNECTED;
        $this->_data['user'] = $user;
        $this->_data['pass'] = $pass;
        $this->_data['host'] = $host;
        $this->_data['port'] = $port;
        $this->_data['logintype'] = $logintype;
        $this->_data['euser'] = $euser;
        $this->_sock = &new Net_Socket();
        $this->_debug = $debug;
        $this->_bypassAuth = $bypassAuth;
        $this->_useTLS = $useTLS;
        /*
        * Include the Auth_SASL package.  If the package is not available,
        * we disable the authentication methods that depend upon it.
        */
        if ((@include_once 'Auth/SASL.php') === false) {
            if($this->_debug){
                echo "AUTH_SASL NOT PRESENT!\n";
            }
            foreach($this->supportedSASLAuthMethods as $SASLMethod){
                $pos = array_search( $SASLMethod, $this->supportedAuthMethods );
                if($this->_debug){
                    echo "DISABLING METHOD $SASLMethod\n";
                }
                unset($this->supportedAuthMethods[$pos]);
            }
        }
        if( ($user != null) && ($pass != null) ){
            $this->_error = $this->_handleConnectAndLogin();
        }
    }

    /**
    * Handles the errors the class can find
    * on the server
    *
    * @access private
    * @param mixed $msg  Text error message or PEAR error object
    * @param integer $code  Numeric error code
    * @return PEAR_Error
    */
    function _raiseError($msg, $code)
    {
        include_once 'PEAR.php';
        return PEAR::raiseError($msg, $code);
    }

    /**
    * Handles connect and login.
    * on the server
    *
    * @access private
    * @return mixed Indexed array of scriptnames or PEAR_Error on failure
    */
    function _handleConnectAndLogin()
    {
        if (PEAR::isError($res = $this->connect($this->_data['host'] , $this->_data['port'], null, $this->_useTLS ))) {
            return $res;
        }
        if($this->_bypassAuth === false) {
           if (PEAR::isError($res = $this->login($this->_data['user'], $this->_data['pass'], $this->_data['logintype'] , $this->_data['euser'] , $this->_bypassAuth) ) ) {
                return $res;
            }
        }
        return true;
    }

    /**
    * Returns an indexed array of scripts currently
    * on the server
    *
    * @return mixed Indexed array of scriptnames or PEAR_Error on failure
    */
    function listScripts()
    {
        if (is_array($scripts = $this->_cmdListScripts())) {
            $this->_active = $scripts[1];
            return $scripts[0];
        } else {
            return $scripts;
        }
    }

    /**
    * Returns the active script
    *
    * @return mixed The active scriptname or PEAR_Error on failure
    */
    function getActive()
    {
        if (!empty($this->_active)) {
            return $this->_active;

        } elseif (is_array($scripts = $this->_cmdListScripts())) {
            $this->_active = $scripts[1];
            return $scripts[1];
        }
    }

    /**
    * Sets the active script
    *
    * @param  string $scriptname The name of the script to be set as active
    * @return mixed              true on success, PEAR_Error on failure
    */
    function setActive($scriptname)
    {
        return $this->_cmdSetActive($scriptname);
    }

    /**
    * Retrieves a script
    *
    * @param  string $scriptname The name of the script to be retrieved
    * @return mixed              The script on success, PEAR_Error on failure
    */
    function getScript($scriptname)
    {
        return $this->_cmdGetScript($scriptname);
    }

    /**
    * Adds a script to the server
    *
    * @param  string $scriptname Name of the script
    * @param  string $script     The script
    * @param  boolean $makeactive Whether to make this the active script
    * @return mixed              true on success, PEAR_Error on failure
    */
    function installScript($scriptname, $script, $makeactive = false)
    {
        if (PEAR::isError($res = $this->_cmdPutScript($scriptname, $script))) {
            return $res;

        } elseif ($makeactive) {
            return $this->_cmdSetActive($scriptname);

        } else {
            return true;
        }
    }

    /**
    * Removes a script from the server
    *
    * @param  string $scriptname Name of the script
    * @return mixed              True on success, PEAR_Error on failure
    */
    function removeScript($scriptname)
    {
        return $this->_cmdDeleteScript($scriptname);
    }

    /**
    * Returns any error that may have been generated in the
    * constructor
    *
    * @return mixed False if no error, PEAR_Error otherwise
    */
    function getError()
    {
        return PEAR::isError($this->_error) ? $this->_error : false;
    }

    /**
    * Handles connecting to the server and checking the
    * response is valid.
    *
    * @access private
    * @param  string $host Hostname of server
    * @param  string $port Port of server
    * @param  array  $options List of options to pass to connect
    * @param  boolean $useTLS Use TLS if available
    * @return mixed        True on success, PEAR_Error otherwise
    */
    function connect($host, $port, $options = null, $useTLS = true)
    {
        if (NET_SIEVE_STATE_DISCONNECTED != $this->_state) {
            $msg='Not currently in DISCONNECTED state';
            $code=1;
            return $this->_raiseError($msg,$code);
        }

        if (PEAR::isError($res = $this->_sock->connect($host, $port, false, 5, $options))) {
            return $res;
        }

        if($this->_bypassAuth === false) {
            $this->_state = NET_SIEVE_STATE_AUTHORISATION;
            if (PEAR::isError($res = $this->_doCmd())) {
                return $res;
            }
        } else {
            $this->_state = NET_SIEVE_STATE_TRANSACTION;
        }

        // Explicitly ask for the capabilities in case the connection
        // is picked up from an existing connection.
        if(PEAR::isError($res = $this->_cmdCapability() )) {
            $msg='Failed to connect, server said: ' . $res->getMessage();
            $code=2;
            return $this->_raiseError($msg,$code);
        }

        // Get logon greeting/capability and parse
        $this->_parseCapability($res);

        if($useTLS === true) {
            // check if we can enable TLS via STARTTLS
            if(isset($this->_capability['starttls']) && function_exists('stream_socket_enable_crypto') === true) {
                if (PEAR::isError($res = $this->_startTLS())) {
                    return $res;
                }
            }
        }

        return true;
    }

    /**
    * Logs into server.
    *
    * @param  string  $user          Login username
    * @param  string  $pass          Login password
    * @param  string  $logintype     Type of login method to use
    * @param  string  $euser         Effective UID (perform on behalf of $euser)
    * @param  boolean $bypassAuth    Do not perform authentication
    * @return mixed                  True on success, PEAR_Error otherwise
    */
    function login($user, $pass, $logintype = null , $euser = '', $bypassAuth = false)
    {
        if (NET_SIEVE_STATE_AUTHORISATION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
        }

        if( $bypassAuth === false ){
            if(PEAR::isError($res=$this->_cmdAuthenticate($user , $pass , $logintype, $euser ) ) ){
                return $res;
            }
        }
        $this->_state = NET_SIEVE_STATE_TRANSACTION;
        return true;
    }

    /**
     * Handles the authentication using any known method
     *
     * @param string $uid The userid to authenticate as.
     * @param string $pwd The password to authenticate with.
     * @param string $userMethod The method to use ( if $userMethod == '' then the class chooses the best method (the stronger is the best ) )
     * @param string $euser The effective uid to authenticate as.
     *
     * @return mixed  string or PEAR_Error
     *
     * @access private
     * @since  1.0
     */
    function _cmdAuthenticate($uid , $pwd , $userMethod = null , $euser = '' )
    {
        if ( PEAR::isError( $method = $this->_getBestAuthMethod($userMethod) ) ) {
            return $method;
        }
        switch ($method) {
            case 'DIGEST-MD5':
                $result = $this->_authDigest_MD5( $uid , $pwd , $euser );
                return $result;
                break;
            case 'CRAM-MD5':
                $result = $this->_authCRAM_MD5( $uid , $pwd, $euser);
                break;
            case 'LOGIN':
                $result = $this->_authLOGIN( $uid , $pwd , $euser );
                break;
            case 'PLAIN':
                $result = $this->_authPLAIN( $uid , $pwd , $euser );
                break;
            default :
                $result = new PEAR_Error( "$method is not a supported authentication method" );
                break;
        }

        if (PEAR::isError($res = $this->_doCmd() )) {
            return $res;
        }
        return $result;
    }

    /**
     * Authenticates the user using the PLAIN method.
     *
     * @param string $user The userid to authenticate as.
     * @param string $pass The password to authenticate with.
     * @param string $euser The effective uid to authenticate as.
     *
     * @return array Returns an array containing the response
     *
     * @access private
     * @since  1.0
     */
    function _authPLAIN($user, $pass , $euser )
    {
        if ($euser != '') {
            $cmd=sprintf('AUTHENTICATE "PLAIN" "%s"', base64_encode($euser . chr(0) . $user . chr(0) . $pass ) ) ;
        } else {
            $cmd=sprintf('AUTHENTICATE "PLAIN" "%s"', base64_encode( chr(0) . $user . chr(0) . $pass ) );
        }
        return  $this->_sendCmd( $cmd ) ;
    }

    /**
     * Authenticates the user using the PLAIN method.
     *
     * @param string $user The userid to authenticate as.
     * @param string $pass The password to authenticate with.
     * @param string $euser The effective uid to authenticate as.
     *
     * @return array Returns an array containing the response
     *
     * @access private
     * @since  1.0
     */
    function _authLOGIN($user, $pass , $euser )
    {
        $this->_sendCmd('AUTHENTICATE "LOGIN"');
        $this->_doCmd(sprintf('"%s"', base64_encode($user)));
        $this->_doCmd(sprintf('"%s"', base64_encode($pass)));
    }

    /**
     * Authenticates the user using the CRAM-MD5 method.
     *
     * @param string $uid The userid to authenticate as.
     * @param string $pwd The password to authenticate with.
     * @param string $euser The effective uid to authenticate as.
     *
     * @return array Returns an array containing the response
     *
     * @access private
     * @since  1.0
     */
    function _authCRAM_MD5($uid, $pwd, $euser)
    {
        if ( PEAR::isError( $challenge = $this->_doCmd( 'AUTHENTICATE "CRAM-MD5"' ) ) ) {
            $this->_error=$challenge;
            return $challenge;
        }
        $challenge=trim($challenge);
        $challenge = base64_decode( trim($challenge) );
        $cram = &Auth_SASL::factory('crammd5');
        if ( PEAR::isError($resp=$cram->getResponse( $uid , $pwd , $challenge ) ) ) {
            $this->_error=$resp;
            return $resp;
        }
        $auth_str = base64_encode( $resp );
        if ( PEAR::isError($error = $this->_sendStringResponse( $auth_str  ) ) ) {
            $this->_error=$error;
            return $error;
        }

    }

    /**
     * Authenticates the user using the DIGEST-MD5 method.
     *
     * @param string $uid The userid to authenticate as.
     * @param string $pwd The password to authenticate with.
     * @param string $euser The effective uid to authenticate as.
     *
     * @return array Returns an array containing the response
     *
     * @access private
     * @since  1.0
     */
    function _authDigest_MD5($uid, $pwd, $euser)
    {
        if ( PEAR::isError( $challenge = $this->_doCmd('AUTHENTICATE "DIGEST-MD5"') ) ) {
            $this->_error= $challenge;
            return $challenge;
        }
        $challenge = base64_decode( $challenge );
        $digest = &Auth_SASL::factory('digestmd5');

        if(PEAR::isError($param=$digest->getResponse($uid, $pwd, $challenge, "localhost", "sieve" , $euser) )) {
            return $param;
        }
        $auth_str = base64_encode($param);

        if ( PEAR::isError($error = $this->_sendStringResponse( $auth_str  ) ) ) {
            $this->_error=$error;
            return $error;
        }

        if ( PEAR::isError( $challenge = $this->_doCmd() ) ) {
            $this->_error=$challenge ;
            return $challenge ;
        }

        if( strtoupper(substr($challenge,0,2))== 'OK' ){
                return true;
        }

        /**
        * We don't use the protocol's third step because SIEVE doesn't allow
        * subsequent authentication, so we just silently ignore it.
        */
        if ( PEAR::isError($error = $this->_sendStringResponse( '' ) ) ) {
            $this->_error=$error;
            return $error;
        }

        if (PEAR::isError($res = $this->_doCmd() )) {
            return $res;
        }
    }

    /**
    * Removes a script from the server
    *
    * @access private
    * @param  string $scriptname Name of the script to delete
    * @return mixed              True on success, PEAR_Error otherwise
    */
    function _cmdDeleteScript($scriptname)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
        }
        if (PEAR::isError($res = $this->_doCmd(sprintf('DELETESCRIPT "%s"', $scriptname) ) )) {
            return $res;
        }
        return true;
    }

    /**
    * Retrieves the contents of the named script
    *
    * @access private
    * @param  string $scriptname Name of the script to retrieve
    * @return mixed              The script if successful, PEAR_Error otherwise
    */
    function _cmdGetScript($scriptname)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
        }

        if (PEAR::isError($res = $this->_doCmd(sprintf('GETSCRIPT "%s"', $scriptname) ) ) ) {
            return $res;
        }

        return preg_replace('/{[0-9]+}\r\n/', '', $res);
    }

    /**
    * Sets the ACTIVE script, ie the one that gets run on new mail
    * by the server
    *
    * @access private
    * @param  string $scriptname The name of the script to mark as active
    * @return mixed              True on success, PEAR_Error otherwise
    */
    function _cmdSetActive($scriptname)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
        }

        if (PEAR::isError($res = $this->_doCmd(sprintf('SETACTIVE "%s"', $scriptname) ) ) ) {
            return $res;
        }

        $this->_activeScript = $scriptname;
        return true;
    }

    /**
    * Sends the LISTSCRIPTS command
    *
    * @access private
    * @return mixed Two item array of scripts, and active script on success,
    *               PEAR_Error otherwise.
    */
    function _cmdListScripts()
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
        }

        $scripts = array();
        $activescript = null;

        if (PEAR::isError($res = $this->_doCmd('LISTSCRIPTS'))) {
            return $res;
        }

        $res = explode("\r\n", $res);

        foreach ($res as $value) {
            if (preg_match('/^"(.*)"( ACTIVE)?$/i', $value, $matches)) {
                $scripts[] = $matches[1];
                if (!empty($matches[2])) {
                    $activescript = $matches[1];
                }
            }
        }

        return array($scripts, $activescript);
    }

    /**
    * Sends the PUTSCRIPT command to add a script to
    * the server.
    *
    * @access private
    * @param  string $scriptname Name of the new script
    * @param  string $scriptdata The new script
    * @return mixed              True on success, PEAR_Error otherwise
    */
    function _cmdPutScript($scriptname, $scriptdata)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in TRANSACTION state';
            $code=1;
            return $this->_raiseError($msg,$code);
        }

        $stringLength = $this->_getLineLength($scriptdata);

        if (PEAR::isError($res = $this->_doCmd(sprintf("PUTSCRIPT \"%s\" {%d+}\r\n%s", $scriptname, $stringLength, $scriptdata) ))) {
            return $res;
        }

        return true;
    }

    /**
    * Sends the LOGOUT command and terminates the connection
    *
    * @access private
    * @param boolean $sendLogoutCMD True to send LOGOUT command before disconnecting
    * @return mixed True on success, PEAR_Error otherwise
    */
    function _cmdLogout($sendLogoutCMD=true)
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=1;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently connected');
        }

        if($sendLogoutCMD){
            if (PEAR::isError($res = $this->_doCmd('LOGOUT'))) {
                return $res;
            }
        }

        $this->_sock->disconnect();
        $this->_state = NET_SIEVE_STATE_DISCONNECTED;
        return true;
    }

    /**
    * Sends the CAPABILITY command
    *
    * @access private
    * @return mixed True on success, PEAR_Error otherwise
    */
    function _cmdCapability()
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=1;
            return $this->_raiseError($msg,$code);
        }

        if (PEAR::isError($res = $this->_doCmd('CAPABILITY'))) {
            return $res;
        }
        $this->_parseCapability($res);
        return true;
    }

    /**
    * Checks if the server has space to store the script
    * by the server
    *
    * @param  string $scriptname The name of the script to mark as active
    * @param integer $size The size of the script
    * @return mixed              True on success, PEAR_Error otherwise
    */
    function haveSpace($scriptname,$size)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in TRANSACTION state';
            $code=1;
            return $this->_raiseError($msg,$code);
        }

        if (PEAR::isError($res = $this->_doCmd(sprintf('HAVESPACE "%s" %d', $scriptname, $size) ) ) ) {
            return $res;
        }

        return true;
    }

    /**
    * Parses the response from the capability command. Stores
    * the result in $this->_capability
    *
    * @access private
    * @param string $data The response from the capability command
    */
    function _parseCapability($data)
    {
        $data = preg_split('/\r?\n/', $data, -1, PREG_SPLIT_NO_EMPTY);

        for ($i = 0; $i < count($data); $i++) {
            if (preg_match('/^"([a-z]+)"( "(.*)")?$/i', $data[$i], $matches)) {
                switch (strtolower($matches[1])) {
                    case 'implementation':
                        $this->_capability['implementation'] = $matches[3];
                        break;

                    case 'sasl':
                        $this->_capability['sasl'] = preg_split('/\s+/', $matches[3]);
                        break;

                    case 'sieve':
                        $this->_capability['extensions'] = preg_split('/\s+/', $matches[3]);
                        break;

                    case 'starttls':
                        $this->_capability['starttls'] = true;
                        break;
                }
            }
        }
    }

    /**
    * Sends a command to the server
    *
    * @access private
    * @param string $cmd The command to send
    */
    function _sendCmd($cmd)
    {
        $status = $this->_sock->getStatus();
        if (PEAR::isError($status) || $status['eof']) {
            return new PEAR_Error( 'Failed to write to socket: (connection lost!) ' );
        }
        if ( PEAR::isError( $error = $this->_sock->write( $cmd . "\r\n" ) ) ) {
            return new PEAR_Error( 'Failed to write to socket: ' . $error->getMessage() );
        }

        if( $this->_debug ){
            // C: means this data was sent by  the client (this class)
            echo "C:$cmd\n";
        }
        return true;
    }

    /**
    * Sends a string response to the server
    *
    * @access private
    * @param string $cmd The command to send
    */
    function _sendStringResponse($str)
    {
        $response='{' .  $this->_getLineLength($str) . "+}\r\n" . $str  ;
        return $this->_sendCmd($response);
    }

    function _recvLn()
    {
        $lastline='';
        if (PEAR::isError( $lastline = $this->_sock->gets( 8192 ) ) ) {
            return new PEAR_Error( 'Failed to write to socket: ' . $lastline->getMessage() );
        }
        $lastline=rtrim($lastline);
        if($this->_debug){
            // S: means this data was sent by  the IMAP Server
            echo "S:$lastline\n" ;
        }

        if( $lastline === '' ) {
            return new PEAR_Error( 'Failed to receive from the socket' );
        }

        return $lastline;
    }

    /**
    * Send a command and retrieves a response from the server.
    *
    *
    * @access private
    * @param string $cmd The command to send
    * @return mixed Reponse string if an OK response, PEAR_Error if a NO response
    */
    function _doCmd($cmd = '' )
    {
        $referralCount=0;
        while($referralCount < $this->_maxReferralCount ){

            if($cmd != '' ){
                if(PEAR::isError($error = $this->_sendCmd($cmd) )) {
                    return $error;
                }
            }
            $response = '';

            while (true) {
                    if(PEAR::isError( $line=$this->_recvLn() )){
                        return $line;
                    }
                    if ('ok' === strtolower(substr($line, 0, 2))) {
                        $response .= $line;
                        return rtrim($response);

                    } elseif ('no' === strtolower(substr($line, 0, 2))) {
                        // Check for string literal error message
                        if (preg_match('/^no {([0-9]+)\+?}/i', $line, $matches)) {
                            $line .= str_replace("\r\n", ' ', $this->_sock->read($matches[1] + 2 ));
                            if($this->_debug){
                                echo "S:$line\n";
                            }
                        }
                        $msg=trim($response . substr($line, 2));
                        $code=3;
                        return $this->_raiseError($msg,$code);
                    } elseif ('bye' === strtolower(substr($line, 0, 3))) {

                        if(PEAR::isError($error = $this->disconnect(false) ) ){
                            $msg="Can't handle bye, The error was= " . $error->getMessage() ;
                            $code=4;
                            return $this->_raiseError($msg,$code);
                        }
                        //if (preg_match('/^bye \(referral "([^"]+)/i', $line, $matches)) {
                        if (preg_match('/^bye \(referral "(sieve:\/\/)?([^"]+)/i', $line, $matches)) {
                            // Check for referral, then follow it.  Otherwise, carp an error.
                            // Replace the old host with the referral host preserving any protocol prefix
                            $this->_data['host'] = preg_replace('/\w+(?!(\w|\:\/\/)).*/',$matches[2],$this->_data['host']);
                           if (PEAR::isError($error = $this->_handleConnectAndLogin() ) ){
                                $msg="Can't follow referral to " . $this->_data['host'] . ", The error was= " . $error->getMessage() ;
                                $code=5;
                                return $this->_raiseError($msg,$code);
                            }
                            break;
                            // Retry the command
                            if(PEAR::isError($error = $this->_sendCmd($cmd) )) {
                                return $error;
                            }
                            continue;
                        }
                        $msg=trim($response . $line);
                        $code=6;
                        return $this->_raiseError($msg,$code);
                    } elseif (preg_match('/^{([0-9]+)\+?}/i', $line, $matches)) {
                        // Matches String Responses.
                        //$line = str_replace("\r\n", ' ', $this->_sock->read($matches[1] + 2 ));
                        $str_size = $matches[1] + 2;
                        $line = '';
                        $line_length = 0;
                        while ($line_length < $str_size) {
                            $line .= $this->_sock->read($str_size - $line_length);
                            $line_length = $this->_getLineLength($line);
                        }
                        if($this->_debug){
                            echo "S:$line\n";
                        }
                        if($this->_state != NET_SIEVE_STATE_AUTHORISATION) {
                            // receive the pending OK only if we aren't authenticating
                            // since string responses during authentication don't need an
                            // OK.
                            $this->_recvLn();
                        }
                        return $line;
                    }
                    $response .= $line . "\r\n";
                    $referralCount++;
                }
        }
        $msg="Max referral count reached ($referralCount times) Cyrus murder loop error?";
        $code=7;
        return $this->_raiseError($msg,$code);
    }

    /**
    * Sets the debug state
    *
    * @param boolean $debug
    * @return void
    */
    function setDebug($debug = true)
    {
        $this->_debug = $debug;
    }

    /**
    * Disconnect from the Sieve server
    *
    * @param  string $scriptname The name of the script to be set as active
    * @return mixed              true on success, PEAR_Error on failure
    */
    function disconnect($sendLogoutCMD=true)
    {
        return $this->_cmdLogout($sendLogoutCMD);
    }

    /**
     * Returns the name of the best authentication method that the server
     * has advertised.
     *
     * @param string if !=null,authenticate with this method ($userMethod).
     *
     * @return mixed    Returns a string containing the name of the best
     *                  supported authentication method or a PEAR_Error object
     *                  if a failure condition is encountered.
     * @access private
     * @since  1.0
     */
    function _getBestAuthMethod($userMethod = null)
    {
       if( isset($this->_capability['sasl']) ){
           $serverMethods=$this->_capability['sasl'];
       }else{
           // if the server don't send an sasl capability fallback to login auth
           //return 'LOGIN';
           return new PEAR_Error("This server don't support any Auth methods SASL problem?");
       }

        if($userMethod != null ){
            $methods = array();
            $methods[] = $userMethod;
        }else{

            $methods = $this->supportedAuthMethods;
        }
        if( ($methods != null) && ($serverMethods != null)){
            foreach ( $methods as $method ) {
                if ( in_array( $method , $serverMethods ) ) {
                    return $method;
                }
            }
            $serverMethods=implode(',' , $serverMethods );
            $myMethods=implode(',' ,$this->supportedAuthMethods);
            return new PEAR_Error("$method NOT supported authentication method!. This server " .
                "supports these methods= $serverMethods, but I support $myMethods");
        }else{
            return new PEAR_Error("This server don't support any Auth methods");
        }
    }

    /**
    * Return the list of extensions the server supports
    *
    * @return mixed              array  on success, PEAR_Error on failure
    */
    function getExtensions()
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=7;
            return $this->_raiseError($msg,$code);
        }

        return $this->_capability['extensions'];
    }

    /**
    * Return true if tyhe server has that extension
    *
    * @param string  the extension to compare
    * @return mixed              array  on success, PEAR_Error on failure
    */
    function hasExtension($extension)
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=7;
            return $this->_raiseError($msg,$code);
        }

        if(is_array($this->_capability['extensions'] ) ){
            foreach( $this->_capability['extensions'] as $ext){
                if( trim( strtolower( $ext ) ) === trim( strtolower( $extension ) ) )
                    return true;
            }
        }
        return false;
    }

    /**
    * Return the list of auth methods the server supports
    *
    * @return mixed              array  on success, PEAR_Error on failure
    */
    function getAuthMechs()
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=7;
            return $this->_raiseError($msg,$code);
        }
        if(!isset($this->_capability['sasl']) ){
            $this->_capability['sasl']=array();
        }
        return $this->_capability['sasl'];
    }

    /**
    * Return true if the server has that extension
    *
    * @param string  the extension to compare
    * @return mixed              array  on success, PEAR_Error on failure
    */
    function hasAuthMech($method)
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=7;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently connected');
        }

        if(is_array($this->_capability['sasl'] ) ){
            foreach( $this->_capability['sasl'] as $ext){
                if( trim( strtolower( $ext ) ) === trim( strtolower( $method ) ) )
                    return true;
            }
        }
        return false;
    }

    /**
    * Return true if the TLS negotiation was successful
    *
    * @access private
    * @return mixed              true on success, PEAR_Error on failure
    */
    function _startTLS()
    {
        if (PEAR::isError($res = $this->_doCmd("STARTTLS"))) {
            return $res;
        }
	
        if(stream_socket_enable_crypto($this->_sock->fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) == false) {
            $msg='Failed to establish TLS connection';
            $code=2;
            return $this->_raiseError($msg,$code);
        }

        if($this->_debug === true) {
            echo "STARTTLS Negotiation Successful\n";
        }
	
	// skip capability strings received after AUTHENTICATE
	// wait for OK "TLS negotiation successful." 
	if(PEAR::isError($ret = $this->_doCmd() )) {
            $msg='Failed to establish TLS connection, server said: ' . $res->getMessage();
            $code=2;
            return $this->_raiseError($msg,$code);
        }

        // RFC says we need to query the server capabilities again
	// @TODO: don;'t call for capabilities if they are returned
	// in tls negotiation result above
        if(PEAR::isError($res = $this->_cmdCapability() )) {
            $msg='Failed to connect, server said: ' . $res->getMessage();
            $code=2;
            return $this->_raiseError($msg,$code);
        }
        return true;
    }

    function _getLineLength($string) {
        if (extension_loaded('mbstring') || @dl(PHP_SHLIB_PREFIX.'mbstring.'.PHP_SHLIB_SUFFIX)) {
          return mb_strlen($string,'latin1');
        } else {
          return strlen($string);
        }
    }
}
?>

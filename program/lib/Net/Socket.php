<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Stig Bakken <ssb@php.net>                                   |
// |          Chuck Hagenbuch <chuck@horde.org>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'PEAR.php';

/**
 * Generalized Socket class. More docs to be written.
 *
 * @version 1.0
 * @author Stig Bakken <ssb@php.net>
 * @author Chuck Hagenbuch <chuck@horde.org>
 */
class Net_Socket extends PEAR {
    // {{{ properties

    /** Socket file pointer. */
    var $fp = null;

    /** Whether the socket is blocking. */
    var $blocking = true;

    /** Whether the socket is persistent. */
    var $persistent = false;

    /** The IP address to connect to. */
    var $addr = '';

    /** The port number to connect to. */
    var $port = 0;

    /** Number of seconds to wait on socket connections before
        assuming there's no more data. */
    var $timeout = false;

    /** Number of bytes to read at a time in readLine() and
        readAll(). */
    var $lineLength = 2048;
    // }}}

    // {{{ constructor
    /**
     * Constructs a new Net_Socket object.
     *
     * @access public
     */
    function Net_Socket()
    {
        $this->PEAR();
    }
    // }}}

    // {{{ connect()
    /**
     * Connect to the specified port. If called when the socket is
     * already connected, it disconnects and connects again.
     *
     * @param $addr string IP address or host name
     * @param $port int TCP port number
     * @param $persistent bool (optional) whether the connection is
     *        persistent (kept open between requests by the web server)
     * @param $timeout int (optional) how long to wait for data
     * @param $options array see options for stream_context_create
     * @access public
     * @return mixed true on success or error object
     */
    function connect($addr, $port, $persistent = null, $timeout = null, $options = null)
    {
        if (is_resource($this->fp)) {
            @fclose($this->fp);
            $this->fp = null;
        }

        if (strspn($addr, '.0123456789') == strlen($addr)) {
            $this->addr = $addr;
        } else {
            $this->addr = gethostbyname($addr);
        }
        $this->port = $port % 65536;
        if ($persistent !== null) {
            $this->persistent = $persistent;
        }
        if ($timeout !== null) {
            $this->timeout = $timeout;
        }
        $openfunc = $this->persistent ? 'pfsockopen' : 'fsockopen';
        $errno = 0;
        $errstr = '';
        if ($options && function_exists('stream_context_create')) {
            if ($this->timeout) {
                $timeout = $this->timeout;
            } else {
                $timeout = 0;
            }
            $context = stream_context_create($options);
            $fp = $openfunc($this->addr, $this->port, $errno, $errstr, $timeout, $context);
        } else {
            if ($this->timeout) {
                $fp = @$openfunc($this->addr, $this->port, $errno, $errstr, $this->timeout);
            } else {
                $fp = @$openfunc($this->addr, $this->port, $errno, $errstr);
            }
        }

        if (!$fp) {
            return $this->raiseError($errstr, $errno);
        }

        $this->fp = $fp;

        return $this->setBlocking($this->blocking);
    }
    // }}}

    // {{{ disconnect()
    /**
     * Disconnects from the peer, closes the socket.
     *
     * @access public
     * @return mixed true on success or an error object otherwise
     */
    function disconnect()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
            $this->fp = null;
            return true;
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ isBlocking()
    /**
     * Find out if the socket is in blocking mode.
     *
     * @access public
     * @return bool the current blocking mode.
     */
    function isBlocking()
    {
        return $this->blocking;
    }
    // }}}

    // {{{ setBlocking()
    /**
     * Sets whether the socket connection should be blocking or
     * not. A read call to a non-blocking socket will return immediately
     * if there is no data available, whereas it will block until there
     * is data for blocking sockets.
     *
     * @param $mode bool true for blocking sockets, false for nonblocking
     * @access public
     * @return mixed true on success or an error object otherwise
     */
    function setBlocking($mode)
    {
        if (is_resource($this->fp)) {
            $this->blocking = $mode;
            socket_set_blocking($this->fp, $this->blocking);
            return true;
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ setTimeout()
    /**
     * Sets the timeout value on socket descriptor,
     * expressed in the sum of seconds and microseconds
     *
     * @param $seconds int seconds
     * @param $microseconds int microseconds
     * @access public
     * @return mixed true on success or an error object otherwise
     */
    function setTimeout($seconds, $microseconds)
    {
        if (is_resource($this->fp)) {
            socket_set_timeout($this->fp, $seconds, $microseconds);
            return true;
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ getStatus()
    /**
     * Returns information about an existing socket resource.
     * Currently returns four entries in the result array:
     *
     * <p>
     * timed_out (bool) - The socket timed out waiting for data<br>
     * blocked (bool) - The socket was blocked<br>
     * eof (bool) - Indicates EOF event<br>
     * unread_bytes (int) - Number of bytes left in the socket buffer<br>
     * </p>
     *
     * @access public
     * @return mixed Array containing information about existing socket resource or an error object otherwise
     */
    function getStatus()
    {
        if (is_resource($this->fp)) {
            return socket_get_status($this->fp);
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ gets()
    /**
     * Get a specified line of data
     *
     * @access public
     * @return $size bytes of data from the socket, or a PEAR_Error if
     *         not connected.
     */
    function gets($size)
    {
        if (is_resource($this->fp)) {
            return fgets($this->fp, $size);
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ read()
    /**
     * Read a specified amount of data. This is guaranteed to return,
     * and has the added benefit of getting everything in one fread()
     * chunk; if you know the size of the data you're getting
     * beforehand, this is definitely the way to go.
     *
     * @param $size The number of bytes to read from the socket.
     * @access public
     * @return $size bytes of data from the socket, or a PEAR_Error if
     *         not connected.
     */
    function read($size)
    {
        if (is_resource($this->fp)) {
            return fread($this->fp, $size);
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ write()
    /**
     * Write a specified amount of data.
     *
     * @access public
     * @return mixed true on success or an error object otherwise
     */
    function write($data)
    {
        if (is_resource($this->fp)) {
            return fwrite($this->fp, $data);
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ writeLine()
    /**
     * Write a line of data to the socket, followed by a trailing "\r\n".
     *
     * @access public
     * @return mixed fputs result, or an error
     */
    function writeLine ($data)
    {
        if (is_resource($this->fp)) {
            return $this->write($data . "\r\n");
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ eof()
    /**
     * Tests for end-of-file on a socket descriptor
     *
     * @access public
     * @return bool
     */
    function eof()
    {
        return (is_resource($this->fp) && feof($this->fp));
    }
    // }}}

    // {{{ readByte()
    /**
     * Reads a byte of data
     *
     * @access public
     * @return 1 byte of data from the socket, or a PEAR_Error if
     *         not connected.
     */
    function readByte()
    {
        if (is_resource($this->fp)) {
            return ord($this->read(1));
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ readWord()
    /**
     * Reads a word of data
     *
     * @access public
     * @return 1 word of data from the socket, or a PEAR_Error if
     *         not connected.
     */
    function readWord()
    {
        if (is_resource($this->fp)) {
            $buf = $this->read(2);
            return (ord($buf[0]) + (ord($buf[1]) << 8));
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ readInt()
    /**
     * Reads an int of data
     *
     * @access public
     * @return 1 int of data from the socket, or a PEAR_Error if
     *         not connected.
     */
    function readInt()
    {
        if (is_resource($this->fp)) {
            $buf = $this->read(4);
            return (ord($buf[0]) + (ord($buf[1]) << 8) +
                    (ord($buf[2]) << 16) + (ord($buf[3]) << 24));
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ readString()
    /**
     * Reads a zeroterminated string of data
     *
     * @access public
     * @return string, or a PEAR_Error if
     *         not connected.
     */
    function readString()
    {
        if (is_resource($this->fp)) {
            $string = '';
            while (($char = $this->read(1)) != "\x00")  {
                $string .= $char;
            }
            return $string;
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ readIPAddress()
    /**
     * Reads an IP Address and returns it in a dot formated string
     *
     * @access public
     * @return Dot formated string, or a PEAR_Error if
     *         not connected.
     */
    function readIPAddress()
    {
        if (is_resource($this->fp)) {
            $buf = $this->read(4);
            return sprintf("%s.%s.%s.%s", ord($buf[0]), ord($buf[1]),
                           ord($buf[2]), ord($buf[3]));
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ readLine()
    /**
     * Read until either the end of the socket or a newline, whichever
     * comes first. Strips the trailing newline from the returned data.
     *
     * @access public
     * @return All available data up to a newline, without that
     *         newline, or until the end of the socket, or a PEAR_Error if
     *         not connected.
     */
    function readLine()
    {
        if (is_resource($this->fp)) {
            $line = '';
            $timeout = time() + $this->timeout;
            while (!$this->eof() && (!$this->timeout || time() < $timeout)) {
                $line .= $this->gets($this->lineLength);
                if (substr($line, -2) == "\r\n" ||
                    substr($line, -1) == "\n") {
                    return rtrim($line, "\r\n");
                }
            }
            return $line;
        }
        return $this->raiseError("not connected");
    }
    // }}}

    // {{{ readAll()
    /**
     * Read until the socket closes. THIS FUNCTION WILL NOT EXIT if the
     * socket is in blocking mode until the socket closes.
     *
     * @access public
     * @return All data until the socket closes, or a PEAR_Error if
     *         not connected.
     */
    function readAll()
    {
        if (is_resource($this->fp)) {
            $data = '';
            while (!$this->eof())
                $data .= $this->read($this->lineLength);
            return $data;
        }
        return $this->raiseError("not connected");
    }
    // }}}

}

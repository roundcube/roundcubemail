<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Crypt_GPG is a package to use GPG from PHP
 *
 * This file contains an engine that handles GPG subprocess control and I/O.
 * PHP's process manipulation functions are used to handle the GPG subprocess.
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; either version 2.1 of the
 * License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Nathan Fredrickson <nathan@silverorange.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2005-2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   CVS: $Id: Engine.php 302822 2010-08-26 17:30:57Z gauthierm $
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://www.gnupg.org/
 */

/**
 * Crypt_GPG base class.
 */
require_once 'Crypt/GPG.php';

/**
 * GPG exception classes.
 */
require_once 'Crypt/GPG/Exceptions.php';

/**
 * Standard PEAR exception is used if GPG binary is not found.
 */
require_once 'PEAR/Exception.php';

// {{{ class Crypt_GPG_Engine

/**
 * Native PHP Crypt_GPG I/O engine
 *
 * This class is used internally by Crypt_GPG and does not need be used
 * directly. See the {@link Crypt_GPG} class for end-user API.
 *
 * This engine uses PHP's native process control functions to directly control
 * the GPG process. The GPG executable is required to be on the system.
 *
 * All data is passed to the GPG subprocess using file descriptors. This is the
 * most secure method of passing data to the GPG subprocess.
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Nathan Fredrickson <nathan@silverorange.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2005-2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://www.gnupg.org/
 */
class Crypt_GPG_Engine
{
    // {{{ constants

    /**
     * Size of data chunks that are sent to and retrieved from the IPC pipes.
     *
     * PHP reads 8192 bytes. If this is set to less than 8192, PHP reads 8192
     * and buffers the rest so we might as well just read 8192.
     *
     * Using values other than 8192 also triggers PHP bugs.
     *
     * @see http://bugs.php.net/bug.php?id=35224
     */
    const CHUNK_SIZE = 8192;

    /**
     * Standard input file descriptor. This is used to pass data to the GPG
     * process.
     */
    const FD_INPUT = 0;

    /**
     * Standard output file descriptor. This is used to receive normal output
     * from the GPG process.
     */
    const FD_OUTPUT = 1;

    /**
     * Standard output file descriptor. This is used to receive error output
     * from the GPG process.
     */
    const FD_ERROR = 2;

    /**
     * GPG status output file descriptor. The status file descriptor outputs
     * detailed information for many GPG commands. See the second section of
     * the file <b>doc/DETAILS</b> in the
     * {@link http://www.gnupg.org/download/ GPG package} for a detailed
     * description of GPG's status output.
     */
    const FD_STATUS = 3;

    /**
     * Command input file descriptor. This is used for methods requiring
     * passphrases.
     */
    const FD_COMMAND = 4;

    /**
     * Extra message input file descriptor. This is used for passing signed
     * data when verifying a detached signature.
     */
    const FD_MESSAGE = 5;

    /**
     * Minimum version of GnuPG that is supported.
     */
    const MIN_VERSION = '1.0.2';

    // }}}
    // {{{ private class properties

    /**
     * Whether or not to use debugging mode
     *
     * When set to true, every GPG command is echoed before it is run. Sensitive
     * data is always handled using pipes and is not specified as part of the
     * command. As a result, sensitive data is never displayed when debug is
     * enabled. Sensitive data includes private key data and passphrases.
     *
     * Debugging is off by default.
     *
     * @var boolean
     * @see Crypt_GPG_Engine::__construct()
     */
    private $_debug = false;

    /**
     * Location of GPG binary
     *
     * @var string
     * @see Crypt_GPG_Engine::__construct()
     * @see Crypt_GPG_Engine::_getBinary()
     */
    private $_binary = '';

    /**
     * Directory containing the GPG key files
     *
     * This property only contains the path when the <i>homedir</i> option
     * is specified in the constructor.
     *
     * @var string
     * @see Crypt_GPG_Engine::__construct()
     */
    private $_homedir = '';

    /**
     * File path of the public keyring
     *
     * This property only contains the file path when the <i>public_keyring</i>
     * option is specified in the constructor.
     *
     * If the specified file path starts with <kbd>~/</kbd>, the path is
     * relative to the <i>homedir</i> if specified, otherwise to
     * <kbd>~/.gnupg</kbd>.
     *
     * @var string
     * @see Crypt_GPG_Engine::__construct()
     */
    private $_publicKeyring = '';

    /**
     * File path of the private (secret) keyring
     *
     * This property only contains the file path when the <i>private_keyring</i>
     * option is specified in the constructor.
     *
     * If the specified file path starts with <kbd>~/</kbd>, the path is
     * relative to the <i>homedir</i> if specified, otherwise to
     * <kbd>~/.gnupg</kbd>.
     *
     * @var string
     * @see Crypt_GPG_Engine::__construct()
     */
    private $_privateKeyring = '';

    /**
     * File path of the trust database
     *
     * This property only contains the file path when the <i>trust_db</i>
     * option is specified in the constructor.
     *
     * If the specified file path starts with <kbd>~/</kbd>, the path is
     * relative to the <i>homedir</i> if specified, otherwise to
     * <kbd>~/.gnupg</kbd>.
     *
     * @var string
     * @see Crypt_GPG_Engine::__construct()
     */
    private $_trustDb = '';

    /**
     * Array of pipes used for communication with the GPG binary
     *
     * This is an array of file descriptor resources.
     *
     * @var array
     */
    private $_pipes = array();

    /**
     * Array of currently opened pipes
     *
     * This array is used to keep track of remaining opened pipes so they can
     * be closed when the GPG subprocess is finished. This array is a subset of
     * the {@link Crypt_GPG_Engine::$_pipes} array and contains opened file
     * descriptor resources.
     *
     * @var array
     * @see Crypt_GPG_Engine::_closePipe()
     */
    private $_openPipes = array();

    /**
     * A handle for the GPG process
     *
     * @var resource
     */
    private $_process = null;

    /**
     * Whether or not the operating system is Darwin (OS X)
     *
     * @var boolean
     */
    private $_isDarwin = false;

    /**
     * Commands to be sent to GPG's command input stream
     *
     * @var string
     * @see Crypt_GPG_Engine::sendCommand()
     */
    private $_commandBuffer = '';

    /**
     * Array of status line handlers
     *
     * @var array
     * @see Crypt_GPG_Engine::addStatusHandler()
     */
    private $_statusHandlers = array();

    /**
     * Array of error line handlers
     *
     * @var array
     * @see Crypt_GPG_Engine::addErrorHandler()
     */
    private $_errorHandlers = array();

    /**
     * The error code of the current operation
     *
     * @var integer
     * @see Crypt_GPG_Engine::getErrorCode()
     */
    private $_errorCode = Crypt_GPG::ERROR_NONE;

    /**
     * File related to the error code of the current operation
     *
     * @var string
     * @see Crypt_GPG_Engine::getErrorFilename()
     */
    private $_errorFilename = '';

    /**
     * Key id related to the error code of the current operation
     *
     * @var string
     * @see Crypt_GPG_Engine::getErrorKeyId()
     */
    private $_errorkeyId = '';

    /**
     * The number of currently needed passphrases
     *
     * If this is not zero when the GPG command is completed, the error code is
     * set to {@link Crypt_GPG::ERROR_MISSING_PASSPHRASE}.
     *
     * @var integer
     */
    private $_needPassphrase = 0;

    /**
     * The input source
     *
     * This is data to send to GPG. Either a string or a stream resource.
     *
     * @var string|resource
     * @see Crypt_GPG_Engine::setInput()
     */
    private $_input = null;

    /**
     * The extra message input source
     *
     * Either a string or a stream resource.
     *
     * @var string|resource
     * @see Crypt_GPG_Engine::setMessage()
     */
    private $_message = null;

    /**
     * The output location
     *
     * This is where the output from GPG is sent. Either a string or a stream
     * resource.
     *
     * @var string|resource
     * @see Crypt_GPG_Engine::setOutput()
     */
    private $_output = '';

    /**
     * The GPG operation to execute
     *
     * @var string
     * @see Crypt_GPG_Engine::setOperation()
     */
    private $_operation;

    /**
     * Arguments for the current operation
     *
     * @var array
     * @see Crypt_GPG_Engine::setOperation()
     */
    private $_arguments = array();

    /**
     * The version number of the GPG binary
     *
     * @var string
     * @see Crypt_GPG_Engine::getVersion()
     */
    private $_version = '';

    /**
     * Cached value indicating whether or not mbstring function overloading is
     * on for strlen
     *
     * This is cached for optimal performance inside the I/O loop.
     *
     * @var boolean
     * @see Crypt_GPG_Engine::_byteLength()
     * @see Crypt_GPG_Engine::_byteSubstring()
     */
    private static $_mbStringOverload = null;

    // }}}
    // {{{ __construct()

    /**
     * Creates a new GPG engine
     *
     * Available options are:
     *
     * - <kbd>string  homedir</kbd>        - the directory where the GPG
     *                                       keyring files are stored. If not
     *                                       specified, Crypt_GPG uses the
     *                                       default of <kbd>~/.gnupg</kbd>.
     * - <kbd>string  publicKeyring</kbd>  - the file path of the public
     *                                       keyring. Use this if the public
     *                                       keyring is not in the homedir, or
     *                                       if the keyring is in a directory
     *                                       not writable by the process
     *                                       invoking GPG (like Apache). Then
     *                                       you can specify the path to the
     *                                       keyring with this option
     *                                       (/foo/bar/pubring.gpg), and specify
     *                                       a writable directory (like /tmp)
     *                                       using the <i>homedir</i> option.
     * - <kbd>string  privateKeyring</kbd> - the file path of the private
     *                                       keyring. Use this if the private
     *                                       keyring is not in the homedir, or
     *                                       if the keyring is in a directory
     *                                       not writable by the process
     *                                       invoking GPG (like Apache). Then
     *                                       you can specify the path to the
     *                                       keyring with this option
     *                                       (/foo/bar/secring.gpg), and specify
     *                                       a writable directory (like /tmp)
     *                                       using the <i>homedir</i> option.
     * - <kbd>string  trustDb</kbd>        - the file path of the web-of-trust
     *                                       database. Use this if the trust
     *                                       database is not in the homedir, or
     *                                       if the database is in a directory
     *                                       not writable by the process
     *                                       invoking GPG (like Apache). Then
     *                                       you can specify the path to the
     *                                       trust database with this option
     *                                       (/foo/bar/trustdb.gpg), and specify
     *                                       a writable directory (like /tmp)
     *                                       using the <i>homedir</i> option.
     * - <kbd>string  binary</kbd>         - the location of the GPG binary. If
     *                                       not specified, the driver attempts
     *                                       to auto-detect the GPG binary
     *                                       location using a list of known
     *                                       default locations for the current
     *                                       operating system. The option
     *                                       <kbd>gpgBinary</kbd> is a
     *                                       deprecated alias for this option.
     * - <kbd>boolean debug</kbd>          - whether or not to use debug mode.
     *                                       When debug mode is on, all
     *                                       communication to and from the GPG
     *                                       subprocess is logged. This can be
     *                                       useful to diagnose errors when
     *                                       using Crypt_GPG.
     *
     * @param array $options optional. An array of options used to create the
     *                       GPG object. All options are optional and are
     *                       represented as key-value pairs.
     *
     * @throws Crypt_GPG_FileException if the <kbd>homedir</kbd> does not exist
     *         and cannot be created. This can happen if <kbd>homedir</kbd> is
     *         not specified, Crypt_GPG is run as the web user, and the web
     *         user has no home directory. This exception is also thrown if any
     *         of the options <kbd>publicKeyring</kbd>,
     *         <kbd>privateKeyring</kbd> or <kbd>trustDb</kbd> options are
     *         specified but the files do not exist or are are not readable.
     *         This can happen if the user running the Crypt_GPG process (for
     *         example, the Apache user) does not have permission to read the
     *         files.
     *
     * @throws PEAR_Exception if the provided <kbd>binary</kbd> is invalid, or
     *         if no <kbd>binary</kbd> is provided and no suitable binary could
     *         be found.
     */
    public function __construct(array $options = array())
    {
        $this->_isDarwin = (strncmp(strtoupper(PHP_OS), 'DARWIN', 6) === 0);

        // populate mbstring overloading cache if not set
        if (self::$_mbStringOverload === null) {
            self::$_mbStringOverload = (extension_loaded('mbstring')
                && (ini_get('mbstring.func_overload') & 0x02) === 0x02);
        }

        // get homedir
        if (array_key_exists('homedir', $options)) {
            $this->_homedir = (string)$options['homedir'];
        } else {
            // note: this requires the package OS dep exclude 'windows'
            $info = posix_getpwuid(posix_getuid());
            $this->_homedir = $info['dir'].'/.gnupg';
        }

        // attempt to create homedir if it does not exist
        if (!is_dir($this->_homedir)) {
            if (@mkdir($this->_homedir, 0777, true)) {
                // Set permissions on homedir. Parent directories are created
                // with 0777, homedir is set to 0700.
                chmod($this->_homedir, 0700);
            } else {
                throw new Crypt_GPG_FileException('The \'homedir\' "' .
                    $this->_homedir . '" is not readable or does not exist '.
                    'and cannot be created. This can happen if \'homedir\' '.
                    'is not specified in the Crypt_GPG options, Crypt_GPG is '.
                    'run as the web user, and the web user has no home '.
                    'directory.',
                    0, $this->_homedir);
            }
        }

        // get binary
        if (array_key_exists('binary', $options)) {
            $this->_binary = (string)$options['binary'];
        } elseif (array_key_exists('gpgBinary', $options)) {
            // deprecated alias
            $this->_binary = (string)$options['gpgBinary'];
        } else {
            $this->_binary = $this->_getBinary();
        }

        if ($this->_binary == '' || !is_executable($this->_binary)) {
            throw new PEAR_Exception('GPG binary not found. If you are sure '.
                'the GPG binary is installed, please specify the location of '.
                'the GPG binary using the \'binary\' driver option.');
        }

        /*
         * Note:
         *
         * Normally, GnuPG expects keyrings to be in the homedir and expects
         * to be able to write temporary files in the homedir. Sometimes,
         * keyrings are not in the homedir, or location of the keyrings does
         * not allow writing temporary files. In this case, the <i>homedir</i>
         * option by itself is not enough to specify the keyrings because GnuPG
         * can not write required temporary files. Additional options are
         * provided so you can specify the location of the keyrings separately
         * from the homedir.
         */

        // get public keyring
        if (array_key_exists('publicKeyring', $options)) {
            $this->_publicKeyring = (string)$options['publicKeyring'];
            if (!is_readable($this->_publicKeyring)) {
                 throw new Crypt_GPG_FileException('The \'publicKeyring\' "' .
                    $this->_publicKeyring . '" does not exist or is ' .
                    'not readable. Check the location and ensure the file ' .
                    'permissions are correct.', 0, $this->_publicKeyring);
            }
        }

        // get private keyring
        if (array_key_exists('privateKeyring', $options)) {
            $this->_privateKeyring = (string)$options['privateKeyring'];
            if (!is_readable($this->_privateKeyring)) {
                 throw new Crypt_GPG_FileException('The \'privateKeyring\' "' .
                    $this->_privateKeyring . '" does not exist or is ' .
                    'not readable. Check the location and ensure the file ' .
                    'permissions are correct.', 0, $this->_privateKeyring);
            }
        }

        // get trust database
        if (array_key_exists('trustDb', $options)) {
            $this->_trustDb = (string)$options['trustDb'];
            if (!is_readable($this->_trustDb)) {
                 throw new Crypt_GPG_FileException('The \'trustDb\' "' .
                    $this->_trustDb . '" does not exist or is not readable. ' .
                    'Check the location and ensure the file permissions are ' .
                    'correct.', 0, $this->_trustDb);
            }
        }

        if (array_key_exists('debug', $options)) {
            $this->_debug = (boolean)$options['debug'];
        }
    }

    // }}}
    // {{{ __destruct()

    /**
     * Closes open GPG subprocesses when this object is destroyed
     *
     * Subprocesses should never be left open by this class unless there is
     * an unknown error and unexpected script termination occurs.
     */
    public function __destruct()
    {
        $this->_closeSubprocess();
    }

    // }}}
    // {{{ addErrorHandler()

    /**
     * Adds an error handler method
     *
     * The method is run every time a new error line is received from the GPG
     * subprocess. The handler method must accept the error line to be handled
     * as its first parameter.
     *
     * @param callback $callback the callback method to use.
     * @param array    $args     optional. Additional arguments to pass as
     *                           parameters to the callback method.
     *
     * @return void
     */
    public function addErrorHandler($callback, array $args = array())
    {
        $this->_errorHandlers[] = array(
            'callback' => $callback,
            'args'     => $args
        );
    }

    // }}}
    // {{{ addStatusHandler()

    /**
     * Adds a status handler method
     *
     * The method is run every time a new status line is received from the
     * GPG subprocess. The handler method must accept the status line to be
     * handled as its first parameter.
     *
     * @param callback $callback the callback method to use.
     * @param array    $args     optional. Additional arguments to pass as
     *                           parameters to the callback method.
     *
     * @return void
     */
    public function addStatusHandler($callback, array $args = array())
    {
        $this->_statusHandlers[] = array(
            'callback' => $callback,
            'args'     => $args
        );
    }

    // }}}
    // {{{ sendCommand()

    /**
     * Sends a command to the GPG subprocess over the command file-descriptor
     * pipe
     *
     * @param string $command the command to send.
     *
     * @return void
     *
     * @sensitive $command
     */
    public function sendCommand($command)
    {
        if (array_key_exists(self::FD_COMMAND, $this->_openPipes)) {
            $this->_commandBuffer .= $command . PHP_EOL;
        }
    }

    // }}}
    // {{{ reset()

    /**
     * Resets the GPG engine, preparing it for a new operation
     *
     * @return void
     *
     * @see Crypt_GPG_Engine::run()
     * @see Crypt_GPG_Engine::setOperation()
     */
    public function reset()
    {
        $this->_operation      = '';
        $this->_arguments      = array();
        $this->_input          = null;
        $this->_message        = null;
        $this->_output         = '';
        $this->_errorCode      = Crypt_GPG::ERROR_NONE;
        $this->_needPassphrase = 0;
        $this->_commandBuffer  = '';

        $this->_statusHandlers = array();
        $this->_errorHandlers  = array();

        $this->addStatusHandler(array($this, '_handleErrorStatus'));
        $this->addErrorHandler(array($this, '_handleErrorError'));

        if ($this->_debug) {
            $this->addStatusHandler(array($this, '_handleDebugStatus'));
            $this->addErrorHandler(array($this, '_handleDebugError'));
        }
    }

    // }}}
    // {{{ run()

    /**
     * Runs the current GPG operation
     *
     * This creates and manages the GPG subprocess.
     *
     * The operation must be set with {@link Crypt_GPG_Engine::setOperation()}
     * before this method is called.
     *
     * @return void
     *
     * @throws Crypt_GPG_InvalidOperationException if no operation is specified.
     *
     * @see Crypt_GPG_Engine::reset()
     * @see Crypt_GPG_Engine::setOperation()
     */
    public function run()
    {
        if ($this->_operation === '') {
            throw new Crypt_GPG_InvalidOperationException('No GPG operation ' .
                'specified. Use Crypt_GPG_Engine::setOperation() before ' .
                'calling Crypt_GPG_Engine::run().');
        }

        $this->_openSubprocess();
        $this->_process();
        $this->_closeSubprocess();
    }

    // }}}
    // {{{ getErrorCode()

    /**
     * Gets the error code of the last executed operation
     *
     * This value is only meaningful after {@link Crypt_GPG_Engine::run()} has
     * been executed.
     *
     * @return integer the error code of the last executed operation.
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    // }}}
    // {{{ getErrorFilename()

    /**
     * Gets the file related to the error code of the last executed operation
     *
     * This value is only meaningful after {@link Crypt_GPG_Engine::run()} has
     * been executed. If there is no file related to the error, an empty string
     * is returned.
     *
     * @return string the file related to the error code of the last executed
     *                operation.
     */
    public function getErrorFilename()
    {
        return $this->_errorFilename;
    }

    // }}}
    // {{{ getErrorKeyId()

    /**
     * Gets the key id related to the error code of the last executed operation
     *
     * This value is only meaningful after {@link Crypt_GPG_Engine::run()} has
     * been executed. If there is no key id related to the error, an empty
     * string is returned.
     *
     * @return string the key id related to the error code of the last executed
     *                operation.
     */
    public function getErrorKeyId()
    {
        return $this->_errorKeyId;
    }

    // }}}
    // {{{ setInput()

    /**
     * Sets the input source for the current GPG operation
     *
     * @param string|resource &$input either a reference to the string
     *                                containing the input data or an open
     *                                stream resource containing the input
     *                                data.
     *
     * @return void
     */
    public function setInput(&$input)
    {
        $this->_input =& $input;
    }

    // }}}
    // {{{ setMessage()

    /**
     * Sets the message source for the current GPG operation
     *
     * Detached signature data should be specified here.
     *
     * @param string|resource &$message either a reference to the string
     *                                  containing the message data or an open
     *                                  stream resource containing the message
     *                                  data.
     *
     * @return void
     */
    public function setMessage(&$message)
    {
        $this->_message =& $message;
    }

    // }}}
    // {{{ setOutput()

    /**
     * Sets the output destination for the current GPG operation
     *
     * @param string|resource &$output either a reference to the string in
     *                                 which to store GPG output or an open
     *                                 stream resource to which the output data
     *                                 should be written.
     *
     * @return void
     */
    public function setOutput(&$output)
    {
        $this->_output =& $output;
    }

    // }}}
    // {{{ setOperation()

    /**
     * Sets the operation to perform
     *
     * @param string $operation the operation to perform. This should be one
     *                          of GPG's operations. For example,
     *                          <kbd>--encrypt</kbd>, <kbd>--decrypt</kbd>,
     *                          <kbd>--sign</kbd>, etc.
     * @param array  $arguments optional. Additional arguments for the GPG
     *                          subprocess. See the GPG manual for specific
     *                          values.
     *
     * @return void
     *
     * @see Crypt_GPG_Engine::reset()
     * @see Crypt_GPG_Engine::run()
     */
    public function setOperation($operation, array $arguments = array())
    {
        $this->_operation = $operation;
        $this->_arguments = $arguments;
    }

    // }}}
    // {{{ getVersion()

    /**
     * Gets the version of the GnuPG binary
     *
     * @return string a version number string containing the version of GnuPG
     *                being used. This value is suitable to use with PHP's
     *                version_compare() function.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     *
     * @throws Crypt_GPG_UnsupportedException if the provided binary is not
     *         GnuPG or if the GnuPG version is less than 1.0.2.
     */
    public function getVersion()
    {
        if ($this->_version == '') {

            $options = array(
                'homedir' => $this->_homedir,
                'binary'  => $this->_binary,
                'debug'   => $this->_debug
            );

            $engine = new self($options);
            $info   = '';

            // Set a garbage version so we do not end up looking up the version
            // recursively.
            $engine->_version = '1.0.0';

            $engine->reset();
            $engine->setOutput($info);
            $engine->setOperation('--version');
            $engine->run();

            $code = $this->getErrorCode();

            if ($code !== Crypt_GPG::ERROR_NONE) {
                throw new Crypt_GPG_Exception(
                    'Unknown error getting GnuPG version information. Please ' .
                    'use the \'debug\' option when creating the Crypt_GPG ' .
                    'object, and file a bug report at ' . Crypt_GPG::BUG_URI,
                    $code);
            }

            $matches    = array();
            $expression = '/gpg \(GnuPG\) (\S+)/';

            if (preg_match($expression, $info, $matches) === 1) {
                $this->_version = $matches[1];
            } else {
                throw new Crypt_GPG_Exception(
                    'No GnuPG version information provided by the binary "' .
                    $this->_binary . '". Are you sure it is GnuPG?');
            }

            if (version_compare($this->_version, self::MIN_VERSION, 'lt')) {
                throw new Crypt_GPG_Exception(
                    'The version of GnuPG being used (' . $this->_version .
                    ') is not supported by Crypt_GPG. The minimum version ' .
                    'required by Crypt_GPG is ' . self::MIN_VERSION);
            }
        }


        return $this->_version;
    }

    // }}}
    // {{{ _handleErrorStatus()

    /**
     * Handles error values in the status output from GPG
     *
     * This method is responsible for setting the
     * {@link Crypt_GPG_Engine::$_errorCode}. See <b>doc/DETAILS</b> in the
     * {@link http://www.gnupg.org/download/ GPG distribution} for detailed
     * information on GPG's status output.
     *
     * @param string $line the status line to handle.
     *
     * @return void
     */
    private function _handleErrorStatus($line)
    {
        $tokens = explode(' ', $line);
        switch ($tokens[0]) {
        case 'BAD_PASSPHRASE':
            $this->_errorCode = Crypt_GPG::ERROR_BAD_PASSPHRASE;
            break;

        case 'MISSING_PASSPHRASE':
            $this->_errorCode = Crypt_GPG::ERROR_MISSING_PASSPHRASE;
            break;

        case 'NODATA':
            $this->_errorCode = Crypt_GPG::ERROR_NO_DATA;
            break;

        case 'DELETE_PROBLEM':
            if ($tokens[1] == '1') {
                $this->_errorCode = Crypt_GPG::ERROR_KEY_NOT_FOUND;
                break;
            } elseif ($tokens[1] == '2') {
                $this->_errorCode = Crypt_GPG::ERROR_DELETE_PRIVATE_KEY;
                break;
            }
            break;

        case 'IMPORT_RES':
            if ($tokens[12] > 0) {
                $this->_errorCode = Crypt_GPG::ERROR_DUPLICATE_KEY;
            }
            break;

        case 'NO_PUBKEY':
        case 'NO_SECKEY':
            $this->_errorKeyId = $tokens[1];
            $this->_errorCode  = Crypt_GPG::ERROR_KEY_NOT_FOUND;
            break;

        case 'NEED_PASSPHRASE':
            $this->_needPassphrase++;
            break;

        case 'GOOD_PASSPHRASE':
            $this->_needPassphrase--;
            break;

        case 'EXPSIG':
        case 'EXPKEYSIG':
        case 'REVKEYSIG':
        case 'BADSIG':
            $this->_errorCode = Crypt_GPG::ERROR_BAD_SIGNATURE;
            break;

        }
    }

    // }}}
    // {{{ _handleErrorError()

    /**
     * Handles error values in the error output from GPG
     *
     * This method is responsible for setting the
     * {@link Crypt_GPG_Engine::$_errorCode}.
     *
     * @param string $line the error line to handle.
     *
     * @return void
     */
    private function _handleErrorError($line)
    {
        if ($this->_errorCode === Crypt_GPG::ERROR_NONE) {
            $pattern = '/no valid OpenPGP data found/';
            if (preg_match($pattern, $line) === 1) {
                $this->_errorCode = Crypt_GPG::ERROR_NO_DATA;
            }
        }

        if ($this->_errorCode === Crypt_GPG::ERROR_NONE) {
            $pattern = '/No secret key|secret key not available/';
            if (preg_match($pattern, $line) === 1) {
                $this->_errorCode = Crypt_GPG::ERROR_KEY_NOT_FOUND;
            }
        }

        if ($this->_errorCode === Crypt_GPG::ERROR_NONE) {
            $pattern = '/No public key|public key not found/';
            if (preg_match($pattern, $line) === 1) {
                $this->_errorCode = Crypt_GPG::ERROR_KEY_NOT_FOUND;
            }
        }

        if ($this->_errorCode === Crypt_GPG::ERROR_NONE) {
            $matches = array();
            $pattern = '/can\'t (?:access|open) `(.*?)\'/';
            if (preg_match($pattern, $line, $matches) === 1) {
                $this->_errorFilename = $matches[1];
                $this->_errorCode = Crypt_GPG::ERROR_FILE_PERMISSIONS;
            }
        }
    }

    // }}}
    // {{{ _handleDebugStatus()

    /**
     * Displays debug output for status lines
     *
     * @param string $line the status line to handle.
     *
     * @return void
     */
    private function _handleDebugStatus($line)
    {
        $this->_debug('STATUS: ' . $line);
    }

    // }}}
    // {{{ _handleDebugError()

    /**
     * Displays debug output for error lines
     *
     * @param string $line the error line to handle.
     *
     * @return void
     */
    private function _handleDebugError($line)
    {
        $this->_debug('ERROR: ' . $line);
    }

    // }}}
    // {{{ _process()

    /**
     * Performs internal streaming operations for the subprocess using either
     * strings or streams as input / output points
     *
     * This is the main I/O loop for streaming to and from the GPG subprocess.
     *
     * The implementation of this method is verbose mainly for performance
     * reasons. Adding streams to a lookup array and looping the array inside
     * the main I/O loop would be siginficantly slower for large streams.
     *
     * @return void
     *
     * @throws Crypt_GPG_Exception if there is an error selecting streams for
     *         reading or writing. If this occurs, please file a bug report at
     *         http://pear.php.net/bugs/report.php?package=Crypt_GPG.
     */
    private function _process()
    {
        $this->_debug('BEGIN PROCESSING');

        $this->_commandBuffer = '';    // buffers input to GPG
        $messageBuffer        = '';    // buffers input to GPG
        $inputBuffer          = '';    // buffers input to GPG
        $outputBuffer         = '';    // buffers output from GPG
        $statusBuffer         = '';    // buffers output from GPG
        $errorBuffer          = '';    // buffers output from GPG
        $inputComplete        = false; // input stream is completely buffered
        $messageComplete      = false; // message stream is completely buffered

        if (is_string($this->_input)) {
            $inputBuffer   = $this->_input;
            $inputComplete = true;
        }

        if (is_string($this->_message)) {
            $messageBuffer   = $this->_message;
            $messageComplete = true;
        }

        if (is_string($this->_output)) {
            $outputBuffer =& $this->_output;
        }

        // convenience variables
        $fdInput   = $this->_pipes[self::FD_INPUT];
        $fdOutput  = $this->_pipes[self::FD_OUTPUT];
        $fdError   = $this->_pipes[self::FD_ERROR];
        $fdStatus  = $this->_pipes[self::FD_STATUS];
        $fdCommand = $this->_pipes[self::FD_COMMAND];
        $fdMessage = $this->_pipes[self::FD_MESSAGE];

        while (true) {

            $inputStreams     = array();
            $outputStreams    = array();
            $exceptionStreams = array();

            // set up input streams
            if (is_resource($this->_input) && !$inputComplete) {
                if (feof($this->_input)) {
                    $inputComplete = true;
                } else {
                    $inputStreams[] = $this->_input;
                }
            }

            // close GPG input pipe if there is no more data
            if ($inputBuffer == '' && $inputComplete) {
                $this->_debug('=> closing GPG input pipe');
                $this->_closePipe(self::FD_INPUT);
            }

            if (is_resource($this->_message) && !$messageComplete) {
                if (feof($this->_message)) {
                    $messageComplete = true;
                } else {
                    $inputStreams[] = $this->_message;
                }
            }

            // close GPG message pipe if there is no more data
            if ($messageBuffer == '' && $messageComplete) {
                $this->_debug('=> closing GPG message pipe');
                $this->_closePipe(self::FD_MESSAGE);
            }

            if (!feof($fdOutput)) {
                $inputStreams[] = $fdOutput;
            }

            if (!feof($fdStatus)) {
                $inputStreams[] = $fdStatus;
            }

            if (!feof($fdError)) {
                $inputStreams[] = $fdError;
            }

            // set up output streams
            if ($outputBuffer != '' && is_resource($this->_output)) {
                $outputStreams[] = $this->_output;
            }

            if ($this->_commandBuffer != '') {
                $outputStreams[] = $fdCommand;
            }

            if ($messageBuffer != '') {
                $outputStreams[] = $fdMessage;
            }

            if ($inputBuffer != '') {
                $outputStreams[] = $fdInput;
            }

            // no streams left to read or write, we're all done
            if (count($inputStreams) === 0 && count($outputStreams) === 0) {
                break;
            }

            $this->_debug('selecting streams');

            $ready = stream_select(
                $inputStreams,
                $outputStreams,
                $exceptionStreams,
                null
            );

            $this->_debug('=> got ' . $ready);

            if ($ready === false) {
                throw new Crypt_GPG_Exception(
                    'Error selecting stream for communication with GPG ' .
                    'subprocess. Please file a bug report at: ' .
                    'http://pear.php.net/bugs/report.php?package=Crypt_GPG');
            }

            if ($ready === 0) {
                throw new Crypt_GPG_Exception(
                    'stream_select() returned 0. This can not happen! Please ' .
                    'file a bug report at: ' .
                    'http://pear.php.net/bugs/report.php?package=Crypt_GPG');
            }

            // write input (to GPG)
            if (in_array($fdInput, $outputStreams)) {
                $this->_debug('GPG is ready for input');

                $chunk = self::_byteSubstring(
                    $inputBuffer,
                    0,
                    self::CHUNK_SIZE
                );

                $length = self::_byteLength($chunk);

                $this->_debug(
                    '=> about to write ' . $length . ' bytes to GPG input'
                );

                $length = fwrite($fdInput, $chunk, $length);

                $this->_debug('=> wrote ' . $length . ' bytes');

                $inputBuffer = self::_byteSubstring(
                    $inputBuffer,
                    $length
                );
            }

            // read input (from PHP stream)
            if (in_array($this->_input, $inputStreams)) {
                $this->_debug('input stream is ready for reading');
                $this->_debug(
                    '=> about to read ' . self::CHUNK_SIZE .
                    ' bytes from input stream'
                );

                $chunk        = fread($this->_input, self::CHUNK_SIZE);
                $length       = self::_byteLength($chunk);
                $inputBuffer .= $chunk;

                $this->_debug('=> read ' . $length . ' bytes');
            }

            // write message (to GPG)
            if (in_array($fdMessage, $outputStreams)) {
                $this->_debug('GPG is ready for message data');

                $chunk = self::_byteSubstring(
                    $messageBuffer,
                    0,
                    self::CHUNK_SIZE
                );

                $length = self::_byteLength($chunk);

                $this->_debug(
                    '=> about to write ' . $length . ' bytes to GPG message'
                );

                $length = fwrite($fdMessage, $chunk, $length);
                $this->_debug('=> wrote ' . $length . ' bytes');

                $messageBuffer = self::_byteSubstring($messageBuffer, $length);
            }

            // read message (from PHP stream)
            if (in_array($this->_message, $inputStreams)) {
                $this->_debug('message stream is ready for reading');
                $this->_debug(
                    '=> about to read ' . self::CHUNK_SIZE .
                    ' bytes from message stream'
                );

                $chunk          = fread($this->_message, self::CHUNK_SIZE);
                $length         = self::_byteLength($chunk);
                $messageBuffer .= $chunk;

                $this->_debug('=> read ' . $length . ' bytes');
            }

            // read output (from GPG)
            if (in_array($fdOutput, $inputStreams)) {
                $this->_debug('GPG output stream ready for reading');
                $this->_debug(
                    '=> about to read ' . self::CHUNK_SIZE .
                    ' bytes from GPG output'
                );

                $chunk         = fread($fdOutput, self::CHUNK_SIZE);
                $length        = self::_byteLength($chunk);
                $outputBuffer .= $chunk;

                $this->_debug('=> read ' . $length . ' bytes');
            }

            // write output (to PHP stream)
            if (in_array($this->_output, $outputStreams)) {
                $this->_debug('output stream is ready for data');

                $chunk = self::_byteSubstring(
                    $outputBuffer,
                    0,
                    self::CHUNK_SIZE
                );

                $length = self::_byteLength($chunk);

                $this->_debug(
                    '=> about to write ' . $length . ' bytes to output stream'
                );

                $length = fwrite($this->_output, $chunk, $length);

                $this->_debug('=> wrote ' . $length . ' bytes');

                $outputBuffer = self::_byteSubstring($outputBuffer, $length);
            }

            // read error (from GPG)
            if (in_array($fdError, $inputStreams)) {
                $this->_debug('GPG error stream ready for reading');
                $this->_debug(
                    '=> about to read ' . self::CHUNK_SIZE .
                    ' bytes from GPG error'
                );

                $chunk        = fread($fdError, self::CHUNK_SIZE);
                $length       = self::_byteLength($chunk);
                $errorBuffer .= $chunk;

                $this->_debug('=> read ' . $length . ' bytes');

                // pass lines to error handlers
                while (($pos = strpos($errorBuffer, PHP_EOL)) !== false) {
                    $line = self::_byteSubstring($errorBuffer, 0, $pos);
                    foreach ($this->_errorHandlers as $handler) {
                        array_unshift($handler['args'], $line);
                        call_user_func_array(
                            $handler['callback'],
                            $handler['args']
                        );

                        array_shift($handler['args']);
                    }
                    $errorBuffer = self::_byteSubString(
                        $errorBuffer,
                        $pos + self::_byteLength(PHP_EOL)
                    );
                }
            }

            // read status (from GPG)
            if (in_array($fdStatus, $inputStreams)) {
                $this->_debug('GPG status stream ready for reading');
                $this->_debug(
                    '=> about to read ' . self::CHUNK_SIZE .
                    ' bytes from GPG status'
                );

                $chunk         = fread($fdStatus, self::CHUNK_SIZE);
                $length        = self::_byteLength($chunk);
                $statusBuffer .= $chunk;

                $this->_debug('=> read ' . $length . ' bytes');

                // pass lines to status handlers
                while (($pos = strpos($statusBuffer, PHP_EOL)) !== false) {
                    $line = self::_byteSubstring($statusBuffer, 0, $pos);
                    // only pass lines beginning with magic prefix
                    if (self::_byteSubstring($line, 0, 9) == '[GNUPG:] ') {
                        $line = self::_byteSubstring($line, 9);
                        foreach ($this->_statusHandlers as $handler) {
                            array_unshift($handler['args'], $line);
                            call_user_func_array(
                                $handler['callback'],
                                $handler['args']
                            );

                            array_shift($handler['args']);
                        }
                    }
                    $statusBuffer = self::_byteSubString(
                        $statusBuffer,
                        $pos + self::_byteLength(PHP_EOL)
                    );
                }
            }

            // write command (to GPG)
            if (in_array($fdCommand, $outputStreams)) {
                $this->_debug('GPG is ready for command data');

                // send commands
                $chunk = self::_byteSubstring(
                    $this->_commandBuffer,
                    0,
                    self::CHUNK_SIZE
                );

                $length = self::_byteLength($chunk);

                $this->_debug(
                    '=> about to write ' . $length . ' bytes to GPG command'
                );

                $length = fwrite($fdCommand, $chunk, $length);

                $this->_debug('=> wrote ' . $length);

                $this->_commandBuffer = self::_byteSubstring(
                    $this->_commandBuffer,
                    $length
                );
            }

        } // end loop while streams are open

        $this->_debug('END PROCESSING');
    }

    // }}}
    // {{{ _openSubprocess()

    /**
     * Opens an internal GPG subprocess for the current operation
     *
     * Opens a GPG subprocess, then connects the subprocess to some pipes. Sets
     * the private class property {@link Crypt_GPG_Engine::$_process} to
     * the new subprocess.
     *
     * @return void
     *
     * @throws Crypt_GPG_OpenSubprocessException if the subprocess could not be
     *         opened.
     *
     * @see Crypt_GPG_Engine::setOperation()
     * @see Crypt_GPG_Engine::_closeSubprocess()
     * @see Crypt_GPG_Engine::$_process
     */
    private function _openSubprocess()
    {
        $version = $this->getVersion();

        $env = $_ENV;

        // Newer versions of GnuPG return localized results. Crypt_GPG only
        // works with English, so set the locale to 'C' for the subprocess.
        $env['LC_ALL'] = 'C';

        $commandLine = $this->_binary;

        $defaultArguments = array(
            '--status-fd ' . escapeshellarg(self::FD_STATUS),
            '--command-fd ' . escapeshellarg(self::FD_COMMAND),
            '--no-secmem-warning',
            '--no-tty',
            '--no-default-keyring', // ignored if keying files are not specified
            '--no-options'          // prevent creation of ~/.gnupg directory
        );

        if (version_compare($version, '1.0.7', 'ge')) {
            if (version_compare($version, '2.0.0', 'lt')) {
                $defaultArguments[] = '--no-use-agent';
            }
            $defaultArguments[] = '--no-permission-warning';
        }

        if (version_compare($version, '1.4.2', 'ge')) {
            $defaultArguments[] = '--exit-on-status-write-error';
        }

        if (version_compare($version, '1.3.2', 'ge')) {
            $defaultArguments[] = '--trust-model always';
        } else {
            $defaultArguments[] = '--always-trust';
        }

        $arguments = array_merge($defaultArguments, $this->_arguments);

        if ($this->_homedir) {
            $arguments[] = '--homedir ' . escapeshellarg($this->_homedir);

            // the random seed file makes subsequent actions faster so only
            // disable it if we have to.
            if (!is_writeable($this->_homedir)) {
                $arguments[] = '--no-random-seed-file';
            }
        }

        if ($this->_publicKeyring) {
            $arguments[] = '--keyring ' . escapeshellarg($this->_publicKeyring);
        }

        if ($this->_privateKeyring) {
            $arguments[] = '--secret-keyring ' .
                escapeshellarg($this->_privateKeyring);
        }

        if ($this->_trustDb) {
            $arguments[] = '--trustdb-name ' . escapeshellarg($this->_trustDb);
        }

        $commandLine .= ' ' . implode(' ', $arguments) . ' ' .
            $this->_operation;

        // Binary operations will not work on Windows with PHP < 5.2.6. This is
        // in case stream_select() ever works on Windows.
        $rb = (version_compare(PHP_VERSION, '5.2.6') < 0) ? 'r' : 'rb';
        $wb = (version_compare(PHP_VERSION, '5.2.6') < 0) ? 'w' : 'wb';

        $descriptorSpec = array(
            self::FD_INPUT   => array('pipe', $rb), // stdin
            self::FD_OUTPUT  => array('pipe', $wb), // stdout
            self::FD_ERROR   => array('pipe', $wb), // stderr
            self::FD_STATUS  => array('pipe', $wb), // status
            self::FD_COMMAND => array('pipe', $rb), // command
            self::FD_MESSAGE => array('pipe', $rb)  // message
        );

        $this->_debug('OPENING SUBPROCESS WITH THE FOLLOWING COMMAND:');
        $this->_debug($commandLine);

        $this->_process = proc_open(
            $commandLine,
            $descriptorSpec,
            $this->_pipes,
            null,
            $env,
            array('binary_pipes' => true)
        );

        if (!is_resource($this->_process)) {
            throw new Crypt_GPG_OpenSubprocessException(
                'Unable to open GPG subprocess.', 0, $commandLine);
        }

        $this->_openPipes = $this->_pipes;
        $this->_errorCode = Crypt_GPG::ERROR_NONE;
    }

    // }}}
    // {{{ _closeSubprocess()

    /**
     * Closes a the internal GPG subprocess
     *
     * Closes the internal GPG subprocess. Sets the private class property
     * {@link Crypt_GPG_Engine::$_process} to null.
     *
     * @return void
     *
     * @see Crypt_GPG_Engine::_openSubprocess()
     * @see Crypt_GPG_Engine::$_process
     */
    private function _closeSubprocess()
    {
        if (is_resource($this->_process)) {
            $this->_debug('CLOSING SUBPROCESS');

            // close remaining open pipes
            foreach (array_keys($this->_openPipes) as $pipeNumber) {
                $this->_closePipe($pipeNumber);
            }

            $exitCode = proc_close($this->_process);

            if ($exitCode != 0) {
                $this->_debug(
                    '=> subprocess returned an unexpected exit code: ' .
                    $exitCode
                );

                if ($this->_errorCode === Crypt_GPG::ERROR_NONE) {
                    if ($this->_needPassphrase > 0) {
                        $this->_errorCode = Crypt_GPG::ERROR_MISSING_PASSPHRASE;
                    } else {
                        $this->_errorCode = Crypt_GPG::ERROR_UNKNOWN;
                    }
                }
            }

            $this->_process = null;
            $this->_pipes   = array();
        }
    }

    // }}}
    // {{{ _closePipe()

    /**
     * Closes an opened pipe used to communicate with the GPG subprocess
     *
     * If the pipe is already closed, it is ignored. If the pipe is open, it
     * is flushed and then closed.
     *
     * @param integer $pipeNumber the file descriptor number of the pipe to
     *                            close.
     *
     * @return void
     */
    private function _closePipe($pipeNumber)
    {
        $pipeNumber = intval($pipeNumber);
        if (array_key_exists($pipeNumber, $this->_openPipes)) {
            fflush($this->_openPipes[$pipeNumber]);
            fclose($this->_openPipes[$pipeNumber]);
            unset($this->_openPipes[$pipeNumber]);
        }
    }

    // }}}
    // {{{ _getBinary()

    /**
     * Gets the name of the GPG binary for the current operating system
     *
     * This method is called if the '<kbd>binary</kbd>' option is <i>not</i>
     * specified when creating this driver.
     *
     * @return string the name of the GPG binary for the current operating
     *                system. If no suitable binary could be found, an empty
     *                string is returned.
     */
    private function _getBinary()
    {
        $binary = '';

        if ($this->_isDarwin) {
            $binaryFiles = array(
                '/opt/local/bin/gpg', // MacPorts
                '/usr/local/bin/gpg', // Mac GPG
                '/sw/bin/gpg',        // Fink
                '/usr/bin/gpg'
            );
        } else {
            $binaryFiles = array(
                '/usr/bin/gpg',
                '/usr/local/bin/gpg'
            );
        }

        foreach ($binaryFiles as $binaryFile) {
            if (is_executable($binaryFile)) {
                $binary = $binaryFile;
                break;
            }
        }

        return $binary;
    }

    // }}}
    // {{{ _debug()

    /**
     * Displays debug text if debugging is turned on
     *
     * Debugging text is prepended with a debug identifier and echoed to stdout.
     *
     * @param string $text the debugging text to display.
     *
     * @return void
     */
    private function _debug($text)
    {
        if ($this->_debug) {
            if (array_key_exists('SHELL', $_ENV)) {
                foreach (explode(PHP_EOL, $text) as $line) {
                    echo "Crypt_GPG DEBUG: ", $line, PHP_EOL;
                }
            } else {
                // running on a web server, format debug output nicely
                foreach (explode(PHP_EOL, $text) as $line) {
                    echo "Crypt_GPG DEBUG: <strong>", $line,
                        '</strong><br />', PHP_EOL;
                }
            }
        }
    }

    // }}}
    // {{{ _byteLength()

    /**
     * Gets the length of a string in bytes even if mbstring function
     * overloading is turned on
     *
     * This is used for stream-based communication with the GPG subprocess.
     *
     * @param string $string the string for which to get the length.
     *
     * @return integer the length of the string in bytes.
     *
     * @see Crypt_GPG_Engine::$_mbStringOverload
     */
    private static function _byteLength($string)
    {
        if (self::$_mbStringOverload) {
            return mb_strlen($string, '8bit');
        }

        return strlen((binary)$string);
    }

    // }}}
    // {{{ _byteSubstring()

    /**
     * Gets the substring of a string in bytes even if mbstring function
     * overloading is turned on
     *
     * This is used for stream-based communication with the GPG subprocess.
     *
     * @param string  $string the input string.
     * @param integer $start  the starting point at which to get the substring.
     * @param integer $length optional. The length of the substring.
     *
     * @return string the extracted part of the string. Unlike the default PHP
     *                <kbd>substr()</kbd> function, the returned value is
     *                always a string and never false.
     *
     * @see Crypt_GPG_Engine::$_mbStringOverload
     */
    private static function _byteSubstring($string, $start, $length = null)
    {
        if (self::$_mbStringOverload) {
            if ($length === null) {
                return mb_substr(
                    $string,
                    $start,
                    self::_byteLength($string) - $start, '8bit'
                );
            }

            return mb_substr($string, $start, $length, '8bit');
        }

        if ($length === null) {
            return (string)substr((binary)$string, $start);
        }

        return (string)substr((binary)$string, $start, $length);
    }

    // }}}
}

// }}}

?>

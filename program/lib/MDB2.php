<?php
// vim: set et ts=4 sw=4 fdm=marker:
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2004 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//

/**
 * @package  MDB2
 * @category Database
 * @author   Lukas Smith <smith@pooteeweet.org>
 */

require_once 'PEAR.php';

/**
 * The method mapErrorCode in each MDB2_dbtype implementation maps
 * native error codes to one of these.
 *
 * If you add an error code here, make sure you also add a textual
 * version of it in MDB2::errorMessage().
 */

define('MDB2_OK',                         1);
define('MDB2_ERROR',                     -1);
define('MDB2_ERROR_SYNTAX',              -2);
define('MDB2_ERROR_CONSTRAINT',          -3);
define('MDB2_ERROR_NOT_FOUND',           -4);
define('MDB2_ERROR_ALREADY_EXISTS',      -5);
define('MDB2_ERROR_UNSUPPORTED',         -6);
define('MDB2_ERROR_MISMATCH',            -7);
define('MDB2_ERROR_INVALID',             -8);
define('MDB2_ERROR_NOT_CAPABLE',         -9);
define('MDB2_ERROR_TRUNCATED',          -10);
define('MDB2_ERROR_INVALID_NUMBER',     -11);
define('MDB2_ERROR_INVALID_DATE',       -12);
define('MDB2_ERROR_DIVZERO',            -13);
define('MDB2_ERROR_NODBSELECTED',       -14);
define('MDB2_ERROR_CANNOT_CREATE',      -15);
define('MDB2_ERROR_CANNOT_DELETE',      -16);
define('MDB2_ERROR_CANNOT_DROP',        -17);
define('MDB2_ERROR_NOSUCHTABLE',        -18);
define('MDB2_ERROR_NOSUCHFIELD',        -19);
define('MDB2_ERROR_NEED_MORE_DATA',     -20);
define('MDB2_ERROR_NOT_LOCKED',         -21);
define('MDB2_ERROR_VALUE_COUNT_ON_ROW', -22);
define('MDB2_ERROR_INVALID_DSN',        -23);
define('MDB2_ERROR_CONNECT_FAILED',     -24);
define('MDB2_ERROR_EXTENSION_NOT_FOUND',-25);
define('MDB2_ERROR_NOSUCHDB',           -26);
define('MDB2_ERROR_ACCESS_VIOLATION',   -27);
define('MDB2_ERROR_CANNOT_REPLACE',     -28);
define('MDB2_ERROR_CONSTRAINT_NOT_NULL',-29);
define('MDB2_ERROR_DEADLOCK',           -30);
define('MDB2_ERROR_CANNOT_ALTER',       -31);
define('MDB2_ERROR_MANAGER',            -32);
define('MDB2_ERROR_MANAGER_PARSE',      -33);
define('MDB2_ERROR_LOADMODULE',         -34);
define('MDB2_ERROR_INSUFFICIENT_DATA',  -35);

/**
 * This is a special constant that tells MDB2 the user hasn't specified
 * any particular get mode, so the default should be used.
 */

define('MDB2_FETCHMODE_DEFAULT', 0);

/**
 * Column data indexed by numbers, ordered from 0 and up
 */

define('MDB2_FETCHMODE_ORDERED',  1);

/**
 * Column data indexed by column names
 */

define('MDB2_FETCHMODE_ASSOC',    2);

/**
 * Column data as object properties
 */
define('MDB2_FETCHMODE_OBJECT',   3);

/**
 * For multi-dimensional results: normally the first level of arrays
 * is the row number, and the second level indexed by column number or name.
 * MDB2_FETCHMODE_FLIPPED switches this order, so the first level of arrays
 * is the column name, and the second level the row number.
 */

define('MDB2_FETCHMODE_FLIPPED',  4);

// }}}
// {{{ portability modes


/**
 * Portability: turn off all portability features.
 * @see MDB2_Driver_Common::setOption()
 */
define('MDB2_PORTABILITY_NONE', 0);

/**
 * Portability: convert names of tables and fields to case defined in the
 * "field_case" option when using the query*(), fetch*() and tableInfo() methods.
 * @see MDB2_Driver_Common::setOption()
 */
define('MDB2_PORTABILITY_FIX_CASE', 1);

/**
 * Portability: right trim the data output by query*() and fetch*().
 * @see MDB2_Driver_Common::setOption()
 */
define('MDB2_PORTABILITY_RTRIM', 2);

/**
 * Portability: force reporting the number of rows deleted.
 * @see MDB2_Driver_Common::setOption()
 */
define('MDB2_PORTABILITY_DELETE_COUNT', 4);

/**
 * Portability: not needed in MDB2 (just left here for compatibility to DB)
 * @see MDB2_Driver_Common::setOption()
 */
define('MDB2_PORTABILITY_NUMROWS', 8);

/**
 * Portability: makes certain error messages in certain drivers compatible
 * with those from other DBMS's.
 *
 * + mysql, mysqli:  change unique/primary key constraints
 *   MDB2_ERROR_ALREADY_EXISTS -> MDB2_ERROR_CONSTRAINT
 *
 * + odbc(access):  MS's ODBC driver reports 'no such field' as code
 *   07001, which means 'too few parameters.'  When this option is on
 *   that code gets mapped to MDB2_ERROR_NOSUCHFIELD.
 *
 * @see MDB2_Driver_Common::setOption()
 */
define('MDB2_PORTABILITY_ERRORS', 16);

/**
 * Portability: convert empty values to null strings in data output by
 * query*() and fetch*().
 * @see MDB2_Driver_Common::setOption()
 */
define('MDB2_PORTABILITY_EMPTY_TO_NULL', 32);

/**
 * Portability: removes database/table qualifiers from associative indexes
 * @see MDB2_Driver_Common::setOption()
 */
define('MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES', 64);

/**
 * Portability: turn on all portability features.
 * @see MDB2_Driver_Common::setOption()
 */
define('MDB2_PORTABILITY_ALL', 127);

/**
 * These are global variables that are used to track the various class instances
 */

$GLOBALS['_MDB2_databases'] = array();
$GLOBALS['_MDB2_dsninfo_default'] = array(
    'phptype'  => false,
    'dbsyntax' => false,
    'username' => false,
    'password' => false,
    'protocol' => false,
    'hostspec' => false,
    'port'     => false,
    'socket'   => false,
    'database' => false,
    'mode'     => false,
);

/**
 * The main 'MDB2' class is simply a container class with some static
 * methods for creating DB objects as well as some utility functions
 * common to all parts of DB.
 *
 * The object model of MDB2 is as follows (indentation means inheritance):
 *
 * MDB2          The main MDB2 class.  This is simply a utility class
 *              with some 'static' methods for creating MDB2 objects as
 *              well as common utility functions for other MDB2 classes.
 *
 * MDB2_Driver_Common   The base for each MDB2 implementation.  Provides default
 * |            implementations (in OO lingo virtual methods) for
 * |            the actual DB implementations as well as a bunch of
 * |            query utility functions.
 * |
 * +-MDB2_mysql  The MDB2 implementation for MySQL. Inherits MDB2_Driver_Common.
 *              When calling MDB2::factory or MDB2::connect for MySQL
 *              connections, the object returned is an instance of this
 *              class.
 * +-MDB2_pgsql  The MDB2 implementation for PostGreSQL. Inherits MDB2_Driver_Common.
 *              When calling MDB2::factory or MDB2::connect for PostGreSQL
 *              connections, the object returned is an instance of this
 *              class.
 *
 * MDB2_Date     This class provides several method to convert from and to
 *              MDB2 timestamps.
 *
 * @package  MDB2
 * @category Database
 * @author   Lukas Smith <smith@pooteeweet.org>
 */
class MDB2
{
    // }}}
    // {{{ setOptions()

    /**
     * set option array in an exiting database object
     *
     * @param   object  $db       MDB2 object
     * @param   array   $options  An associative array of option names and their values.
     * @access  public
     */
    function setOptions(&$db, $options)
    {
        if (is_array($options)) {
            foreach ($options as $option => $value) {
                $test = $db->setOption($option, $value);
                if (PEAR::isError($test)) {
                    return $test;
                }
            }
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ factory()

    /**
     * Create a new MDB2 object for the specified database type
     *
     * IMPORTANT: In order for MDB2 to work properly it is necessary that
     * you make sure that you work with a reference of the original
     * object instead of a copy (this is a PHP4 quirk).
     *
     * For example:
     *     $db =& MDB2::factory($dsn);
     *          ^^
     * And not:
     *     $db = MDB2::factory($dsn);
     *
     * @param   mixed   $dsn      'data source name', see the MDB2::parseDSN
     *                            method for a description of the dsn format.
     *                            Can also be specified as an array of the
     *                            format returned by MDB2::parseDSN.
     * @param   array   $options  An associative array of option names and
     *                            their values.
     * @return  mixed   a newly created MDB2 object, or false on error
     * @access  public
     */
    function &factory($dsn, $options = false)
    {
        $dsninfo = MDB2::parseDSN($dsn);
        if (!array_key_exists('phptype', $dsninfo)) {
            $err =& MDB2::raiseError(MDB2_ERROR_NOT_FOUND,
                null, null, 'no RDBMS driver specified');
            return $err;
        }
        $class_name = 'MDB2_Driver_'.$dsninfo['phptype'];

        if (!class_exists($class_name)) {
            $file_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
            if (!MDB2::fileExists($file_name)) {
                $err =& MDB2::raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    'unable to find: '.$file_name);
                return $err;
            }
            if (!include_once($file_name)) {
                $err =& MDB2::raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    'unable to load driver class: '.$file_name);
                return $err;
            }
        }

        $db =& new $class_name();
        $db->setDSN($dsninfo);
        $err = MDB2::setOptions($db, $options);
        if (PEAR::isError($err)) {
            return $err;
        }

        return $db;
    }

    // }}}
    // {{{ connect()

    /**
     * Create a new MDB2 connection object and connect to the specified
     * database
     *
     * IMPORTANT: In order for MDB2 to work properly it is necessary that
     * you make sure that you work with a reference of the original
     * object instead of a copy (this is a PHP4 quirk).
     *
     * For example:
     *     $db =& MDB2::connect($dsn);
     *          ^^
     * And not:
     *     $db = MDB2::connect($dsn);
     *          ^^
     *
     * @param   mixed   $dsn      'data source name', see the MDB2::parseDSN
     *                            method for a description of the dsn format.
     *                            Can also be specified as an array of the
     *                            format returned by MDB2::parseDSN.
     * @param   array   $options  An associative array of option names and
     *                            their values.
     * @return  mixed   a newly created MDB2 connection object, or a MDB2
     *                  error object on error
     * @access  public
     * @see     MDB2::parseDSN
     */
    function &connect($dsn, $options = false)
    {
        $db =& MDB2::factory($dsn, $options);
        if (PEAR::isError($db)) {
            return $db;
        }

        $err = $db->connect();
        if (PEAR::isError($err)) {
            $dsn = $db->getDSN('string', 'xxx');
            $db->disconnect();
            $err->addUserInfo($dsn);
            return $err;
        }

        return $db;
    }

    // }}}
    // {{{ singleton()

    /**
     * Returns a MDB2 connection with the requested DSN.
     * A newnew MDB2 connection object is only created if no object with the
     * reuested DSN exists yet.
     *
     * IMPORTANT: In order for MDB2 to work properly it is necessary that
     * you make sure that you work with a reference of the original
     * object instead of a copy (this is a PHP4 quirk).
     *
     * For example:
     *     $db =& MDB2::singleton($dsn);
     *          ^^
     * And not:
     *     $db = MDB2::singleton($dsn);
     *          ^^
     *
     * @param   mixed   $dsn      'data source name', see the MDB2::parseDSN
     *                            method for a description of the dsn format.
     *                            Can also be specified as an array of the
     *                            format returned by MDB2::parseDSN.
     * @param   array   $options  An associative array of option names and
     *                            their values.
     * @return  mixed   a newly created MDB2 connection object, or a MDB2
     *                  error object on error
     * @access  public
     * @see     MDB2::parseDSN
     */
    function &singleton($dsn = null, $options = false)
    {
        if ($dsn) {
            $dsninfo = MDB2::parseDSN($dsn);
            $dsninfo = array_merge($GLOBALS['_MDB2_dsninfo_default'], $dsninfo);
            $keys = array_keys($GLOBALS['_MDB2_databases']);
            for ($i=0, $j=count($keys); $i<$j; ++$i) {
                $tmp_dsn = $GLOBALS['_MDB2_databases'][$keys[$i]]->getDSN('array');
                if (count(array_diff($tmp_dsn, $dsninfo)) == 0) {
                    MDB2::setOptions($GLOBALS['_MDB2_databases'][$keys[$i]], $options);
                    return $GLOBALS['_MDB2_databases'][$keys[$i]];
                }
            }
        } elseif (is_array($GLOBALS['_MDB2_databases']) && reset($GLOBALS['_MDB2_databases'])) {
            $db =& $GLOBALS['_MDB2_databases'][key($GLOBALS['_MDB2_databases'])];
            return $db;
        }
        $db =& MDB2::factory($dsn, $options);
        return $db;
    }

    // }}}
    // {{{ loadFile()

    /**
     * load a file (like 'Date')
     *
     * @param  string     $file  name of the file in the MDB2 directory (without '.php')
     * @return string     name of the file that was included
     * @access public
     */
    function loadFile($file)
    {
        $file_name = 'MDB2'.DIRECTORY_SEPARATOR.$file.'.php';
        if (!MDB2::fileExists($file_name)) {
            return MDB2::raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'unable to find: '.$file_name);
        }
        if (!include_once($file_name)) {
            return MDB2::raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'unable to load driver class: '.$file_name);
        }
        return $file_name;
    }

    // }}}
    // {{{ apiVersion()

    /**
     * Return the MDB2 API version
     *
     * @return string     the MDB2 API version number
     * @access public
     */
    function apiVersion()
    {
        return '@package_version@';
    }

    // }}}
    // {{{ raiseError()

    /**
     * This method is used to communicate an error and invoke error
     * callbacks etc.  Basically a wrapper for PEAR::raiseError
     * without the message string.
     *
     * @param mixed    integer error code
     *
     * @param int      error mode, see PEAR_Error docs
     *
     * @param mixed    If error mode is PEAR_ERROR_TRIGGER, this is the
     *                 error level (E_USER_NOTICE etc).  If error mode is
     *                 PEAR_ERROR_CALLBACK, this is the callback function,
     *                 either as a function name, or as an array of an
     *                 object and method name.  For other error modes this
     *                 parameter is ignored.
     *
     * @param string   Extra debug information.  Defaults to the last
     *                 query and native error code.
     *
     * @return object  a PEAR error object
     *
     * @see PEAR_Error
     */
    function &raiseError($code = null, $mode = null, $options = null, $userinfo = null)
    {
        $err =& PEAR::raiseError(null, $code, $mode, $options, $userinfo, 'MDB2_Error', true);
        return $err;
    }

    // }}}
    // {{{ isError()

    /**
     * Tell whether a value is a MDB2 error.
     *
     * @param   mixed $data   the value to test
     * @param   int   $code   if $data is an error object, return true
     *                        only if $code is a string and
     *                        $db->getMessage() == $code or
     *                        $code is an integer and $db->getCode() == $code
     * @access  public
     * @return  bool    true if parameter is an error
     */
    function isError($data, $code = null)
    {
        if (is_a($data, 'MDB2_Error')) {
            if (is_null($code)) {
                return true;
            } elseif (is_string($code)) {
                return $data->getMessage() === $code;
            } else {
                $code = (array)$code;
                return in_array($data->getCode(), $code);
            }
        }
        return false;
    }

    // }}}
    // {{{ isConnection()
    /**
     * Tell whether a value is a MDB2 connection
     *
     * @param mixed $value value to test
     * @return bool whether $value is a MDB2 connection
     * @access public
     */
    function isConnection($value)
    {
        return is_a($value, 'MDB2_Driver_Common');
    }

    // }}}
    // {{{ isResult()
    /**
     * Tell whether a value is a MDB2 result
     *
     * @param mixed $value value to test
     * @return bool whether $value is a MDB2 result
     * @access public
     */
    function isResult($value)
    {
        return is_a($value, 'MDB2_Result');
    }

    // }}}
    // {{{ isResultCommon()
    /**
     * Tell whether a value is a MDB2 result implementing the common interface
     *
     * @param mixed $value value to test
     * @return bool whether $value is a MDB2 result implementing the common interface
     * @access public
     */
    function isResultCommon($value)
    {
        return is_a($value, 'MDB2_Result_Common');
    }

    // }}}
    // {{{ isStatement()
    /**
     * Tell whether a value is a MDB2 statement interface
     *
     * @param mixed $value value to test
     * @return bool whether $value is a MDB2 statement interface
     * @access public
     */
    function isStatement($value)
    {
        return is_a($value, 'MDB2_Statement');
    }

    // }}}
    // {{{ isManip()

    /**
     * Tell whether a query is a data manipulation query (insert,
     * update or delete) or a data definition query (create, drop,
     * alter, grant, revoke).
     *
     * @param   string   $query the query
     * @return  boolean  whether $query is a data manipulation query
     * @access public
     */
    function isManip($query)
    {
        $manips = 'INSERT|UPDATE|DELETE|REPLACE|'
               . 'CREATE|DROP|'
               . 'LOAD DATA|SELECT .* INTO|COPY|'
               . 'ALTER|GRANT|REVOKE|SET|'
               . 'LOCK|UNLOCK';
        if (preg_match('/^\s*"?('.$manips.')\s+/i', $query)) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ errorMessage()

    /**
     * Return a textual error message for a MDB2 error code
     *
     * @param   int     $value error code
     * @return  string  error message, or false if the error code was
     *                  not recognized
     * @access public
     */
    function errorMessage($value)
    {
        static $errorMessages;
        if (!isset($errorMessages)) {
            $errorMessages = array(
                MDB2_ERROR                    => 'unknown error',
                MDB2_ERROR_ALREADY_EXISTS     => 'already exists',
                MDB2_ERROR_CANNOT_CREATE      => 'can not create',
                MDB2_ERROR_CANNOT_ALTER       => 'can not alter',
                MDB2_ERROR_CANNOT_REPLACE     => 'can not replace',
                MDB2_ERROR_CANNOT_DELETE      => 'can not delete',
                MDB2_ERROR_CANNOT_DROP        => 'can not drop',
                MDB2_ERROR_CONSTRAINT         => 'constraint violation',
                MDB2_ERROR_CONSTRAINT_NOT_NULL=> 'null value violates not-null constraint',
                MDB2_ERROR_DIVZERO            => 'division by zero',
                MDB2_ERROR_INVALID            => 'invalid',
                MDB2_ERROR_INVALID_DATE       => 'invalid date or time',
                MDB2_ERROR_INVALID_NUMBER     => 'invalid number',
                MDB2_ERROR_MISMATCH           => 'mismatch',
                MDB2_ERROR_NODBSELECTED       => 'no database selected',
                MDB2_ERROR_NOSUCHFIELD        => 'no such field',
                MDB2_ERROR_NOSUCHTABLE        => 'no such table',
                MDB2_ERROR_NOT_CAPABLE        => 'MDB2 backend not capable',
                MDB2_ERROR_NOT_FOUND          => 'not found',
                MDB2_ERROR_NOT_LOCKED         => 'not locked',
                MDB2_ERROR_SYNTAX             => 'syntax error',
                MDB2_ERROR_UNSUPPORTED        => 'not supported',
                MDB2_ERROR_VALUE_COUNT_ON_ROW => 'value count on row',
                MDB2_ERROR_INVALID_DSN        => 'invalid DSN',
                MDB2_ERROR_CONNECT_FAILED     => 'connect failed',
                MDB2_OK                       => 'no error',
                MDB2_ERROR_NEED_MORE_DATA     => 'insufficient data supplied',
                MDB2_ERROR_EXTENSION_NOT_FOUND=> 'extension not found',
                MDB2_ERROR_NOSUCHDB           => 'no such database',
                MDB2_ERROR_ACCESS_VIOLATION   => 'insufficient permissions',
                MDB2_ERROR_LOADMODULE         => 'error while including on demand module',
                MDB2_ERROR_TRUNCATED          => 'truncated',
                MDB2_ERROR_DEADLOCK           => 'deadlock detected',
            );
        }

        if (PEAR::isError($value)) {
            $value = $value->getCode();
        }

        return isset($errorMessages[$value]) ?
           $errorMessages[$value] : $errorMessages[MDB2_ERROR];
    }

    // }}}
    // {{{ parseDSN()

    /**
     * Parse a data source name.
     *
     * Additional keys can be added by appending a URI query string to the
     * end of the DSN.
     *
     * The format of the supplied DSN is in its fullest form:
     * <code>
     *  phptype(dbsyntax)://username:password@protocol+hostspec/database?option=8&another=true
     * </code>
     *
     * Most variations are allowed:
     * <code>
     *  phptype://username:password@protocol+hostspec:110//usr/db_file.db?mode=0644
     *  phptype://username:password@hostspec/database_name
     *  phptype://username:password@hostspec
     *  phptype://username@hostspec
     *  phptype://hostspec/database
     *  phptype://hostspec
     *  phptype(dbsyntax)
     *  phptype
     * </code>
     *
     * @param string $dsn Data Source Name to be parsed
     *
     * @return array an associative array with the following keys:
     *  + phptype:  Database backend used in PHP (mysql, odbc etc.)
     *  + dbsyntax: Database used with regards to SQL syntax etc.
     *  + protocol: Communication protocol to use (tcp, unix etc.)
     *  + hostspec: Host specification (hostname[:port])
     *  + database: Database to use on the DBMS server
     *  + username: User name for login
     *  + password: Password for login
     *
     * @author Tomas V.V.Cox <cox@idecnet.com>
     */
    function parseDSN($dsn)
    {
        $parsed = $GLOBALS['_MDB2_dsninfo_default'];

        if (is_array($dsn)) {
            $dsn = array_merge($parsed, $dsn);
            if (!$dsn['dbsyntax']) {
                $dsn['dbsyntax'] = $dsn['phptype'];
            }
            return $dsn;
        }

        // Find phptype and dbsyntax
        if (($pos = strpos($dsn, '://')) !== false) {
            $str = substr($dsn, 0, $pos);
            $dsn = substr($dsn, $pos + 3);
        } else {
            $str = $dsn;
            $dsn = null;
        }

        // Get phptype and dbsyntax
        // $str => phptype(dbsyntax)
        if (preg_match('|^(.+?)\((.*?)\)$|', $str, $arr)) {
            $parsed['phptype']  = $arr[1];
            $parsed['dbsyntax'] = !$arr[2] ? $arr[1] : $arr[2];
        } else {
            $parsed['phptype']  = $str;
            $parsed['dbsyntax'] = $str;
        }

        if (!count($dsn)) {
            return $parsed;
        }

        // Get (if found): username and password
        // $dsn => username:password@protocol+hostspec/database
        if (($at = strrpos($dsn,'@')) !== false) {
            $str = substr($dsn, 0, $at);
            $dsn = substr($dsn, $at + 1);
            if (($pos = strpos($str, ':')) !== false) {
                $parsed['username'] = rawurldecode(substr($str, 0, $pos));
                $parsed['password'] = rawurldecode(substr($str, $pos + 1));
            } else {
                $parsed['username'] = rawurldecode($str);
            }
        }

        // Find protocol and hostspec

        // $dsn => proto(proto_opts)/database
        if (preg_match('|^([^(]+)\((.*?)\)/?(.*?)$|', $dsn, $match)) {
            $proto       = $match[1];
            $proto_opts  = $match[2] ? $match[2] : false;
            $dsn         = $match[3];

        // $dsn => protocol+hostspec/database (old format)
        } else {
            if (strpos($dsn, '+') !== false) {
                list($proto, $dsn) = explode('+', $dsn, 2);
            }
            if (strpos($dsn, '/') !== false) {
                list($proto_opts, $dsn) = explode('/', $dsn, 2);
            } else {
                $proto_opts = $dsn;
                $dsn = null;
            }
        }

        // process the different protocol options
        $parsed['protocol'] = (!empty($proto)) ? $proto : 'tcp';
        $proto_opts = rawurldecode($proto_opts);
        if ($parsed['protocol'] == 'tcp') {
            if (strpos($proto_opts, ':') !== false) {
                list($parsed['hostspec'], $parsed['port']) = explode(':', $proto_opts);
            } else {
                $parsed['hostspec'] = $proto_opts;
            }
        } elseif ($parsed['protocol'] == 'unix') {
            $parsed['socket'] = $proto_opts;
        }

        // Get dabase if any
        // $dsn => database
        if ($dsn) {
            // /database
            if (($pos = strpos($dsn, '?')) === false) {
                $parsed['database'] = $dsn;
            // /database?param1=value1&param2=value2
            } else {
                $parsed['database'] = substr($dsn, 0, $pos);
                $dsn = substr($dsn, $pos + 1);
                if (strpos($dsn, '&') !== false) {
                    $opts = explode('&', $dsn);
                } else { // database?param1=value1
                    $opts = array($dsn);
                }
                foreach ($opts as $opt) {
                    list($key, $value) = explode('=', $opt);
                    if (!isset($parsed[$key])) {
                        // don't allow params overwrite
                        $parsed[$key] = rawurldecode($value);
                    }
                }
            }
        }

        return $parsed;
    }

    // }}}
    // {{{ fileExists()

    /**
     * checks if a file exists in the include path
     *
     * @access public
     * @param  string   filename
     *
     * @return boolean true success and false on error
     */
    function fileExists($file)
    {
        $dirs = explode(PATH_SEPARATOR, ini_get('include_path'));
        foreach ($dirs as $dir) {
            if (is_readable($dir . DIRECTORY_SEPARATOR . $file)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * MDB2_Error implements a class for reporting portable database error
 * messages.
 *
 * @package MDB2
 * @category Database
 * @author  Stig Bakken <ssb@fast.no>
 */
class MDB2_Error extends PEAR_Error
{
    // }}}
    // {{{ constructor

    /**
     * MDB2_Error constructor.
     *
     * @param mixed   $code      MDB error code, or string with error message.
     * @param integer $mode      what 'error mode' to operate in
     * @param integer $level     what error level to use for
     *                           $mode & PEAR_ERROR_TRIGGER
     * @param smixed  $debuginfo additional debug info, such as the last query
     */
    function MDB2_Error($code = MDB2_ERROR, $mode = PEAR_ERROR_RETURN,
              $level = E_USER_NOTICE, $debuginfo = null)
    {
        $this->PEAR_Error('MDB2 Error: '.MDB2::errorMessage($code), $code,
            $mode, $level, $debuginfo);
    }
}

/**
 * MDB2_Driver_Common: Base class that is extended by each MDB2 driver
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Common extends PEAR
{
    /**
     * index of the MDB2 object within the $GLOBALS['_MDB2_databases'] array
     * @var integer
     * @access public
     */
    var $db_index = 0;

    /**
     * DSN used for the next query
     * @var array
     * @access protected
     */
    var $dsn = array();

    /**
     * DSN that was used to create the current connection
     * @var array
     * @access protected
     */
    var $connected_dsn = array();

    /**
     * connection resource
     * @var mixed
     * @access protected
     */
    var $connection = 0;

    /**
     * if the current opened connection is a persistent connection
     * @var boolean
     * @access protected
     */
    var $opened_persistent;

    /**
     * the name of the database for the next query
     * @var string
     * @access protected
     */
    var $database_name = '';

    /**
     * the name of the database currrently selected
     * @var string
     * @access protected
     */
    var $connected_database_name = '';

    /**
     * list of all supported features of the given driver
     * @var array
     * @access public
     */
    var $supported = array(
        'sequences' => false,
        'indexes' => false,
        'affected_rows' => false,
        'summary_functions' => false,
        'order_by_text' => false,
        'transactions' => false,
        'current_id' => false,
        'limit_queries' => false,
        'LOBs' => false,
        'replace' => false,
        'sub_selects' => false,
        'auto_increment' => false,
        'primary_key' => false,
    );

    /**
     * $options['ssl'] -> determines if ssl should be used for connections
     * $options['field_case'] -> determines what case to force on field/table names
     * $options['disable_query'] -> determines if querys should be executed
     * $options['result_class'] -> class used for result sets
     * $options['buffered_result_class'] -> class used for buffered result sets
     * $options['result_wrap_class'] -> class used to wrap result sets into
     * $options['result_buffering'] -> boolean|integer should results be buffered or not?
     * $options['fetch_class'] -> class to use when fetch mode object is used
     * $options['persistent'] -> boolean persistent connection?
     * $options['debug'] -> integer numeric debug level
     * $options['debug_handler'] -> string function/meothd that captures debug messages
     * $options['lob_buffer_length'] -> integer LOB buffer length
     * $options['log_line_break'] -> string line-break format
     * $options['seqname_format'] -> string pattern for sequence name
     * $options['seqcol_name'] -> string sequence column name
     * $options['use_transactions'] -> boolean
     * $options['decimal_places'] -> integer
     * $options['portability'] -> portability constant
     * $options['modules'] -> short to long module name mapping for __call()
     * @var array
     * @access public
     */
    var $options = array(
            'ssl' => false,
            'field_case' => CASE_LOWER,
            'disable_query' => false,
            'result_class' => 'MDB2_Result_%s',
            'buffered_result_class' => 'MDB2_BufferedResult_%s',
            'result_wrap_class' => false,
            'result_buffering' => true,
            'fetch_class' => 'stdClass',
            'persistent' => false,
            'debug' => 0,
            'debug_handler' => 'MDB2_defaultDebugOutput',
            'lob_buffer_length' => 8192,
            'log_line_break' => "\n",
            'seqname_format' => '%s_seq',
            'seqcol_name' => 'sequence',
            'use_transactions' => false,
            'decimal_places' => 2,
            'portability' => MDB2_PORTABILITY_ALL,
            'modules' => array(
                'ex' => 'Extended',
                'dt' => 'Datatype',
                'mg' => 'Manager',
                'rv' => 'Reverse',
                'na' => 'Native',
            ),
        );

    /**
     * escape character
     * @var string
     * @access protected
     */
    var $escape_quotes = '';

    /**
     * warnings
     * @var array
     * @access protected
     */
    var $warnings = array();

    /**
     * string with the debugging information
     * @var string
     * @access public
     */
    var $debug_output = '';

    /**
     * determine if there is an open transaction
     * @var boolean
     * @access protected
     */
    var $in_transaction = false;

    /**
     * result offset used in the next query
     * @var integer
     * @access protected
     */
    var $row_offset = 0;

    /**
     * result limit used in the next query
     * @var integer
     * @access protected
     */
    var $row_limit = 0;

    /**
     * Database backend used in PHP (mysql, odbc etc.)
     * @var string
     * @access protected
     */
    var $phptype;

    /**
     * Database used with regards to SQL syntax etc.
     * @var string
     * @access protected
     */
    var $dbsyntax;

    /**
     * the last query sent to the driver
     * @var string
     * @access public
     */
    var $last_query;

    /**
     * the default fetchmode used
     * @var integer
     * @access protected
     */
    var $fetchmode = MDB2_FETCHMODE_ORDERED;

    /**
     * array of module instances
     * @var array
     * @access protected
     */
    var $modules = array();

    /**
     * determines of the PHP4 destructor emulation has been enabled yet
    * @var array
    * @access protected
    */
    var $destructor_registered = true;

    // }}}
    // {{{ constructor

    /**
     * Constructor
     */
    function __construct()
    {
        end($GLOBALS['_MDB2_databases']);
        $db_index = key($GLOBALS['_MDB2_databases']) + 1;
        $GLOBALS['_MDB2_databases'][$db_index] = &$this;
        $this->db_index = $db_index;
    }

    // }}}
    // {{{ MDB2_Driver_Common

    /**
     * PHP 4 Constructor
     */
    function MDB2_Driver_Common()
    {
        $this->destructor_registered = false;
        $this->__construct();
    }

    // }}}
    // {{{ Destructor

    /**
     *  Destructor
     */
    function __destruct()
    {
        if ($this->connection) {
            if ($this->opened_persistent) {
                if ($this->in_transaction) {
                    $this->rollback();
                }
            } else {
                $this->disconnect();
            }
        }
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal references so that the instance can be destroyed
     *
     * @return boolean true on success, false if result is invalid
     * @access public
     */
    function free()
    {
        unset($GLOBALS['_MDB2_databases'][$this->db_index]);
        unset($this->db_index);
        return MDB2_OK;
    }

    // }}}
    // {{{ __toString()

    /**
     * String conversation
     *
     * @return string
     * @access public
     */
    function __toString()
    {
        $info = get_class($this);
        $info.= ': (phptype = '.$this->phptype.', dbsyntax = '.$this->dbsyntax.')';
        if ($this->connection) {
            $info.= ' [connected]';
        }
        return $info;
    }

    // }}}
    // {{{ errorInfo()

    /**
     * This method is used to collect information about an error
     *
     * @param integer $error
     * @return array
     * @access public
     */
    function errorInfo($error = null)
    {
        return array($error, null, null);
    }

    // }}}
    // {{{ raiseError()

    /**
     * This method is used to communicate an error and invoke error
     * callbacks etc.  Basically a wrapper for PEAR::raiseError
     * without the message string.
     *
     * @param mixed    integer error code, or a PEAR error object (all
     *                 other parameters are ignored if this parameter is
     *                 an object
     *
     * @param int      error mode, see PEAR_Error docs
     *
     * @param mixed    If error mode is PEAR_ERROR_TRIGGER, this is the
     *                 error level (E_USER_NOTICE etc).  If error mode is
     *                 PEAR_ERROR_CALLBACK, this is the callback function,
     *                 either as a function name, or as an array of an
     *                 object and method name.  For other error modes this
     *                 parameter is ignored.
     *
     * @param string   Extra debug information.  Defaults to the last
     *                 query and native error code.
     *
     * @return object  a PEAR error object
     *
     * @see PEAR_Error
     */
    function &raiseError($code = null, $mode = null, $options = null, $userinfo = null)
    {
        // The error is yet a MDB2 error object
        if (PEAR::isError($code)) {
            // because we use the static PEAR::raiseError, our global
            // handler should be used if it is set
            if (is_null($mode) && !empty($this->_default_error_mode)) {
                $mode    = $this->_default_error_mode;
                $options = $this->_default_error_options;
            }
        } else {
            if (is_null($userinfo) && isset($this->connection)) {
                if (!empty($this->last_query)) {
                    $userinfo = "[Last query: {$this->last_query}]\n";
                }
                $native_errno = $native_msg = null;
                list($code, $native_errno, $native_msg) = $this->errorInfo($code);
                if (!is_null($native_errno)) {
                    $userinfo.= "[Native code: $native_errno]\n";
                }
                if (!is_null($native_msg)) {
                    $userinfo.= "[Native message: ". strip_tags($native_msg) ."]\n";
                }
            } else {
                $userinfo = "[Error message: $userinfo]\n";
            }
        }

        $err =& PEAR::raiseError(null, $code, $mode, $options, $userinfo, 'MDB2_Error', true);
        return $err;
    }

    // }}}
    // {{{ errorNative()

    /**
     * returns an errormessage, provides by the database
     *
     * @return mixed MDB2 Error Object or message
     * @access public
     */
    function errorNative()
    {
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED);
    }

    // }}}
    // {{{ resetWarnings()

    /**
     * reset the warning array
     *
     * @access public
     */
    function resetWarnings()
    {
        $this->warnings = array();
    }

    // }}}
    // {{{ getWarnings()

    /**
     * get all warnings in reverse order.
     * This means that the last warning is the first element in the array
     *
     * @return array with warnings
     * @access public
     * @see resetWarnings()
     */
    function getWarnings()
    {
        return array_reverse($this->warnings);
    }

    // }}}
    // {{{ setFetchMode()

    /**
     * Sets which fetch mode should be used by default on queries
     * on this connection
     *
     * @param integer $fetchmode    MDB2_FETCHMODE_ORDERED, MDB2_FETCHMODE_ASSOC
     *                               or MDB2_FETCHMODE_OBJECT
     * @param string $object_class  the class name of the object to be returned
     *                               by the fetch methods when the
     *                               MDB2_FETCHMODE_OBJECT mode is selected.
     *                               If no class is specified by default a cast
     *                               to object from the assoc array row will be
     *                               done.  There is also the posibility to use
     *                               and extend the 'MDB2_row' class.
     *
     * @see MDB2_FETCHMODE_ORDERED, MDB2_FETCHMODE_ASSOC, MDB2_FETCHMODE_OBJECT
     * @access public
     */
    function setFetchMode($fetchmode, $object_class = 'stdClass')
    {
        switch ($fetchmode) {
        case MDB2_FETCHMODE_OBJECT:
            $this->options['fetch_class'] = $object_class;
        case MDB2_FETCHMODE_ORDERED:
        case MDB2_FETCHMODE_ASSOC:
            $this->fetchmode = $fetchmode;
            break;
        default:
            return $this->raiseError('invalid fetchmode mode');
        }
    }

    // }}}
    // {{{ setOption()

    /**
     * set the option for the db class
     *
     * @param string $option option name
     * @param mixed $value value for the option
     * @return mixed MDB2_OK or MDB2 Error Object
     * @access public
     */
    function setOption($option, $value)
    {
        if (array_key_exists($option, $this->options)) {
            $this->options[$option] = $value;
            return MDB2_OK;
        }
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            "unknown option $option");
    }

    // }}}
    // {{{ getOption()

    /**
     * returns the value of an option
     *
     * @param string $option option name
     * @return mixed the option value or error object
     * @access public
     */
    function getOption($option)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            "unknown option $option");
    }

    // }}}
    // {{{ debug()

    /**
     * set a debug message
     *
     * @param string $message Message with information for the user.
     * @access public
     */
    function debug($message, $scope = '')
    {
        if ($this->options['debug'] && $this->options['debug_handler']) {
            call_user_func_array($this->options['debug_handler'], array(&$this, $scope, $message));
        }
    }

    // }}}
    // {{{ debugOutput()

    /**
     * output debug info
     *
     * @return string content of the debug_output class variable
     * @access public
     */
    function debugOutput()
    {
        return $this->debug_output;
    }

    // }}}
    // {{{ escape()

    /**
     * Quotes a string so it can be safely used in a query. It will quote
     * the text so it can safely be used within a query.
     *
     * @param string $text the input string to quote
     * @return string quoted string
     * @access public
     */
    function escape($text)
    {
        if ($this->escape_quotes !== "'") {
            $text = str_replace($this->escape_quotes, $this->escape_quotes.$this->escape_quotes, $text);
        }
        return str_replace("'", $this->escape_quotes . "'", $text);
    }

    // }}}
    // {{{ quoteIdentifier()

    /**
     * Quote a string so it can be safely used as a table or column name
     *
     * Delimiting style depends on which database driver is being used.
     *
     * NOTE: just because you CAN use delimited identifiers doesn't mean
     * you SHOULD use them.  In general, they end up causing way more
     * problems than they solve.
     *
     * Portability is broken by using the following characters inside
     * delimited identifiers:
     *   + backtick (<kbd>`</kbd>) -- due to MySQL
     *   + double quote (<kbd>"</kbd>) -- due to Oracle
     *   + brackets (<kbd>[</kbd> or <kbd>]</kbd>) -- due to Access
     *
     * Delimited identifiers are known to generally work correctly under
     * the following drivers:
     *   + mssql
     *   + mysql
     *   + mysqli
     *   + oci8
     *   + odbc(access)
     *   + odbc(db2)
     *   + pgsql
     *   + sqlite
     *   + sybase
     *
     * InterBase doesn't seem to be able to use delimited identifiers
     * via PHP 4.  They work fine under PHP 5.
     *
     * @param string $str  identifier name to be quoted
     *
     * @return string  quoted identifier string
     *
     * @access public
     */
    function quoteIdentifier($str)
    {
        return '"' . str_replace('"', '""', $str) . '"';
    }

    // }}}
    // {{{ _fixResultArrayValues()

    /**
     * Do all necessary conversions on result arrays to fix DBMS quirks
     *
     * @param array  $array  the array to be fixed (passed by reference)
     * @return void
     * @access protected
     */
    function _fixResultArrayValues(&$array, $mode)
    {
        switch ($mode) {
        case MDB2_PORTABILITY_RTRIM:
            foreach ($array as $key => $value) {
                if (is_string($value)) {
                    $array[$key] = rtrim($value);
                }
            }
        case MDB2_PORTABILITY_EMPTY_TO_NULL:
            foreach ($array as $key => $value) {
                if ($value === '') {
                    $array[$key] = null;
                }
            }
        case MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES:
            $tmp_array = array();
            foreach ($array as $key => $value) {
                $tmp_array[preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $key)] = $value;
            }
            $array = $tmp_array;
        case (MDB2_PORTABILITY_RTRIM + MDB2_PORTABILITY_EMPTY_TO_NULL):
            foreach ($array as $key => $value) {
                if ($value === '') {
                    $array[$key] = null;
                } elseif (is_string($value)) {
                    $array[$key] = rtrim($value);
                }
            }
        case (MDB2_PORTABILITY_RTRIM + MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES):
            $tmp_array = array();
            foreach ($array as $key => $value) {
                if (is_string($value)) {
                    $value = rtrim($value);
                }
                $tmp_array[preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $key)] = $value;
            }
            $array = $tmp_array;
        case (MDB2_PORTABILITY_EMPTY_TO_NULL + MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES):
            $tmp_array = array();
            foreach ($array as $key => $value) {
                if ($value === '') {
                    $value = null;
                }
                $tmp_array[preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $key)] = $value;
            }
            $array = $tmp_array;
        case (MDB2_PORTABILITY_RTRIM + MDB2_PORTABILITY_EMPTY_TO_NULL + MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES):
            $tmp_array = array();
            foreach ($array as $key => $value) {
                if ($value === '') {
                    $value = null;
                } elseif (is_string($value)) {
                    $value = rtrim($value);
                }
                $tmp_array[preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $key)] = $value;
            }
            $array = $tmp_array;
        }
    }

    // }}}
    // {{{ loadModule()

    /**
     * loads a module
     *
     * @param string $module name of the module that should be loaded
     *      (only used for error messages)
     * @param string $property name of the property into which the class will be loaded
     * @return object on success a reference to the given module is returned
     *                and on failure a PEAR error
     * @access public
     */
    function &loadModule($module, $property = null)
    {
        if (!$property) {
            $property = strtolower($module);
        }

        if (!isset($this->{$property})) {
            $version = false;
            $class_name = 'MDB2_'.$module;
            $file_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
            if (!class_exists($class_name) && !MDB2::fileExists($file_name)) {
                $class_name = 'MDB2_Driver_'.$module.'_'.$this->phptype;
                $file_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
                $version = true;
                if (!class_exists($class_name) && !MDB2::fileExists($file_name)) {
                    $err =& $this->raiseError(MDB2_ERROR_LOADMODULE, null, null,
                        'unable to find module: '.$file_name);
                    return $err;
                }
            }

            if (!class_exists($class_name) && !include_once($file_name)) {
                $err =& $this->raiseError(MDB2_ERROR_LOADMODULE, null, null,
                    'unable to load manager driver class: '.$file_name);
                return $err;
            }

            // load modul in a specific version
            if ($version) {
                if (method_exists($class_name, 'getClassName')) {
                    $class_name_new = call_user_func(array($class_name, 'getClassName'), $this->db_index);
                    if ($class_name != $class_name_new) {
                        $class_name = $class_name_new;
                        $file_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
                        if (!MDB2::fileExists($file_name)) {
                            $err =& $this->raiseError(MDB2_ERROR_LOADMODULE, null, null,
                                'unable to find module: '.$file_name);
                            return $err;
                        }
                        if (!include_once($file_name)) {
                            $err =& $this->raiseError(MDB2_ERROR_LOADMODULE, null, null,
                                'unable to load manager driver class: '.$file_name);
                            return $err;
                        }
                    }
                }
            }

            if (!class_exists($class_name)) {
                $err =& $this->raiseError(MDB2_ERROR_LOADMODULE, null, null,
                    'unable to load module: '.$module.' into property: '.$property);
                return $err;
            }
            $this->{$property} =& new $class_name($this->db_index);
            $this->modules[$module] =& $this->{$property};
            if ($version) {
                // this wil be used in the connect method to determine if the module
                // needs to be loaded with a different version if the server
                // version changed in between connects
                $this->loaded_version_modules[] = $property;
            }
        }

        return $this->{$property};
    }

    /**
     * Calls a module method using the __call magic method
     *
     * @param string Method name.
     * @param array Arguments.
     * @return mixed Returned value.
     */
    function __call($method, $params)
    {
        $module = null;
        if (preg_match('/^([a-z]+)([A-Z])(.*)$/', $method, $match)
            && isset($this->options['modules'][$match[1]])
        ) {
            $module = $this->options['modules'][$match[1]];
            $method = strtolower($match[2]).$match[3];
            if (!isset($this->modules[$module]) || !is_object($this->modules[$module])) {
                $result =& $this->loadModule($module);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        } else {
            foreach ($this->modules as $key => $foo) {
                if (is_object($this->modules[$key])
                    && method_exists($this->modules[$key], $method)
                ) {
                    $module = $key;
                    break;
                }
            }
        }
        if (!is_null($module)) {
            return call_user_func_array(array(&$this->modules[$module], $method), $params);
        }
        trigger_error(sprintf('Call to undefined function: %s::%s().', get_class($this), $method), E_USER_ERROR);
    }

    // }}}
    // {{{ beginTransaction()

    /**
     * Start a transaction.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function beginTransaction()
    {
        $this->debug('Starting transaction', 'beginTransaction');
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'beginTransaction: transactions are not supported');
    }

    // }}}
    // {{{ commit()

    /**
     * Commit the database changes done during a transaction that is in
     * progress. This function may only be called when auto-committing is
     * disabled, otherwise it will fail. Therefore, a new transaction is
     * implicitly started after committing the pending changes.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function commit()
    {
        $this->debug('commiting transaction', 'commit');
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'commit: commiting transactions is not supported');
    }

    // }}}
    // {{{ rollback()

    /**
     * Cancel any database changes done during a transaction that is in
     * progress. This function may only be called when auto-committing is
     * disabled, otherwise it will fail. Therefore, a new transaction is
     * implicitly started after canceling the pending changes.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function rollback()
    {
        $this->debug('rolling back transaction', 'rollback');
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'rollback: rolling back transactions is not supported');
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database.
     *
     * @return mixed true on success, false if not connected and error
     *                object on error
     * @access public
     */
    function disconnect()
    {
        return MDB2_OK;
    }

    // }}}
    // {{{ setDatabase()

    /**
     * Select a different database
     *
     * @param string $name name of the database that should be selected
     * @return string name of the database previously connected to
     * @access public
     */
    function setDatabase($name)
    {
        $previous_database_name = (isset($this->database_name)) ? $this->database_name : '';
        $this->database_name = $name;
        return $previous_database_name;
    }

    // }}}
    // {{{ getDatabase()

    /**
     * get the current database
     *
     * @return string name of the database
     * @access public
     */
    function getDatabase()
    {
        return $this->database_name;
    }

    // }}}
    // {{{ setDSN()

    /**
     * set the DSN
     *
     * @param mixed     $dsn    DSN string or array
     * @return MDB2_OK
     * @access public
     */
    function setDSN($dsn)
    {
        $dsn_default = $GLOBALS['_MDB2_dsninfo_default'];
        $dsn = MDB2::parseDSN($dsn);
        if (array_key_exists('database', $dsn)) {
            $this->database_name = $dsn['database'];
            unset($dsn['database']);
        }
        $this->dsn = array_merge($dsn_default, $dsn);
        return MDB2_OK;
    }

    // }}}
    // {{{ getDSN()

    /**
     * return the DSN as a string
     *
     * @param string     $type    format to return ("array", "string")
     * @param string     $hidepw  string to hide the password with
     * @return mixed DSN in the chosen type
     * @access public
     */
    function getDSN($type = 'string', $hidepw = false)
    {
        $dsn = array_merge($GLOBALS['_MDB2_dsninfo_default'], $this->dsn);
        $dsn['phptype'] = $this->phptype;
        $dsn['database'] = $this->database_name;
        if ($hidepw) {
            $dsn['password'] = $hidepw;
        }
        switch ($type) {
        // expand to include all possible options
        case 'string':
            $dsn = $dsn['phptype'].'://'.$dsn['username'].':'.
                $dsn['password'].'@'.$dsn['hostspec'].
                ($dsn['port'] ? (':'.$dsn['port']) : '').
                '/'.$dsn['database'];
            break;
        case 'array':
        default:
            break;
        }
        return $dsn;
    }

    // }}}
    // {{{ standaloneQuery()

   /**
     * execute a query as database administrator
     *
     * @param string $query the SQL query
     * @param mixed   $types  array that contains the types of the columns in
     *                        the result set
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function &standaloneQuery($query, $types = null)
    {
        $isManip = MDB2::isManip($query);
        $offset = $this->row_offset;
        $limit = $this->row_limit;
        $this->row_offset = $this->row_limit = 0;
        $query = $this->_modifyQuery($query, $isManip, $limit, $offset);

        $connected = $this->connect();
        if (PEAR::isError($connected)) {
            return $connected;
        }

        $result = $this->_doQuery($query, $isManip, $this->connection, false);
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($isManip) {
            return $result;
        }

        $result =& $this->_wrapResult($result, $types, true, false, $limit, $offset);
        return $result;
    }

    // }}}
    // {{{ _modifyQuery()

    /**
     * Changes a query string for various DBMS specific reasons
     *
     * @param string $query  query to modify
     * @return the new (modified) query
     * @access protected
     */
    function _modifyQuery($query)
    {
        return $query;
    }

    // }}}
    // {{{ _doQuery()

    /**
     * Execute a query
     * @param string $query  query
     * @param boolean $isManip  if the query is a manipulation query
     * @param resource $connection
     * @param string $database_name
     * @return result or error object
     * @access protected
     */
    function _doQuery($query, $isManip = false, $connection = null, $database_name = null)
    {
        $this->last_query = $query;
        $this->debug($query, 'query');
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'query: method not implemented');
    }

    // }}}
    // {{{ query()

    /**
     * Send a query to the database and return any results
     *
     * @param string $query the SQL query
     * @param mixed   $types  array that contains the types of the columns in
     *                        the result set
     * @param mixed $result_class string which specifies which result class to use
     * @param mixed $result_wrap_class string which specifies which class to wrap results in
     * @return mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function &query($query, $types = null, $result_class = true, $result_wrap_class = false)
    {
        $isManip = MDB2::isManip($query);
        $offset = $this->row_offset;
        $limit = $this->row_limit;
        $this->row_offset = $this->row_limit = 0;
        $query = $this->_modifyQuery($query, $isManip, $limit, $offset);

        $connected = $this->connect();
        if (PEAR::isError($connected)) {
            return $connected;
        }

        $result = $this->_doQuery($query, $isManip, $this->connection, $this->database_name);
        if (PEAR::isError($result)) {
            return $result;
        }

        // check for integers instead of manip since drivers may do some
        // custom checks not covered by MDB2::isManip()
        if (is_int($result)) {
            return $result;
        }

        $result =& $this->_wrapResult($result, $types, $result_class, $result_wrap_class, $limit, $offset);
        return $result;
    }

    // }}}
    // {{{ _wrapResult()

    /**
     * wrap a result set into the correct class
     *
     * @param ressource $result
     * @param mixed   $types  array that contains the types of the columns in
     *                        the result set
     * @param mixed $result_class string which specifies which result class to use
     * @param mixed $result_wrap_class string which specifies which class to wrap results in
     * @param string $limit number of rows to select
     * @param string $offset first row to select
     * @return mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access protected
     */
    function &_wrapResult($result, $types = array(), $result_class = true,
        $result_wrap_class = false, $limit = null, $offset = null)
    {
        if ($result_class === true) {
            $result_class = $this->options['result_buffering']
                ? $this->options['buffered_result_class'] : $this->options['result_class'];
        }

        if ($result_class) {
            $class_name = sprintf($result_class, $this->phptype);
            if (!class_exists($class_name)) {
                $err =& $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    '_wrapResult: result class does not exist '.$class_name);
                return $err;
            }
            $result =& new $class_name($this, $result, $limit, $offset);
            if (!MDB2::isResultCommon($result)) {
                $err =& $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    '_wrapResult: result class is not extended from MDB2_Result_Common');
                return $err;
            }
            if (!empty($types)) {
                $err = $result->setResultTypes($types);
                if (PEAR::isError($err)) {
                    $result->free();
                    return $err;
                }
            }
        }
        if ($result_wrap_class === true) {
            $result_wrap_class = $this->options['result_wrap_class'];
        }
        if ($result_wrap_class) {
            if (!class_exists($result_wrap_class)) {
                $err =& $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    '_wrapResult: result wrap class does not exist '.$result_wrap_class);
                return $err;
            }
            $result =& new $result_wrap_class($result, $this->fetchmode);
        }
        return $result;
    }

    // }}}
    // {{{ setLimit()

    /**
     * set the range of the next query
     *
     * @param string $limit number of rows to select
     * @param string $offset first row to select
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function setLimit($limit, $offset = null)
    {
        if (!$this->supports('limit_queries')) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'setLimit: limit is not supported by this driver');
        }
        $limit = (int)$limit;
        if ($limit < 0) {
            return $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                'setLimit: it was not specified a valid selected range row limit');
        }
        $this->row_limit = $limit;
        if (!is_null($offset)) {
            $offset = (int)$offset;
            if ($offset < 0) {
                return $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                    'setLimit: it was not specified a valid first selected range row');
            }
            $this->row_offset = $offset;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ subSelect()

    /**
     * simple subselect emulation: leaves the query untouched for all RDBMS
     * that support subselects
     *
     * @access public
     *
     * @param string $query the SQL query for the subselect that may only
     *                      return a column
     * @param string $type determines type of the field
     *
     * @return string the query
     */
    function subSelect($query, $type = false)
    {
        if ($this->supports('sub_selects') === true) {
            return $query;
        }

        if (!$this->supports('sub_selects')) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'subSelect: method not implemented');
        }

        $col = $this->queryCol($query, $type);
        if (PEAR::isError($col)) {
            return $col;
        }
        if (!is_array($col) || count($col) == 0) {
            return 'NULL';
        }
        if ($type) {
            $this->loadModule('Datatype');
            return $this->datatype->implodeArray($col, $type);
        }
        return implode(', ', $col);
    }

    // }}}
    // {{{ replace()

    /**
     * Execute a SQL REPLACE query. A REPLACE query is identical to a INSERT
     * query, except that if there is already a row in the table with the same
     * key field values, the REPLACE query just updates its values instead of
     * inserting a new row.
     *
     * The REPLACE type of query does not make part of the SQL standards. Since
     * pratically only MySQL implements it natively, this type of query is
     * emulated through this method for other DBMS using standard types of
     * queries inside a transaction to assure the atomicity of the operation.
     *
     * @param string $table name of the table on which the REPLACE query will
     *       be executed.
     * @param array $fields associative array that describes the fields and the
     *       values that will be inserted or updated in the specified table. The
     *       indexes of the array are the names of all the fields of the table.
     *       The values of the array are also associative arrays that describe
     *       the values and other properties of the table fields.
     *
     *       Here follows a list of field properties that need to be specified:
     *
     *       value
     *           Value to be assigned to the specified field. This value may be
     *           of specified in database independent type format as this
     *           function can perform the necessary datatype conversions.
     *
     *           Default: this property is required unless the Null property is
     *           set to 1.
     *
     *       type
     *           Name of the type of the field. Currently, all types Metabase
     *           are supported except for clob and blob.
     *
     *           Default: no type conversion
     *
     *       null
     *           Boolean property that indicates that the value for this field
     *           should be set to null.
     *
     *           The default value for fields missing in INSERT queries may be
     *           specified the definition of a table. Often, the default value
     *           is already null, but since the REPLACE may be emulated using
     *           an UPDATE query, make sure that all fields of the table are
     *           listed in this function argument array.
     *
     *           Default: 0
     *
     *       key
     *           Boolean property that indicates that this field should be
     *           handled as a primary key or at least as part of the compound
     *           unique index of the table that will determine the row that will
     *           updated if it exists or inserted a new row otherwise.
     *
     *           This function will fail if no key field is specified or if the
     *           value of a key field is set to null because fields that are
     *           part of unique index they may not be null.
     *
     *           Default: 0
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function replace($table, $fields)
    {
        if (!$this->supports('replace')) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'replace: replace query is not supported');
        }
        $count = count($fields);
        $condition = $values = array();
        for ($colnum = 0, reset($fields); $colnum < $count; next($fields), $colnum++) {
            $name = key($fields);
            if (isset($fields[$name]['null']) && $fields[$name]['null']) {
                $value = 'NULL';
            } else {
                if (isset($fields[$name]['type'])) {
                    $value = $this->quote($fields[$name]['value'], $fields[$name]['type']);
                } else {
                    $value = $fields[$name]['value'];
                }
            }
            $values[$name] = $value;
            if (isset($fields[$name]['key']) && $fields[$name]['key']) {
                if ($value === 'NULL') {
                    return $this->raiseError(MDB2_ERROR_CANNOT_REPLACE, null, null,
                        'replace: key value '.$name.' may not be NULL');
                }
                $condition[] = $name . '=' . $value;
            }
        }
        if (empty($condition)) {
            return $this->raiseError(MDB2_ERROR_CANNOT_REPLACE, null, null,
                'replace: not specified which fields are keys');
        }

        $result = null;
        $in_transaction = $this->in_transaction;
        if (!$in_transaction && PEAR::isError($result = $this->beginTransaction())) {
            return $result;
        }

        $condition = ' WHERE '.implode(' AND ', $condition);
        $query = "DELETE FROM $table$condition";
        $affected_rows = $result = $this->_doQuery($query, true);
        if (!PEAR::isError($result)) {
            $insert = implode(', ', array_keys($values));
            $values = implode(', ', $values);
            $query = "INSERT INTO $table ($insert) VALUES ($values)";
            $result = $this->_doQuery($query, true);
            if (!PEAR::isError($result)) {
                $affected_rows += $result;
            }
        }

        if (!$in_transaction) {
            if (PEAR::isError($result)) {
                $this->rollback();
            } else {
                $result = $this->commit();
            }
        }

        if (PEAR::isError($result)) {
            return $result;
        }

        return $affected_rows;
    }

    // }}}
    // {{{ prepare()

    /**
     * Prepares a query for multiple execution with execute().
     * With some database backends, this is emulated.
     * prepare() requires a generic query as string like
     * 'INSERT INTO numbers VALUES(?,?)' or
     * 'INSERT INTO numbers VALUES(:foo,:bar)'.
     * The ? and :[a-zA-Z] and  are placeholders which can be set using
     * bindParam() and the query can be send off using the execute() method.
     *
     * @param string $query the query to prepare
     * @param mixed   $types  array that contains the types of the placeholders
     * @param mixed   $result_types  array that contains the types of the columns in
     *                        the result set
     * @return mixed resource handle for the prepared query on success, a MDB2
     *        error on failure
     * @access public
     * @see bindParam, execute
     */
    function &prepare($query, $types = null, $result_types = null)
    {
        $this->debug($query, 'prepare');
        $positions = array();
        $placeholder_type_guess = $placeholder_type = null;
        $question = '?';
        $colon = ':';
        $position = 0;
        while ($position < strlen($query)) {
            $q_position = strpos($query, $question, $position);
            $c_position = strpos($query, $colon, $position);
            if ($q_position && $c_position) {
                $p_position = min($q_position, $c_position);
            } elseif ($q_position) {
                $p_position = $q_position;
            } elseif ($c_position) {
                $p_position = $c_position;
            } else {
                break;
            }
            if (is_null($placeholder_type)) {
                $placeholder_type_guess = $query[$p_position];
            }
            if (is_int($quote = strpos($query, "'", $position)) && $quote < $p_position) {
                if (!is_int($end_quote = strpos($query, "'", $quote + 1))) {
                    $err =& $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                        'prepare: query with an unterminated text string specified');
                    return $err;
                }
                switch ($this->escape_quotes) {
                case '':
                case "'":
                    $position = $end_quote + 1;
                    break;
                default:
                    if ($end_quote == $quote + 1) {
                        $position = $end_quote + 1;
                    } else {
                        if ($query[$end_quote-1] == $this->escape_quotes) {
                            $position = $end_quote;
                        } else {
                            $position = $end_quote + 1;
                        }
                    }
                    break;
                }
            } elseif ($query[$position] == $placeholder_type_guess) {
                if (is_null($placeholder_type)) {
                    $placeholder_type = $query[$p_position];
                    $question = $colon = $placeholder_type;
                }
                if ($placeholder_type == ':') {
                    $parameter = preg_replace('/^.{'.($position+1).'}([a-z0-9_]+).*$/si', '\\1', $query);
                    if ($parameter === '') {
                        $err =& $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                            'prepare: named parameter with an empty name');
                        return $err;
                    }
                    $positions[$parameter] = $p_position;
                    $query = substr_replace($query, '?', $position, strlen($parameter)+1);
                } else {
                    $positions[] = $p_position;
                }
                $position = $p_position + 1;
            } else {
                $position = $p_position;
            }
        }
        $class_name = 'MDB2_Statement_'.$this->phptype;
        $obj =& new $class_name($this, $positions, $query, $types, $result_types);
        return $obj;
    }

    // }}}
    // {{{ quote()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param string $type type to which the value should be converted to
     * @return string text string that represents the given argument value in
     *       a DBMS specific format.
     * @access public
     */
    function quote($value, $type = null, $quote = true)
    {
        $result = $this->loadModule('Datatype');
        if (PEAR::isError($result)) {
            return $result;
        }
        return $this->datatype->quote($value, $type, $quote);
    }

    // }}}
    // {{{ getDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare
     * of the given type
     *
     * @param string $type type to which the value should be converted to
     * @param string  $name   name the field to be declared.
     * @param string  $field  definition of the field
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     * @access public
     */
    function getDeclaration($type, $name, $field)
    {
        $result = $this->loadModule('Datatype');
        if (PEAR::isError($result)) {
            return $result;
        }
        return $this->datatype->getDeclaration($type, $name, $field);
    }

    // }}}
    // {{{ compareDefinition()

    /**
     * Obtain an array of changes that may need to applied
     *
     * @param array $current new definition
     * @param array  $previous old definition
     * @return array  containg all changes that will need to be applied
     * @access public
     */
    function compareDefinition($current, $previous)
    {
        $result = $this->loadModule('Datatype');
        if (PEAR::isError($result)) {
            return $result;
        }
        return $this->datatype->compareDefinition($current, $previous);
    }

    // }}}
    // {{{ supports()

    /**
     * Tell whether a DB implementation or its backend extension
     * supports a given feature.
     *
     * @param string $feature name of the feature (see the MDB2 class doc)
     * @return boolean|string   whether this DB implementation supports $feature
     *                          false means no, true means native, 'emulated' means emulated
     * @access public
     */
    function supports($feature)
    {
        if (array_key_exists($feature, $this->supported)) {
            return $this->supported[$feature];
        }
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            "unknown support feature $feature");
    }

    // }}}
    // {{{ getSequenceName()

    /**
     * adds sequence name formating to a sequence name
     *
     * @param string $sqn name of the sequence
     * @return string formatted sequence name
     * @access public
     */
    function getSequenceName($sqn)
    {
        return sprintf($this->options['seqname_format'],
            preg_replace('/[^a-z0-9_]/i', '_', $sqn));
    }

    // }}}
    // {{{ nextID()

    /**
     * returns the next free id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @param boolean $ondemand when true the seqence is
     *                           automatic created, if it
     *                           not exists
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function nextID($seq_name, $ondemand = true)
    {
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'nextID: method not implemented');
    }

    // }}}
    // {{{ lastInsertID()

    /**
     * returns the autoincrement ID if supported or $id
     *
     * @param mixed $id value as returned by getBeforeId()
     * @param string $table name of the table into which a new row was inserted
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function lastInsertID($table = null, $field = null)
    {
        $seq = $table.(empty($field) ? '' : '_'.$field);
        return $this->currID($seq);
    }

    // }}}
    // {{{ currID()

    /**
     * returns the current id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function currID($seq_name)
    {
        $this->warnings[] = 'database does not support getting current
            sequence value, the sequence value was incremented';
        return $this->nextID($seq_name);
    }

    // }}}
    // {{{ queryOne()

    /**
     * Execute the specified query, fetch the value from the first column of
     * the first row of the result set and then frees
     * the result set.
     *
     * @param string $query the SELECT query statement to be executed.
     * @param string $type optional argument that specifies the expected
     *       datatype of the result set field, so that an eventual conversion
     *       may be performed. The default datatype is text, meaning that no
     *       conversion is performed
     * @return mixed MDB2_OK or field value on success, a MDB2 error on failure
     * @access public
     */
    function queryOne($query, $type = null)
    {
        $result = $this->query($query, $type);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $one = $result->fetchOne();
        $result->free();
        return $one;
    }

    // }}}
    // {{{ queryRow()

    /**
     * Execute the specified query, fetch the values from the first
     * row of the result set into an array and then frees
     * the result set.
     *
     * @param string $query the SELECT query statement to be executed.
     * @param array $types optional array argument that specifies a list of
     *       expected datatypes of the result set columns, so that the eventual
     *       conversions may be performed. The default list of datatypes is
     *       empty, meaning that no conversion is performed.
     * @param int $fetchmode how the array data should be indexed
     * @return mixed MDB2_OK or data array on success, a MDB2 error on failure
     * @access public
     */
    function queryRow($query, $types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT)
    {
        $result = $this->query($query, $types);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $row = $result->fetchRow($fetchmode);
        $result->free();
        return $row;
    }

    // }}}
    // {{{ queryCol()

    /**
     * Execute the specified query, fetch the value from the first column of
     * each row of the result set into an array and then frees the result set.
     *
     * @param string $query the SELECT query statement to be executed.
     * @param string $type optional argument that specifies the expected
     *       datatype of the result set field, so that an eventual conversion
     *       may be performed. The default datatype is text, meaning that no
     *       conversion is performed
     * @param int $colnum the row number to fetch
     * @return mixed MDB2_OK or data array on success, a MDB2 error on failure
     * @access public
     */
    function queryCol($query, $type = null, $colnum = 0)
    {
        $result = $this->query($query, $type);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $col = $result->fetchCol($colnum);
        $result->free();
        return $col;
    }

    // }}}
    // {{{ queryAll()

    /**
     * Execute the specified query, fetch all the rows of the result set into
     * a two dimensional array and then frees the result set.
     *
     * @param string $query the SELECT query statement to be executed.
     * @param array $types optional array argument that specifies a list of
     *       expected datatypes of the result set columns, so that the eventual
     *       conversions may be performed. The default list of datatypes is
     *       empty, meaning that no conversion is performed.
     * @param int $fetchmode how the array data should be indexed
     * @param boolean $rekey if set to true, the $all will have the first
     *       column as its first dimension
     * @param boolean $force_array used only when the query returns exactly
     *       two columns. If true, the values of the returned array will be
     *       one-element arrays instead of scalars.
     * @param boolean $group if true, the values of the returned array is
     *       wrapped in another array.  If the same key value (in the first
     *       column) repeats itself, the values will be appended to this array
     *       instead of overwriting the existing values.
     * @return mixed MDB2_OK or data array on success, a MDB2 error on failure
     * @access public
     */
    function queryAll($query, $types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT,
        $rekey = false, $force_array = false, $group = false)
    {
        $result = $this->query($query, $types);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $all = $result->fetchAll($fetchmode, $rekey, $force_array, $group);
        $result->free();
        return $all;
    }
}

class MDB2_Result
{
}

class MDB2_Result_Common extends MDB2_Result
{
    var $db;
    var $result;
    var $rownum = -1;
    var $types;
    var $values;
    var $offset;
    var $offset_count = 0;
    var $limit;
    var $column_names;

    // {{{ constructor

    /**
     * Constructor
     */
    function __construct(&$db, &$result, $limit = 0, $offset = 0)
    {
        $this->db =& $db;
        $this->result =& $result;
        $this->offset = $offset;
        $this->limit = max(0, $limit - 1);
    }

    function MDB2_Result_Common(&$db, &$result, $limit = 0, $offset = 0)
    {
        $this->__construct($db, $result, $limit, $offset);
    }

    // }}}
    // {{{ setResultTypes()

    /**
     * Define the list of types to be associated with the columns of a given
     * result set.
     *
     * This function may be called before invoking fetchRow(), fetchOne(),
     * fetchCol() and fetchAll() so that the necessary data type
     * conversions are performed on the data to be retrieved by them. If this
     * function is not called, the type of all result set columns is assumed
     * to be text, thus leading to not perform any conversions.
     *
     * @param string $types array variable that lists the
     *       data types to be expected in the result set columns. If this array
     *       contains less types than the number of columns that are returned
     *       in the result set, the remaining columns are assumed to be of the
     *       type text. Currently, the types clob and blob are not fully
     *       supported.
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function setResultTypes($types)
    {
        $load = $this->db->loadModule('Datatype');
        if (PEAR::isError($load)) {
            return $load;
        }
        return $this->db->datatype->setResultTypes($this, $types);
    }

    // }}}
    // {{{ seek()

    /**
     * seek to a specific row in a result set
     *
     * @param int    $rownum    number of the row where the data can be found
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function seek($rownum = 0)
    {
        $target_rownum = $rownum - 1;
        if ($this->rownum > $target_rownum) {
            return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'seek: seeking to previous rows not implemented');
        }
        while ($this->rownum < $target_rownum) {
            $this->fetchRow();
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ fetchRow()

    /**
     * Fetch and return a row of data
     *
     * @param int       $fetchmode  how the array data should be indexed
     * @param int    $rownum    number of the row where the data can be found
     * @return int data array on success, a MDB2 error on failure
     * @access public
     */
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        $err =& $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'fetch: method not implemented');
        return $err;
    }

    // }}}
    // {{{ fetchOne()

    /**
     * fetch single column from the first row from a result set
     *
     * @param int $colnum the column number to fetch
     * @return string data on success, a MDB2 error on failure
     * @access public
     */
    function fetchOne($colnum = 0)
    {
        $fetchmode = is_numeric($colnum) ? MDB2_FETCHMODE_ORDERED : MDB2_FETCHMODE_ASSOC;
        $row = $this->fetchRow($fetchmode);
        if (!is_array($row) || PEAR::isError($row)) {
            return $row;
        }
        if (!array_key_exists($colnum, $row)) {
            return $this->db->raiseError(MDB2_ERROR_TRUNCATED);
        }
        return $row[$colnum];
    }

    // }}}
    // {{{ fetchCol()

    /**
     * Fetch and return a column of data (it uses current for that)
     *
     * @param int $colnum the column number to fetch
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function fetchCol($colnum = 0)
    {
        $column = array();
        $fetchmode = is_numeric($colnum) ? MDB2_FETCHMODE_ORDERED : MDB2_FETCHMODE_ASSOC;
        $row = $this->fetchRow($fetchmode);
        if (is_array($row)) {
            if (!array_key_exists($colnum, $row)) {
                return $this->db->raiseError(MDB2_ERROR_TRUNCATED);
            }
            do {
                $column[] = $row[$colnum];
            } while (is_array($row = $this->fetchRow($fetchmode)));
        }
        if (PEAR::isError($row)) {
            return $row;
        }
        return $column;
    }

    // }}}
    // {{{ fetchAll()

    /**
     * Fetch and return a column of data (it uses fetchRow for that)
     *
     * @param int    $fetchmode  the fetch mode to use:
     *                            + MDB2_FETCHMODE_ORDERED
     *                            + MDB2_FETCHMODE_ASSOC
     *                            + MDB2_FETCHMODE_ORDERED | MDB2_FETCHMODE_FLIPPED
     *                            + MDB2_FETCHMODE_ASSOC | MDB2_FETCHMODE_FLIPPED
     * @param boolean $rekey if set to true, the $all will have the first
     *       column as its first dimension
     * @param boolean $force_array used only when the query returns exactly
     *       two columns. If true, the values of the returned array will be
     *       one-element arrays instead of scalars.
     * @param boolean $group if true, the values of the returned array is
     *       wrapped in another array.  If the same key value (in the first
     *       column) repeats itself, the values will be appended to this array
     *       instead of overwriting the existing values.
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     * @see getAssoc()
     */
    function fetchAll($fetchmode = MDB2_FETCHMODE_DEFAULT, $rekey = false,
        $force_array = false, $group = false)
    {
        $all = array();
        $row = $this->fetchRow($fetchmode);
        if (PEAR::isError($row)) {
            return $row;
        } elseif (!$row) {
            return $all;
        }

        $shift_array = $rekey ? false : null;
        if (!is_null($shift_array)) {
            if (is_object($row)) {
                $colnum = count(get_object_vars($row));
            } else {
                $colnum = count($row);
            }
            if ($colnum < 2) {
                return $this->db->raiseError(MDB2_ERROR_TRUNCATED);
            }
            $shift_array = (!$force_array && $colnum == 2);
        }

        if ($rekey) {
            do {
                if (is_object($row)) {
                    $arr = get_object_vars($row);
                    $key = reset($arr);
                    unset($row->{$key});
                } else {
                    if ($fetchmode & MDB2_FETCHMODE_ASSOC) {
                        $key = reset($row);
                        unset($row[key($row)]);
                    } else {
                        $key = array_shift($row);
                    }
                    if ($shift_array) {
                        $row = array_shift($row);
                    }
                }
                if ($group) {
                    $all[$key][] = $row;
                } else {
                    $all[$key] = $row;
                }
            } while (($row = $this->fetchRow($fetchmode)));
        } elseif ($fetchmode & MDB2_FETCHMODE_FLIPPED) {
            do {
                foreach ($row as $key => $val) {
                    $all[$key][] = $val;
                }
            } while (($row = $this->fetchRow($fetchmode)));
        } else {
            do {
                $all[] = $row;
            } while (($row = $this->fetchRow($fetchmode)));
        }

        return $all;
    }

    // }}}
    // {{{ rowCount()

    /**
     * returns the actual row number that was last fetched (count from 0)
     * @return integer
     */
    function rowCount()
    {
        return $this->rownum + 1;
    }

    // }}}
    // {{{ numRows()

    /**
     * returns the number of rows in a result object
     *
     * @return mixed MDB2 Error Object or the number of rows
     * @access public
     */
    function numRows()
    {
        return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'numRows: method not implemented');
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal result pointer to the next available result
     *
     * @param a valid result resource
     * @return true on success or an error object on failure
     * @access public
     */
    function nextResult()
    {
        return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'nextResult: method not implemented');
    }

    // }}}
    // {{{ getColumnNames()

    /**
     * Retrieve the names of columns returned by the DBMS in a query result or
     * from the cache.
     *
     * @return mixed associative array variable
     *       that holds the names of columns. The indexes of the array are
     *       the column names mapped to lower case and the values are the
     *       respective numbers of the columns starting from 0. Some DBMS may
     *       not return any columns when the result set does not contain any
     *       rows.
     *      a MDB2 error on failure
     * @access public
     */
    function getColumnNames()
    {
        if (!isset($this->column_names)) {
            $result = $this->_getColumnNames();
            if (PEAR::isError($result)) {
                return $result;
            }
            $this->column_names = $result;
        }
        return $this->column_names;
    }

    // }}}
    // {{{ _getColumnNames()

    /**
     * Retrieve the names of columns returned by the DBMS in a query result.
     *
     * @return mixed associative array variable
     *       that holds the names of columns. The indexes of the array are
     *       the column names mapped to lower case and the values are the
     *       respective numbers of the columns starting from 0. Some DBMS may
     *       not return any columns when the result set does not contain any
     *       rows.
     *      a MDB2 error on failure
     * @access private
     */
    function _getColumnNames()
    {
        return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'getColumnNames: method not implemented');
    }

    // }}}
    // {{{ numCols()

    /**
     * Count the number of columns returned by the DBMS in a query result.
     *
     * @return mixed integer value with the number of columns, a MDB2 error
     *       on failure
     * @access public
     */
    function numCols()
    {
        return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'numCols: method not implemented');
    }

    // }}}
    // {{{ getResource()

    /**
     * return the resource associated with the result object
     *
     * @return resource
     * @access public
     */
    function getResource()
    {
        return $this->result;
    }


    // }}}
    // {{{ bindColumn()

    /**
     * Set bind variable to a column.
     *
     * @param int $column
     * @param mixed $value
     * @param string $type specifies the type of the field
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function bindColumn($column, &$value, $type = null)
    {
        if (!is_numeric($column)) {
            $column_names = $this->getColumnNames();
            if ($this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($this->db->options['field_case'] == CASE_LOWER) {
                    $column = strtolower($column);
                } else {
                    $column = strtoupper($column);
                }
            }
            $column = $column_names[$column];
        }
        $this->values[$column] =& $value;
        if (!is_null($type)) {
            $this->types[$column] = $type;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ _assignBindColumns()

    /**
     * Bind a variable to a value in the result row.
     *
     * @param array $row
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access private
     */
    function _assignBindColumns($row)
    {
        $row = array_values($row);
        foreach ($row as $column => $value) {
            if (array_key_exists($column, $this->values)) {
                $this->values[$column] = $value;
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal resources associated with result.
     *
     * @return boolean true on success, false if result is invalid
     * @access public
     */
    function free()
    {
        $this->result = null;
        return MDB2_OK;
    }
}

// }}}
// {{{ class MDB2_Row

/**
 * Pear MDB2 Row Object
 * @see MDB2_Driver_Common::setFetchMode()
 */
class MDB2_Row
{
    // {{{ constructor

    /**
     * constructor
     *
     * @param resource row data as array
     */
    function __construct(&$row)
    {
        foreach ($row as $key => $value) {
            $this->$key = &$row[$key];
        }
    }

    function MDB2_Row(&$row)
    {
        $this->__construct($row);
    }
}

class MDB2_Statement_Common
{
    var $db;
    var $statement;
    var $query;
    var $result_types;
    var $types;
    var $values;
    var $row_limit;
    var $row_offset;

    // {{{ constructor

    /**
     * Constructor
     */
    function __construct(&$db, &$statement, $query, $types, $result_types, $limit = null, $offset = null)
    {
        $this->db =& $db;
        $this->statement =& $statement;
        $this->query = $query;
        $this->types = (array)$types;
        $this->result_types = (array)$result_types;
        $this->row_limit = $limit;
        $this->row_offset = $offset;
    }

    function MDB2_Statement_Common(&$db, &$statement, $query, $types, $result_types, $limit = null, $offset = null)
    {
        $this->__construct($db, $statement, $query, $types, $result_types, $limit, $offset);
    }

    // }}}
    // {{{ bindParam()

    /**
     * Set the value of a parameter of a prepared query.
     *
     * @param int $parameter the order number of the parameter in the query
     *       statement. The order number of the first parameter is 1.
     * @param mixed $value value that is meant to be assigned to specified
     *       parameter. The type of the value depends on the $type argument.
     * @param string $type specifies the type of the field
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function bindParam($parameter, &$value, $type = null)
    {
        if (!is_numeric($parameter)) {
            $parameter = preg_replace('/^:(.*)$/', '\\1', $parameter);
        }
        $this->values[$parameter] =& $value;
        if (!is_null($type)) {
            $this->types[$parameter] = $type;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ bindParamArray()

    /**
     * Set the values of multiple a parameter of a prepared query in bulk.
     *
     * @param array $values array thats specifies all necessary infromation
     *       for bindParam() the array elements must use keys corresponding to
     *       the number of the position of the parameter.
     * @param array $types specifies the types of the fields
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     * @see bindParam()
     */
    function bindParamArray(&$values, $types = null)
    {
        $types = is_array($types) ? array_values($types) : array_fill(0, count($values), null);
        $parameters = array_keys($values);
        foreach ($parameters as $key => $parameter) {
            $this->bindParam($parameter, $values[$parameter], $types[$key]);
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ execute()

    /**
     * Execute a prepared query statement.
     *
     * @param array $values array thats specifies all necessary infromation
     *       for bindParam() the array elements must use keys corresponding to
     *       the number of the position of the parameter.
     * @param mixed $result_class string which specifies which result class to use
     * @param mixed $result_wrap_class string which specifies which class to wrap results in
     * @return mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function &execute($values = null, $result_class = true, $result_wrap_class = false)
    {
        if (!empty($values)) {
            $this->bindParamArray($values);
        }
        $result =& $this->_execute($result_class, $result_wrap_class);
        if (is_numeric($result)) {
            $this->rownum = $result - 1;
        }
        return $result;
    }

    // }}}
    // {{{ _execute()

    /**
     * Execute a prepared query statement helper method.
     *
     * @param mixed $result_class string which specifies which result class to use
     * @param mixed $result_wrap_class string which specifies which class to wrap results in
     * @return mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access private
     */
    function &_execute($result_class = true, $result_wrap_class = false)
    {
        $query = '';
        $last_position = $i = 0;
        foreach ($this->values as $parameter => $value) {
            $current_position = $this->statement[$parameter];
            $query.= substr($this->query, $last_position, $current_position - $last_position);
            if (!isset($value)) {
                $value_quoted = 'NULL';
            } else {
                $type = array_key_exists($parameter, $this->types) ? $this->types[$parameter] : null;
                $value_quoted = $this->db->quote($value, $type);
                if (PEAR::isError($value_quoted)) {
                    return $value_quoted;
                }
            }
            $query.= $value_quoted;
            $last_position = $current_position + 1;
            ++$i;
        }
        $query.= substr($this->query, $last_position);

        $this->db->row_offset = $this->row_offset;
        $this->db->row_limit = $this->row_limit;
        $result =& $this->db->query($query, $this->result_types, $result_class, $result_wrap_class);
        return $result;
    }

    // }}}
    // {{{ free()

    /**
     * Release resources allocated for the specified prepared query.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function free()
    {
        return MDB2_OK;
    }
}

class MDB2_Module_Common
{
    /**
     * contains the key to the global MDB2 instance array of the associated
     * MDB2 instance
     *
     * @var integer
     * @access protected
     */
    var $db_index;

    // {{{ constructor

    /**
     * Constructor
     */
    function __construct($db_index)
    {
        $this->db_index = $db_index;
    }

    function MDB2_Module_Common($db_index)
    {
        $this->__construct($db_index);
    }


    // }}}
    // {{{ getDBInstance()

    /**
     * get the instance of MDB2 associated with the module instance
     *
     * @return object MDB2 instance or a MDB2 error on failure
     * @access public
     */
    function &getDBInstance()
    {
        if (isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            $result =& $GLOBALS['_MDB2_databases'][$this->db_index];
        } else {
            $result =& MDB2::raiseError(MDB2_ERROR, null, null, 'could not find MDB2 instance');
        }
        return $result;
    }
}

// }}}
// {{{ MDB2_closeOpenTransactions()

/**
 * close any open transactions form persistant connections
 *
 * @return void
 * @access public
 */
function MDB2_closeOpenTransactions()
{
    reset($GLOBALS['_MDB2_databases']);
    while (next($GLOBALS['_MDB2_databases'])) {
        $key = key($GLOBALS['_MDB2_databases']);
        if ($GLOBALS['_MDB2_databases'][$key]->opened_persistent
            && $GLOBALS['_MDB2_databases'][$key]->in_transaction
        ) {
            $GLOBALS['_MDB2_databases'][$key]->rollback();
        }
    }
}

// }}}
// {{{ MDB2_defaultDebugOutput()

/**
 * default debug output handler
 *
 * @param object $db reference to an MDB2 database object
 * @param string $message message that should be appended to the debug
 *       variable
 * @return string the corresponding error message, of false
 * if the error code was unknown
 * @access public
 */
function MDB2_defaultDebugOutput(&$db, $scope, $message)
{
    $db->debug_output.= $scope.'('.$db->db_index.'): ';
    $db->debug_output.= $message.$db->getOption('log_line_break');
}

?>
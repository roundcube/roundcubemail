<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_mdb2.php                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   PEAR:DB wrapper class that implements PEAR MDB2 functions           |
 |   See http://pear.php.net/package/MDB2                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Lukas Kahwe Smith <smith@pooteeweet.org>                      |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Database independent query interface
 *
 * This is a wrapper for the PEAR::MDB2 class
 *
 * @package    Database
 * @author     David Saez Padros <david@ols.es>
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Lukas Kahwe Smith <smith@pooteeweet.org>
 * @version    1.16
 * @link       http://pear.php.net/package/MDB2
 */
class rcube_mdb2
{
    var $db_dsnw;               // DSN for write operations
    var $db_dsnr;               // DSN for read operations
    var $db_connected = false;  // Already connected ?
    var $db_mode = '';          // Connection mode
    var $db_handle = 0;         // Connection handle
    var $db_error = false;
    var $db_error_msg = '';

    private $debug_mode = false;
    private $a_query_results = array('dummy');
    private $last_res_id = 0;
    private $tables;


    /**
     * Object constructor
     *
     * @param  string  DSN for read/write operations
     * @param  string  Optional DSN for read only operations
     */
    function __construct($db_dsnw, $db_dsnr='', $pconn=false)
    {
        if ($db_dsnr == '')
            $db_dsnr = $db_dsnw;

        $this->db_dsnw = $db_dsnw;
        $this->db_dsnr = $db_dsnr;
        $this->db_pconn = $pconn;

        $dsn_array = MDB2::parseDSN($db_dsnw);
        $this->db_provider = $dsn_array['phptype'];
    }


    /**
     * Connect to specific database
     *
     * @param  string  DSN for DB connections
     * @return object  PEAR database handle
     * @access private
     */
    private function dsn_connect($dsn)
    {
        // Use persistent connections if available
        $db_options = array(
            'persistent'       => $this->db_pconn,
            'emulate_prepared' => $this->debug_mode,
            'debug'            => $this->debug_mode,
            'debug_handler'    => 'mdb2_debug_handler',
            'portability'      => MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_EMPTY_TO_NULL);

        if ($this->db_provider == 'pgsql') {
            $db_options['disable_smart_seqname'] = true;
            $db_options['seqname_format'] = '%s';
        }

        $dbh = MDB2::connect($dsn, $db_options);

        if (MDB2::isError($dbh)) {
            $this->db_error = true;
            $this->db_error_msg = $dbh->getMessage();

            raise_error(array('code' => 500, 'type' => 'db',
                'line' => __LINE__, 'file' => __FILE__,
                'message' => $dbh->getUserInfo()), true, false);
        }
        else if ($this->db_provider == 'sqlite') {
            $dsn_array = MDB2::parseDSN($dsn);
            if (!filesize($dsn_array['database']) && !empty($this->sqlite_initials))
                $this->_sqlite_create_database($dbh, $this->sqlite_initials);
        }
        else if ($this->db_provider!='mssql' && $this->db_provider!='sqlsrv')
            $dbh->setCharset('utf8');

        return $dbh;
    }


    /**
     * Connect to appropiate database depending on the operation
     *
     * @param  string  Connection mode (r|w)
     * @access public
     */
    function db_connect($mode)
    {
        $this->db_mode = $mode;

        // Already connected
        if ($this->db_connected) {
            // no replication, current connection is ok
            if ($this->db_dsnw == $this->db_dsnr)
                return;

            // connected to master, current connection is ok
            if ($this->db_mode == 'w')
                return;

            // Same mode, current connection is ok
            if ($this->db_mode == $mode)
                return;
        }

        $dsn = ($mode == 'r') ? $this->db_dsnr : $this->db_dsnw;

        $this->db_handle = $this->dsn_connect($dsn);
        $this->db_connected = true;
    }


    /**
     * Activate/deactivate debug mode
     *
     * @param boolean True if SQL queries should be logged
     * @access public
     */
    function set_debug($dbg = true)
    {
        $this->debug_mode = $dbg;
        if ($this->db_connected) {
            $this->db_handle->setOption('debug', $dbg);
            $this->db_handle->setOption('emulate_prepared', $dbg);
        }
    }


    /**
     * Getter for error state
     *
     * @param  boolean  True on error
     * @access public
     */
    function is_error()
    {
        return $this->db_error ? $this->db_error_msg : false;
    }


    /**
     * Connection state checker
     *
     * @param  boolean  True if in connected state
     * @access public
     */
    function is_connected()
    {
        return PEAR::isError($this->db_handle) ? false : $this->db_connected;
    }


    /**
     * Execute a SQL query
     *
     * @param  string  SQL query to execute
     * @param  mixed   Values to be inserted in query
     * @return number  Query handle identifier
     * @access public
     */
    function query()
    {
        if (!$this->is_connected())
            return null;

        $params = func_get_args();
        $query = array_shift($params);

        return $this->_query($query, 0, 0, $params);
    }


    /**
     * Execute a SQL query with limits
     *
     * @param  string  SQL query to execute
     * @param  number  Offset for LIMIT statement
     * @param  number  Number of rows for LIMIT statement
     * @param  mixed   Values to be inserted in query
     * @return number  Query handle identifier
     * @access public
     */
    function limitquery()
    {
        $params  = func_get_args();
        $query   = array_shift($params);
        $offset  = array_shift($params);
        $numrows = array_shift($params);

        return $this->_query($query, $offset, $numrows, $params);
    }


    /**
     * Execute a SQL query with limits
     *
     * @param  string  SQL query to execute
     * @param  number  Offset for LIMIT statement
     * @param  number  Number of rows for LIMIT statement
     * @param  array   Values to be inserted in query
     * @return number  Query handle identifier
     * @access private
     */
    private function _query($query, $offset, $numrows, $params)
    {
        // Read or write ?
        $mode = (strtolower(substr(trim($query),0,6)) == 'select') ? 'r' : 'w';

        $this->db_connect($mode);

        if ($this->db_provider == 'sqlite')
            $this->_sqlite_prepare();

        if ($numrows || $offset)
            $result = $this->db_handle->setLimit($numrows,$offset);

        if (empty($params))
            $result = $mode == 'r' ? $this->db_handle->query($query) : $this->db_handle->exec($query);
        else {
            $params = (array)$params;
            $q = $this->db_handle->prepare($query, null, $mode=='w' ? MDB2_PREPARE_MANIP : null);
            if ($this->db_handle->isError($q)) {
                $this->db_error = true;
                $this->db_error_msg = $q->userinfo;

                raise_error(array('code' => 500, 'type' => 'db',
                    'line' => __LINE__, 'file' => __FILE__,
                    'message' => $this->db_error_msg), true, true);
            }
            else {
                $result = $q->execute($params);
                $q->free();
            }
        }

        // add result, even if it's an error
        return $this->_add_result($result);
    }


    /**
     * Get number of rows for a SQL query
     * If no query handle is specified, the last query will be taken as reference
     *
     * @param  number  Optional query handle identifier
     * @return mixed   Number of rows or false on failure
     * @access public
     */
    function num_rows($res_id=null)
    {
        if (!$this->db_handle)
            return false;

        if ($result = $this->_get_result($res_id))
            return $result->numRows();
        else
            return false;
    }


    /**
     * Get number of affected rows for the last query
     *
     * @param  number  Optional query handle identifier
     * @return mixed   Number of rows or false on failure
     * @access public
     */
    function affected_rows($res_id = null)
    {
        if (!$this->db_handle)
            return false;

        return (int) $this->_get_result($res_id);
    }


    /**
     * Get last inserted record ID
     * For Postgres databases, a sequence name is required
     *
     * @param  string  Table name (to find the incremented sequence)
     * @return mixed   ID or false on failure
     * @access public
     */
    function insert_id($table = '')
    {
        if (!$this->db_handle || $this->db_mode == 'r')
            return false;

        if ($table) {
            if ($this->db_provider == 'pgsql')
                // find sequence name
                $table = get_sequence_name($table);
            else
                // resolve table name
                $table = get_table_name($table);
        }

        $id = $this->db_handle->lastInsertID($table);

        return $this->db_handle->isError($id) ? null : $id;
    }


    /**
     * Get an associative array for one row
     * If no query handle is specified, the last query will be taken as reference
     *
     * @param  number  Optional query handle identifier
     * @return mixed   Array with col values or false on failure
     * @access public
     */
    function fetch_assoc($res_id=null)
    {
        $result = $this->_get_result($res_id);
        return $this->_fetch_row($result, MDB2_FETCHMODE_ASSOC);
    }


    /**
     * Get an index array for one row
     * If no query handle is specified, the last query will be taken as reference
     *
     * @param  number  Optional query handle identifier
     * @return mixed   Array with col values or false on failure
     * @access public
     */
    function fetch_array($res_id=null)
    {
        $result = $this->_get_result($res_id);
        return $this->_fetch_row($result, MDB2_FETCHMODE_ORDERED);
    }


    /**
     * Get col values for a result row
     *
     * @param  object  Query result handle
     * @param  number  Fetch mode identifier
     * @return mixed   Array with col values or false on failure
     * @access private
     */
    private function _fetch_row($result, $mode)
    {
        if ($result === false || PEAR::isError($result) || !$this->is_connected())
            return false;

        return $result->fetchRow($mode);
    }


    /**
     * Wrapper for the SHOW TABLES command
     *
     * @return array List of all tables of the current database
     * @access public
     * @since 0.4-beta
     */
    function list_tables()
    {
        // get tables if not cached
        if (!$this->tables) {
            $this->db_handle->loadModule('Manager');
            if (!PEAR::isError($result = $this->db_handle->listTables()))
                $this->tables = $result;
            else
                $this->tables = array();
        }

        return $this->tables;
    }


    /**
     * Formats input so it can be safely used in a query
     *
     * @param  mixed   Value to quote
     * @param  string  Type of data
     * @return string  Quoted/converted string for use in query
     * @access public
     */
    function quote($input, $type = null)
    {
        // handle int directly for better performance
        if ($type == 'integer')
            return intval($input);

        // create DB handle if not available
        if (!$this->db_handle)
            $this->db_connect('r');

        return $this->db_handle->quote($input, $type);
    }


    /**
     * Quotes a string so it can be safely used as a table or column name
     *
     * @param  string  Value to quote
     * @return string  Quoted string for use in query
     * @deprecated     Replaced by rcube_MDB2::quote_identifier
     * @see            rcube_mdb2::quote_identifier
     * @access public
     */
    function quoteIdentifier($str)
    {
        return $this->quote_identifier($str);
    }


    /**
     * Quotes a string so it can be safely used as a table or column name
     *
     * @param  string  Value to quote
     * @return string  Quoted string for use in query
     * @access public
     */
    function quote_identifier($str)
    {
        if (!$this->db_handle)
            $this->db_connect('r');

        return $this->db_handle->quoteIdentifier($str);
    }


    /**
     * Escapes a string
     *
     * @param  string  The string to be escaped
     * @return string  The escaped string
     * @access public
     * @since  0.1.1
     */
    function escapeSimple($str)
    {
        if (!$this->db_handle)
            $this->db_connect('r');

        return $this->db_handle->escape($str);
    }


    /**
     * Return SQL function for current time and date
     *
     * @return string SQL function to use in query
     * @access public
     */
    function now()
    {
        switch($this->db_provider) {
            case 'mssql':
            case 'sqlsrv':
                return "getdate()";

            default:
                return "now()";
        }
    }


    /**
     * Return list of elements for use with SQL's IN clause
     *
     * @param  array  Input array
     * @param  string Type of data
     * @return string Comma-separated list of quoted values for use in query
     * @access public
     */
    function array2list($arr, $type = null)
    {
        if (!is_array($arr))
            return $this->quote($arr, $type);

        foreach ($arr as $idx => $item)
            $arr[$idx] = $this->quote($item, $type);

        return implode(',', $arr);
    }


    /**
     * Return SQL statement to convert a field value into a unix timestamp
     *
     * @param  string  Field name
     * @return string  SQL statement to use in query
     * @access public
     */
    function unixtimestamp($field)
    {
        switch($this->db_provider) {
            case 'pgsql':
                return "EXTRACT (EPOCH FROM $field)";

            case 'mssql':
            case 'sqlsrv':
                return "DATEDIFF(second, '19700101', $field) + DATEDIFF(second, GETDATE(), GETUTCDATE())";

            default:
                return "UNIX_TIMESTAMP($field)";
        }
    }


    /**
     * Return SQL statement to convert from a unix timestamp
     *
     * @param  string  Field name
     * @return string  SQL statement to use in query
     * @access public
     */
    function fromunixtime($timestamp)
    {
        switch($this->db_provider) {
            case 'mysqli':
            case 'mysql':
            case 'sqlite':
                return sprintf("FROM_UNIXTIME(%d)", $timestamp);

            default:
                return date("'Y-m-d H:i:s'", $timestamp);
        }
    }


    /**
     * Return SQL statement for case insensitive LIKE
     *
     * @param  string  Field name
     * @param  string  Search value
     * @return string  SQL statement to use in query
     * @access public
     */
    function ilike($column, $value)
    {
        // TODO: use MDB2's matchPattern() function
        switch($this->db_provider) {
            case 'pgsql':
                return $this->quote_identifier($column).' ILIKE '.$this->quote($value);
            default:
                return $this->quote_identifier($column).' LIKE '.$this->quote($value);
        }
    }


    /**
     * Encodes non-UTF-8 characters in string/array/object (recursive)
     *
     * @param  mixed  Data to fix
     * @return mixed  Properly UTF-8 encoded data
     * @access public
     */
    function encode($input)
    {
        if (is_object($input)) {
            foreach (get_object_vars($input) as $idx => $value)
                $input->$idx = $this->encode($value);
            return $input;
        }
        else if (is_array($input)) {
            foreach ($input as $idx => $value)
                $input[$idx] = $this->encode($value);
            return $input;	
        }

        return utf8_encode($input);
    }


    /**
     * Decodes encoded UTF-8 string/object/array (recursive)
     *
     * @param  mixed  Input data
     * @return mixed  Decoded data
     * @access public
     */
    function decode($input)
    {
        if (is_object($input)) {
            foreach (get_object_vars($input) as $idx => $value)
                $input->$idx = $this->decode($value);
            return $input;
        }
        else if (is_array($input)) {
            foreach ($input as $idx => $value)
                $input[$idx] = $this->decode($value);
            return $input;	
        }

        return utf8_decode($input);
    }


    /**
     * Adds a query result and returns a handle ID
     *
     * @param  object  Query handle
     * @return mixed   Handle ID
     * @access private
     */
    private function _add_result($res)
    {
        // sql error occured
        if (PEAR::isError($res)) {
            $this->db_error = true;
            $this->db_error_msg = $res->getMessage();
            raise_error(array('code' => 500, 'type' => 'db',
                'line' => __LINE__, 'file' => __FILE__,
                'message' => $res->getMessage() . " Query: " 
                . substr(preg_replace('/[\r\n]+\s*/', ' ', $res->userinfo), 0, 512)),
                true, false);
        }

        $res_id = sizeof($this->a_query_results);
        $this->last_res_id = $res_id;
        $this->a_query_results[$res_id] = $res;
        return $res_id;
    }


    /**
     * Resolves a given handle ID and returns the according query handle
     * If no ID is specified, the last resource handle will be returned
     *
     * @param  number  Handle ID
     * @return mixed   Resource handle or false on failure
     * @access private
     */
    private function _get_result($res_id = null)
    {
        if ($res_id == null)
            $res_id = $this->last_res_id;

        if (isset($this->a_query_results[$res_id]))
            if (!PEAR::isError($this->a_query_results[$res_id]))
                return $this->a_query_results[$res_id];

        return false;
    }


    /**
     * Create a sqlite database from a file
     *
     * @param  object  SQLite database handle
     * @param  string  File path to use for DB creation
     * @access private
     */
    private function _sqlite_create_database($dbh, $file_name)
    {
        if (empty($file_name) || !is_string($file_name))
            return;

        $data = file_get_contents($file_name);

        if (strlen($data))
            if (!sqlite_exec($dbh->connection, $data, $error) || MDB2::isError($dbh)) 
                raise_error(array('code' => 500, 'type' => 'db',
                    'line' => __LINE__, 'file' => __FILE__,
                    'message' => $error), true, false); 
    }


    /**
     * Add some proprietary database functions to the current SQLite handle
     * in order to make it MySQL compatible
     *
     * @access private
     */
    private function _sqlite_prepare()
    {
        include_once('include/rcube_sqlite.inc');

        // we emulate via callback some missing MySQL function
        sqlite_create_function($this->db_handle->connection,
            'from_unixtime', 'rcube_sqlite_from_unixtime');
        sqlite_create_function($this->db_handle->connection,
            'unix_timestamp', 'rcube_sqlite_unix_timestamp');
        sqlite_create_function($this->db_handle->connection,
            'now', 'rcube_sqlite_now');
        sqlite_create_function($this->db_handle->connection,
            'md5', 'rcube_sqlite_md5');
    }

}  // end class rcube_db


/* this is our own debug handler for the MDB2 connection */
function mdb2_debug_handler(&$db, $scope, $message, $context = array())
{
    if ($scope != 'prepare') {
        $debug_output = $scope . '('.$db->db_index.'): ' . $message;
        write_log('sql', $debug_output);
    }
}


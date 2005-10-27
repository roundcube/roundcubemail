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
// | Author: Lorenzo Alberton <l.alberton@quipo.it>                       |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * MDB2 FireBird/InterBase driver
 *
 * @package MDB2
 * @category Database
 * @author  Lorenzo Alberton <l.alberton@quipo.it>
 */
class MDB2_Driver_ibase extends MDB2_Driver_Common
{
    // {{{ properties
    var $escape_quotes = "'";

    var $transaction_id = 0;

    var $query_parameters = array();
    var $query_parameter_values = array();

    // }}}
    // {{{ constructor

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        $this->phptype  = 'ibase';
        $this->dbsyntax = 'ibase';

        $this->supported['sequences'] = true;
        $this->supported['indexes'] = true;
        $this->supported['affected_rows'] = function_exists('ibase_affected_rows');
        $this->supported['summary_functions'] = true;
        $this->supported['order_by_text'] = true;
        $this->supported['transactions'] = true;
        $this->supported['current_id'] = true;
        // maybe this needs different handling for ibase and firebird?
        $this->supported['limit_queries'] = 'emulated';
        $this->supported['LOBs'] = true;
        $this->supported['replace'] = false;
        $this->supported['sub_selects'] = true;
        $this->supported['auto_increment'] = true;
        $this->supported['primary_key'] = true;

        $this->options['database_path'] = '';
        $this->options['database_extension'] = '.gdb';
        $this->options['default_text_field_length'] = 4096;
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
        $native_msg = @ibase_errmsg();

        if (function_exists('ibase_errcode')) {
            $native_code = @ibase_errcode();
        } else {
            // memo for the interbase php module hackers: we need something similar
            // to mysql_errno() to retrieve error codes instead of this ugly hack
            if (preg_match('/^([^0-9\-]+)([0-9\-]+)\s+(.*)$/', $native_msg, $m)) {
                $native_code = (int)$m[2];
            } else {
                $native_code = null;
            }
        }
        if (is_null($error)) {
            $error = MDB2_ERROR;
            if ($native_code) {
                // try to interpret Interbase error code (that's why we need ibase_errno()
                // in the interbase module to return the real error code)
                switch ($native_code) {
                case -204:
                    if (isset($m[3]) && is_int(strpos($m[3], 'Table unknown'))) {
                        $errno = MDB2_ERROR_NOSUCHTABLE;
                    }
                break;
                default:
                    static $ecode_map;
                    if (empty($ecode_map)) {
                        $ecode_map = array(
                            -104 => MDB2_ERROR_SYNTAX,
                            -150 => MDB2_ERROR_ACCESS_VIOLATION,
                            -151 => MDB2_ERROR_ACCESS_VIOLATION,
                            -155 => MDB2_ERROR_NOSUCHTABLE,
                            -157 => MDB2_ERROR_NOSUCHFIELD,
                            -158 => MDB2_ERROR_VALUE_COUNT_ON_ROW,
                            -170 => MDB2_ERROR_MISMATCH,
                            -171 => MDB2_ERROR_MISMATCH,
                            -172 => MDB2_ERROR_INVALID,
                            // -204 =>  // Covers too many errors, need to use regex on msg
                            -205 => MDB2_ERROR_NOSUCHFIELD,
                            -206 => MDB2_ERROR_NOSUCHFIELD,
                            -208 => MDB2_ERROR_INVALID,
                            -219 => MDB2_ERROR_NOSUCHTABLE,
                            -297 => MDB2_ERROR_CONSTRAINT,
                            -303 => MDB2_ERROR_INVALID,
                            -413 => MDB2_ERROR_INVALID_NUMBER,
                            -530 => MDB2_ERROR_CONSTRAINT,
                            -551 => MDB2_ERROR_ACCESS_VIOLATION,
                            -552 => MDB2_ERROR_ACCESS_VIOLATION,
                            // -607 =>  // Covers too many errors, need to use regex on msg
                            -625 => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                            -803 => MDB2_ERROR_CONSTRAINT,
                            -804 => MDB2_ERROR_VALUE_COUNT_ON_ROW,
                            -904 => MDB2_ERROR_CONNECT_FAILED,
                            -922 => MDB2_ERROR_NOSUCHDB,
                            -923 => MDB2_ERROR_CONNECT_FAILED,
                            -924 => MDB2_ERROR_CONNECT_FAILED
                        );
                    }
                    if (isset($ecode_map[$native_code])) {
                        $error = $ecode_map[$native_code];
                    }
                    break;
                }
            } else {
                static $error_regexps;
                if (!isset($error_regexps)) {
                    $error_regexps = array(
                        '/generator .* is not defined/'
                            => MDB2_ERROR_SYNTAX,  // for compat. w ibase_errcode()
                        '/table.*(not exist|not found|unknown)/i'
                            => MDB2_ERROR_NOSUCHTABLE,
                        '/table .* already exists/i'
                            => MDB2_ERROR_ALREADY_EXISTS,
                        '/unsuccessful metadata update .* failed attempt to store duplicate value/i'
                            => MDB2_ERROR_ALREADY_EXISTS,
                        '/unsuccessful metadata update .* not found/i'
                            => MDB2_ERROR_NOT_FOUND,
                        '/validation error for column .* value "\*\*\* null/i'
                            => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                        '/violation of [\w ]+ constraint/i'
                            => MDB2_ERROR_CONSTRAINT,
                        '/conversion error from string/i'
                            => MDB2_ERROR_INVALID_NUMBER,
                        '/no permission for/i'
                            => MDB2_ERROR_ACCESS_VIOLATION,
                        '/arithmetic exception, numeric overflow, or string truncation/i'
                            => MDB2_ERROR_INVALID,
                    );
                }
                foreach ($error_regexps as $regexp => $code) {
                    if (preg_match($regexp, $native_msg, $m)) {
                        $error = $code;
                        break;
                    }
                }
            }
        }
        return array($error, $native_code, $native_msg);
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
        $this->debug('starting transaction', 'beginTransaction');
        if ($this->in_transaction) {
            return MDB2_OK;  //nothing to do
        }
        if (!$this->destructor_registered && $this->opened_persistent) {
            $this->destructor_registered = true;
            register_shutdown_function('MDB2_closeOpenTransactions');
        }
        $result = ibase_trans();
        if (!$result) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'beginTransaction: could not start a transaction');
        }
        $this->transaction_id = $result;
        $this->in_transaction = true;
        return MDB2_OK;
    }

    // }}}
    // {{{ commit()

    /**
     * Commit the database changes done during a transaction that is in
     * progress.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function commit()
    {
        $this->debug('commit transaction', 'commit');
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'commit: transaction changes are being auto committed');
        }
        if (!ibase_commit($this->transaction_id)) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'commit: could not commit a transaction');
        }
        $this->in_transaction = false;
        //$this->transaction_id = 0;
        return MDB2_OK;
    }

    // }}}
    // {{{ rollback()

    /**
     * Cancel any database changes done during a transaction that is in
     * progress.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function rollback()
    {
        $this->debug('rolling back transaction', 'rollback');
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'rollback: transactions can not be rolled back when changes are auto committed');
        }
        if ($this->transaction_id && !ibase_rollback($this->transaction_id)) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'rollback: Could not rollback a pending transaction: '.ibase_errmsg());
        }
        $this->in_transaction = false;
        $this->transaction_id = 0;
        return MDB2_OK;
    }

    // }}}
    // {{{ getDatabaseFile()

    /**
     * Builds the string with path+dbname+extension
     *
     * @return string full database path+file
     * @access protected
     */
    function _getDatabaseFile($database_name)
    {
        if ($database_name == '') {
            return $database_name;
        }
        return $this->options['database_path'].$database_name.$this->options['database_extension'];
    }

    // }}}
    // {{{ _doConnect()

    /**
     * Does the grunt work of connecting to the database
     *
     * @return mixed connection resource on success, MDB2 Error Object on failure
     * @access protected
     */
    function _doConnect($database_name, $persistent = false)
    {
        $user    = $this->dsn['username'];
        $pw      = $this->dsn['password'];
        $dbhost  = $this->dsn['hostspec'] ?
            ($this->dsn['hostspec'].':'.$database_name) : $database_name;

        $params = array();
        $params[] = $dbhost;
        $params[] = !empty($user) ? $user : null;
        $params[] = !empty($pw) ? $pw : null;
        $params[] = isset($this->dsn['charset']) ? $this->dsn['charset'] : null;
        $params[] = isset($this->dsn['buffers']) ? $this->dsn['buffers'] : null;
        $params[] = isset($this->dsn['dialect']) ? $this->dsn['dialect'] : null;
        $params[] = isset($this->dsn['role'])    ? $this->dsn['role'] : null;

        $connect_function = $persistent ? 'ibase_pconnect' : 'ibase_connect';

        $connection = @call_user_func_array($connect_function, $params);
        if ($connection <= 0) {
            return $this->raiseError(MDB2_ERROR_CONNECT_FAILED);
        }
        if (function_exists('ibase_timefmt')) {
            @ibase_timefmt("%Y-%m-%d %H:%M:%S", IBASE_TIMESTAMP);
            @ibase_timefmt("%Y-%m-%d", IBASE_DATE);
        } else {
            @ini_set("ibase.timestampformat", "%Y-%m-%d %H:%M:%S");
            //@ini_set("ibase.timeformat", "%H:%M:%S");
            @ini_set("ibase.dateformat", "%Y-%m-%d");
        }

        return $connection;
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database
     *
     * @return true on success, MDB2 Error Object on failure
     * @access public
     */
    function connect()
    {
        $database_file = $this->_getDatabaseFile($this->database_name);
        if (is_resource($this->connection)) {
            if (count(array_diff($this->connected_dsn, $this->dsn)) == 0
                && $this->connected_database_name == $database_file
                && $this->opened_persistent == $this->options['persistent']
            ) {
                return MDB2_OK;
            }
            $this->disconnect(false);
        }

        if (!PEAR::loadExtension('interbase')) {
            return $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'connect: extension '.$this->phptype.' is not compiled into PHP');
        }

        if (!empty($this->database_name)) {
            $connection = $this->_doConnect($database_file, $this->options['persistent']);
            if (PEAR::isError($connection)) {
                return $connection;
            }
            $this->connection =& $connection;
            $this->connected_dsn = $this->dsn;
            $this->connected_database_name = $database_file;
            $this->opened_persistent = $this->options['persistent'];
            $this->dbsyntax = $this->dsn['dbsyntax'] ? $this->dsn['dbsyntax'] : $this->phptype;
        }
        return MDB2_OK;
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
    function disconnect($force = true)
    {
        if (is_resource($this->connection)) {
            if (!$this->opened_persistent || $force) {
                @ibase_close($this->connection);
            }
            $this->connection = 0;
        }
        return MDB2_OK;
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
        if ($this->getOption('disable_query')) {
            if ($isManip) {
                return 0;
            }
            return null;
        }

        if (is_null($connection)) {
            if ($this->in_transaction) {
                $connection = $this->transaction_id;
            } else {
                $err = $this->connect();
                if (PEAR::isError($err)) {
                    return $err;
                }
                $connection = $this->connection;
            }
        }
        $result = ibase_query($connection, $query);

        if ($result === false) {
            return $this->raiseError();
        }

        if ($isManip) {
            //return $result;
            return (function_exists('ibase_affected_rows') ? ibase_affected_rows($connection) : 0);
        }
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
    function _modifyQuery($query, $isManip, $limit, $offset)
    {
        if ($limit > 0 && $this->dsn['dbsyntax'] == 'firebird') {
            $query = preg_replace('/^([\s(])*SELECT(?!\s*FIRST\s*\d+)/i',
                "SELECT FIRST $limit SKIP $offset", $query);
        }
        return $query;
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
                if ($placeholder_type_guess == '?') {
                    break;
                }
                if (is_null($placeholder_type)) {
                    $placeholder_type = $query[$p_position];
                    $question = $colon = $placeholder_type;
                }
                $name = preg_replace('/^.{'.($position+1).'}([a-z0-9_]+).*$/si', '\\1', $query);
                if ($name === '') {
                    $err =& $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                        'prepare: named parameter with an empty name');
                    return $err;
                }
                $query = substr_replace($query, '?', $position, strlen($name)+1);
                $position = $p_position + 1;
            } else {
                $position = $p_position;
            }
        }
        $connection = ($this->in_transaction ? $this->transaction_id : $this->connection);
        $statement = ibase_prepare($connection, $query);

        $class_name = 'MDB2_Statement_'.$this->phptype;
        $obj =& new $class_name($this, $statement, $query, $types, $result_types, $this->row_limit, $this->row_offset);
        return $obj;
    }

    // }}}
    // {{{ nextID()

    /**
     * returns the next free id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @param boolean $ondemand when true the seqence is
     *                          automatic created, if it
     *                          not exists
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function nextID($seq_name, $ondemand = true)
    {
        $sequence_name = $this->getSequenceName($seq_name);
        $query = 'SELECT GEN_ID('.strtoupper($sequence_name).', 1) as the_value FROM RDB$DATABASE';
        $this->expectError('*');
        $result = $this->queryOne($query, 'integer');
        $this->popExpect();
        if (PEAR::isError($result)) {
            if ($ondemand) {
                $this->loadModule('Manager');
                // Since we are creating the sequence on demand
                // we know the first id = 1 so initialize the
                // sequence at 2
                $result = $this->manager->createSequence($seq_name, 2);
                if (PEAR::isError($result)) {
                    return $this->raiseError(MDB2_ERROR, null, null,
                        'nextID: on demand sequence could not be created');
                } else {
                    // First ID of a newly created sequence is 1
                    // return 1;
                    // BUT generators are not always reset, so return the actual value
                    return $this->currID($seq_name);
                }
            }
        }
        return $result;
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
        $sequence_name = $this->getSequenceName($seq_name);
        $query = 'SELECT GEN_ID('.strtoupper($sequence_name).', 0) as the_value FROM RDB$DATABASE';
        $value = @$this->queryOne($query);
        if (PEAR::isError($value)) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'currID: Unable to select from ' . $seq_name) ;
        }
        if (!is_numeric($value)) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'currID: could not find value in sequence table');
        }
        return $value;
    }

    // }}}
}

class MDB2_Result_ibase extends MDB2_Result_Common
{
    // {{{ _skipLimitOffset()

    /**
     * Skip the first row of a result set.
     *
     * @param resource $result
     * @return mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access protected
     */
    function _skipLimitOffset()
    {
        if ($this->db->dsn['dbsyntax'] == 'firebird') {
            return true;
        }
        if ($this->limit) {
            if ($this->rownum > $this->limit) {
                return false;
            }
        }
        if ($this->offset) {
            while ($this->offset_count < $this->offset) {
                ++$this->offset_count;
                if (!is_array(@ibase_fetch_row($this->result))) {
                    $this->offset_count = $this->offset;
                    return false;
                }
            }
        }
        return true;
    }

    // }}}
    // {{{ fetchRow()

    /**
     * Fetch a row and insert the data into an existing array.
     *
     * @param int  $fetchmode how the array data should be indexed
     * @param int  $rownum    number of the row where the data can be found
     * @return int data array on success, a MDB2 error on failure
     * @access public
     */
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        if ($this->result === true) {
            //query successfully executed, but without results...
            $null = null;
            return $null;
        }
        if (!$this->_skipLimitOffset()) {
            $null = null;
            return $null;
        }
        if (!is_null($rownum)) {
            $seek = $this->seek($rownum);
            if (PEAR::isError($seek)) {
                return $seek;
            }
        }
        if ($fetchmode == MDB2_FETCHMODE_DEFAULT) {
            $fetchmode = $this->db->fetchmode;
        }
        if ($fetchmode & MDB2_FETCHMODE_ASSOC) {
            $row = @ibase_fetch_assoc($this->result);
            if (is_array($row)
                && $this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            ) {
                $row = array_change_key_case($row, $this->db->options['field_case']);
            }
        } else {
            $row = @ibase_fetch_row($this->result);
        }
        if (!$row) {
            if (is_null($this->result)) {
                $err =& $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'fetchRow: resultset has already been freed');
                return $err;
            }
            $null = null;
            return $null;
        }
        if (($mode = ($this->db->options['portability'] & MDB2_PORTABILITY_RTRIM)
            + ($this->db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL))
        ) {
            $this->db->_fixResultArrayValues($row, $mode);
        }
        if (!empty($this->values)) {
            $this->_assignBindColumns($row);
        }
        if (!empty($this->types)) {
            $row = $this->db->datatype->convertResultRow($this->types, $row);
        }
        if ($fetchmode === MDB2_FETCHMODE_OBJECT) {
            $object_class = $this->db->options['fetch_class'];
            if ($object_class == 'stdClass') {
                $row = (object) $row;
            } else {
                $row = &new $object_class($row);
            }
        }
        ++$this->rownum;
        return $row;
    }

    // }}}
    // {{{ _getColumnNames()

    /**
     * Retrieve the names of columns returned by the DBMS in a query result.
     *
     * @return mixed associative array variable
     *      that holds the names of columns. The indexes of the array are
     *      the column names mapped to lower case and the values are the
     *      respective numbers of the columns starting from 0. Some DBMS may
     *      not return any columns when the result set does not contain any
     *      rows.
     * @access private
     */
    function _getColumnNames()
    {
        $columns = array();
        $numcols = $this->numCols();
        if (PEAR::isError($numcols)) {
            return $numcols;
        }
        for ($column = 0; $column < $numcols; $column++) {
            $column_info = @ibase_field_info($this->result, $column);
            $columns[$column_info['alias']] = $column;
        }
        if ($this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $columns = array_change_key_case($columns, $this->db->options['field_case']);
        }
        return $columns;
    }

    // }}}
    // {{{ numCols()

    /**
     * Count the number of columns returned by the DBMS in a query result.
     *
     * @return mixed integer value with the number of columns, a MDB2 error
     *      on failure
     * @access public
     */
    function numCols()
    {
        if ($this->result === true) {
            //query successfully executed, but without results...
            return 0;
        }

        if (!is_resource($this->result)) {
            return $this->db->raiseError('numCols(): not a valid ibase resource');
        }
        $cols = @ibase_num_fields($this->result);
        if (is_null($cols)) {
            if (is_null($this->result)) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'numCols: resultset has already been freed');
            }
            return $this->db->raiseError();
        }
        return $cols;
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal resources associated with $result.
     *
     * @return boolean true on success, false if $result is invalid
     * @access public
     */
    function free()
    {
        if (is_resource($this->result)) {
            $free = @ibase_free_result($this->result);
            if (!$free) {
                if (is_null($this->result)) {
                    return MDB2_OK;
                }
                return $this->db->raiseError();
            }
        }
        $this->result = null;
        return MDB2_OK;
    }

    // }}}
}

class MDB2_BufferedResult_ibase extends MDB2_Result_ibase
{
    // {{{ class vars

    var $buffer;
    var $buffer_rownum = - 1;

    // }}}
    // {{{ _fillBuffer()

    /**
     * Fill the row buffer
     *
     * @param int $rownum   row number upto which the buffer should be filled
     *                      if the row number is null all rows are ready into the buffer
     * @return boolean true on success, false on failure
     * @access protected
     */
    function _fillBuffer($rownum = null)
    {
        if (isset($this->buffer) && is_array($this->buffer)) {
            if (is_null($rownum)) {
                if (!end($this->buffer)) {
                    return false;
                }
            } elseif (isset($this->buffer[$rownum])) {
                return (bool) $this->buffer[$rownum];
            }
        }

        if (!$this->_skipLimitOffset()) {
            return false;
        }

        $buffer = true;
        while ((is_null($rownum) || $this->buffer_rownum < $rownum)
            && (!$this->limit || $this->buffer_rownum < $this->limit)
            && ($buffer = @ibase_fetch_row($this->result))
        ) {
            ++$this->buffer_rownum;
            $this->buffer[$this->buffer_rownum] = $buffer;
        }

        if (!$buffer) {
            ++$this->buffer_rownum;
            $this->buffer[$this->buffer_rownum] = false;
            return false;
        } elseif ($this->limit && $this->buffer_rownum >= $this->limit) {
            ++$this->buffer_rownum;
            $this->buffer[$this->buffer_rownum] = false;
        }
        return true;
    }

    // }}}
    // {{{ fetchRow()

    /**
     * Fetch a row and insert the data into an existing array.
     *
     * @param int       $fetchmode  how the array data should be indexed
     * @param int    $rownum    number of the row where the data can be found
     * @return int data array on success, a MDB2 error on failure
     * @access public
     */
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        if ($this->result === true) {
            //query successfully executed, but without results...
            $null = null;
            return $null;
        }
        if (is_null($this->result)) {
            $err =& $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'fetchRow: resultset has already been freed');
            return $err;
        }
        if (!is_null($rownum)) {
            $seek = $this->seek($rownum);
            if (PEAR::isError($seek)) {
                return $seek;
            }
        }
        $target_rownum = $this->rownum + 1;
        if ($fetchmode == MDB2_FETCHMODE_DEFAULT) {
            $fetchmode = $this->db->fetchmode;
        }
        if (!$this->_fillBuffer($target_rownum)) {
            $null = null;
            return $null;
        }
        $row = $this->buffer[$target_rownum];
        if ($fetchmode & MDB2_FETCHMODE_ASSOC) {
            $column_names = $this->getColumnNames();
            foreach ($column_names as $name => $i) {
                $column_names[$name] = $row[$i];
            }
            $row = $column_names;
        }
        if (($mode = ($this->db->options['portability'] & MDB2_PORTABILITY_RTRIM)
            + ($this->db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL))
        ) {
            $this->db->_fixResultArrayValues($row, $mode);
        }
        if (!empty($this->values)) {
            $this->_assignBindColumns($row);
        }
        if (!empty($this->types)) {
            $row = $this->db->datatype->convertResultRow($this->types, $row);
        }
        if ($fetchmode === MDB2_FETCHMODE_OBJECT) {
            $object_class = $this->db->options['fetch_class'];
            if ($object_class == 'stdClass') {
                $row = (object) $row;
            } else {
                $row = &new $object_class($row);
            }
        }
        ++$this->rownum;
        return $row;
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
        if (is_null($this->result)) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'seek: resultset has already been freed');
        }
        $this->rownum = $rownum - 1;
        return MDB2_OK;
    }

    // }}}
    // {{{ valid()

    /**
     * check if the end of the result set has been reached
     *
     * @return mixed true or false on sucess, a MDB2 error on failure
     * @access public
     */
    function valid()
    {
        if (is_null($this->result)) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'valid: resultset has already been freed');
        }
        if ($this->_fillBuffer($this->rownum + 1)) {
            return true;
        }
        return false;
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
        if (is_null($this->result)) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'seek: resultset has already been freed');
        }
        $this->_fillBuffer();
        return $this->buffer_rownum;
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal resources associated with $result.
     *
     * @return boolean true on success, false if $result is invalid
     * @access public
     */
    function free()
    {
        $this->buffer = null;
        $this->buffer_rownum = null;
        $free = parent::free();
    }

    // }}}
}

class MDB2_Statement_ibase extends MDB2_Statement_Common
{
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
        $isManip = MDB2::isManip($this->query);
        $this->db->last_query = $this->query;
        $this->db->debug($this->query, 'execute');
        if ($this->db->getOption('disable_query')) {
            if ($isManip) {
                $return = 0;
                return $return;
            }
            $null = null;
            return $null;
        }

        $connected = $this->db->connect();
        if (PEAR::isError($connected)) {
            return $connected;
        }
        $connection = $this->db->in_transaction
            ? $this->db->transaction_id : $this->db->connection;

        $parameters = array(0 => $this->statement);
        $i = 0;
        foreach ($this->values as $parameter => $value) {
            $type = array_key_exists($parameter, $this->types) ? $this->types[$parameter] : null;
            $parameters[] = $this->db->quote($value, $type, false);
            ++$i;
        }

        $result = call_user_func_array('ibase_execute', $parameters);
        if ($result === false) {
            $err =& $this->db->raiseError();
            return $err;
        }

        if ($isManip) {
            $affected_rows = (function_exists('ibase_affected_rows') ? ibase_affected_rows($connection) : 0);
            return $affected_rows;
        }

        $result =& $this->db->_wrapResult($result, $this->types,
            $result_class, $result_wrap_class, $this->row_limit, $this->row_offset);
        return $result;
    }

    // }}}

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
        if (!@ibase_free_query($this->statement)) {
            return $this->db->raiseError();
        }
        return MDB2_OK;
    }
}
?>
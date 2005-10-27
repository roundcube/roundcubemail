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

// $Id$

/**
 * MDB2 OCI8 driver
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_oci8 extends MDB2_Driver_Common
{
    // {{{ properties
    var $escape_quotes = "'";

    var $uncommitedqueries = 0;

    // }}}
    // {{{ constructor

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        $this->phptype = 'oci8';
        $this->dbsyntax = 'oci8';

        $this->supported['sequences'] = true;
        $this->supported['indexes'] = true;
        $this->supported['summary_functions'] = true;
        $this->supported['order_by_text'] = true;
        $this->supported['current_id'] = true;
        $this->supported['affected_rows'] = true;
        $this->supported['transactions'] = true;
        $this->supported['limit_queries'] = 'emulated';
        $this->supported['LOBs'] = true;
        $this->supported['replace'] = 'emulated';
        $this->supported['sub_selects'] = true;
        $this->supported['auto_increment'] = false; // not implemented
        $this->supported['primary_key'] =  false; // not implemented

        $this->options['DBA_username'] = false;
        $this->options['DBA_password'] = false;
        $this->options['database_name_prefix'] = false;
        $this->options['emulate_database'] = true;
        $this->options['default_tablespace'] = false;
        $this->options['default_text_field_length'] = 4000;
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
        if (is_resource($error)) {
            $error_data = @OCIError($error);
            $error = null;
        } elseif ($this->connection) {
            $error_data = @OCIError($this->connection);
        } else {
            $error_data = @OCIError();
        }
        $native_code = $error_data['code'];
        $native_msg  = $error_data['message'];
        if (is_null($error)) {
            static $ecode_map;
            if (empty($ecode_map)) {
                $ecode_map = array(
                    1    => MDB2_ERROR_CONSTRAINT,
                    900  => MDB2_ERROR_SYNTAX,
                    904  => MDB2_ERROR_NOSUCHFIELD,
                    913  => MDB2_ERROR_VALUE_COUNT_ON_ROW,
                    921  => MDB2_ERROR_SYNTAX,
                    923  => MDB2_ERROR_SYNTAX,
                    942  => MDB2_ERROR_NOSUCHTABLE,
                    955  => MDB2_ERROR_ALREADY_EXISTS,
                    1400 => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                    1401 => MDB2_ERROR_INVALID,
                    1407 => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                    1418 => MDB2_ERROR_NOT_FOUND,
                    1476 => MDB2_ERROR_DIVZERO,
                    1722 => MDB2_ERROR_INVALID_NUMBER,
                    2289 => MDB2_ERROR_NOSUCHTABLE,
                    2291 => MDB2_ERROR_CONSTRAINT,
                    2292 => MDB2_ERROR_CONSTRAINT,
                    2449 => MDB2_ERROR_CONSTRAINT,
                );
            }
            if (isset($ecode_map[$native_code])) {
                $error = $ecode_map[$native_code];
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
        $this->in_transaction = true;
        ++$this->uncommitedqueries;
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
        if ($this->uncommitedqueries) {
            if (!@OCICommit($this->connection)) {
                return $this->raiseError();
            }
            $this->uncommitedqueries = 0;
        }
        $this->in_transaction = false;
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
        if ($this->uncommitedqueries) {
            if (!@OCIRollback($this->connection)) {
                return $this->raiseError();
            }
            $this->uncommitedqueries = 0;
        }
        $this->in_transaction = false;
        return MDB2_OK;
    }

    // }}}
    // {{{ _doConnect()

    /**
     * do the grunt work of the connect
     *
     * @return connection on success or MDB2 Error Object on failure
     * @access protected
     */
    function _doConnect($username, $password, $persistent = false)
    {
        if (!PEAR::loadExtension($this->phptype)) {
            return $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'extension '.$this->phptype.' is not compiled into PHP');
        }

        if (isset($this->dsn['hostspec'])) {
            $sid = $this->dsn['hostspec'];
        } else {
            $sid = getenv('ORACLE_SID');
        }
        if (empty($sid)) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'it was not specified a valid Oracle Service Identifier (SID)');
        }

        if (function_exists('oci_connect')) {
            if (isset($this->dsn['new_link'])
                && ($this->dsn['new_link'] == 'true' || $this->dsn['new_link'] === true)
            ) {
                $connect_function = 'oci_new_connect';
            } else {
                $connect_function = $persistent ? 'oci_pconnect' : 'oci_connect';
            }

            $charset = empty($this->dsn['charset']) ? null : $this->dsn['charset'];
            $connection = @$connect_function($username, $password, $sid, $charset);
            $error = @OCIError();
            if (isset($error['code']) && $error['code'] == 12541) {
                // Couldn't find TNS listener.  Try direct connection.
                $connection = @$connect_function($username, $password, null, $charset);
            }
        } else {
            $connect_function = $persistent ? 'OCIPLogon' : 'OCILogon';
            $connection = @$connect_function($username, $password, $sid);
        }

        if (!$connection) {
            return $this->raiseError(MDB2_ERROR_CONNECT_FAILED);
        }

        return $connection;
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database
     *
     * @return MDB2_OK on success, MDB2 Error Object on failure
     * @access public
     */
    function connect()
    {
        if ($this->database_name && $this->options['emulate_database']) {
             $this->dsn['username'] = $this->options['database_name_prefix'].$this->database_name;
        }
        if (is_resource($this->connection)) {
            if (count(array_diff($this->connected_dsn, $this->dsn)) == 0
                && $this->opened_persistent == $this->options['persistent']
            ) {
                return MDB2_OK;
            }
            $this->disconnect(false);
        }

        $connection = $this->_doConnect(
            $this->dsn['username'],
            $this->dsn['password'],
            $this->options['persistent']
        );
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $this->connection = $connection;
        $this->connected_dsn = $this->dsn;
        $this->opened_persistent = $this->options['persistent'];
        $this->dbsyntax = $this->dsn['dbsyntax'] ? $this->dsn['dbsyntax'] : $this->phptype;

        $query = "ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'";
        $err = $this->_doQuery($query, true);
        if (PEAR::isError($err)) {
            $this->disconnect(false);
            return $err;
        }

        $query = "ALTER SESSION SET NLS_NUMERIC_CHARACTERS='. '";
        $err = $this->_doQuery($query, true);
        if (PEAR::isError($err)) {
            $this->disconnect(false);
            return $err;
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
                if (function_exists('oci_close')) {
                    @oci_close($this->connection);
                } else {
                    @OCILogOff($this->connection);
                }
            }
            $this->connection = 0;
            $this->uncommitedqueries = 0;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ standaloneQuery()

   /**
     * execute a query as DBA
     *
     * @param string $query the SQL query
     * @param mixed   $types  array that contains the types of the columns in
     *                        the result set
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function &standaloneQuery($query, $types = null)
    {
        $connection = $this->_doConnect(
            $this->options['DBA_username'],
            $this->options['DBA_password'],
            $this->options['persistent']
        );
        if (PEAR::isError($connection)) {
            return $connection;
        }

        $isManip = MDB2::isManip($query);
        $offset = $this->row_offset;
        $limit = $this->row_limit;
        $this->row_offset = $this->row_limit = 0;
        $query = $this->_modifyQuery($query, $isManip, $limit, $offset);

        $result = $this->_doQuery($query, $isManip, $connection, false);

        @OCILogOff($connection);
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($isManip) {
            return $result;
        }
        $return =& $this->_wrapResult($result, $types, true, false, $limit, $offset);
        return $return;
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
        // "SELECT 2+2" must be "SELECT 2+2 FROM dual" in Oracle
        if (preg_match('/^\s*SELECT/i', $query)
            && !preg_match('/\sFROM\s/i', $query)
        ) {
            $query.= " FROM dual";
        }
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
        if ($this->getOption('disable_query')) {
            if ($isManip) {
                return 0;
            }
            return null;
        }

        if (is_null($connection)) {
            $err = $this->connect();
            if (PEAR::isError($err)) {
                return $err;
            }
            $connection = $this->connection;
        }

        $result = @OCIParse($connection, $query);
        if (!$result) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'Could not create statement');
        }

        $mode = $this->in_transaction ? OCI_DEFAULT : OCI_COMMIT_ON_SUCCESS;
        if (!@OCIExecute($result, $mode)) {
            return $this->raiseError($result);
        }

        if ($isManip) {
            return @OCIRowCount($result);
        }

        if (is_numeric($this->options['result_buffering'])) {
            @ocisetprefetch($result, $this->options['result_buffering']);
        }
        return $result;
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
        $query = $this->_modifyQuery($query);
        $contains_lobs = false;
        if (is_array($types)) {
            $columns = $variables = '';
            foreach ($types as $parameter => $type) {
                if ($type == 'clob' || $type == 'blob') {
                    $contains_lobs = true;
                    $columns.= ($columns ? ', ' : ' RETURNING ').$parameter;
                    $variables.= ($variables ? ', ' : ' INTO ').':'.$parameter;
                }
            }
        }
        $placeholder_type_guess = $placeholder_type = null;
        $question = '?';
        $colon = ':';
        $position = 0;
        $parameter = -1;
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
                if ($placeholder_type_guess == ':' && !$contains_lobs) {
                    break;
                }
                if (is_null($placeholder_type)) {
                    $placeholder_type = $query[$p_position];
                    $question = $colon = $placeholder_type;
                }
                if ($contains_lobs) {
                    if ($placeholder_type == ':') {
                        $parameter = preg_replace('/^.{'.($position+1).'}([a-z0-9_]+).*$/si', '\\1', $query);
                        if ($parameter === '') {
                            $err =& $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                                'prepare: named parameter with an empty name');
                            return $err;
                        }
                        $length = strlen($parameter)+1;
                    } else {
                        ++$parameter;
                        $length = strlen($parameter);
                    }
                    if (isset($types[$parameter])
                        && ($types[$parameter] == 'clob' || $types[$parameter] == 'blob')
                    ) {
                        $value = $this->quote(true, $types[$parameter]);
                        $query = substr_replace($query, $value, $p_position, $length);
                        $position = $p_position + strlen($value) - 1;
                    } elseif ($placeholder_type == '?') {
                        $query = substr_replace($query, ':'.$parameter, $p_position, 1);
                        $position = $p_position + strlen($parameter);
                    }
                } else {
                    $query = substr_replace($query, ':'.++$parameter, $p_position, 1);
                    $position = $p_position + strlen($parameter);
                }
            } else {
                $position = $p_position;
            }
        }
        if (is_array($types)) {
            $query.= $columns.$variables;
        }
        $statement = @OCIParse($this->connection, $query);
        if (!$statement) {
            $err =& $this->raiseError(MDB2_ERROR, null, null,
                'Could not create statement');
            return $err;
        }

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
     *                           automatic created, if it
     *                           not exists
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function nextID($seq_name, $ondemand = true)
    {
        $sequence_name = $this->getSequenceName($seq_name);
        $query = "SELECT $sequence_name.nextval FROM DUAL";
        $this->expectError(MDB2_ERROR_NOSUCHTABLE);
        $result = $this->queryOne($query, 'integer');
        $this->popExpect();
        if (PEAR::isError($result)) {
            if ($ondemand && $result->getCode() == MDB2_ERROR_NOSUCHTABLE) {
                $this->loadModule('Manager');
                $result = $this->manager->createSequence($seq_name, 1);
                if (PEAR::isError($result)) {
                    return $result;
                }
                return $this->nextId($seq_name, false);
            }
        }
        return $result;
    }

    // }}}
    // {{{ currId()

    /**
     * returns the current id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @return mixed MDB2_Error or id
     * @access public
     */
    function currId($seq_name)
    {
        $sequence_name = $this->getSequenceName($seq_name);
        return $this->queryOne("SELECT $sequence_name.currval FROM DUAL");
    }
}

class MDB2_Result_oci8 extends MDB2_Result_Common
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
        if ($this->limit) {
            if ($this->rownum > $this->limit) {
                return false;
            }
        }
        if ($this->offset) {
            while ($this->offset_count < $this->offset) {
                ++$this->offset_count;
                if (!@OCIFetchInto($this->result, $row, OCI_RETURN_NULLS)) {
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
     * @param int       $fetchmode  how the array data should be indexed
     * @param int    $rownum    number of the row where the data can be found
     * @return int data array on success, a MDB2 error on failure
     * @access public
     */
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
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
            @OCIFetchInto($this->result, $row, OCI_ASSOC+OCI_RETURN_NULLS);
            if (is_array($row)
                && $this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            ) {
                $row = array_change_key_case($row, $this->db->options['field_case']);
            }
        } else {
            @OCIFetchInto($this->result, $row, OCI_RETURN_NULLS);
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
        if ($this->db->options['portability'] & MDB2_PORTABILITY_RTRIM) {
            $this->db->_fixResultArrayValues($row, MDB2_PORTABILITY_RTRIM);
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
            $column_name = @OCIColumnName($this->result, $column + 1);
            $columns[$column_name] = $column;
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
        $cols = @OCINumCols($this->result);
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
        $free = @OCIFreeCursor($this->result);
        if (!$free) {
            if (is_null($this->result)) {
                return MDB2_OK;
            }
            return $this->db->raiseError();
        }
        $this->result = null;
        return MDB2_OK;
    }
}

class MDB2_BufferedResult_oci8 extends MDB2_Result_oci8
{
    var $buffer;
    var $buffer_rownum = - 1;

    // {{{ _fillBuffer()

    /**
     * Fill the row buffer
     *
     * @param int $rownum   row number upto which the buffer should be filled
                            if the row number is null all rows are ready into the buffer
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
                return (bool)$this->buffer[$rownum];
            }
        }

        if (!$this->_skipLimitOffset()) {
            return false;
        }

        $row = true;
        while ((is_null($rownum) || $this->buffer_rownum < $rownum)
            && (!$this->limit || $this->buffer_rownum < $this->limit)
            && ($row = @OCIFetchInto($this->result, $buffer, OCI_RETURN_NULLS))
        ) {
            ++$this->buffer_rownum;
            $this->buffer[$this->buffer_rownum] = $buffer;
        }

        if (!$row) {
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
        if (!empty($this->values)) {
            $this->_assignBindColumns($row);
        }
        if (!empty($this->types)) {
            $row = $this->db->datatype->convertResultRow($this->types, $row);
        }
        if ($this->db->options['portability'] & MDB2_PORTABILITY_RTRIM) {
            $this->db->_fixResultArrayValues($row, MDB2_PORTABILITY_RTRIM);
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
}

class MDB2_Statement_oci8 extends MDB2_Statement_Common
{
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

        $result = MDB2_OK;
        $lobs = $quoted_values = array();
        $i = 0;
        foreach ($this->values as $parameter => $value) {
            $type = array_key_exists($parameter, $this->types) ? $this->types[$parameter] : null;
            if ($type == 'clob' || $type == 'blob') {
                $lobs[$i]['file'] = false;
                if (is_resource($value)) {
                    $fp = $value;
                    $value = '';
                    while (!feof($fp)) {
                        $value.= fread($fp, 8192);
                    }
                } elseif (preg_match('/^(\w+:\/\/)(.*)$/', $value, $match)) {
                    $lobs[$i]['file'] = true;
                    if ($match[1] == 'file://') {
                        $value = $match[2];
                    }
                }
                $lobs[$i]['value'] = $value;
                $lobs[$i]['descriptor'] = @OCINewDescriptor($this->db->connection, OCI_D_LOB);
                if (!is_object($lobs[$i]['descriptor'])) {
                    $result = $this->db->raiseError();
                    break;
                }
                $lob_type = ($type == 'blob' ? OCI_B_BLOB : OCI_B_CLOB);
                if (!@OCIBindByName($this->statement, ':'.$parameter, $lobs[$i]['descriptor'], -1, $lob_type)) {
                    $result = $this->db->raiseError($this->statement);
                    break;
                }
            } else {
                $quoted_values[$i] = $this->db->quote($value, $type, false);
                if (PEAR::isError($quoted_values[$i])) {
                    return $quoted_values[$i];
                }
                if (!@OCIBindByName($this->statement, ':'.$parameter, $quoted_values[$i])) {
                    $result = $this->db->raiseError($this->statement);
                    break;
                }
            }
            ++$i;
        }

        if (!PEAR::isError($result)) {
            $mode = (!empty($lobs) || $this->db->in_transaction) ? OCI_DEFAULT : OCI_COMMIT_ON_SUCCESS;
            if (!@OCIExecute($this->statement, $mode)) {
                $err =& $this->db->raiseError($this->statement);
                return $err;
            }

            if (!empty($lobs)) {
                foreach ($lobs as $i => $stream) {
                    if (!is_null($stream['value']) && $stream['value'] !== '') {
                        if ($stream['file']) {
                            $result = $lobs[$i]['descriptor']->savefile($stream['value']);
                        } else {
                            $result = $lobs[$i]['descriptor']->save($stream['value']);
                        }
                        if (!$result) {
                            $result = $this->db->raiseError();
                            break;
                        }
                    }
                }

                if (!PEAR::isError($result)) {
                    if (!$this->db->in_transaction) {
                        if (!@OCICommit($this->db->connection)) {
                            $result = $this->db->raiseError();
                        }
                    } else {
                        ++$this->db->uncommitedqueries;
                    }
                }
            }
        }

        $keys = array_keys($lobs);
        foreach ($keys as $key) {
            $lobs[$key]['descriptor']->free();
        }

        if (PEAR::isError($result)) {
            return $result;
        }

        if ($isManip) {
            $return = @OCIRowCount($this->statement);
        } else {
            $return = $this->db->_wrapResult($this->statement, $isManip, $this->types,
                $result_class, $result_wrap_class, $this->row_offset, $this->row_limit);
        }
        return $return;
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
        if (!@OCIFreeStatement($this->statement)) {
            return $this->db->raiseError();
        }
        return MDB2_OK;
    }
}
?>
<?php
// vim: set et ts=4 sw=4 fdm=marker:
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2004 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith, Frank M. Kromann                       |
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
 * MDB2 FrontBase driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 * @author  Frank M. Kromann <frank@kromann.info>
 */
class MDB2_Driver_fbsql extends MDB2_Driver_Common
{
    // {{{ properties
    var $escape_quotes = "'";

    // }}}
    // {{{ constructor

    /**
    * Constructor
    */
    function __construct()
    {
        parent::__construct();

        $this->phptype = 'fbsql';
        $this->dbsyntax = 'fbsql';

        $this->supported['sequences'] = 'emulated';
        $this->supported['indexes'] = true;
        $this->supported['affected_rows'] = true;
        $this->supported['transactions'] = true;
        $this->supported['summary_functions'] = true;
        $this->supported['order_by_text'] = true;
        $this->supported['current_id'] = 'emulated';
        $this->supported['limit_queries'] = 'emulated';
        $this->supported['LOBs'] = true;
        $this->supported['replace'] ='emulated';
        $this->supported['sub_selects'] = true;
        $this->supported['auto_increment'] = false; // not implemented
        $this->supported['primary_key'] = false; // not implemented
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
       if ($this->connection) {
           $native_code = @fbsql_errno($this->connection);
           $native_msg  = @fbsql_error($this->connection);
       } else {
           $native_code = @fbsql_errno();
           $native_msg  = @fbsql_error();
       }
        if (is_null($error)) {
            static $ecode_map;
            if (empty($ecode_map)) {
                $ecode_map = array(
                     22 => MDB2_ERROR_SYNTAX,
                     85 => MDB2_ERROR_ALREADY_EXISTS,
                    108 => MDB2_ERROR_SYNTAX,
                    116 => MDB2_ERROR_NOSUCHTABLE,
                    124 => MDB2_ERROR_VALUE_COUNT_ON_ROW,
                    215 => MDB2_ERROR_NOSUCHFIELD,
                    217 => MDB2_ERROR_INVALID_NUMBER,
                    226 => MDB2_ERROR_NOSUCHFIELD,
                    231 => MDB2_ERROR_INVALID,
                    239 => MDB2_ERROR_TRUNCATED,
                    251 => MDB2_ERROR_SYNTAX,
                    266 => MDB2_ERROR_NOT_FOUND,
                    357 => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                    358 => MDB2_ERROR_CONSTRAINT,
                    360 => MDB2_ERROR_CONSTRAINT,
                    361 => MDB2_ERROR_CONSTRAINT,
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
        $result = $this->_doQuery('SET COMMIT FALSE;', true);
        if (PEAR::isError($result)) {
            return $result;
        }
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
                'commit: transaction changes are being auto commited');
        }
        $result = $this->_doQuery('COMMIT;', true);
        if (PEAR::isError($result)) {
            return $result;
        }
        $result = $this->_doQuery('SET COMMIT TRUE;', true);
        if (PEAR::isError($result)) {
            return $result;
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
        $result = $this->_doQuery('ROLLBACK;', true);
        if (PEAR::isError($result)) {
            return $result;
        }
        $result = $this->_doQuery('SET COMMIT TRUE;', true);
        if (PEAR::isError($result)) {
            return $result;
        }
        $this->in_transaction = false;
        return MDB2_OK;
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database
     *
     * @return true on success, MDB2 Error Object on failure
     **/
    function connect()
    {
        if (is_resource($this->connection)) {
            if (count(array_diff($this->connected_dsn, $this->dsn)) == 0
                && $this->opened_persistent == $this->options['persistent']
            ) {
                return MDB2_OK;
            }
            $this->disconnect(false);
        }

        if (!PEAR::loadExtension($this->phptype)) {
            return $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'connect: extension '.$this->phptype.' is not compiled into PHP');
        }

        $params = array(
            $this->dsn['hostspec'] ? $this->dsn['hostspec'] : 'localhost',
            $this->dsn['username'] ? $this->dsn['username'] : null,
            $this->dsn['password'] ? $this->dsn['password'] : null,
        );

        $connect_function = $this->options['persistent'] ? 'fbsql_pconnect' : 'fbsql_connect';

        @ini_set('track_errors', true);
        $php_errormsg = '';
        $connection = @call_user_func_array($connect_function, $params);
        @ini_restore('track_errors');
        if ($connection <= 0) {
            return $this->raiseError(MDB2_ERROR_CONNECT_FAILED);
        }

        $this->connection = $connection;
        $this->connected_dsn = $this->dsn;
        $this->connected_database_name = '';
        $this->opened_persistent = $this->options['persistent'];
        $this->dbsyntax = $this->dsn['dbsyntax'] ? $this->dsn['dbsyntax'] : $this->phptype;

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
                @fbsql_close($this->connection);
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
        if ($this->options['disable_query']) {
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
        if (is_null($database_name)) {
            $database_name = $this->database_name;
        }

        if ($database_name) {
            if ($database_name != $this->connected_database_name) {
                if (!@fbsql_select_db($database_name, $connection)) {
                    return $this->raiseError();
                }
                $this->connected_database_name = $database_name;
            }
        }

        $result = @fbsql_query($query, $connection);
        if (!$result) {
            return $this->raiseError();
        }

        if ($isManip) {
            return @fbsql_affected_rows($connection);
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
        if ($limit > 0) {
            if ($isManip) {
                return preg_replace('/^([\s(])*SELECT(?!\s*TOP\s*\()/i',
                    "\\1SELECT TOP($limit)", $query);
            } else {
                return preg_replace('/([\s(])*SELECT(?!\s*TOP\s*\()/i',
                    "\\1SELECT TOP($offset,$limit)", $query);
            }
        }
        // Add ; to the end of the query. This is required by FrontBase
        return $query.';';
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
     *
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function nextID($seq_name, $ondemand = true)
    {
        $sequence_name = $this->getSequenceName($seq_name);
        $query = "INSERT INTO $sequence_name (".$this->options['seqcol_name'].") VALUES (NULL);";
        $this->expectError(MDB2_ERROR_NOSUCHTABLE);
        $result = $this->_doQuery($query, true);
        $this->popExpect();
        if (PEAR::isError($result)) {
            if ($ondemand && $result->getCode() == MDB2_ERROR_NOSUCHTABLE) {
                $this->loadModule('Manager');
                // Since we are creating the sequence on demand
                // we know the first id = 1 so initialize the
                // sequence at 2
                $result = $this->manager->createSequence($seq_name, 2);
                if (PEAR::isError($result)) {
                    return $this->raiseError(MDB2_ERROR, null, null,
                        'nextID: on demand sequence '.$seq_name.' could not be created');
                } else {
                    // First ID of a newly created sequence is 1
                    return 1;
                }
            }
            return $result;
        }
        $value = $this->queryOne("SELECT UNIQUE FROM $sequence_name", 'integer');
        if (is_numeric($value)) {
            $query = "DELETE FROM $sequence_name WHERE ".$this->options['seqcol_name']." < $value;";
            $result = $this->_doQuery($query, true);
            if (PEAR::isError($result)) {
                $this->warnings[] = 'nextID: could not delete previous sequence table values from '.$seq_name;
            }
        }
        return $value;
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
        $value = @fbsql_insert_id($this->connection);
        if (!$value) {
            return $this->raiseError();
        }
        return $value;
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
        $query = "SELECT MAX(".$this->options['seqcol_name'].") FROM $sequence_name";
        return $this->queryOne($query, 'integer');
    }
}

class MDB2_Result_fbsql extends MDB2_Result_Common
{
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
            $row = @fbsql_fetch_assoc($this->result);
            if (is_array($row)
                && $this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            ) {
                $row = array_change_key_case($row, $this->db->options['field_case']);
            }
        } else {
           $row = @fbsql_fetch_row($this->result);
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
        if ($this->db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL) {
            $this->db->_fixResultArrayValues($row, MDB2_PORTABILITY_EMPTY_TO_NULL);
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
     * @return mixed                an associative array variable
     *                              that will hold the names of columns. The
     *                              indexes of the array are the column names
     *                              mapped to lower case and the values are the
     *                              respective numbers of the columns starting
     *                              from 0. Some DBMS may not return any
     *                              columns when the result set does not
     *                              contain any rows.
     *
     *                              a MDB2 error on failure
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
            $column_name = @fbsql_field_name($this->result, $column);
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
     *                       on failure
     * @access public
     */
    function numCols()
    {
        $cols = @fbsql_num_fields($this->result);
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
    // {{{ nextResult()

    /**
     * Move the internal result pointer to the next available result
     * Currently not supported
     *
     * @return true if a result is available otherwise return false
     * @access public
     */
    function nextResult()
    {
        if (is_null($this->result)) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'nextResult: resultset has already been freed');
        }
        return @fbsql_next_result($this->result);
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
        $free = @fbsql_free_result($this->result);
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

class MDB2_BufferedResult_fbsql extends MDB2_Result_fbsql
{
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
        if ($this->rownum != ($rownum - 1) && !@fbsql_data_seek($this->result, $rownum)) {
            if (is_null($this->result)) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'seek: resultset has already been freed');
            }
            return $this->db->raiseError(MDB2_ERROR_INVALID, null, null,
                'seek: tried to seek to an invalid row number ('.$rownum.')');
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
        $numrows = $this->numRows();
        if (PEAR::isError($numrows)) {
            return $numrows;
        }
        return $this->rownum < ($numrows - 1);
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
        $rows = @fbsql_num_rows($this->result);
        if (is_null($rows)) {
            if (is_null($this->result)) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'numRows: resultset has already been freed');
            }
            return $this->raiseError();
        }
        return $rows;
    }
}

class MDB2_Statement_fbsql extends MDB2_Statement_Common
{

}

?>
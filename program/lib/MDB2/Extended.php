<?php
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

/**
 * @package  MDB2
 * @category Database
 * @author   Lukas Smith <smith@pooteeweet.org>
 */

/**
 * Used by autoPrepare()
 */
define('MDB2_AUTOQUERY_INSERT', 1);
define('MDB2_AUTOQUERY_UPDATE', 2);

/**
 * MDB2_Extended: class which adds several high level methods to MDB2
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Extended extends MDB2_Module_Common
{
    // }}}
    // {{{ autoPrepare()

    /**
     * Make automaticaly an insert or update query and call prepare() with it
     *
     * @param string $table name of the table
     * @param array $table_fields ordered array containing the fields names
     * @param int $mode type of query to make (MDB2_AUTOQUERY_INSERT or MDB2_AUTOQUERY_UPDATE)
     * @param string $where in case of update queries, this string will be put after the sql WHERE statement
     * @return resource handle for the query
     * @param mixed   $types  array that contains the types of the placeholders
     * @param mixed   $result_types  array that contains the types of the columns in
     *                        the result set
     * @see buildManipSQL
     * @access public
     */
    function autoPrepare($table, $table_fields, $mode = MDB2_AUTOQUERY_INSERT,
        $where = false, $types = null, $result_types = null)
    {
        $query = $this->buildManipSQL($table, $table_fields, $mode, $where);
        if (PEAR::isError($query)) {
            return $query;
        }
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->prepare($query, $types, $result_types);
    }

    // {{{
    // }}} autoExecute()

    /**
     * Make automaticaly an insert or update query and call prepare() and execute() with it
     *
     * @param string $table name of the table
     * @param array $fields_values assoc ($key=>$value) where $key is a field name and $value its value
     * @param int $mode type of query to make (MDB2_AUTOQUERY_INSERT or MDB2_AUTOQUERY_UPDATE)
     * @param string $where in case of update queries, this string will be put after the sql WHERE statement
     * @param mixed   $types  array that contains the types of the placeholders
     * @param mixed   $result_types  array that contains the types of the columns in
     *                        the result set
     * @param mixed $result_class string which specifies which result class to use
     * @return mixed  a new MDB2_Result or a MDB2 Error Object when fail
     * @see buildManipSQL
     * @see autoPrepare
     * @access public
    */
    function &autoExecute($table, $fields_values, $mode = MDB2_AUTOQUERY_INSERT,
        $where = false, $types = null, $result_types = null, $result_class = true)
    {
        $stmt = $this->autoPrepare($table, array_keys($fields_values), $mode, $where, $types, $result_types);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }
        $params = array_values($fields_values);
        $stmt->bindParamArray($params);
        $result =& $stmt->execute($result_class);
        $stmt->free();
        return $result;
    }

    // {{{
    // }}} buildManipSQL()

    /**
     * Make automaticaly an sql query for prepare()
     *
     * Example : buildManipSQL('table_sql', array('field1', 'field2', 'field3'), MDB2_AUTOQUERY_INSERT)
     *           will return the string : INSERT INTO table_sql (field1,field2,field3) VALUES (?,?,?)
     * NB : - This belongs more to a SQL Builder class, but this is a simple facility
     *      - Be carefull ! If you don't give a $where param with an UPDATE query, all
     *        the records of the table will be updated !
     *
     * @param string $table name of the table
     * @param array $table_fields ordered array containing the fields names
     * @param int $mode type of query to make (MDB2_AUTOQUERY_INSERT or MDB2_AUTOQUERY_UPDATE)
     * @param string $where in case of update queries, this string will be put after the sql WHERE statement
     * @return string sql query for prepare()
     * @access public
     */
    function buildManipSQL($table, $table_fields, $mode, $where = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        if (count($table_fields) == 0) {
            return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA);
        }
        switch ($mode) {
        case MDB2_AUTOQUERY_INSERT:
            $cols = implode(', ', $table_fields);
            $values = '?'.str_repeat(', ?', count($table_fields)-1);
            return 'INSERT INTO '.$table.' ('.$cols.') VALUES ('.$values.')';
            break;
        case MDB2_AUTOQUERY_UPDATE:
            $set = implode(' = ?, ', $table_fields).' = ?';
            $sql = 'UPDATE '.$table.' SET '.$set;
            if ($where !== false) {
                $sql.= ' WHERE '.$where;
            }
            return $sql;
            break;
        }
        return $db->raiseError(MDB2_ERROR_SYNTAX);
    }

    // {{{
    // }}} limitQuery()

    /**
     * Generates a limited query
     *
     * @param string $query query
     * @param mixed   $types  array that contains the types of the columns in
     *                        the result set
     * @param integer $from the row to start to fetching
     * @param integer $count the numbers of rows to fetch
     * @param mixed $result_class string which specifies which result class to use
     * @return mixed a valid ressource pointer or a MDB2 Error Object
     * @access public
     */
    function &limitQuery($query, $types, $count, $from = 0, $result_class = true)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $result = $db->setLimit($count, $from);
        if (PEAR::isError($result)) {
            return $result;
        }
        $result =& $db->query($query, $types, $result_class);
        return $result;
    }

    // {{{
    // }}} getOne()

    /**
     * Fetch the first column of the first row of data returned from
     * a query.  Takes care of doing the query and freeing the results
     * when finished.
     *
     * @param string $query the SQL query
     * @param string $type string that contains the type of the column in the
     *       result set
     * @param array $params if supplied, prepare/execute will be used
     *       with this array as execute parameters
     * @param array $param_types array that contains the types of the values
     *       defined in $params
     * @param mixed $colnum which column to return
     * @return mixed MDB2_OK or data on success, a MDB2 error on failure
     * @access public
     */
    function getOne($query, $type = null, $params = array(),
        $param_types = null, $colnum = 0)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        settype($params, 'array');
        settype($type, 'array');
        if (count($params) == 0) {
            return $db->queryOne($query, $type, $colnum);
        }

        $stmt = $db->prepare($query, $param_types, $type);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }

        $stmt->bindParamArray($params);
        $result = $stmt->execute();
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $one = $result->fetchOne($colnum);
        $stmt->free();
        $result->free();
        return $one;
    }

    // }}}
    // {{{ getRow()

    /**
     * Fetch the first row of data returned from a query.  Takes care
     * of doing the query and freeing the results when finished.
     *
     * @param string $query the SQL query
     * @param array $types array that contains the types of the columns in
     *       the result set
     * @param array $params array if supplied, prepare/execute will be used
     *       with this array as execute parameters
     * @param array $param_types array that contains the types of the values
     *       defined in $params
     * @param integer $fetchmode the fetch mode to use
     * @return mixed MDB2_OK or data array on success, a MDB2 error on failure
     * @access public
     */
    function getRow($query, $types = null, $params = array(),
        $param_types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        settype($params, 'array');
        if (count($params) == 0) {
            return $db->queryRow($query, $types, $fetchmode);
        }

        $stmt = $db->prepare($query, $param_types, $types);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }

        $stmt->bindParamArray($params);
        $result = $stmt->execute();
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $row = $result->fetchRow($fetchmode);
        $stmt->free();
        $result->free();
        return $row;
    }

    // }}}
    // {{{ getCol()

    /**
     * Fetch a single column from a result set and return it as an
     * indexed array.
     *
     * @param string $query the SQL query
     * @param string $type string that contains the type of the column in the
     *       result set
     * @param array $params array if supplied, prepare/execute will be used
     *       with this array as execute parameters
     * @param array $param_types array that contains the types of the values
     *       defined in $params
     * @param mixed $colnum which column to return
     * @return mixed MDB2_OK or data array on success, a MDB2 error on failure
     * @access public
     */
    function getCol($query, $type = null, $params = array(),
        $param_types = null, $colnum = 0)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        settype($params, 'array');
        settype($type, 'array');
        if (count($params) == 0) {
            return $db->queryCol($query, $type, $colnum);
        }

        $stmt = $db->prepare($query, $param_types, $type);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }

        $stmt->bindParamArray($params);
        $result = $stmt->execute();
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $col = $result->fetchCol($colnum);
        $stmt->free();
        $result->free();
        return $col;
    }

    // }}}
    // {{{ getAll()

    /**
     * Fetch all the rows returned from a query.
     *
     * @param string $query the SQL query
     * @param array $types array that contains the types of the columns in
     *       the result set
     * @param array $params array if supplied, prepare/execute will be used
     *       with this array as execute parameters
     * @param array $param_types array that contains the types of the values
     *       defined in $params
     * @param integer $fetchmode the fetch mode to use
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
    function getAll($query, $types = null, $params = array(),
        $param_types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT,
        $rekey = false, $force_array = false, $group = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        settype($params, 'array');
        if (count($params) == 0) {
            return $db->queryAll($query, $types, $fetchmode, $rekey, $force_array, $group);
        }

        $stmt = $db->prepare($query, $param_types, $types);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }

        $stmt->bindParamArray($params);
        $result = $stmt->execute();
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $all = $result->fetchAll($fetchmode, $rekey, $force_array, $group);
        $stmt->free();
        $result->free();
        return $all;
    }

    // }}}
    // {{{ getAssoc()

    /**
     * Fetch the entire result set of a query and return it as an
     * associative array using the first column as the key.
     *
     * If the result set contains more than two columns, the value
     * will be an array of the values from column 2-n.  If the result
     * set contains only two columns, the returned value will be a
     * scalar with the value of the second column (unless forced to an
     * array with the $force_array parameter).  A MDB error code is
     * returned on errors.  If the result set contains fewer than two
     * columns, a MDB2_ERROR_TRUNCATED error is returned.
     *
     * For example, if the table 'mytable' contains:
     *
     *   ID      TEXT       DATE
     * --------------------------------
     *   1       'one'      944679408
     *   2       'two'      944679408
     *   3       'three'    944679408
     *
     * Then the call getAssoc('SELECT id,text FROM mytable') returns:
     *    array(
     *      '1' => 'one',
     *      '2' => 'two',
     *      '3' => 'three',
     *    )
     *
     * ...while the call getAssoc('SELECT id,text,date FROM mytable') returns:
     *    array(
     *      '1' => array('one', '944679408'),
     *      '2' => array('two', '944679408'),
     *      '3' => array('three', '944679408')
     *    )
     *
     * If the more than one row occurs with the same value in the
     * first column, the last row overwrites all previous ones by
     * default.  Use the $group parameter if you don't want to
     * overwrite like this.  Example:
     *
     * getAssoc('SELECT category,id,name FROM mytable', null, null
     *           MDB2_FETCHMODE_ASSOC, false, true) returns:
     *    array(
     *      '1' => array(array('id' => '4', 'name' => 'number four'),
     *                   array('id' => '6', 'name' => 'number six')
     *             ),
     *      '9' => array(array('id' => '4', 'name' => 'number four'),
     *                   array('id' => '6', 'name' => 'number six')
     *             )
     *    )
     *
     * Keep in mind that database functions in PHP usually return string
     * values for results regardless of the database's internal type.
     *
     * @param string $query the SQL query
     * @param array $types array that contains the types of the columns in
     *       the result set
     * @param array $params array if supplied, prepare/execute will be used
     *       with this array as execute parameters
     * @param array $param_types array that contains the types of the values
     *       defined in $params
     * @param boolean $force_array used only when the query returns
     * exactly two columns.  If TRUE, the values of the returned array
     * will be one-element arrays instead of scalars.
     * @param boolean $group if TRUE, the values of the returned array
     *       is wrapped in another array.  If the same key value (in the first
     *       column) repeats itself, the values will be appended to this array
     *       instead of overwriting the existing values.
     * @return array associative array with results from the query.
     * @access public
     */
    function getAssoc($query, $types = null, $params = array(), $param_types = null,
        $fetchmode = MDB2_FETCHMODE_DEFAULT, $force_array = false, $group = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        settype($params, 'array');
        if (count($params) == 0) {
            return $db->queryAll($query, $types, $fetchmode, true, $force_array, $group);
        }

        $stmt = $db->prepare($query, $param_types, $types);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }

        $stmt->bindParamArray($params);
        $result = $stmt->execute();
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $all = $result->fetchAll($fetchmode, true, $force_array, $group);
        $stmt->free();
        $result->free();
        return $all;
    }

    // }}}
    // {{{ executeMultiple()

    /**
     * This function does several execute() calls on the same statement handle.
     * $params must be an array indexed numerically from 0, one execute call is
     * done for every 'row' in the array.
     *
     * If an error occurs during execute(), executeMultiple() does not execute
     * the unfinished rows, but rather returns that error.
     *
     * @param resource $stmt query handle from prepare()
     * @param array $params numeric array containing the
     *        data to insert into the query
     * @return mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access public
     * @see prepare(), execute()
     */
    function executeMultiple(&$stmt, $params = null)
    {
        for ($i = 0, $j = count($params); $i < $j; $i++) {
            $stmt->bindParamArray($params[$i]);
            $result = $stmt->execute();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ getBeforeID()

    /**
     * returns the next free id of a sequence if the RDBMS
     * does not support auto increment
     *
     * @param string $table name of the table into which a new row was inserted
     * @param boolean $ondemand when true the seqence is
     *                          automatic created, if it
     *                          not exists
     *
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function getBeforeID($table, $field, $ondemand = true)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        if ($db->supports('auto_increment') !== true) {
            $seq = $table.(empty($field) ? '' : '_'.$field);
            $id = $db->nextID($seq, $ondemand);
            if (PEAR::isError($id)) {
                return $id;
            }
            return $db->quote($id, 'integer');
        }
        return 'NULL';
    }

    // }}}
    // {{{ getAfterID()

    /**
     * returns the autoincrement ID if supported or $id
     *
     * @param mixed $id value as returned by getBeforeId()
     * @param string $table name of the table into which a new row was inserted
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function getAfterID($id, $table, $field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        if ($db->supports('auto_increment') !== true) {
            return $id;
        }
        return $db->lastInsertID($table, $field);
    }

}
?>
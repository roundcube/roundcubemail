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
//

/**
 * Wrapper that makes MDB2 behave like PEAR DB
 * WARNING: this wrapper is broken and unmaintained
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */

require_once 'MDB2.php';

/*
 * The method mapErrorCode in each MDB2_dbtype implementation maps
 * native error codes to one of these.
 *
 * If you add an error code here, make sure you also add a textual
 * version of it in DB::errorMessage().
 */

define('DB_OK',                         MDB2_OK);
define('DB_ERROR',                      MDB2_ERROR);
define('DB_ERROR_SYNTAX',               MDB2_ERROR_SYNTAX);
define('DB_ERROR_CONSTRAINT',           MDB2_ERROR_CONSTRAINT);
define('DB_ERROR_NOT_FOUND',            MDB2_ERROR_NOT_FOUND);
define('DB_ERROR_ALREADY_EXISTS',       MDB2_ERROR_ALREADY_EXISTS);
define('DB_ERROR_UNSUPPORTED',          MDB2_ERROR_UNSUPPORTED);
define('DB_ERROR_MISMATCH',             MDB2_ERROR_MISMATCH);
define('DB_ERROR_INVALID',              MDB2_ERROR_INVALID);
define('DB_ERROR_NOT_CAPABLE',          MDB2_ERROR_NOT_CAPABLE);
define('DB_ERROR_TRUNCATED',            MDB2_ERROR_TRUNCATED);
define('DB_ERROR_INVALID_NUMBER',       MDB2_ERROR_INVALID_NUMBER);
define('DB_ERROR_INVALID_DATE',         MDB2_ERROR_INVALID_DATE);
define('DB_ERROR_DIVZERO',              MDB2_ERROR_DIVZERO);
define('DB_ERROR_NODBSELECTED',         MDB2_ERROR_NODBSELECTED);
define('DB_ERROR_CANNOT_CREATE',        MDB2_ERROR_CANNOT_CREATE);
define('DB_ERROR_CANNOT_DROP',          MDB2_ERROR_CANNOT_DROP);
define('DB_ERROR_NOSUCHTABLE',          MDB2_ERROR_NOSUCHTABLE);
define('DB_ERROR_NOSUCHFIELD',          MDB2_ERROR_NOSUCHFIELD);
define('DB_ERROR_NEED_MORE_DATA',       MDB2_ERROR_NEED_MORE_DATA);
define('DB_ERROR_NOT_LOCKED',           MDB2_ERROR_NOT_LOCKED);
define('DB_ERROR_VALUE_COUNT_ON_ROW',   MDB2_ERROR_VALUE_COUNT_ON_ROW);
define('DB_ERROR_INVALID_DSN',          MDB2_ERROR_INVALID_DSN);
define('DB_ERROR_CONNECT_FAILED',       MDB2_ERROR_CONNECT_FAILED);
define('DB_ERROR_EXTENSION_NOT_FOUND',  MDB2_ERROR_EXTENSION_NOT_FOUND);
define('DB_ERROR_ACCESS_VIOLATION',     MDB2_ERROR_ACCESS_VIOLATION);
define('DB_ERROR_NOSUCHDB',             MDB2_ERROR_NOSUCHDB);

define('DB_WARNING',           -1000);
define('DB_WARNING_READ_ONLY', -1001);

define('DB_PARAM_SCALAR',   1);
define('DB_PARAM_OPAQUE',   2);
define('DB_PARAM_MISC',     3);

define('DB_BINMODE_PASSTHRU',   1);
define('DB_BINMODE_RETURN',     2);
define('DB_BINMODE_CONVERT',    3);

define('DB_FETCHMODE_DEFAULT',      MDB2_FETCHMODE_DEFAULT);
define('DB_FETCHMODE_ORDERED',      MDB2_FETCHMODE_ORDERED);
define('DB_FETCHMODE_ASSOC',        MDB2_FETCHMODE_ASSOC);
define('DB_FETCHMODE_OBJECT',       MDB2_FETCHMODE_OBJECT);
define('DB_FETCHMODE_FLIPPED',      MDB2_FETCHMODE_FLIPPED);

define('DB_GETMODE_ORDERED', DB_FETCHMODE_ORDERED);
define('DB_GETMODE_ASSOC',   DB_FETCHMODE_ASSOC);
define('DB_GETMODE_FLIPPED', DB_FETCHMODE_FLIPPED);

require_once 'MDB2/Extended.php';
define('DB_AUTOQUERY_INSERT', MDB2_AUTOQUERY_INSERT);
define('DB_AUTOQUERY_UPDATE', MDB2_AUTOQUERY_UPDATE);

require_once 'MDB2/Driver/Reverse/Common.php';
define('DB_TABLEINFO_ORDER',        MDB2_TABLEINFO_ORDER);
define('DB_TABLEINFO_ORDERTABLE',   MDB2_TABLEINFO_ORDERTABLE);
define('DB_TABLEINFO_FULL',         MDB2_TABLEINFO_FULL);

define('DB_PORTABILITY_NONE', MDB2_PORTABILITY_NONE);
define('DB_PORTABILITY_LOWERCASE', MDB2_PORTABILITY_FIX_CASE);
define('DB_PORTABILITY_RTRIM', MDB2_PORTABILITY_RTRIM);
define('DB_PORTABILITY_DELETE_COUNT', MDB2_PORTABILITY_DELETE_COUNT);
define('DB_PORTABILITY_NUMROWS', MDB2_PORTABILITY_NUMROWS);
define('DB_PORTABILITY_ERRORS', MDB2_PORTABILITY_ERRORS);
define('DB_PORTABILITY_NULL_TO_EMPTY', MDB2_PORTABILITY_EMPTY_TO_NULL);
define('DB_PORTABILITY_ALL', MDB2_PORTABILITY_ALL);

/**
 * Wrapper that makes MDB2 behave like PEAR DB
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class DB
{
    function &factory($type)
    {
        $db =& MDB2::factory($type);
        if (PEAR::isError($db)) {
            return $db;
        }
        $obj =& new MDB2_PEARProxy($db);
        return $obj;
    }

    function &connect($dsn, $options = false)
    {
        if (!is_array($options) && $options) {
            $options = array('persistent' => true);
        }
        $db =& MDB2::connect($dsn, $options);
        if (PEAR::isError($db)) {
            return $db;
        }
        $obj =& new MDB2_PEARProxy($db);
        return $obj;
    }

    function apiVersion()
    {
        return 2;
    }

    function isError($value)
    {
        return PEAR::isError($value);
    }

    function isManip($query)
    {
        return MDB2::isManip($query);
    }

    function errorMessage($value)
    {
        return MDB2::errorMessage($value);
    }

    function parseDSN($dsn)
    {
        return MDB2::parseDSN($dsn);
    }

    function assertExtension($name)
    {
        if (!extension_loaded($name)) {
            $dlext = OS_WINDOWS ? '.dll' : '.so';
            @dl($name . $dlext);
        }
        return extension_loaded($name);
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
class DB_Error extends PEAR_Error
{
    function DB_Error($code = DB_ERROR, $mode = PEAR_ERROR_RETURN,
              $level = E_USER_NOTICE, $debuginfo = null)
    {
        if (is_int($code)) {
            $this->PEAR_Error('DB Error: ' . DB::errorMessage($code), $code, $mode, $level, $debuginfo);
        } else {
            $this->PEAR_Error("DB Error: $code", DB_ERROR, $mode, $level, $debuginfo);
        }
    }
}

/**
 * Wrapper that makes MDB2 behave like PEAR DB
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class DB_result extends MDB2_Result_Common
{
    var $result;
    var $row_counter = null;

    var $limit_from  = null;

    var $limit_count = null;

    function DB_result($result)
    {
        $this->result = $result;
    }

    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        $arr = $this->result->fetchRow($fetchmode, $rownum);
        if ($this->result->mdb->options['portability'] & DB_PORTABILITY_NULL_TO_EMPTY) {
            $this->_convertNullArrayValuesToEmpty($arr);
        }
        return $arr;
    }

    function fetchInto(&$arr, $fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        $arr = $this->fetchRow($fetchmode, $rownum);
        if ($this->result->mdb->options['portability'] & DB_PORTABILITY_NULL_TO_EMPTY) {
            $this->_convertNullArrayValuesToEmpty($arr);
        }
        return DB_OK;
    }

    function _convertNullArrayValuesToEmpty(&$array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_null($value)) {
                    $array[$key] = '';
                }
            }
        }
    }

    function numCols()
    {
        return $this->result->numCols();
    }

    function numRows()
    {
        return $this->result->numRows();
    }

    function nextResult()
    {
        return $this->result->nextResult();
    }

    function free()
    {
        $err = $this->result->free();
        if (PEAR::isError($err)) {
            return $err;
        }
        $this->result = false;
        return true;
    }

    function tableInfo($mode = null)
    {
        $this->result->db->loadModule('Reverse');
        return $this->result->db->reverse->tableInfo($this->result, $mode);
    }

    function getRowCounter()
    {
        return $this->result->rowCount()+1+$this->result->offset;
    }
}

class DB_row
{
    function DB_row(&$arr)
    {
        for (reset($arr); $key = key($arr); next($arr)) {
            $this->$key = &$arr[$key];
        }
    }
}

class MDB2_PEARProxy extends PEAR
{
    var $db_object;
    var $phptype;
    var $connection;
    var $dsn;

    function MDB2_PEARProxy(&$db_object)
    {
        $this->db_object =& $db_object;
        $this->PEAR('DB_Error');
        $this->db_object->setOption('seqcol_name', 'id');
        $this->db_object->setOption('result_wrap_class', 'DB_result');
        $this->phptype = $this->db_object->phptype;
        $this->connection = $this->db_object->connection;
        $this->dsn = $this->db_object->getDSN();
    }

    function connect($dsninfo, $persistent = false)
    {
        $this->options['persistent'] = $presistent;
        return $this->db_object->connect();
    }

    function disconnect()
    {
        return $this->db_object->disconnect();
    }

    function toString()
    {
        return $this->db_object->__toString();
    }

    function quoteString($string)
    {
        $string = $this->quote($string);
        if ($string{0} == "'") {
            return substr($string, 1, -1);
        }
        return $string;
    }

    function quote($string)
    {
        if (is_null($string)) {
            return 'NULL';
        }
        return $this->db_object->quote($string);
    }

    function escapeSimple($str)
    {
        return $this->db_object->escape($str);
    }

    function quoteSmart($in)
    {
        if (is_int($in) || is_double($in)) {
            return $in;
        } elseif (is_bool($in)) {
            return $in ? 1 : 0;
        } elseif (is_null($in)) {
            return 'NULL';
        } else {
            return "'" . $this->escapeSimple($in) . "'";
        }
    }

    function quoteIdentifier($string)
    {
        return $this->db_object->quoteIdentifier($string);
    }

    // map?
    function provides($feature)
    {
        return $this->db_object->support($feature);
    }

    // remove?
    function errorCode($nativecode)
    {
        return $this->db_object->errorCode($nativecode);
    }

    // remove?
    function errorMessage($dbcode)
    {
        return $this->db_object->errorMessage($dbcode);
    }

    // remove?
    function &raiseError($code = MDB2_ERROR, $mode = null, $options = null,
        $userinfo = null, $nativecode = null)
    {
        return $this->db_object->raiseError($code, $mode, $options, $userinfo, $nativecode);
    }

    function setFetchMode($fetchmode, $object_class = 'stdClass')
    {
        return $this->db_object->setFetchMode($fetchmode, $object_class);
    }

    function setOption($option, $value)
    {
        return $this->db_object->setOption($option, $value);
    }

    function getOption($option)
    {
        return $this->db_object->getOption($option);
    }

    function prepare($query)
    {
        // parse for ! and &
        // set types
        return $this->db_object->prepare($query);
    }

    function autoPrepare($table, $table_fields, $mode = MDB2_AUTOQUERY_INSERT, $where = false)
    {
        $this->db_object->loadModule('Extended');
        // types
        return $this->db_object->extended->autoPrepare($table, $table_fields, $mode, $where);
    }

    function &autoExecute($table, $fields_values, $mode, $where)
    {
        $this->db_object->loadModule('Extended');
        // types
        return $this->db_object->extended->autoExecute($table, $fields_values, $mode, $where);
    }

    function buildManipSQL($table, $table_fields, $mode, $where = false)
    {
        $this->db_object->loadModule('Extended');
        return $this->db_object->extended->buildManipSQL($table, $table_fields, $mode, $where);
    }

    function &execute($stmt, $data = false)
    {
        $stmt->bindParamArray($data);
        return $stmt->execute();
    }

    function executeMultiple($stmt, $data)
    {
        $this->db_object->loadModule('Extended');
        return $this->db_object->extended->executeMultiple($stmt, null, $data);
    }

    function &query($query, $params = array()) {
        if (sizeof($params) > 0) {
            $sth = $this->db_object->prepare($query);
            if (PEAR::isError($sth)) {
                return $sth;
            }
            if (!is_array($params)) {
                $params = array($params);
            }
            $stmt->bindParamArray($params);
            return $stmt->execute();
        }
        return $this->db_object->query($query);
    }

    function simpleQuery($query) {
        $result = $this->db_object->query($query);
        if (PEAR::isError($result) || $result === MDB2_OK) {
            return $result;
        } else {
            return $result->result->getResource();
        }
    }

    function limitQuery($query, $from, $count, $params = array())
    {
        $result = $this->db_object->setLimit($count, $from);
        if (PEAR::isError($result)) {
            return $result;
        }
        $result =& $this->query($query, $params);
        return $result;
    }

    function _convertNullArrayValuesToEmpty(&$array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_null($value)) {
                    $array[$key] = '';
                }
            }
        }
    }

    function &getOne($query, $params = array())
    {
        $result = $this->query($query, $params);
        $one = $result->result->fetchOne();
        if (is_null($one)) {
            $one = '';
        }
        return $one;
    }

    function &getRow($query,
                     $params = array(),
                     $fetchmode = MDB2_FETCHMODE_DEFAULT)
    {
        if (!is_array($params)) {
            if (is_array($fetchmode)) {
                if (is_null($params)) {
                    $tmp = DB_FETCHMODE_DEFAULT;
                } else {
                    $tmp = $params;
                }
                $params = $fetchmode;
                $fetchmode = $tmp;
            } elseif (!is_null($params)) {
                $fetchmode = $params;
                $params = array();
            }
        }
        $result =& $this->query($query, $params);
        return $result->result->fetchRow($fetchmode);
    }

    function &getCol($query, $col = 0, $params = array())
    {
        $result =& $this->query($query, $params);
        $col = $result->result->fetchCol($col);
        $this->_convertNullArrayValuesToEmpty($col);
        return $col;
    }

    function &getAssoc($query, $force_array = false, $params = array(),
                       $fetchmode = MDB2_FETCHMODE_ORDERED, $group = false)
    {
        $result =& $this->query($query, $params);
        $all = $result->result->fetchAll($fetchmode, true, $force_array, $group);
        $first = reset($all);
        if (isset($first) && $this->db_object->options['portability'] & DB_PORTABILITY_NULL_TO_EMPTY) {
            if (is_array($first)) {
                foreach ($all as $key => $arr) {
                    $this->_convertNullArrayValuesToEmpty($all[$key]);
                }
            } elseif (is_object($first)) {
                foreach ($all as $key => $arr) {
                    $tmp = get_object_vars($all[$key]);
                    if (is_array($tmp)) {
                        $this->_convertNullArrayValuesToEmpty($tmp);
                        foreach ($tmp as $key2 => $column) {
                            $all[$key]->{$key2} = $column;
                        }
                    }
                }
            }
        }
        return $all;
    }

    function &getAll($query,
                     $params = null,
                     $fetchmode = MDB2_FETCHMODE_DEFAULT)
    {
        if (!is_array($params)) {
            if (is_array($fetchmode)) {
                if (is_null($params)) {
                    $tmp = DB_FETCHMODE_DEFAULT;
                } else {
                    $tmp = $params;
                }
                $params = $fetchmode;
                $fetchmode = $tmp;
            } elseif (!is_null($params)) {
                $fetchmode = $params;
                $params = array();
            }
        }
        $result =& $this->query($query, $params);
        $all = $result->result->fetchAll($fetchmode);
        $first = reset($all);
        if (isset($first) && $this->db_object->options['portability'] & DB_PORTABILITY_NULL_TO_EMPTY) {
            if (is_array($first)) {
                foreach ($all as $key => $arr) {
                    $this->_convertNullArrayValuesToEmpty($all[$key]);
                }
            } elseif (is_object($first)) {
                foreach ($all as $key => $arr) {
                    $tmp = get_object_vars($all[$key]);
                    if (is_array($tmp)) {
                        $this->_convertNullArrayValuesToEmpty($tmp);
                        foreach ($tmp as $key2 => $column) {
                            $all[$key]->{$key2} = $column;
                        }
                    }
                }
            }
        }
        return $all;
    }

    function autoCommit($onoff = false)
    {
        return $this->db_object->autoCommit($onoff);
    }

    function commit()
    {
        return $this->db_object->commit();
    }

    function rollback()
    {
        return $this->db_object->rollback();
    }

    function affectedRows()
    {
        return $this->db_object->affectedRows();
    }

    // remove?
    function errorNative()
    {
        return $this->db_object->errorNative();
    }

    function nextId($seq_name, $ondemand = true)
    {
        return $this->db_object->nextID($seq_name, $ondemand);
    }

    function createSequence($seq_name)
    {
        $this->db_object->loadModule('Manager');
        return $this->db_object->manager->createSequence($seq_name, 1);
    }

    function dropSequence($seq_name)
    {
        $this->db_object->loadModule('Manager');
        return $this->db_object->manager->dropSequence($seq_name);
    }

    function &_wrapResource($result)
    {
        if (is_resource($result)) {
            $result_class = $this->db_object->getOption('result_buffering')
                ? $this->db_object->getOption('buffered_result_class') : $$this->db_object->getOption('result_class');
            $class_name = sprintf($result_class, $this->db_object->phptype);
            $result =& new $class_name($this->db_object, $result);
        }
        return $result;
    }

    function fetchInto($result, &$arr, $fetchmode, $rownum = null)
    {
        $result = $this->_wrapResource($result);
        if (!is_null($rownum)) {
            $result->result->seek($rownum);
        }
        $arr = $result->fetchRow($fetchmode);
    }

    function freePrepared($prepared)
    {
        return $this->db_object->freePrepared($prepared);
    }

    function freeResult($result)
    {
        $result = $this->_wrapResource($result);
        return $result->free();
    }

    function numCols($result)
    {
        $result = $this->_wrapResource($result);
        return $result->numCols();
    }

    function numRows($result)
    {
        $result = $this->_wrapResource($result);
        return $result->numRows();
    }

    function nextResult($result)
    {
        $result = $this->_wrapResource($result);
        return $result->nextResult();
    }

    function tableInfo($result, $mode = null)
    {
        $result = $this->_wrapResource($result);
        if (is_string($result) || MDB2::isResultCommon($result)) {
            $this->db_object->loadModule('Reverse');
            return $this->db_object->reverse->tableInfo($result, $mode);
        }
        return $result->tableInfo($mode);
    }

    function getTables()
    {
        return $this->getListOf('tables');
    }

    function getListOf($type)
    {
        $this->db_object->loadModule('Manager');
        switch ($type) {
        case 'tables':
            return $this->db_object->manager->listTables();
        case 'views':
            return $this->db_object->manager->listViews();
        case 'users':
            return $this->db_object->manager->listUsers();
        case 'functions':
            return $this->db_object->manager->listFunctions();
        case 'databases':
            return $this->db_object->manager->listDatabases();
        default:
            return $this->db_object->raiseError(MDB2_ERROR_UNSUPPORTED);
        }
    }
}
?>

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
// | Author: Paul Cooper <pgc@ucecom.com>                                 |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'MDB2/Driver/Reverse/Common.php';

/**
 * MDB2 PostGreSQL driver for the schema reverse engineering module
 *
 * @package MDB2
 * @category Database
 * @author  Paul Cooper <pgc@ucecom.com>
 */
class MDB2_Driver_Reverse_pgsql extends MDB2_Driver_Reverse_Common
{
    // {{{ getTableFieldDefinition()

    /**
     * get the stucture of a field into an array
     *
     * @param string    $table         name of table that should be used in method
     * @param string    $field_name     name of field that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableFieldDefinition($table, $field_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $result = $db->loadModule('Datatype');
        if (PEAR::isError($result)) {
            return $result;
        }

        $column = $db->queryRow("SELECT
                    attnum,attname,typname,attlen,attnotnull,
                    atttypmod,usename,usesysid,pg_class.oid,relpages,
                    reltuples,relhaspkey,relhasrules,relacl,adsrc
                    FROM pg_class,pg_user,pg_type,
                         pg_attribute left outer join pg_attrdef on
                         pg_attribute.attrelid=pg_attrdef.adrelid
                    WHERE (pg_class.relname='$table')
                        and (pg_class.oid=pg_attribute.attrelid)
                        and (pg_class.relowner=pg_user.usesysid)
                        and (pg_attribute.atttypid=pg_type.oid)
                        and attnum > 0
                        and attname = '$field_name'
                        ORDER BY attnum
                        ", null, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($column)) {
            return $column;
        }

        list($types, $length) = $db->datatype->mapNativeDatatype($column);
        $notnull = false;
        if (array_key_exists('attnotnull', $column) && $column['attnotnull'] == 't') {
            $notnull = true;
        }
        $default = false;
        // todo .. check how default look like
        if (!preg_match("/nextval\('([^']+)'/", $column['adsrc'])
            && strlen($column['adsrc']) > 2
        ) {
            $default = substr($column['adsrc'], 1, -1);
            if (is_null($default) && $notnull) {
                $default = '';
            }
        }
        $autoincrement = false;
        if (preg_match("/nextval\('([^']+)'/", $column['adsrc'], $nextvals)) {
            $autoincrement = true;
        }
        $definition = array();
        foreach ($types as $key => $type) {
            $definition[$key] = array(
                'type' => $type,
                'notnull' => $notnull,
            );
            if ($length > 0) {
                $definition[$key]['length'] = $length;
            }
            if ($default !== false) {
                $definition[$key]['default'] = $default;
            }
            if ($autoincrement !== false) {
                $definition[$key]['autoincrement'] = $autoincrement;
            }
        }
        return $definition;
    }


    // }}}
    // {{{ getTableIndexDefinition()
    /**
     * get the stucture of an index into an array
     *
     * @param string    $table      name of table that should be used in method
     * @param string    $index_name name of index that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableIndexDefinition($table, $index_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT * FROM pg_index, pg_class
            WHERE (pg_class.relname='$index_name') AND (pg_class.oid=pg_index.indexrelid)";
        $row = $db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($row)) {
            return $row;
        }
        if ($row['relname'] != $index_name) {
            return $db->raiseError(MDB2_ERROR, null, null,
                'getTableIndexDefinition: it was not specified an existing table index');
        }

        $db->loadModule('Manager');
        $columns = $db->manager->listTableFields($table);

        $definition = array();
        if ($row['indisunique'] == 't') {
            $definition['unique'] = true;
        }

        $index_column_numbers = explode(' ', $row['indkey']);

        foreach ($index_column_numbers as $number) {
            $definition['fields'][$columns[($number - 1)]] = array('sorting' => 'ascending');
        }
        return $definition;
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set
     *
     * NOTE: only supports 'table' and 'flags' if <var>$result</var>
     * is a table name.
     *
     * @param object|string  $result  MDB2_result object from a query or a
     *                                 string containing the name of a table.
     *                                 While this also accepts a query result
     *                                 resource identifier, this behavior is
     *                                 deprecated.
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A MDB2_Error object on failure.
     *
     * @see MDB2_Driver_Common::tableInfo()
     */
    function tableInfo($result, $mode = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $id = $db->_doQuery("SELECT * FROM $result LIMIT 0");
            if (PEAR::isError($id)) {
                return $id;
            }
            $got_string = true;
        } elseif (MDB2::isResultCommon($result)) {
            /*
             * Probably received a result object.
             * Extract the result resource identifier.
             */
            $id = $result->getResource();
            $got_string = false;
        } else {
            /*
             * Probably received a result resource identifier.
             * Copy it.
             * Deprecated.  Here for compatibility only.
             */
            $id = $result;
            $got_string = false;
        }

        if (!is_resource($id)) {
            return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA);
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $case_func = 'strtolower';
            } else {
                $case_func = 'strtoupper';
            }
        } else {
            $case_func = 'strval';
        }

        $count = @pg_num_fields($id);
        $res   = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        for ($i = 0; $i < $count; $i++) {
            $res[$i] = array(
                'table' => $got_string ? $case_func($result) : '',
                'name'  => $case_func(@pg_field_name($id, $i)),
                'type'  => @pg_field_type($id, $i),
                'len'   => @pg_field_size($id, $i),
                'flags' => $got_string
                           ? $this->_pgFieldFlags($id, $i, $result)
                           : '',
            );
            if ($mode & MDB2_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & MDB2_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }

        // free the result only if we were called on a table
        if ($got_string) {
            @pg_free_result($id);
        }
        return $res;
    }

    // }}}
    // {{{ _pgFieldFlags()

    /**
     * Get a column's flags
     *
     * Supports "not_null", "default_value", "primary_key", "unique_key"
     * and "multiple_key".  The default value is passed through
     * rawurlencode() in case there are spaces in it.
     *
     * @param int $resource   the PostgreSQL result identifier
     * @param int $num_field  the field number
     *
     * @return string  the flags
     *
     * @access protected
     */
    function _pgFieldFlags($resource, $num_field, $table_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $field_name = @pg_field_name($resource, $num_field);

        $result = @pg_query($db->connection, "SELECT f.attnotnull, f.atthasdef
                                FROM pg_attribute f, pg_class tab, pg_type typ
                                WHERE tab.relname = typ.typname
                                AND typ.typrelid = f.attrelid
                                AND f.attname = '$field_name'
                                AND tab.relname = '$table_name'");
        if (@pg_num_rows($result) > 0) {
            $row = @pg_fetch_row($result, 0);
            $flags  = ($row[0] == 't') ? 'not_null ' : '';

            if ($row[1] == 't') {
                $result = @pg_query($db->connection, "SELECT a.adsrc
                                    FROM pg_attribute f, pg_class tab, pg_type typ, pg_attrdef a
                                    WHERE tab.relname = typ.typname AND typ.typrelid = f.attrelid
                                    AND f.attrelid = a.adrelid AND f.attname = '$field_name'
                                    AND tab.relname = '$table_name' AND f.attnum = a.adnum");
                $row = @pg_fetch_row($result, 0);
                $num = preg_replace("/'(.*)'::\w+/", "\\1", $row[0]);
                $flags.= 'default_' . rawurlencode($num) . ' ';
            }
        } else {
            $flags = '';
        }
        $result = @pg_query($db->connection, "SELECT i.indisunique, i.indisprimary, i.indkey
                                FROM pg_attribute f, pg_class tab, pg_type typ, pg_index i
                                WHERE tab.relname = typ.typname
                                AND typ.typrelid = f.attrelid
                                AND f.attrelid = i.indrelid
                                AND f.attname = '$field_name'
                                AND tab.relname = '$table_name'");
        $count = @pg_num_rows($result);

        for ($i = 0; $i < $count ; $i++) {
            $row = @pg_fetch_row($result, $i);
            $keys = explode(' ', $row[2]);

            if (in_array($num_field + 1, $keys)) {
                $flags.= ($row[0] == 't' && $row[1] == 'f') ? 'unique_key ' : '';
                $flags.= ($row[1] == 't') ? 'primary_key ' : '';
                if (count($keys) > 1)
                    $flags.= 'multiple_key ';
            }
        }

        return trim($flags);
    }
}
?>
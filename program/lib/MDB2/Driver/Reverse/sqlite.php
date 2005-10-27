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
// | Author: Lukas Smith <smith@backendmedia.com>                         |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'MDB2/Driver/Reverse/Common.php';

/**
 * MDB2 SQlite driver for the schema reverse engineering module
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@backendmedia.com>
 */
class MDB2_Driver_Reverse_sqlite extends MDB2_Driver_Reverse_Common
{

    function _getTableColumns($query)
    {
        $start_pos = strpos($query, '(');
        $end_pos = strrpos($query, ')');
        $column_def = substr($query, $start_pos+1, $end_pos-$start_pos-1);
        $column_sql = split(',', $column_def);
        $columns = array();
        $count = count($column_sql);
        if ($count == 0) {
            return $db->raiseError('unexpected empty table column definition list');
        }
        $regexp = '/^([^ ]+) (CHAR|VARCHAR|VARCHAR2|TEXT|INT|INTEGER|BIGINT|DOUBLE|FLOAT|DATETIME|DATE|TIME|LONGTEXT|LONGBLOB)( PRIMARY)?( \(([1-9][0-9]*)(,([1-9][0-9]*))?\))?( DEFAULT (\'[^\']*\'|[^ ]+))?( NOT NULL)?$/i';
        for ($i=0, $j=0; $i<$count; ++$i) {
            if (!preg_match($regexp, $column_sql[$i], $matches)) {
                return $db->raiseError('unexpected table column SQL definition');
            }
            $columns[$j]['name'] = $matches[1];
            $columns[$j]['type'] = strtolower($matches[2]);
            if (isset($matches[5]) && strlen($matches[5])) {
                $columns[$j]['length'] = $matches[5];
            }
            if (isset($matches[7]) && strlen($matches[7])) {
                $columns[$j]['decimal'] = $matches[7];
            }
            if (isset($matches[9]) && strlen($matches[9])) {
                $default = $matches[9];
                if (strlen($default) && $default[0]=="'") {
                    $default = str_replace("''", "'", substr($default, 1, strlen($default)-2));
                }
                $columns[$j]['default'] = $default;
            }
            if (isset($matches[10]) && strlen($matches[10])) {
                $columns[$j]['notnull'] = true;
            }
            ++$j;
        }
        return $columns;
    }

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
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $result = $db->loadModule('Datatype');
        if (PEAR::isError($result)) {
            return $result;
        }
        $query = "SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'";
        $query = $db->queryOne($query);
        if (PEAR::isError($query)) {
            return $query;
        }
        if (PEAR::isError($columns = $this->_getTableColumns($query))) {
            return $columns;
        }
        foreach ($columns as $column) {
            if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
                $column['name'] = strtolower($column['name']);
            } else {
                $column = array_change_key_case($column, CASE_LOWER);
            }
            if ($field_name == $column['name']) {
                list($types, $length) = $db->datatype->mapNativeDatatype($column);
                unset($notnull);
                if (isset($column['null']) && $column['null'] != 'YES') {
                    $notnull = true;
                }
                unset($default);
                if (isset($column['default'])) {
                    $default = $column['default'];
                }
                $definition = array();
                foreach ($types as $key => $type) {
                    $definition[0][$key] = array('type' => $type);
                    if (isset($notnull)) {
                        $definition[0][$key]['notnull'] = true;
                    }
                    if (isset($default)) {
                        $definition[0][$key]['default'] = $default;
                    }
                    if (isset($length)) {
                        $definition[0][$key]['length'] = $length;
                    }
                }
                // todo .. handle auto_inrement and primary keys
                return $definition;
            }
        }

        return $db->raiseError(MDB2_ERROR, null, null,
            'getTableFieldDefinition: it was not specified an existing table column');
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
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        if ($index_name == 'PRIMARY') {
            return $db->raiseError(MDB2_ERROR, null, null,
                'getTableIndexDefinition: PRIMARY is an hidden index');
        }
        $query = "SELECT sql FROM sqlite_master WHERE type='index' AND name='$index_name' AND tbl_name='$table' AND sql NOT NULL ORDER BY name";
        $result = $db->query($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        $columns = $result->getColumnNames();
        $column = 'sql';
        if (!isset($columns[$column])) {
            $result->free();
            return $db->raiseError('getTableIndexDefinition: show index does not return the table creation sql');
        }

        $query = strtolower($result->fetchOne());
        $unique = strstr($query, ' unique ');
        $key_name = $index_name;
        $start_pos = strpos($query, '(');
        $end_pos = strrpos($query, ')');
        $column_names = substr($query, $start_pos+1, $end_pos-$start_pos-1);
        $column_names = split(',', $column_names);

        $definition = array();
        if ($unique) {
            $definition['unique'] = true;
        }
        $count = count($column_names);
        for ($i=0; $i<$count; ++$i) {
            $column_name = strtok($column_names[$i]," ");
            $collation = strtok(" ");
            $definition['fields'][$column_name] = array();
            if (!empty($collation)) {
                $definition['fields'][$column_name]['sorting'] = ($collation=='ASC' ? 'ascending' : 'descending');
            }
        }

        $result->free();
        if (!isset($definition['fields'])) {
            return $db->raiseError(MDB2_ERROR, null, null,
                'getTableIndexDefinition: it was not specified an existing table index');
        }
        return $definition;
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table
     *
     * @param string         $result  a string containing the name of a table
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A MDB2_Error object on failure.
     *
     * @see MDB2_common::tableInfo()
     * @since Method available since Release 1.7.0
     */
    function tableInfo($result, $mode = null)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $id = $db->queryAll("PRAGMA table_info('$result');", null, MDB2_FETCHMODE_ASSOC);
            $got_string = true;
        } else {
            return $db->raiseError(MDB2_ERROR_NOT_CAPABLE, null, null,
                                     'This DBMS can not obtain tableInfo' .
                                     ' from result sets');
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = count($id);
        $res   = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        for ($i = 0; $i < $count; $i++) {
            if (strpos($id[$i]['type'], '(') !== false) {
                $bits = explode('(', $id[$i]['type']);
                $type = $bits[0];
                $len  = rtrim($bits[1],')');
            } else {
                $type = $id[$i]['type'];
                $len  = 0;
            }

            $flags = '';
            if ($id[$i]['pk']) {
                $flags .= 'primary_key ';
            }
            if ($id[$i]['notnull']) {
                $flags .= 'not_null ';
            }
            if ($id[$i]['dflt_value'] !== null) {
                $flags .= 'default_' . rawurlencode($id[$i]['dflt_value']);
            }
            $flags = trim($flags);

            $res[$i] = array(
                'table' => $case_func($result),
                'name'  => $case_func($id[$i]['name']),
                'type'  => $type,
                'len'   => $len,
                'flags' => $flags,
            );

            if ($mode & MDB2_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & MDB2_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }

        return $res;
    }
}

?>
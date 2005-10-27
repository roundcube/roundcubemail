<?php
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

require_once 'MDB2/Driver/Reverse/Common.php';

/**
 * MDB2 InterbaseBase driver for the reverse engineering module
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@dybnet.de>
 */
class MDB2_Driver_Reverse_ibase extends MDB2_Driver_Reverse_Common
{
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
            $id = $db->_doQuery("SELECT * FROM $result WHERE 1=0");
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

        $count = @ibase_num_fields($id);
        $res   = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        for ($i = 0; $i < $count; $i++) {
            $info = @ibase_field_info($id, $i);
            $res[$i] = array(
                'table' => $got_string ? $case_func($result) : '',
                'name'  => $case_func($info['name']),
                'type'  => $info['type'],
                'len'   => $info['length'],
                'flags' => ($got_string)
                            ? $this->_ibaseFieldFlags($info['name'], $result) : '',
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
            @ibase_free_result($id);
        }
        return $res;
    }

    // }}}
    // {{{ _ibaseFieldFlags()

    /**
     * Get the column's flags
     *
     * Supports "primary_key", "unique_key", "not_null", "default",
     * "computed" and "blob".
     *
     * @param string $field_name  the name of the field
     * @param string $table_name  the name of the table
     *
     * @return string  the flags
     *
     * @access protected
     */
    function _ibaseFieldFlags($field_name, $table_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT R.RDB$CONSTRAINT_TYPE CTYPE'
               .' FROM RDB$INDEX_SEGMENTS I'
               .'  JOIN RDB$RELATION_CONSTRAINTS R ON I.RDB$INDEX_NAME=R.RDB$INDEX_NAME'
               .' WHERE I.RDB$FIELD_NAME=\'' . $field_name . '\''
               .'  AND UPPER(R.RDB$RELATION_NAME)=\'' . strtoupper($table_name) . '\'';

        $result = $db->_doQuery($query);
        if (PEAR::isError($result)) {
            return $result;
        }

        $flags = '';
        if ($obj = @ibase_fetch_object($result)) {
            @ibase_free_result($result);
            if (isset($obj->CTYPE)  && trim($obj->CTYPE) == 'PRIMARY KEY') {
                $flags.= 'primary_key ';
            }
            if (isset($obj->CTYPE)  && trim($obj->CTYPE) == 'UNIQUE') {
                $flags.= 'unique_key ';
            }
        }

        $query = 'SELECT R.RDB$NULL_FLAG AS NFLAG,'
               .'  R.RDB$DEFAULT_SOURCE AS DSOURCE,'
               .'  F.RDB$FIELD_TYPE AS FTYPE,'
               .'  F.RDB$COMPUTED_SOURCE AS CSOURCE'
               .' FROM RDB$RELATION_FIELDS R '
               .'  JOIN RDB$FIELDS F ON R.RDB$FIELD_SOURCE=F.RDB$FIELD_NAME'
               .' WHERE UPPER(R.RDB$RELATION_NAME)=\'' . strtoupper($table_name) . '\''
               .'  AND R.RDB$FIELD_NAME=\'' . $field_name . '\'';

        $result = $db->_doQuery($query);
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($obj = @ibase_fetch_object($result)) {
            @ibase_free_result($result);
            if (isset($obj->NFLAG)) {
                $flags.= 'not_null ';
            }
            if (isset($obj->DSOURCE)) {
                $flags.= 'default ';
            }
            if (isset($obj->CSOURCE)) {
                $flags.= 'computed ';
            }
            if (isset($obj->FTYPE)  && $obj->FTYPE == 261) {
                $flags.= 'blob ';
            }
        }

        return trim($flags);
    }
}
?>
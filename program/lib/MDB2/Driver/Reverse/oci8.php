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
 * MDB2 Oracle driver for the schema reverse engineering module
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@dybnet.de>
 */
class MDB2_Driver_Reverse_oci8 extends MDB2_Driver_Reverse_Common
{
    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set
     *
     * NOTE: only supports 'table' and 'flags' if <var>$result</var>
     * is a table name.
     *
     * NOTE: flags won't contain index information.
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

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $case_func = 'strtolower';
            } else {
                $case_func = 'strtoupper';
            }
        } else {
            $case_func = 'strval';
        }

        $res = array();

        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $result = strtoupper($result);
            $query = 'SELECT column_name, data_type, data_length, '
                        . 'nullable '
                        . 'FROM user_tab_columns '
                        . "WHERE table_name='$result' ORDER BY column_id";

            $stmt = $db->_doQuery($query);
            if (PEAR::isError($stmt)) {
                return $stmt;
            }

            $i = 0;
            while (@OCIFetch($stmt)) {
                $res[$i] = array(
                    'table' => $case_func($result),
                    'name'  => $case_func(@OCIResult($stmt, 1)),
                    'type'  => @OCIResult($stmt, 2),
                    'len'   => @OCIResult($stmt, 3),
                    'flags' => (@OCIResult($stmt, 4) == 'N') ? 'not_null' : '',
                );
                if ($mode & MDB2_TABLEINFO_ORDER) {
                    $res['order'][$res[$i]['name']] = $i;
                }
                if ($mode & MDB2_TABLEINFO_ORDERTABLE) {
                    $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
                }
                $i++;
            }

            if ($mode) {
                $res['num_fields'] = $i;
            }
            @OCIFreeStatement($stmt);

        } else {
            if (MDB2::isResultCommon($result)) {
                /*
                 * Probably received a result object.
                 * Extract the result resource identifier.
                 */
                $result = $result->getResource();
            }

            $res = array();

            if ($result === $db->last_stmt) {
                $count = @OCINumCols($result);
                if ($mode) {
                    $res['num_fields'] = $count;
                }
                for ($i = 0; $i < $count; $i++) {
                    $res[$i] = array(
                        'table' => '',
                        'name'  => $case_func(@OCIColumnName($result, $i+1)),
                        'type'  => @OCIColumnType($result, $i+1),
                        'len'   => @OCIColumnSize($result, $i+1),
                        'flags' => '',
                    );
                    if ($mode & MDB2_TABLEINFO_ORDER) {
                        $res['order'][$res[$i]['name']] = $i;
                    }
                    if ($mode & MDB2_TABLEINFO_ORDERTABLE) {
                        $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
                    }
                }
            } else {
                return $db->raiseError(MDB2_ERROR_NOT_CAPABLE);
            }
        }
        return $res;
    }
}
?>
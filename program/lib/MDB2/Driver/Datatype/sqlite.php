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
// | Author: Lukas Smith <smith@backendmedia.com>                         |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'MDB2/Driver/Datatype/Common.php';

/**
 * MDB2 SQLite driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@backendmedia.com>
 */
class MDB2_Driver_Datatype_sqlite extends MDB2_Driver_Datatype_Common
{
    // }}}
    // {{{ _getIntegerDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an integer type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string  $name   name the field to be declared.
     * @param string  $field  associative array with the name of the properties
     *                        of the field being declared as array indexes.
     *                        Currently, the types of supported field
     *                        properties are as follows:
     *
     *                       unsigned
     *                        Boolean flag that indicates whether the field
     *                        should be declared as unsigned integer if
     *                        possible.
     *
     *                       default
     *                        Integer value to be used as default for this
     *                        field.
     *
     *                       notnull
     *                        Boolean flag that indicates whether this field is
     *                        constrained to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     * @access protected
     */
    function _getIntegerDeclaration($name, $field)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $unsigned = (isset($field['unsigned']) && $field['unsigned']) ? ' UNSIGNED' : '';
        $default = isset($field['default']) ? ' DEFAULT '.
            $this->quote($field['default'], 'integer') : '';
        $notnull = (isset($field['notnull']) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' INT'.$unsigned.$default.$notnull;
    }

    // }}}
    // {{{ _getCLOBDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an character
     * large object type field to be used in statements like CREATE TABLE.
     *
     * @param string  $name   name the field to be declared.
     * @param string  $field  associative array with the name of the
     *                        properties of the field being declared as array
     *                        indexes. Currently, the types of supported field
     *                        properties are as follows:
     *
     *                       length
     *                        Integer value that determines the maximum length
     *                        of the large object field. If this argument is
     *                        missing the field should be declared to have the
     *                        longest length allowed by the DBMS.
     *
     *                       notnull
     *                        Boolean flag that indicates whether this field
     *                        is constrained to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     * @access protected
     */
    function _getCLOBDeclaration($name, $field)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        if (isset($field['length'])) {
            $length = $field['length'];
            if ($length <= 255) {
                $type = 'TINYTEXT';
            } else {
                if ($length <= 65535) {
                    $type = 'TEXT';
                } else {
                    if ($length <= 16777215) {
                        $type = 'MEDIUMTEXT';
                    } else {
                        $type = 'LONGTEXT';
                    }
                }
            }
        } else {
            $type = 'LONGTEXT';
        }
        $notnull = (isset($field['notnull']) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$type.$notnull;
    }

    // }}}
    // {{{ _getBLOBDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an binary large
     * object type field to be used in statements like CREATE TABLE.
     *
     * @param string  $name   name the field to be declared.
     * @param string  $field  associative array with the name of the properties
     *                        of the field being declared as array indexes.
     *                        Currently, the types of supported field
     *                        properties are as follows:
     *
     *                       length
     *                        Integer value that determines the maximum length
     *                        of the large object field. If this argument is
     *                        missing the field should be declared to have the
     *                        longest length allowed by the DBMS.
     *
     *                       notnull
     *                        Boolean flag that indicates whether this field is
     *                        constrained to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     * @access protected
     */
    function _getBLOBDeclaration($name, $field)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        if (isset($field['length'])) {
            $length = $field['length'];
            if ($length <= 255) {
                $type = 'TINYBLOB';
            } else {
                if ($length <= 65535) {
                    $type = 'BLOB';
                } else {
                    if ($length <= 16777215) {
                        $type = 'MEDIUMBLOB';
                    } else {
                        $type = 'LONGBLOB';
                    }
                }
            }
        }
        else {
            $type = 'LONGBLOB';
        }
        $notnull = (isset($field['notnull']) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$type.$notnull;
    }

    // }}}
    // {{{ _getDateDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an date type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string  $name   name the field to be declared.
     * @param string  $field  associative array with the name of the properties
     *                        of the field being declared as array indexes.
     *                        Currently, the types of supported field properties
     *                        are as follows:
     *
     *                       default
     *                        Date value to be used as default for this field.
     *
     *                       notnull
     *                        Boolean flag that indicates whether this field is
     *                        constrained to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     * @access protected
     */
    function _getDateDeclaration($name, $field)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $default = isset($field['default']) ? ' DEFAULT '.
            $this->quote($field['default'], 'date') : '';
        $notnull = (isset($field['notnull']) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' DATE'.$default.$notnull;
    }

    // }}}
    // {{{ _getTimestampDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an timestamp
     * type field to be used in statements like CREATE TABLE.
     *
     * @param string  $name   name the field to be declared.
     * @param string  $field  associative array with the name of the properties
     *                        of the field being declared as array indexes.
     *                        Currently, the types of supported field
     *                        properties are as follows:
     *
     *                       default
     *                        Time stamp value to be used as default for this
     *                        field.
     *
     *                       notnull
     *                        Boolean flag that indicates whether this field is
     *                        constrained to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     * @access protected
     */
    function _getTimestampDeclaration($name, $field)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $default = isset($field['default']) ? ' DEFAULT '.
            $this->quote($field['default'], 'timestamp') : '';
        $notnull = (isset($field['notnull']) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' DATETIME'.$default.$notnull;
    }

    // }}}
    // {{{ _getTimeDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an time type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string  $name   name the field to be declared.
     * @param string  $field  associative array with the name of the properties
     *                        of the field being declared as array indexes.
     *                        Currently, the types of supported field
     *                        properties are as follows:
     *
     *                       default
     *                        Time value to be used as default for this field.
     *
     *                       notnull
     *                        Boolean flag that indicates whether this field is
     *                        constrained to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     * @access protected
     */
    function _getTimeDeclaration($name, $field)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $default = isset($field['default']) ? ' DEFAULT '.
            $this->quote($field['default'], 'time') : '';
        $notnull = (isset($field['notnull']) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' TIME'.$default.$notnull;
    }

    // }}}
    // {{{ _getFloatDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an float type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string  $name   name the field to be declared.
     * @param string  $field  associative array with the name of the properties
     *                        of the field being declared as array indexes.
     *                        Currently, the types of supported field
     *                        properties are as follows:
     *
     *                       default
     *                        Integer value to be used as default for this
     *                        field.
     *
     *                       notnull
     *                        Boolean flag that indicates whether this field is
     *                        constrained to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     * @access protected
     */
    function _getFloatDeclaration($name, $field)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $type = 'DOUBLE'.($db->options['fixed_float'] ? '('.
            ($db->options['fixed_float']+2).','.$db->options['fixed_float'].')' : '');
        $default = isset($field['default']) ? ' DEFAULT '.
            $this->quote($field['default'], 'float') : '';
        $notnull = (isset($field['notnull']) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$type.$default.$notnull;
    }

    // }}}
    // {{{ _getDecimalDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an decimal type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string  $name   name the field to be declared.
     * @param string  $field  associative array with the name of the properties
     *                        of the field being declared as array indexes.
     *                        Currently, the types of supported field
     *                        properties are as follows:
     *
     *                       default
     *                        Integer value to be used as default for this
     *                        field.
     *
     *                       notnull
     *                        Boolean flag that indicates whether this field is
     *                        constrained to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     * @access protected
     */
    function _getDecimalDeclaration($name, $field)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $type = 'BIGINT';
        $default = isset($field['default']) ? ' DEFAULT '.
            $this->quote($field['default'], 'decimal') : '';
        $notnull = (isset($field['notnull']) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$type.$default.$notnull;
    }

    // }}}
    // {{{ _quoteFloat()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string  $value text string value that is intended to be converted.
     * @return string  text string that represents the given argument value in
     *                 a DBMS specific format.
     * @access protected
     */
    function _quoteFloat($value)
    {
        return (float)$value;
    }

    // }}}
    // {{{ _quoteDecimal()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string  $value text string value that is intended to be converted.
     * @return string  text string that represents the given argument value in
     *                 a DBMS specific format.
     * @access protected
     */
    function _quoteDecimal($value)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        return $db->escape($value);
    }

    // }}}
    // {{{ mapNativeDatatype()

    /**
     * Maps a native array description of a field to a MDB2 datatype and length
     *
     * @param array  $field native field description
     * @return array containing the various possible types and the length
     * @access public
     */
    function mapNativeDatatype($field)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $db_type = $field['type'];
        $length = isset($field['length']) ? $field['length'] : null;
        $type = array();
        switch ($db_type) {
        case 'tinyint':
        case 'smallint':
        case 'mediumint':
        case 'int':
        case 'integer':
        case 'bigint':
            $type[] = 'integer';
            if ($length == '1') {
                $type[] = 'boolean';
                if (preg_match('/^[is|has]/', $field['name'])) {
                    $type = array_reverse($type);
                }
            }
            $type[] = 'decimal';
            break;
        case 'tinytext':
        case 'mediumtext':
        case 'longtext':
        case 'text':
        case 'char':
        case 'varchar':
        case "varchar2":
            $type[] = 'text';
            if ($length == '1') {
                $type[] = 'boolean';
                if (preg_match('/[is|has]/', $field['name'])) {
                    $type = array_reverse($type);
                }
            } elseif (strstr($db_type, 'text'))
                $type[] = 'clob';
            break;
        case 'enum':
            preg_match_all('/\'.+\'/U',$row[$type_column], $matches);
            $length = 0;
            if (is_array($matches)) {
                foreach ($matches[0] as $value) {
                    $length = max($length, strlen($value)-2);
                }
            }
        case 'set':
            $type[] = 'text';
            $type[] = 'integer';
            break;
        case 'date':
            $type[] = 'date';
            $length = null;
            break;
        case 'datetime':
        case 'timestamp':
            $type[] = 'timestamp';
            $length = null;
            break;
        case 'time':
            $type[] = 'time';
            $length = null;
            break;
        case 'float':
        case 'double':
        case 'real':
            $type[] = 'float';
            break;
        case 'decimal':
        case 'numeric':
            $type[] = 'decimal';
            break;
        case 'tinyblob':
        case 'mediumblob':
        case 'longblob':
        case 'blob':
            $type[] = 'text';
            $length = null;
            break;
        case 'year':
            $type[] = 'integer';
            $type[] = 'date';
            $length = null;
            break;
        default:
            return $db->raiseError(MDB2_ERROR, null, null,
                'getTableFieldDefinition: unknown database attribute type');
        }

        return array($type, $length);
    }
}

?>

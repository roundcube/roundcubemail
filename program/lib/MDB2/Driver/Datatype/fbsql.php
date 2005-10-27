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
//
// $Id$
//

require_once 'MDB2/Driver/Datatype/Common.php';

/**
 * MDB2 FrontbaseSQL driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Datatype_fbsql extends MDB2_Driver_Datatype_Common
{
    // }}}
    // {{{ convertResult()

    /**
     * convert a value to a RDBMS indepdenant MDB2 type
     *
     * @param mixed  $value   value to be converted
     * @param int    $type    constant that specifies which type to convert to
     * @return mixed converted value
     * @access public
     */
    function convertResult($value, $type)
    {
        if (is_null($value)) {
            return null;
        }
        switch ($type) {
         case 'boolean':
             return $value == 'T';
         case 'time':
            if ($value[0] == '+') {
                return substr($value, 1);
            } else {
                return $value;
            }
        default:
            return $this->_baseConvertResult($value, $type);
        }
        return $this->_baseConvertResult($value, $type);
    }

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
        if (array_key_exists('unsigned', $field) && $field['unsigned']) {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }

            $db->warnings[] = "unsigned integer field \"$name\" is being
                declared as signed integer";
        }
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'integer') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' INT'.$default.$notnull;
    }

    // }}}
    // {{{ _getTextDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an text type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name name the field to be declared.
    * @param array $field associative array with the name of the properties
     *       of the field being declared as array indexes. Currently, the types
     *       of supported field properties are as follows:
    *
     *       length
     *           Integer value that determines the maximum length of the text
     *           field. If this argument is missing the field should be
     *           declared to have the longest length allowed by the DBMS.
     *
     *       default
     *           Text value to be used as default for this field.
     *
     *       notnull
     *           Boolean flag that indicates whether this field is constrained
     *           to not be set to NULL.
     * @return string DBMS specific SQL code portion that should be used to
     *       declare the specified field.
     * @access protected
     */
    function _getTextDeclaration($name, $field)
    {
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'text') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        $length = array_key_exists('length', $field) ? $field['length'] : 32768;
        return $name.' VARCHAR ('.$length.')'.$default.$notnull;
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
        return "$name CLOB".((array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '');
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
        return "$name BLOB".((array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '');
    }

    // }}}
    // {{{ _getBooleanDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare a boolean type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name name the field to be declared.
     * @param array $field associative array with the name of the properties
     *       of the field being declared as array indexes. Currently, the types
     *       of supported field properties are as follows:
     *
     *       default
     *           Boolean value to be used as default for this field.
     *
     *       notnullL
     *           Boolean flag that indicates whether this field is constrained
     *           to not be set to NULL.
     * @return string DBMS specific SQL code portion that should be used to
     *       declare the specified field.
     * @access protected
     */
    function _getBooleanDeclaration($name, $field)
    {
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'boolean') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' BOOLEAN)'.$default.$notnull;
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
        $default = array_key_exists('default', $field) ? ' DEFAULT DATE '.
            $this->quote($field['default'], 'date') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
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
        $default = array_key_exists('default', $field) ? ' DEFAULT TIMESTAMP '.
            $this->quote($field['default'], 'timestamp') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' TIMESTAMP'.$default.$notnull;
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
        $default = array_key_exists('default', $field) ? ' DEFAULT TIME '.
            $this->quote($field['default'], 'time') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
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
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'float') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' FLOAT'.$default.$notnull;
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
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $type = 'DECIMAL(18,'.$db->options['decimal_places'].')';
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'decimal') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$type.$default.$notnull;
    }

    // }}}
    // {{{ _quoteBLOB()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param           $value
     * @return string  text string that represents the given argument value in
     *                 a DBMS specific format.
     * @access protected
     */
    function _quoteBLOB($value)
    {
        $value = $this->_readFile($value);
        return "'".addslashes($value)."'";
    }

    // }}}
    // {{{ _quoteBoolean()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @return string text string that represents the given argument value in
     *       a DBMS specific format.
     * @access protected
     */
    function _quoteBoolean($value)
    {
        return ($value ? 'True' : 'False');
    }

    // }}}
    // {{{ _quoteDate()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @return string text string that represents the given argument value in
     *        a DBMS specific format.
     * @access protected
     */
    function _quoteDate($value)
    {
        return 'DATE'.$this->_quoteText($value);
    }

    // }}}
    // {{{ _quoteTimestamp()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @return string text string that represents the given argument value in
     *        a DBMS specific format.
     * @access protected
     */
    function _quoteTimestamp($value)
    {
        return 'TIMESTAMP'.$this->_quoteText($value);
    }

    // }}}
    // {{{ _quoteTime()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     *        compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @return string text string that represents the given argument value in
     *        a DBMS specific format.
     * @access protected
     */
    function _quoteTime($value)
    {
        return 'TIME'.$this->_quoteText($value);
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
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->escape($value);
    }
}

?>

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

require_once 'MDB2/Driver/Datatype/Common.php';

/**
 * MDB2 PostGreSQL driver
 *
 * @package MDB2
 * @category Database
 * @author  Paul Cooper <pgc@ucecom.com>
 */
class MDB2_Driver_Datatype_pgsql extends MDB2_Driver_Datatype_Common
{
    // {{{ convertResult()

    /**
     * convert a value to a RDBMS independent MDB2 type
     *
     * @param mixed $value value to be converted
     * @param int $type constant that specifies which type to convert to
     * @return mixed converted value or a MDB2 error on failure
     * @access public
     */
    function convertResult($value, $type)
    {
        if (is_null($value)) {
            return null;
        }
        switch ($type) {
        case 'boolean':
            return $value == 't';
        case 'float':
            return doubleval($value);
        case 'date':
            return $value;
        case 'time':
            return $value;
        case 'timestamp':
            return substr($value, 0, strlen('YYYY-MM-DD HH:MM:SS'));
        default:
            return $this->_baseConvertResult($value, $type);
        }
    }

    // }}}
    // {{{ getTypeDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an text type
     * field to be used in statements like CREATE TABLE.
     *
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      length
     *          Integer value that determines the maximum length of the text
     *          field. If this argument is missing the field should be
     *          declared to have the longest length allowed by the DBMS.
     *
     *      default
     *          Text value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access public
     */
    function getTypeDeclaration($field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        switch ($field['type']) {
        case 'text':
            return array_key_exists('length', $field) ? 'VARCHAR ('.$field['length'].')' : 'TEXT';
        case 'clob':
            return 'OID';
        case 'blob':
            return 'OID';
        case 'integer':
            if (array_key_exists('autoincrement', $field) && $field['autoincrement']) {
                return 'SERIAL PRIMARY KEY';
            }
            return 'INT';
        case 'boolean':
            return 'BOOLEAN';
        case 'date':
            return 'DATE';
        case 'time':
            return 'TIME without time zone';
        case 'timestamp':
            return 'TIMESTAMP without time zone';
        case 'float':
            return 'FLOAT8';
        case 'decimal':
            return 'NUMERIC(18, '.$db->options['decimal_places'].')';
        }
    }

    // }}}
    // {{{ _getIntegerDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an integer type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name name the field to be declared.
     * @param array $field associative array with the name of the properties
     *       of the field being declared as array indexes. Currently, the types
     *       of supported field properties are as follows:
     *
     *       unsigned
     *           Boolean flag that indicates whether the field should be
     *           declared as unsigned integer if possible.
     *
     *       default
     *           Integer value to be used as default for this field.
     *
     *       notnull
     *           Boolean flag that indicates whether this field is constrained
     *           to not be set to null.
     * @return string DBMS specific SQL code portion that should be used to
     *       declare the specified field.
     * @access protected
     */
    function _getIntegerDeclaration($name, $field)
    {
        if (array_key_exists('unsigned', $field) && $field['unsigned']) {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }

            $db->warnings[] = "unsigned integer field \"$name\" is being declared as signed integer";
        }
        if (array_key_exists('autoincrement', $field) && $field['autoincrement']) {
            return $name.' '.$this->getTypeDeclaration($field);
        }
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'integer') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$default.$notnull;
    }

    // }}}
    // {{{ _getTextDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare a text type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name   name the field to be declared.
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      length
     *          Integer value that determines the maximum length of the text
     *          field. If this argument is missing the field should be
     *          declared to have the longest length allowed by the DBMS.
     *
     *      default
     *          Text value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access protected
     */
    function _getTextDeclaration($name, $field)
    {
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'text') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$default.$notnull;
    }

    // }}}
    // {{{ _getCLOBDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare a character
     * large object type field to be used in statements like CREATE TABLE.
     *
     * @param string $name   name the field to be declared.
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      length
     *          Integer value that determines the maximum length of the large
     *          object field. If this argument is missing the field should be
     *          declared to have the longest length allowed by the DBMS.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access protected
     */
    function _getCLOBDeclaration($name, $field)
    {
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$notnull;
    }

    // }}}
    // {{{ _getBLOBDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare a binary large
     * object type field to be used in statements like CREATE TABLE.
     *
     * @param string $name   name the field to be declared.
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      length
     *          Integer value that determines the maximum length of the large
     *          object field. If this argument is missing the field should be
     *          declared to have the longest length allowed by the DBMS.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access protected
     */
    function _getBLOBDeclaration($name, $field)
    {
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$notnull;
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
     *           to not be set to null.
     * @return string DBMS specific SQL code portion that should be used to
     *       declare the specified field.
     * @access protected
     */
    function _getBooleanDeclaration($name, $field)
    {
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'boolean') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$default.$notnull;
    }

    // }}}
    // {{{ _getDateDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare a date type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name   name the field to be declared.
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      default
     *          Date value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access protected
     */
    function _getDateDeclaration($name, $field)
    {
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'date') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$default.$notnull;
    }

    // }}}
    // {{{ _getTimeDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare a time
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name   name the field to be declared.
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      default
     *          Time value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access protected
     */
    function _getTimeDeclaration($name, $field)
    {
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'time') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$default.$notnull;
    }

    // }}}
    // {{{ _getTimestampDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare a timestamp
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name name the field to be declared.
     * @param array $field associative array with the name of the properties
     *       of the field being declared as array indexes. Currently, the types
     *       of supported field properties are as follows:
     *
     *       default
     *           Timestamp value to be used as default for this field.
     *
     *       notnull
     *           Boolean flag that indicates whether this field is constrained
     *           to not be set to null.
     * @return string DBMS specific SQL code portion that should be used to
     *       declare the specified field.
     * @access protected
     */
    function _getTimestampDeclaration($name, $field)
    {
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'timestamp') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$default.$notnull;
    }

    // }}}
    // {{{ _getFloatDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare a float type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name   name the field to be declared.
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      default
     *          Float value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access protected
     */
    function _getFloatDeclaration($name, $field)
    {
        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'float') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$default.$notnull;
    }

    // }}}
    // {{{ _getDecimalDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare a decimal type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name   name the field to be declared.
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      default
     *          Decimal value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access protected
     */
    function _getDecimalDeclaration($name, $field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $default = array_key_exists('default', $field) ? ' DEFAULT '.
            $this->quote($field['default'], 'float') : '';
        $notnull = (array_key_exists('notnull', $field) && $field['notnull']) ? ' NOT NULL' : '';
        return $name.' '.$this->getTypeDeclaration($field).$default.$notnull;
    }

    // }}}
    // {{{ _quoteLOB()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param           $value
     * @return string text string that represents the given argument value in
     *      a DBMS specific format.
     * @access protected
     */
    function _quoteLOB($value)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $connect = $db->connect();
        if (PEAR::isError($connect)) {
            return $connect;
        }
        if (!$db->in_transaction && !@pg_query($db->connection, 'BEGIN')) {
            return $db->raiseError(MDB2_ERROR, null, null,
                'error starting transaction');
        }
        if (is_resource($value)) {
            $close = false;
        } elseif (preg_match('/^(\w+:\/\/)(.*)$/', $value, $match)) {
            $close = true;
            if ($match[1] == 'file://') {
                $value = $match[2];
            }
            // disabled use of pg_lo_import() for now with the following line
            $value = @fopen($value, 'r');
        } else {
            $close = true;
            $fp = @tmpfile();
            @fwrite($fp, $value);
            @rewind($fp);
            $value = $fp;
        }
        $result = false;
        if (is_resource($value)) {
            if (($lo = @pg_lo_create($db->connection))) {
                if (($handle = @pg_lo_open($db->connection, $lo, 'w'))) {
                    while (!@feof($value)) {
                        $data = @fread($value, $db->options['lob_buffer_length']);
                        if ($data === '') {
                            break;
                        }
                        if (!@pg_lo_write($handle, $data)) {
                            $result = $db->raiseError();
                            break;
                        }
                    }
                    if (!PEAR::isError($result)) {
                        $result = strval($lo);
                    }
                    @pg_lo_close($handle);
                } else {
                    $result = $db->raiseError();
                    @pg_lo_unlink($db->connection, $lo);
                }
            }
            if ($close) {
                @fclose($value);
            }
        } else {
            if (!@pg_lo_import($db->connection, $value)) {
                $result = $db->raiseError();
            }
        }
        if (!$db->in_transaction) {
            if (PEAR::isError($result)) {
                @pg_query($db->connection, 'ROLLBACK');
            } else {
                @pg_query($db->connection, 'COMMIT');
            }
        }
        return $result;
    }

    // }}}
    // {{{ _quoteCLOB()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param           $value
     * @return string text string that represents the given argument value in
     *      a DBMS specific format.
     * @access protected
     */
    function _quoteCLOB($value)
    {
        return $this->_quoteLOB($value);
    }

    // }}}
    // {{{ _quoteBLOB()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param           $value
     * @return string text string that represents the given argument value in
     *      a DBMS specific format.
     * @access protected
     */
    function _quoteBLOB($value)
    {
        return $this->_quoteLOB($value);
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
        return ($value ? "'t'" : "'f'");
    }

    // }}}
    // {{{ _quoteFloat()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @return string text string that represents the given argument value in
     *      a DBMS specific format.
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
     * @param string $value text string value that is intended to be converted.
     * @return string text string that represents the given argument value in
     *      a DBMS specific format.
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

    // }}}
    // {{{ writeLOBToFile()

    /**
     * retrieve LOB from the database
     *
     * @param resource $lob stream handle
     * @param string $file name of the file into which the LOb should be fetched
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access protected
     */
    function writeLOBToFile($lob, $file)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $lob_data = stream_get_meta_data($lob);
        $lob_index = $lob_data['wrapper_data']->lob_index;
        if (!pg_lo_export($db->connection, $this->lobs[$lob_index]['ressource'], $file)) {
            return $db->raiseError();
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ _retrieveLOB()

    /**
     * retrieve LOB from the database
     *
     * @param resource $lob stream handle
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access protected
     */
    function _retrieveLOB(&$lob)
    {
        if (!array_key_exists('handle', $lob)) {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            if (!$db->in_transaction) {
                if (!@pg_query($db->connection, 'BEGIN')) {
                    return $db->raiseError();
                }
                $lob['in_transaction'] = true;
            }
            $lob['handle'] = @pg_lo_open($db->connection, $lob['ressource'], 'r');
            if (!$lob['handle']) {
                if (array_key_exists('in_transaction', $lob)) {
                    @pg_query($db->connection, 'END');
                    unset($lob['in_transaction']);
                }
                return $db->raiseError();
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ _readLOB()

    /**
     * Read data from large object input stream.
     *
     * @param resource $lob stream handle
     * @param blob $data reference to a variable that will hold data to be
     *      read from the large object input stream
     * @param int $length integer value that indicates the largest ammount of
     *      data to be read from the large object input stream.
     * @return mixed length on success, a MDB2 error on failure
     * @access protected
     */
    function _readLOB($lob, $length)
    {
        $data = @pg_lo_read($lob['handle'], $length);
        if (!is_string($data)) {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }

            return $db->raiseError();
        }
        return $data;
    }

    // }}}
    // {{{ _destroyLOB()

    /**
     * Free any resources allocated during the lifetime of the large object
     * handler object.
     *
     * @param int $lob_index from the lob array
     * @access protected
     */
    function _destroyLOB($lob_index)
    {
        if (isset($this->lobs[$lob_index]['handle'])) {
            @pg_lo_close($this->lobs[$lob_index]['handle']);
            unset($this->lobs[$lob_index]['handle']);
            if (isset($this->lobs[$lob_index]['in_transaction'])) {
/*
for some reason this piece of code causes an apache crash
                $db =& $this->getDBInstance();
                if (PEAR::isError($db)) {
                    return $db;
                }

                @pg_query($db->connection, 'END');
*/
            }
        }
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
        $db_type = preg_replace('/\d/','', strtolower($field['typname']) );
        $length = $field['attlen'];
        if ($length == '-1') {
            $length = $field['atttypmod']-4;
        }
        if ((int)$length <= 0) {
            $length = null;
        }
        $type = array();
        switch ($db_type) {
        case 'int':
            $type[] = 'integer';
            if ($length == '1') {
                $type[] = 'boolean';
            }
            break;
        case 'bool':
            $type[] = 'boolean';
            $length = null;
            break;
        case 'text':
        case 'char':
        case 'varchar':
        case 'bpchar':
            $type[] = 'text';
            if ($length == '1') {
                $type[] = 'boolean';
            } elseif (strstr($db_type, 'text'))
                $type[] = 'clob';
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
        case 'money':
        case 'numeric':
            $type[] = 'decimal';
            break;
        case 'tinyblob':
        case 'mediumblob':
        case 'longblob':
        case 'blob':
            $type[] = 'blob';
            $length = null;
            break;
        case 'oid':
            $type[] = 'blob';
            $type[] = 'clob';
            $length = null;
            break;
        case 'year':
            $type[] = 'integer';
            $type[] = 'date';
            $length = null;
            break;
        default:
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }

            return $db->raiseError(MDB2_ERROR, null, null,
                'getTableFieldDefinition: unknown database attribute type');
        }

        return array($type, $length);
    }

    // }}}
}
?>
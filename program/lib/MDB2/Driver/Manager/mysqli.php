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

require_once 'MDB2/Driver/Manager/Common.php';

/**
 * MDB2 MySQLi driver for the management modules
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Manager_mysqli extends MDB2_Driver_Manager_Common
{
    // {{{ properties
    var $verified_table_types = array();#
    // }}}

    // }}}
    // {{{ _verifyTableType()

    /**
     * verify that chosen transactional table hanlder is available in the database
     *
     * @param string $table_type name of the table handler
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access protected
     */
    function _verifyTableType($table_type)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        switch (strtoupper($table_type)) {
        case 'BERKELEYDB':
        case 'BDB':
            $check = array('have_bdb');
            break;
        case 'INNODB':
            $check = array('have_innobase', 'have_innodb');
            break;
        case 'GEMINI':
            $check = array('have_gemini');
            break;
        case 'HEAP':
        case 'ISAM':
        case 'MERGE':
        case 'MRG_MYISAM':
        case 'MYISAM':
        case '':
            return MDB2_OK;
        default:
            return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                $table_type.' is not a supported table type');
        }
        if (isset($this->verified_table_types[$table_type])
            && $this->verified_table_types[$table_type] == $db->connection
        ) {
            return MDB2_OK;
        }
        $not_supported = false;
        for ($i = 0, $j = count($check); $i < $j; ++$i) {
            $query = 'SHOW VARIABLES LIKE '.$db->quote($check[$i], 'text');
            $has = $db->queryRow($query, null, MDB2_FETCHMODE_ORDERED);
            if (PEAR::isError($has)) {
                return $has;
            }
            if (is_array($has)) {
                $not_supported = true;
                if ($has[1] == 'YES') {
                    $this->verified_table_types[$table_type] = $db->connection;
                    return MDB2_OK;
                }
            }
        }
        if ($not_supported) {
            return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                $table_type.' is not a supported table type by this MySQL database server');
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'could not tell if '.$table_type.' is a supported table type');
    }

    // }}}
    // {{{ createDatabase()

    /**
     * create a new database
     *
     * @param string $name name of the database that should be created
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'CREATE DATABASE '.$name;
        $result = $db->query($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ dropDatabase()

    /**
     * drop an existing database
     *
     * @param string $name name of the database that should be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'DROP DATABASE '.$name;
        $result = $db->query($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ createTable()

    /**
     * create a new table
     *
     * @param string $name     Name of the database that should be created
     * @param array $fields Associative array that contains the definition of each field of the new table
     *                        The indexes of the array entries are the names of the fields of the table an
     *                        the array entry values are associative arrays like those that are meant to be
     *                         passed with the field definitions to get[Type]Declaration() functions.
     *
     *                        Example
     *                        array(
     *
     *                            'id' => array(
     *                                'type' => 'integer',
     *                                'unsigned' => 1
     *                                'notnull' => 1
     *                                'default' => 0
     *                            ),
     *                            'name' => array(
     *                                'type' => 'text',
     *                                'length' => 12
     *                            ),
     *                            'password' => array(
     *                                'type' => 'text',
     *                                'length' => 12
     *                            )
     *                        );
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createTable($name, $fields)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        if (!$name) {
            return $db->raiseError(MDB2_ERROR_CANNOT_CREATE, null, null,
                'createTable: no valid table name specified');
        }
        if (empty($fields)) {
            return $db->raiseError(MDB2_ERROR_CANNOT_CREATE, null, null,
                'createTable: no fields specified for table "'.$name.'"');
        }
        $verify = $this->_verifyTableType($db->options['default_table_type']);
        if (PEAR::isError($verify)) {
            return $verify;
        }
        $query_fields = $this->getFieldDeclarationList($fields);
        if (PEAR::isError($query_fields)) {
            return $db->raiseError(MDB2_ERROR_CANNOT_CREATE, null, null,
                'createTable: '.$this->getUserinfo());
        }
        $query = "CREATE TABLE $name ($query_fields)".(strlen($db->options['default_table_type'])
            ? ' TYPE='.$db->options['default_table_type'] : '');

        return $db->query($query);
    }

    // }}}
    // {{{ alterTable()

    /**
     * alter an existing table
     *
     * @param string $name         name of the table that is intended to be changed.
     * @param array $changes     associative array that contains the details of each type
     *                             of change that is intended to be performed. The types of
     *                             changes that are currently supported are defined as follows:
     *
     *                             name
     *
     *                                New name for the table.
     *
     *                            add
     *
     *                                Associative array with the names of fields to be added as
     *                                 indexes of the array. The value of each entry of the array
     *                                 should be set to another associative array with the properties
     *                                 of the fields to be added. The properties of the fields should
     *                                 be the same as defined by the Metabase parser.
     *
     *
     *                            remove
     *
     *                                Associative array with the names of fields to be removed as indexes
     *                                 of the array. Currently the values assigned to each entry are ignored.
     *                                 An empty array should be used for future compatibility.
     *
     *                            rename
     *
     *                                Associative array with the names of fields to be renamed as indexes
     *                                 of the array. The value of each entry of the array should be set to
     *                                 another associative array with the entry named name with the new
     *                                 field name and the entry named Declaration that is expected to contain
     *                                 the portion of the field declaration already in DBMS specific SQL code
     *                                 as it is used in the CREATE TABLE statement.
     *
     *                            change
     *
     *                                Associative array with the names of the fields to be changed as indexes
     *                                 of the array. Keep in mind that if it is intended to change either the
     *                                 name of a field and any other properties, the change array entries
     *                                 should have the new names of the fields as array indexes.
     *
     *                                The value of each entry of the array should be set to another associative
     *                                 array with the properties of the fields to that are meant to be changed as
     *                                 array entries. These entries should be assigned to the new values of the
     *                                 respective properties. The properties of the fields should be the same
     *                                 as defined by the Metabase parser.
     *
     *                            Example
     *                                array(
     *                                    'name' => 'userlist',
     *                                    'add' => array(
     *                                        'quota' => array(
     *                                            'type' => 'integer',
     *                                            'unsigned' => 1
     *                                        )
     *                                    ),
     *                                    'remove' => array(
     *                                        'file_limit' => array(),
     *                                        'time_limit' => array()
     *                                        ),
     *                                    'change' => array(
     *                                        'gender' => array(
     *                                            'default' => 'M',
     *                                        )
     *                                    ),
     *                                    'rename' => array(
     *                                        'sex' => array(
     *                                            'name' => 'gender',
     *                                        )
     *                                    )
     *                                )
     *
     * @param boolean $check     indicates whether the function should just check if the DBMS driver
     *                             can perform the requested table alterations if the value is true or
     *                             actually perform them otherwise.
     * @access public
     *
      * @return mixed MDB2_OK on success, a MDB2 error on failure
     */
    function alterTable($name, $changes, $check)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        foreach ($changes as $change_name => $change) {
            switch ($change_name) {
            case 'add':
            case 'remove':
            case 'change':
            case 'rename':
            case 'name':
                break;
            default:
                return $db->raiseError(MDB2_ERROR_CANNOT_ALTER, null, null,
                    'alterTable: change type "'.$change_name.'" not yet supported');
            }
        }

        if ($check) {
            return MDB2_OK;
        }

        $query = (array_key_exists('name', $changes) ? 'RENAME AS '.$changes['name'] : '');

        if (array_key_exists('add', $changes)) {
            foreach ($changes['add'] as $field_name => $field) {
                $type_declaration = $db->getDeclaration($field['type'], $field_name, $field);
                if (PEAR::isError($type_declaration)) {
                    return $err;
                }
                if ($query) {
                    $query.= ', ';
                }
                $query.= 'ADD ' . $type_declaration;
            }
        }

        if (array_key_exists('remove', $changes)) {
            foreach ($changes['remove'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                }
                $query.= 'DROP ' . $field_name;
            }
        }

        $rename = array();
        if (array_key_exists('rename', $changes)) {
            foreach ($changes['rename'] as $field_name => $field) {
                $rename[$field['name']] = $field_name;
            }
        }

        if (array_key_exists('change', $changes)) {
            foreach ($changes['change'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                }
                if (isset($rename[$field_name])) {
                    $old_field_name = $rename[$field_name];
                    unset($rename[$field_name]);
                } else {
                    $old_field_name = $field_name;
                }
                $query.= "CHANGE $field_name " . $db->getDeclaration($field['type'], $old_field_name, $field);
            }
        }

        if (!empty($rename)) {
            foreach ($rename as $rename_name => $renamed_field) {
                if ($query) {
                    $query.= ', ';
                }
                $old_field_name = $renamed_field;
                $field = $changes['rename'][$old_field_name];
                $query.= 'CHANGE ' . $db->getDeclaration($field['type'], $old_field_name, $field);
            }
        }

        if (!$query) {
            return MDB2_OK;
        }

        return $db->query("ALTER TABLE $name $query");
    }

    // }}}
    // {{{ listDatabases()

    /**
     * list all databases
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listDatabases()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $databases = $db->queryCol('SHOW DATABASES');
        return $databases;
    }

    // }}}
    // {{{ listUsers()

    /**
     * list all users
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listUsers()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $users = $db->queryCol('SELECT DISTINCT USER FROM USER');
        return $users;
    }

    // }}}
    // {{{ listTables()

    /**
     * list all tables in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listTables()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $table_names = $db->queryCol('SHOW TABLES');
        if (PEAR::isError($table_names)) {
            return $table_names;
        }

        $tables = array();
        for ($i = 0, $j = count($table_names); $i < $j; ++$i) {
            if (!$this->_isSequenceName($table_names[$i]))
                $tables[] = $table_names[$i];
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $tables = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $tables);
        }

        return $tables;
    }

    // }}}
    // {{{ listTableFields()

    /**
     * list all fields in a tables in the current database
     *
     * @param string $table name of table that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listTableFields($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $fields = $db->queryCol("SHOW COLUMNS FROM $table");
        if (PEAR::isError($fields)) {
            return $fields;
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $fields = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $fields);
        }

        return $fields;
    }

    // }}}
    // {{{ createIndex()

    /**
     * get the stucture of a field into an array
     *
     * @param string    $table         name of the table on which the index is to be created
     * @param string    $name         name of the index to be created
     * @param array     $definition        associative array that defines properties of the index to be created.
     *                                 Currently, only one property named FIELDS is supported. This property
     *                                 is also an associative with the names of the index fields as array
     *                                 indexes. Each entry of this array is set to another type of associative
     *                                 array that specifies properties of the index that are specific to
     *                                 each field.
     *
     *                                Currently, only the sorting property is supported. It should be used
     *                                 to define the sorting direction of the index. It may be set to either
     *                                 ascending or descending.
     *
     *                                Not all DBMS support index sorting direction configuration. The DBMS
     *                                 drivers of those that do not support it ignore this property. Use the
     *                                 function supports() to determine whether the DBMS driver can manage indexes.

     *                                 Example
     *                                    array(
     *                                        'fields' => array(
     *                                            'user_name' => array(
     *                                                'sorting' => 'ascending'
     *                                            ),
     *                                            'last_login' => array()
     *                                        )
     *                                    )
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createIndex($table, $name, $definition)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        if (array_key_exists('primary', $definition) && $definition['primary']) {
            $type = 'PRIMARY';
            $name = 'KEY';
        } elseif (array_key_exists('unique', $definition) && $definition['unique']) {
            $type = 'UNIQUE';
        } else {
            $type = 'INDEX';
        }

        $query = "ALTER TABLE $table ADD $type $name (";
        $query.= implode(', ', array_keys($definition['fields']));
        $query.= ')';

        return $db->query($query);
    }

    // }}}
    // {{{ dropIndex()

    /**
     * drop existing index
     *
     * @param string    $table         name of table that should be used in method
     * @param string    $name         name of the index to be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropIndex($table, $name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->query("ALTER TABLE $table DROP INDEX $name");
    }

    // }}}
    // {{{ listTableIndexes()

    /**
     * list all indexes in a table
     *
     * @param string    $table      name of table that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listTableIndexes($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $key_name = 'Key_name';
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $key_name = strtolower($key_name);
            } else {
                $key_name = strtoupper($key_name);
            }
        }

        $query = "SHOW INDEX FROM $table";
        $indexes_all = $db->queryCol($query, 'text', $key_name);
        if (PEAR::isError($indexes_all)) {
            return $indexes_all;
        }

        $indexes = array_unique($indexes_all);

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $indexes = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $indexes);
        }

        return $indexes;
    }

    // }}}
    // {{{ createSequence()

    /**
     * create sequence
     *
     * @param string    $seq_name     name of the sequence to be created
     * @param string    $start         start value of the sequence; default is 1
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createSequence($seq_name, $start = 1)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->getSequenceName($seq_name);
        $seqcol_name = $db->options['seqcol_name'];
        $result = $this->_verifyTableType($db->options['default_table_type']);
        if (PEAR::isError($result)) {
            return $result;
        }

        $res = $db->query("CREATE TABLE $sequence_name".
            "($seqcol_name INT NOT NULL AUTO_INCREMENT, PRIMARY KEY ($seqcol_name))".
            (strlen($db->options['default_table_type']) ? ' TYPE='.$db->options['default_table_type'] : '')
        );

        if (PEAR::isError($res)) {
            return $res;
        }

        if ($start == 1) {
            return MDB2_OK;
        }

        $res = $db->query("INSERT INTO $sequence_name ($seqcol_name) VALUES (".($start-1).')');
        if (!PEAR::isError($res)) {
            return MDB2_OK;
        }

        // Handle error
        $result = $db->query("DROP TABLE $sequence_name");
        if (PEAR::isError($result)) {
            return $db->raiseError(MDB2_ERROR, null, null,
                'createSequence: could not drop inconsistent sequence table ('.
                $result->getMessage().' ('.$result->getUserinfo().'))');
        }

        return $db->raiseError(MDB2_ERROR, null, null,
            'createSequence: could not create sequence table ('.
            $res->getMessage().' ('.$res->getUserinfo().'))');
    }

    // }}}
    // {{{ dropSequence()

    /**
     * drop existing sequence
     *
     * @param string    $seq_name     name of the sequence to be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropSequence($seq_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->getSequenceName($seq_name);
        return $db->query("DROP TABLE $sequence_name");
    }

    // }}}
    // {{{ listSequences()

    /**
     * list all sequences in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listSequences()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $table_names = $db->queryCol('SHOW TABLES');
        if (PEAR::isError($table_names)) {
            return $table_names;
        }

        $sequences = array();
        for ($i = 0, $j = count($table_names); $i < $j; ++$i) {
            if ($sqn = $this->_isSequenceName($table_names[$i])) {
                $sequences[] = $sqn;
            }
        }

        return $sequences;
    }

    // }}}
}
?>
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

// $Id$

require_once 'MDB2/Driver/Manager/Common.php';

/**
 * MDB2 oci8 driver for the management modules
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Manager_oci8 extends MDB2_Driver_Manager_Common
{
    // {{{ createDatabase()

    /**
     * create a new database
     *
     * @param object $db database object that is extended by this class
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

        $username = $db->options['database_name_prefix'].$name;
        $password = $db->dsn['password'] ? $db->dsn['password'] : $name;
        $tablespace = $db->options['default_tablespace']
            ? ' DEFAULT TABLESPACE '.$db->options['default_tablespace'] : '';

        $query = 'CREATE USER '.$username.' IDENTIFIED BY '.$password.$tablespace;
        $result = $db->standaloneQuery($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        $query = 'GRANT CREATE SESSION, CREATE TABLE, UNLIMITED TABLESPACE, CREATE SEQUENCE TO '.$username;
        $result = $db->standaloneQuery($query);
        if (PEAR::isError($result)) {
            $query = 'DROP USER '.$username.' CASCADE';
            $result2 = $db->standaloneQuery($query);
            if (PEAR::isError($result2)) {
                return $db->raiseError(MDB2_ERROR, null, null,
                    'createDatabase: could not setup the database user ('.$result->getUserinfo().
                        ') and then could drop its records ('.$result2->getUserinfo().')');
            }
            return $result;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ dropDatabase()

    /**
     * drop an existing database
     *
     * @param object $db database object that is extended by this class
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

        $username = $db->options['database_name_prefix'].$name;
        return $db->standaloneQuery('DROP USER '.$username.' CASCADE');
    }

    function _makeAutoincrement($name, $table, $start = 1)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $index_name  = $table . '_autoincrement_pk';
        $definition = array(
            'primary' => true,
            'fields' => array($name),
        );
        $result = $db->manager->createIndex($table, $index_name, $definition);
        if (PEAR::isError($result)) {
            return $db->raiseError(MDB2_ERROR, null, null,
                '_makeAutoincrement: primary key for autoincrement PK could not be created');
        }

        $result = $db->manager->createSequence($table, $start);
        if (PEAR::isError($result)) {
            return $db->raiseError(MDB2_ERROR, null, null,
                '_makeAutoincrement: sequence for autoincrement PK could not be created');
        }

        $sequence_name = $db->getSequenceName($table);
        $trigger_name  = $table . '_autoincrement_pk';
        $trigger_sql = "CREATE TRIGGER $trigger_name BEFORE INSERT ON $table";
        $trigger_sql.= " FOR EACH ROW BEGIN IF (:new.$name IS NULL) THEN SELECT ";
        $trigger_sql.= "$sequence_name.NEXTVAL INTO :new.$name FROM DUAL; END IF; END;";

        return $db->query($trigger_sql);
    }

    function _dropAutoincrement($name, $table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $result = $db->manager->dropSequence($table);
        if (PEAR::isError($result)) {
            return $db->raiseError(MDB2_ERROR, null, null,
                '_dropAutoincrement: sequence for autoincrement PK could not be dropped');
        }

        $sequence_name = $db->getSequenceName($table);
        $trigger_name  = $table . '_autoincrement_pk';
        $trigger_sql = 'DROP TRIGGER ' . $trigger_name;

        return $db->query($trigger_sql);
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
        $result = parent::createTable($name, $fields);
        if (PEAR::isError($result)) {
            return $result;
        }
        foreach($fields as $field_name => $field) {
            if (array_key_exists('autoincrement', $field) && $field['autoincrement']) {
                return $this->_makeAutoincrement($field_name, $name);
            }
        }
    }

    // }}}
    // {{{ alterTable()

    /**
     * alter an existing table
     *
     * @param object $db database object that is extended by this class
     * @param string $name name of the table that is intended to be changed.
     * @param array $changes associative array that contains the details of each type
     *                              of change that is intended to be performed. The types of
     *                              changes that are currently supported are defined as follows:
     *
     *                              name
     *
     *                                 New name for the table.
     *
     *                             add
     *
     *                                 Associative array with the names of fields to be added as
     *                                  indexes of the array. The value of each entry of the array
     *                                  should be set to another associative array with the properties
     *                                  of the fields to be added. The properties of the fields should
     *                                  be the same as defined by the Metabase parser.
     *
     *
     *                             remove
     *
     *                                 Associative array with the names of fields to be removed as indexes
     *                                  of the array. Currently the values assigned to each entry are ignored.
     *                                  An empty array should be used for future compatibility.
     *
     *                             rename
     *
     *                                 Associative array with the names of fields to be renamed as indexes
     *                                  of the array. The value of each entry of the array should be set to
     *                                  another associative array with the entry named name with the new
     *                                  field name and the entry named Declaration that is expected to contain
     *                                  the portion of the field declaration already in DBMS specific SQL code
     *                                  as it is used in the CREATE TABLE statement.
     *
     *                             change
     *
     *                                 Associative array with the names of the fields to be changed as indexes
     *                                  of the array. Keep in mind that if it is intended to change either the
     *                                  name of a field and any other properties, the change array entries
     *                                  should have the new names of the fields as array indexes.
     *
     *                                 The value of each entry of the array should be set to another associative
     *                                  array with the properties of the fields to that are meant to be changed as
     *                                  array entries. These entries should be assigned to the new values of the
     *                                  respective properties. The properties of the fields should be the same
     *                                  as defined by the Metabase parser.
     *
     *                             Example
     *                                 array(
     *                                     'name' => 'userlist',
     *                                     'add' => array(
     *                                         'quota' => array(
     *                                             'type' => 'integer',
     *                                             'unsigned' => 1
     *                                         )
     *                                     ),
     *                                     'remove' => array(
     *                                         'file_limit' => array(),
     *                                         'time_limit' => array()
     *                                         ),
     *                                     'change' => array(
     *                                         'gender' => array(
     *                                             'default' => 'M',
     *                                         )
     *                                     ),
     *                                     'rename' => array(
     *                                         'sex' => array(
     *                                             'name' => 'gender',
     *                                         )
     *                                     )
     *                                 )
     * @param boolean $check indicates whether the function should just check if the DBMS driver
     *                              can perform the requested table alterations if the value is true or
     *                              actually perform them otherwise.
     * @access public
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
            case 'name':
                break;
            case 'rename':
            default:
                return $db->raiseError(MDB2_ERROR, null, null,
                    'alterTable: change type "'.$change_name.'" not yet supported');
            }
        }

        if ($check) {
            return MDB2_OK;
        }

        if (array_key_exists('remove', $changes)) {
            $query = ' DROP (';
            $fields = $changes['remove'];
            $query.= implode(', ', array_keys($fields));
            $query.= ')';
            if (PEAR::isError($result = $db->query("ALTER TABLE $name $query"))) {
                return $result;
            }
            $query = '';
        }

        $query = (array_key_exists('name', $changes) ? 'RENAME TO '.$changes['name'] : '');

        if (array_key_exists('add', $changes)) {
            foreach ($changes['add'] as $field_name => $field) {
                $type_declaration = $db->getDeclaration($field['type'], $field_name, $field);
                if (PEAR::isError($type_declaration)) {
                    return $err;
                }
                $query.= ' ADD (' . $type_declaration . ')';
            }
        }

        if (array_key_exists('change', $changes)) {
            foreach ($changes['change'] as $field_name => $field) {
                $query.= "MODIFY ($field_name " . $db->getDeclaration($field['type'], $field_name, $field).')';
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

        if ($db->options['database_name_prefix']) {
            $query = 'SELECT SUBSTR(username, '
                .(strlen($db->options['database_name_prefix'])+1)
                .") FROM sys.dba_users WHERE username LIKE '"
                .$db->options['database_name_prefix']."%'";
        } else {
            $query = 'SELECT username FROM sys.dba_users';
        }
        $result = $db->standaloneQuery($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        $databases = $result->fetchCol();
        if (PEAR::isError($databases)) {
            return $databases;
        }
        // is it legit to force this to lowercase?
        $databases = array_keys(array_change_key_case(array_flip($databases), $db->options['field_case']));
        $result->free();
        return $databases;
    }

        // }}}
    // {{{ listUsers()

    /**
     * list all users in the current database
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

        $query = 'SELECT username FROM sys.all_users';
        $users = $db->queryCol($query);
        if (PEAR::isError($users)) {
            return $users;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            && $db->options['field_case'] == CASE_LOWER
        ) {
            $users = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $users);
        }
        return $users;
    }
    // }}}
    // {{{ listViews()

    /**
     * list all views in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listViews()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT view_name FROM sys.user_views';
        $views = $db->queryCol($query);
        if (PEAR::isError($views)) {
            return $views;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            && $db->options['field_case'] == CASE_LOWER
        ) {
            $views = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $views);
        }
        return $views;
    }

    // }}}
    // {{{ listFunctions()

    /**
     * list all functions in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listFunctions()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT name FROM sys.user_source WHERE line = 1 AND type = 'FUNCTION'";
        $functions = $db->queryCol($query);
        if (PEAR::isError($functions)) {
            return $functions;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            && $db->options['field_case'] == CASE_LOWER
        ) {
            $functions = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $functions);
        }
        return $functions;
    }

    // }}}
    // {{{ listTables()

    /**
     * list all tables in the current database
     *
     * @return mixed data array on success, a MDB error on failure
     * @access public
     **/
    function listTables()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT table_name FROM sys.user_tables';
        return $db->queryCol($query);
    }

    // }}}
    // {{{ listTableFields()

    /**
     * list all fields in a tables in the current database
     *
     * @param string $table name of table that should be used in method
     * @return mixed data array on success, a MDB error on failure
     * @access public
     */
    function listTableFields($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $table = strtoupper($table);
        $query = "SELECT column_name FROM user_tab_columns WHERE table_name='$table' ORDER BY column_id";
        $fields = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            && $db->options['field_case'] == CASE_LOWER
        ) {
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
     *
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
            $query = "ALTER TABLE $table ADD CONSTRAINT $name PRIMARY KEY (";
        } else {
            $query = 'CREATE';
            if (array_key_exists('unique', $definition) && $definition['unique']) {
                $query.= ' UNIQUE';
            }
            $query .= " INDEX $name ON $table (";
        }
        $query .= implode(', ', array_keys($definition['fields'])) . ')';

        return $db->query($query);
    }

    // }}}
    // {{{ createSequence()

    /**
     * create sequence
     *
     * @param object $db database object that is extended by this class
     * @param string $seq_name name of the sequence to be created
     * @param string $start start value of the sequence; default is 1
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
        return $db->query("CREATE SEQUENCE $sequence_name START WITH $start INCREMENT BY 1".
            ($start < 1 ? " MINVALUE $start" : ''));
    }

    // }}}
    // {{{ dropSequence()

    /**
     * drop existing sequence
     *
     * @param object $db database object that is extended by this class
     * @param string $seq_name name of the sequence to be dropped
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
        return $db->query("DROP SEQUENCE $sequence_name");
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

        $query = "SELECT sequence_name FROM sys.user_sequences";
        $table_names = $db->queryCol($query);
        if (PEAR::isError($table_names)) {
            return $table_names;
        }
        $sequences = array();
        for ($i = 0, $j = count($table_names); $i < $j; ++$i) {
            if ($sqn = $this->_isSequenceName($table_names[$i]))
                $sequences[] = $sqn;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            && $db->options['field_case'] == CASE_LOWER
        ) {
            $sequences = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $sequences);
        }
        return $sequences;
    }}
?>
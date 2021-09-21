<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Database wrapper class that implements PHP PDO functions            |
 |   for PostgreSQL database                                             |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Database independent query interface
 * This is a wrapper for the PHP PDO
 *
 * @package    Framework
 * @subpackage Database
 */
class rcube_db_pgsql extends rcube_db
{
    public $db_provider = 'postgres';

    // See https://www.postgresql.org/docs/current/static/libpq-connect.html#LIBPQ-PARAMKEYWORDS
    private static $libpq_connect_params = [
        'application_name',
        'sslmode',
        'sslcert',
        'sslkey',
        'sslrootcert',
        'sslcrl',
        'sslcompression',
        'service'
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct($db_dsnw, $db_dsnr = '', $pconn = false)
    {
        parent::__construct($db_dsnw, $db_dsnr, $pconn);

        // use date/time input format with timezone spec.
        $this->options['datetime_format'] = 'c';
    }

    /**
     * Driver-specific configuration of database connection
     *
     * @param array $dsn DSN for DB connections
     * @param PDO   $dbh Connection handler
     */
    protected function conn_configure($dsn, $dbh)
    {
        $dbh->query("SET NAMES 'utf8'");
        $dbh->query("SET DATESTYLE TO ISO");

        // if ?schema= is set in dsn, set the search_path
        if (!empty($dsn['schema'])) {
            $dbh->query("SET search_path TO " . $this->quote($dsn['schema']));
        }
    }

    /**
     * Get last inserted record ID
     *
     * @param string $table Table name (to find the incremented sequence)
     *
     * @return mixed ID or false on failure
     */
    public function insert_id($table = null)
    {
        if (!$this->db_connected || $this->db_mode == 'r') {
            return false;
        }

        if ($table) {
            $table = $this->sequence_name($table);
        }

        return $this->dbh->lastInsertId($table);
    }

    /**
     * Return correct name for a specific database sequence
     *
     * @param string $table Table name
     *
     * @return string Translated sequence name
     */
    protected function sequence_name($table)
    {
        // Note: we support only one sequence per table
        // Note: The sequence name must be <table_name>_seq
        $sequence = $table . '_seq';

        // modify sequence name if prefix is configured
        if ($prefix = $this->options['table_prefix']) {
            return $prefix . $sequence;
        }

        return $sequence;
    }

    /**
     * Return SQL statement to convert a field value into a unix timestamp
     *
     * @param string $field Field name
     *
     * @return string SQL statement to use in query
     * @deprecated
     */
    public function unixtimestamp($field)
    {
        return "EXTRACT (EPOCH FROM $field)";
    }

    /**
     * Return SQL function for current time and date
     *
     * @param int $interval Optional interval (in seconds) to add/subtract
     *
     * @return string SQL function to use in query
     */
    public function now($interval = 0)
    {
        $result = 'now()';

        if ($interval) {
            $result .= ' ' . ($interval > 0 ? '+' : '-') . " interval '"
                . ($interval > 0 ? intval($interval) : intval($interval) * -1)
                . " seconds'";
        }

        return $result;
    }

    /**
     * Return SQL statement for case insensitive LIKE
     *
     * @param string $column Field name
     * @param string $value  Search value
     *
     * @return string SQL statement to use in query
     */
    public function ilike($column, $value)
    {
        return $this->quote_identifier($column) . ' ILIKE ' . $this->quote($value);
    }

    /**
     * Get database runtime variables
     *
     * @param string $varname Variable name
     * @param mixed  $default Default value if variable is not set
     *
     * @return mixed Variable value or default
     */
    public function get_variable($varname, $default = null)
    {
        // There's a known case when max_allowed_packet is queried
        // PostgreSQL doesn't have such limit, return immediately
        if ($varname == 'max_allowed_packet') {
            return rcube::get_instance()->config->get('db_' . $varname, $default);
        }

        $this->variables[$varname] = rcube::get_instance()->config->get('db_' . $varname);

        if (!isset($this->variables)) {
            $this->variables = [];

            $result = $this->query('SHOW ALL');

            while ($row = $this->fetch_array($result)) {
                $this->variables[$row[0]] = $row[1];
            }
        }

        return $this->variables[$varname] ?? $default;
    }

    /**
     * INSERT ... ON CONFLICT DO UPDATE.
     * When not supported by the engine we do UPDATE and INSERT.
     *
     * @param string $table   Table name (should be already passed via table_name() with quoting)
     * @param array  $keys    Hash array (column => value) of the unique constraint
     * @param array  $columns List of columns to update
     * @param array  $values  List of values to update (number of elements
     *                        should be the same as in $columns)
     *
     * @return PDOStatement|bool Query handle or False on error
     * @todo Multi-insert support
     */
    public function insert_or_update($table, $keys, $columns, $values)
    {
        // Check if version >= 9.5, otherwise use fallback
        if ($this->get_variable('server_version_num') < 90500) {
            return parent::insert_or_update($table, $keys, $columns, $values);
        }

        $columns = array_map([$this, 'quote_identifier'], $columns);
        $target  = implode(', ', array_map([$this, 'quote_identifier'], array_keys($keys)));
        $cols    = $target . ', ' . implode(', ', $columns);
        $vals    = implode(', ', array_map(function($i) { return $this->quote($i); }, $keys));
        $vals   .= ', ' . rtrim(str_repeat('?, ', count($columns)), ', ');
        $update  = implode(', ', array_map(function($i) { return "$i = EXCLUDED.$i"; }, $columns));

        return $this->query("INSERT INTO $table ($cols) VALUES ($vals)"
            . " ON CONFLICT ($target) DO UPDATE SET $update", $values);
    }

    /**
     * Returns list of tables in a database
     *
     * @return array List of all tables of the current database
     */
    public function list_tables()
    {
        // get tables if not cached
        if ($this->tables === null) {
            if (($schema = $this->options['table_prefix']) && $schema[strlen($schema)-1] === '.') {
                $add = " AND TABLE_SCHEMA = " . $this->quote(substr($schema, 0, -1));
            }
            else {
                $add = " AND TABLE_SCHEMA NOT IN ('pg_catalog', 'information_schema')";
            }

            $q = $this->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES"
                . " WHERE TABLE_TYPE = 'BASE TABLE'" . $add
                . " ORDER BY TABLE_NAME");

            $this->tables = $q ? $q->fetchAll(PDO::FETCH_COLUMN, 0) : [];
        }

        return $this->tables;
    }

    /**
     * Returns list of columns in database table
     *
     * @param string $table Table name
     *
     * @return array List of table cols
     */
    public function list_cols($table)
    {
        $args = [$table];

        if (($schema = $this->options['table_prefix']) && $schema[strlen($schema)-1] === '.') {
            $add    = " AND TABLE_SCHEMA = ?";
            $args[] = substr($schema, 0, -1);
        }
        else {
            $add = " AND TABLE_SCHEMA NOT IN ('pg_catalog', 'information_schema')";
        }

        $q = $this->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS"
            . " WHERE TABLE_NAME = ?" . $add, $args);

        if ($q) {
            return $q->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        return [];
    }

    /**
     * Returns PDO DSN string from DSN array
     *
     * @param array $dsn DSN parameters
     *
     * @return string DSN string
     */
    protected function dsn_string($dsn)
    {
        $params = [];
        $result = 'pgsql:';

        if (isset($dsn['hostspec'])) {
            $params[] = 'host=' . $dsn['hostspec'];
        }
        else if (isset($dsn['socket'])) {
            $params[] = 'host=' . $dsn['socket'];
        }

        if (isset($dsn['port'])) {
            $params[] = 'port=' . $dsn['port'];
        }

        if (isset($dsn['database'])) {
            $params[] = 'dbname=' . $dsn['database'];
        }

        foreach (self::$libpq_connect_params as $param) {
            if (isset($dsn[$param])) {
                $params[] = $param . '=' . $dsn[$param];
            }
        }

        if (!empty($params)) {
            $result .= implode(';', $params);
        }

        return $result;
    }

    /**
     * Parse SQL file and fix table names according to table prefix
     */
    protected function fix_table_names($sql)
    {
        if (!$this->options['table_prefix']) {
            return $sql;
        }

        $sql = parent::fix_table_names($sql);

        // replace sequence names, and other postgres-specific commands
        $sql = preg_replace_callback(
            '/((SEQUENCE |RENAME TO |nextval\()["\']*)([^"\' \r\n]+)/',
            [$this, 'fix_table_names_callback'],
            $sql
        );

        return $sql;
    }
}

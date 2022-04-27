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
 |   for MySQL database                                                  |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Database independent query interface
 *
 * This is a wrapper for the PHP PDO
 *
 * @package    Framework
 * @subpackage Database
 */
class rcube_db_mysql extends rcube_db
{
    public $db_provider = 'mysql';

    /**
     * {@inheritdoc}
     */
    public function __construct($db_dsnw, $db_dsnr = '', $pconn = false)
    {
        parent::__construct($db_dsnw, $db_dsnr, $pconn);

        // SQL identifiers quoting
        $this->options['identifier_start'] = '`';
        $this->options['identifier_end'] = '`';
    }

    /**
     * Abstract SQL statement for value concatenation
     *
     * @return string ...$args Values to concatenate
     */
    public function concat(...$args)
    {
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }

        return 'CONCAT(' . implode(', ', $args) . ')';
    }

    /**
     * Returns PDO DSN string from DSN array
     *
     * @param array $dsn DSN parameters
     *
     * @return string Connection string
     */
    protected function dsn_string($dsn)
    {
        $params = [];
        $result = 'mysql:';

        if (isset($dsn['database'])) {
            $params[] = 'dbname=' . $dsn['database'];
        }

        if (isset($dsn['hostspec'])) {
            $params[] = 'host=' . $dsn['hostspec'];
        }

        if (isset($dsn['port'])) {
            $params[] = 'port=' . $dsn['port'];
        }

        if (isset($dsn['socket'])) {
            $params[] = 'unix_socket=' . $dsn['socket'];
        }

        $params[] = 'charset=' . (!empty($dsn['charset']) ? $dsn['charset'] : 'utf8mb4');

        if (!empty($params)) {
            $result .= implode(';', $params);
        }

        return $result;
    }

    /**
     * Returns driver-specific connection options
     *
     * @param array $dsn DSN parameters
     *
     * @return array Connection options
     */
    protected function dsn_options($dsn)
    {
        $result = parent::dsn_options($dsn);

        if (!empty($dsn['key'])) {
            $result[PDO::MYSQL_ATTR_SSL_KEY] = $dsn['key'];
        }

        if (!empty($dsn['cipher'])) {
            $result[PDO::MYSQL_ATTR_SSL_CIPHER] = $dsn['cipher'];
        }

        if (!empty($dsn['cert'])) {
            $result[PDO::MYSQL_ATTR_SSL_CERT] = $dsn['cert'];
        }

        if (!empty($dsn['capath'])) {
            $result[PDO::MYSQL_ATTR_SSL_CAPATH] = $dsn['capath'];
        }

        if (!empty($dsn['ca'])) {
            $result[PDO::MYSQL_ATTR_SSL_CA] = $dsn['ca'];
        }

        if (isset($dsn['verify_server_cert'])) {
            $result[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = rcube_utils::get_boolean($dsn['verify_server_cert']);
        }

        // Always return matching (not affected only) rows count
        $result[PDO::MYSQL_ATTR_FOUND_ROWS] = true;

        // Enable AUTOCOMMIT mode (#1488902)
        $result[PDO::ATTR_AUTOCOMMIT] = true;

        // Disable emulating of prepared statements
        $result[PDO::ATTR_EMULATE_PREPARES] = false;

        return $result;
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
            $q = $this->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES"
                . " WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'"
                . " ORDER BY TABLE_NAME", $this->db_dsnw_array['database']);

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
        $q = $this->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS"
            . " WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
            $this->db_dsnw_array['database'], $table);

        if ($q) {
            return $q->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        return [];
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
        if (!isset($this->variables)) {
            $this->variables = [];
        }

        if (array_key_exists($varname, $this->variables)) {
            return $this->variables[$varname];
        }

        // configured value has higher prio
        $conf_value = rcube::get_instance()->config->get('db_' . $varname);
        if ($conf_value !== null) {
            return $this->variables[$varname] = $conf_value;
        }

        $result = $this->query('SHOW VARIABLES LIKE ?', $varname);

        while ($row = $this->fetch_array($result)) {
            $this->variables[$row[0]] = $row[1];
        }

        // not found, use default
        if (!isset($this->variables[$varname])) {
            $this->variables[$varname] = $default;
        }

        return $this->variables[$varname];
    }

    /**
     * INSERT ... ON DUPLICATE KEY UPDATE (or equivalent).
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
        $columns = array_map(function($i) { return "`$i`"; }, $columns);
        $cols    = implode(', ', array_map(function($i) { return "`$i`"; }, array_keys($keys)));
        $cols   .= ', ' . implode(', ', $columns);
        $vals    = implode(', ', array_map(function($i) { return $this->quote($i); }, $keys));
        $vals   .= ', ' . rtrim(str_repeat('?, ', count($columns)), ', ');
        $update  = implode(', ', array_map(function($i) { return "$i = VALUES($i)"; }, $columns));

        return $this->query("INSERT INTO $table ($cols) VALUES ($vals)"
            . " ON DUPLICATE KEY UPDATE $update", $values);
    }
}

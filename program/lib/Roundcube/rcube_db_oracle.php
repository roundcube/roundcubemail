<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011-2014, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Database wrapper class that implements PHP PDO functions            |
 |   for Oracle database                                                 |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

/**
 * Database independent query interface
 * This is a wrapper for the PHP PDO
 *
 * @package    Framework
 * @subpackage Database
 */
class rcube_db_oracle extends rcube_db
{
    public $db_provider = 'oracle';

    /**
     * Driver-specific configuration of database connection
     *
     * @param array $dsn DSN for DB connections
     * @param PDO   $dbh Connection handler
     */
    protected function conn_configure($dsn, $dbh)
    {
        $dbh->query("ALTER SESSION SET nls_date_format = 'YYYY-MM-DD'");
        $dbh->query("ALTER SESSION SET nls_timestamp_format = 'YYYY-MM-DD HH24:MI:SS'");
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
        if (!$this->db_connected || $this->db_mode == 'r' || empty($table)) {
            return false;
        }

        $sequence = $this->quote_identifier($this->sequence_name($table));
        $result   = $dbh->query("SELECT $sequence.currval FROM dual");

        return $result ? $result->fetchColumn() : false;
    }

    /**
     * Formats input so it can be safely used in a query
     * PDO_OCI does not implement quote() method
     *
     * @param mixed  $input Value to quote
     * @param string $type  Type of data (integer, bool, ident)
     *
     * @return string Quoted/converted string for use in query
     */
    public function quote($input, $type = null)
    {
        // handle int directly for better performance
        if ($type == 'integer' || $type == 'int') {
            return intval($input);
        }

        if (is_null($input)) {
            return 'NULL';
        }

        if ($type == 'ident') {
            return $this->quote_identifier($input);
        }

        switch ($type) {
        case 'bool':
        case 'integer':
            return intval($input);
        default:
            return "'" . strtr($input, array(
                    '?' => '??',
                    "'" => "''",
                    rcube_db::DEFAULT_QUOTE => rcube_db::DEFAULT_QUOTE . rcube_db::DEFAULT_QUOTE
            )) . "'";
        }
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
     * Return SQL statement for case insensitive LIKE
     *
     * @param string $column Field name
     * @param string $value  Search value
     *
     * @return string SQL statement to use in query
     */
    public function ilike($column, $value)
    {
        return 'UPPER(' . $this->quote_identifier($column) . ') LIKE UPPER(' . $this->quote($value) . ')';
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
        if ($interval) {
            $interval = intval($interval);
            return "current_timestamp + INTERVAL $interval SECOND";
        }

        return "current_timestamp";
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
        return "(($field - to_date('1970-01-01','YYYY-MM-DD')) * 60 * 60 * 24)";
    }

    /**
     * Adds TOP (LIMIT,OFFSET) clause to the query
     *
     * @param string $query  SQL query
     * @param int    $limit  Number of rows
     * @param int    $offset Offset
     *
     * @return string SQL query
     */
    protected function set_limit($query, $limit = 0, $offset = 0)
    {
        $limit  = intval($limit);
        $offset = intval($offset);
        $end    = $offset + $limit;

        // @TODO: Oracle 12g has better OFFSET support

        $orderby = stristr($query, 'ORDER BY');
        $select  = substr($query, 0, stripos($query, 'FROM'));
        $offset += 1;

        if ($orderby !== false) {
            $query = trim(substr($query, 0, -1 * strlen($orderby)));
        }
        else {
            // it shouldn't happen, paging without sorting has not much sense
            // @FIXME: I don't know how to build paging query without ORDER BY
            $orderby = "ORDER BY 1";
        }

        $query = preg_replace('/^SELECT\s/i', '', $query);
        $query = "$select FROM (SELECT ROW_NUMBER() OVER ($orderby) AS row_number, $query)"
            . " WHERE row_number BETWEEN $offset AND $end";

        return $query;
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

        // replace sequence names, and other Oracle-specific commands
        $sql = preg_replace_callback('/((SEQUENCE ["]?)([^" \r\n]+)/',
            array($this, 'fix_table_names_callback'),
            $sql
        );

        $sql = preg_replace_callback(
            '/([ \r\n]+["]?)([^"\' \r\n\.]+)(["]?\.nextval)/',
            array($this, 'fix_table_names_seq_callback'),
            $sql
        );

        return $sql;
    }

    /**
     * Preg_replace callback for fix_table_names()
     */
    protected function fix_table_names_seq_callback($matches)
    {
        return $matches[1] . $this->options['table_prefix'] . $matches[2] . $matches[3];
    }

    /**
     * Returns PDO DSN string from DSN array
     */
    protected function dsn_string($dsn)
    {
        $params = array();
        $result = 'oci:';

        if ($dsn['hostspec']) {
            $host = $dsn['hostspec'];
            if ($dsn['port']) {
                $host .= ':' . $dsn['port'];
            }

            $dsn['database'] = $host . '/' . $dsn['database'];
        }

        if ($dsn['database']) {
            $params[] = 'dbname=' . $dsn['database'];
        }

        $params['charset'] = 'UTF8';

        if (!empty($params)) {
            $result .= implode(';', $params);
        }

        return $result;
    }
}

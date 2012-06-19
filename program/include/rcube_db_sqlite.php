<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_db_sqlite.php                                   |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Database wrapper class that implements PHP PDO functions            |
 |   for SQLite database                                                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/


/**
 * Database independent query interface
 *
 * This is a wrapper for the PHP PDO
 *
 * @package    Database
 * @version    1.0
 */
class rcube_db_sqlite extends rcube_db
{

    protected function set_charset($charset)
    {
    }

    protected function conn_prepare($dsn)
    {
        // Create database file, required by PDO to exist on connection
        if (!empty($dsn['database']) && !file_exists($dsn['database'])) {
            touch($dsn['database']);
        }
    }

    protected function conn_configure($dsn, $dbh)
    {
        // we emulate via callback some missing functions
        $dbh->sqliteCreateFunction('unix_timestamp', array('rcube_db_sqlite', 'sqlite_unix_timestamp'), 1);
        $dbh->sqliteCreateFunction('now', array('rcube_db_sqlite', 'sqlite_now'), 0);

        // Initialize database structure in file is empty
        if (!empty($dsn['database']) && !filesize($dsn['database'])) {
            $data = file_get_contents(INSTALL_PATH . 'SQL/sqlite.initial.sql');

            if (strlen($data)) {
                if ($this->options['debug_mode']) {
                    $this::debug('INITIALIZE DATABASE');
                }

                $q = $dbh->exec($data);

                if ($q === false) {
                    $error = $this->dbh->errorInfo();
                    $this->db_error = true;
                    $this->db_error_msg = sprintf('[%s] %s', $error[1], $error[2]);

                    rcube::raise_error(array('code' => 500, 'type' => 'db',
                        'line' => __LINE__, 'file' => __FILE__,
                        'message' => $this->db_error_msg), true, false);
                }
            }
        }
    }


    /**
     * Callback for sqlite: unix_timestamp()
     */
    public static function sqlite_unix_timestamp($timestamp = '')
    {
        $timestamp = trim($timestamp);
        if (!$timestamp) {
            $ret = time();
        }
        else if (!preg_match('/^[0-9]+$/s', $timestamp)) {
            $ret = strtotime($timestamp);
        }
        else {
            $ret = $timestamp;
        }

        return $ret;
    }


    /**
     * Callback for sqlite: now()
     */
    public static function sqlite_now()
    {
        return date("Y-m-d H:i:s");
    }


    /**
     * Returns list of tables in database
     *
     * @return array List of all tables of the current database
     */
    public function list_tables()
    {
        if ($this->tables === null) {
            $q = $this->query('SELECT name FROM sqlite_master'
                .' WHERE type = \'table\' ORDER BY name');

            if ($res = $this->_get_result($q)) {
                $this->tables = $res->fetchAll(PDO::FETCH_COLUMN, 0);
            }
            else {
                $this->tables = array();
            }
        }

        return $this->tables;
    }


    /**
     * Returns list of columns in database table
     *
     * @param string Table name
     *
     * @return array List of table cols
     */
    public function list_cols($table)
    {
        $q = $this->query('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?',
            array('table', $table));

        $columns = array();

        if ($sql = $this->fetch_array($q)) {
            $sql       = $sql[0];
            $start_pos = strpos($sql, '(');
            $end_pos   = strrpos($sql, ')');
            $sql       = substr($sql, $start_pos+1, $end_pos-$start_pos-1);
            $lines     = explode(',', $sql);

            foreach ($lines as $line) {
                $line = explode(' ', trim($line));

                if ($line[0] && strpos($line[0], '--') !== 0) {
                    $column = $line[0];
                    $columns[] = trim($column, '"');
                }
            }
        }

        return $columns;
    }


    protected function dsn_string($dsn)
    {
        return $dsn['phptype'] . ':' . $dsn['database'];
    }
}

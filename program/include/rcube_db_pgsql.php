<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_db_pgsql.php                                    |
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
 |   for PostgreSQL database                                             |
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
class rcube_db_pgsql extends rcube_db
{

    /**
     * Get last inserted record ID
     * For Postgres databases, a table name is required
     *
     * @param  string $table  Table name (to find the incremented sequence)
     *
     * @return mixed   ID or false on failure
     */
    public function insert_id($table = '')
    {
        if (!$this->db_connected || $this->db_mode == 'r') {
            return false;
        }

        if ($table) {
            $table = $this->sequence_name($table);
        }

        $id = $this->dbh->lastInsertId($table);

        return $id;
    }


    /**
     * Return SQL statement to convert a field value into a unix timestamp
     *
     * This method is deprecated and should not be used anymore due to limitations
     * of timestamp functions in Mysql (year 2038 problem)
     *
     * @param  string $field Field name
     *
     * @return string  SQL statement to use in query
     * @deprecated
     */
    public function unixtimestamp($field)
    {
        return "EXTRACT (EPOCH FROM $field)";
    }


    /**
     * Return SQL statement for case insensitive LIKE
     *
     * @param  string $column  Field name
     * @param  string $value   Search value
     *
     * @return string  SQL statement to use in query
     */
    public function ilike($column, $value)
    {
        return $this->quote_identifier($column).' ILIKE '.$this->quote($value);
    }

}

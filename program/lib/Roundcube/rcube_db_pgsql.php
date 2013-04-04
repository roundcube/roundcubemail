<?php

/**
 +-----------------------------------------------------------------------+
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

        $id = $this->dbh->lastInsertId($table);

        return $id;
    }

    /**
     * Return correct name for a specific database sequence
     *
     * @param string $sequence Secuence name
     *
     * @return string Translated sequence name
     */
    protected function sequence_name($sequence)
    {
        $rcube = rcube::get_instance();

        // return sequence name if configured
        $config_key = 'db_sequence_'.$sequence;

        if ($name = $rcube->config->get($config_key)) {
            return $name;
        }

        return $sequence;
    }

    /**
     * Return SQL statement to convert a field value into a unix timestamp
     *
     * This method is deprecated and should not be used anymore due to limitations
     * of timestamp functions in Mysql (year 2038 problem)
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
            return $default;
        }

        if (!isset($this->variables)) {
            $this->variables = array();

            $result = $this->query('SHOW ALL');

            while ($row = $this->fetch_array($result)) {
                $this->variables[$row[0]] = $row[1];
            }
        }

        return isset($this->variables[$varname]) ? $this->variables[$varname] : $default;
    }

}

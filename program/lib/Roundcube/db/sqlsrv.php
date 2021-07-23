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
 |   for MS SQL Server database                                          |
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
class rcube_db_sqlsrv extends rcube_db_mssql
{

    /**
     * Get last inserted record ID
     *
     * @param string $table Table name (to find the incremented sequence)
     *
     * @return string|false The ID or False on failure
     */
    public function insert_id($table = '')
    {
        if (!$this->db_connected || $this->db_mode == 'r') {
            return false;
        }

        if ($table) {
            // For some unknown reason the constant described in the driver docs
            // might not exist, we'll fallback to PDO::ATTR_CLIENT_VERSION (#7564)
            if (defined('PDO::ATTR_DRIVER_VERSION')) {
                $driver_version = $this->dbh->getAttribute(PDO::ATTR_DRIVER_VERSION);
            }
            else if (defined('PDO::ATTR_CLIENT_VERSION')) {
                $client_version = $this->dbh->getAttribute(PDO::ATTR_CLIENT_VERSION);
                $driver_version = $client_version['ExtensionVer'];
            }
            else {
                $driver_version = 5;
            }

            // Starting from version 5 of the driver lastInsertId() method expects
            // a sequence name instead of a table name. We'll unset the argument
            // to get the last insert sequence (#7564)
            if (version_compare($driver_version, '5', '>=')) {
                $table = null;
            }
            else {
                // resolve table name
                $table = $this->table_name($table);
            }
        }

        return $this->dbh->lastInsertId($table);
    }

    /**
     * Returns PDO DSN string from DSN array
     */
    protected function dsn_string($dsn)
    {
        $params = [];
        $result = 'sqlsrv:';

        if (isset($dsn['hostspec'])) {
            $host = $dsn['hostspec'];

            if (isset($dsn['port'])) {
                $host .= ',' . $dsn['port'];
            }

            $params[] = 'Server=' . $host;
        }

        if (isset($dsn['database'])) {
            $params[] = 'Database=' . $dsn['database'];
        }

        if (!empty($params)) {
            $result .= implode(';', $params);
        }

        return $result;
    }
}

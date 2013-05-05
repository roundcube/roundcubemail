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
     * Driver initialization/configuration
     */
    protected function init()
    {
        // SQL identifiers quoting
        $this->options['identifier_start'] = '`';
        $this->options['identifier_end'] = '`';
    }

    /**
     * Abstract SQL statement for value concatenation
     *
     * @return string SQL statement to be used in query
     */
    public function concat(/* col1, col2, ... */)
    {
        $args = func_get_args();

        if (is_array($args[0])) {
            $args = $args[0];
        }

        return 'CONCAT(' . join(', ', $args) . ')';
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
        $params = array();
        $result = 'mysql:';

        if ($dsn['database']) {
            $params[] = 'dbname=' . $dsn['database'];
        }

        if ($dsn['hostspec']) {
            $params[] = 'host=' . $dsn['hostspec'];
        }

        if ($dsn['port']) {
            $params[] = 'port=' . $dsn['port'];
        }

        if ($dsn['socket']) {
            $params[] = 'unix_socket=' . $dsn['socket'];
        }

        $params[] = 'charset=utf8';

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
        $result = array();

        if (!empty($dsn['key'])) {
            $result[PDO::MYSQL_ATTR_KEY] = $dsn['key'];
        }

        if (!empty($dsn['cipher'])) {
            $result[PDO::MYSQL_ATTR_CIPHER] = $dsn['cipher'];
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

        // Always return matching (not affected only) rows count
        $result[PDO::MYSQL_ATTR_FOUND_ROWS] = true;

        // Enable AUTOCOMMIT mode (#1488902)
        $dsn_options[PDO::ATTR_AUTOCOMMIT] = true;

        return $result;
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
            $this->variables = array();

            $result = $this->query('SHOW VARIABLES');

            while ($row = $this->fetch_array($result)) {
                $this->variables[$row[0]] = $row[1];
            }
        }

        return isset($this->variables[$varname]) ? $this->variables[$varname] : $default;
    }

}

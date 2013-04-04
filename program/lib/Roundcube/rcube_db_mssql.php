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
class rcube_db_mssql extends rcube_db
{
    public $db_provider = 'mssql';

    /**
     * Driver initialization
     */
    protected function init()
    {
        $this->options['identifier_start'] = '[';
        $this->options['identifier_end'] = ']';
    }

    /**
     * Character setting
     */
    protected function set_charset($charset)
    {
        // UTF-8 is default
    }

    /**
     * Return SQL function for current time and date
     *
     * @return string SQL function to use in query
     */
    public function now()
    {
        return "getdate()";
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
        return "DATEDIFF(second, '19700101', $field) + DATEDIFF(second, GETDATE(), GETUTCDATE())";
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

        return '(' . join('+', $args) . ')';
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

        // query without OFFSET
        if (!$offset) {
            $query = preg_replace('/^SELECT\s/i', "SELECT TOP $limit ", $query);
            return $query;
        }

        $orderby = stristr($query, 'ORDER BY');
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
        $query = "WITH paging AS (SELECT ROW_NUMBER() OVER ($orderby) AS [RowNumber], $query)"
            . " SELECT * FROM paging WHERE [RowNumber] BETWEEN $offset AND $end ORDER BY [RowNumber]";

        return $query;
    }

    /**
     * Returns PDO DSN string from DSN array
     */
    protected function dsn_string($dsn)
    {
        $params = array();
        $result = $dsn['phptype'] . ':';

        if ($dsn['hostspec']) {
            $host = $dsn['hostspec'];
            if ($dsn['port']) {
                $host .= ',' . $dsn['port'];
            }
            $params[] = 'host=' . $host;
        }

        if ($dsn['database']) {
            $params[] = 'dbname=' . $dsn['database'];
        }

        if (!empty($params)) {
            $result .= implode(';', $params);
        }

        return $result;
    }
}

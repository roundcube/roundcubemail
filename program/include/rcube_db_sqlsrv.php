<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_db_sqlsrv.php                                   |
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
 |   for MS SQL Server database                                          |
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
class rcube_db_sqlsrv extends rcube_db
{
    protected function init()
    {
        $this->options['identifier_start'] = '[';
        $this->options['identifier_end'] = ']';
    }

    protected function set_charset($charset)
    {
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
     * @param  string $field Field name
     *
     * @return string  SQL statement to use in query
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
     */
    protected function set_limit($query, $limit = 0, $offset = 0)
    {
        // code from MDB2 package
        if ($limit > 0) {
            $fetch = $offset + $limit;
            return preg_replace('/^([\s(])*SELECT( DISTINCT)?(?!\s*TOP\s*\()/i',
                "\\1SELECT\\2 TOP $fetch", $query);
        }

// @TODO: proper OFFSET handling i _fetch_row()

        return $query;
    }

    /**
     * Returns PDO DSN string from DSN array (parse_dsn() result)
     */
    protected function dsn_string($dsn)
    {
        $params = array();
        $result = 'sqlsrv:';

        if ($dsn['hostspec']) {
            $host = $dsn['hostspec'];

            if ($dsn['port']) {
                $host .= ',' . $dsn['port'];
            }
            $params[] = 'Server=' . $host;
        }

        if ($dsn['database']) {
            $params[] = 'Database=' . $dsn['database'];
        }

        if (!empty($params)) {
            $result .= implode(';', $params);
        }

        return $result;
    }
}

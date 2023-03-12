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
 |   Database wrapper class for query parameters                         |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Database query parameter
 *
 * @package    Framework
 * @subpackage Database
 */
class rcube_db_param
{
    protected $db;
    protected $type;
    protected $value;


    /**
     * Object constructor
     *
     * @param rcube_db $db    Database driver
     * @param mixed    $value Parameter value
     * @param string   $type  Parameter type (One of rcube_db::TYPE_* constants)
     */
    public function __construct($db, $value, $type = null)
    {
        $this->db    = $db;
        $this->value = $value;
        $this->type  = $type;
    }

    /**
     * Returns the value as string for inlining into SQL query
     */
    public function __toString()
    {
        if ($this->type === rcube_db::TYPE_SQL) {
            return (string) $this->value;
        }

        return (string) $this->db->quote($this->value, $this->type);
    }
}

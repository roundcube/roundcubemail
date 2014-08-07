<?php

/*
 +-----------------------------------------------------------------------+
 | Net/LDAP3/Result.php                                                  |
 |                                                                       |
 | Based on code created by the Roundcube Webmail team.                  |
 |                                                                       |
 | Copyright (C) 2006-2014, The Roundcube Dev Team                       |
 | Copyright (C) 2012-2014, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for plugins.                        |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide advanced functionality for accessing LDAP directories       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Authors: Thomas Bruederli <roundcube@gmail.com>                       |
 |          Jeroen van Meeuwen <vanmeeuwen@kolabsys.com>                 |
 +-----------------------------------------------------------------------+
*/

/**
 * Model class representing an LDAP search result
 *
 * @package LDAP
 */
class Net_LDAP3_Result implements Iterator
{
    protected $conn;
    protected $base_dn;
    protected $filter;
    protected $scope;

    private $count;
    private $current;
    private $iteratorkey = 0;

    /**
     * Default constructor
     *
     * @param resource $conn      LDAP link identifier
     * @param string   $base_dn   Base DN used to get this result
     * @param string   $filter    Filter query used to get this result
     * @param string   $scope     Scope of the result
     * @param resource $result    LDAP result entry identifier
     */
    function __construct($conn, $base_dn, $filter, $scope, $result)
    {
        $this->conn    = $conn;
        $this->base_dn = $base_dn;
        $this->filter  = $filter;
        $this->scope   = $scope;
        $this->result  = $result;
    }

    public function get($property, $default = null)
    {
        if (isset($this->$property)) {
            return $this->$property;
        } else {
            return $default;
        }
    }

    public function set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Wrapper for ldap_sort()
     */
    public function sort($attr)
    {
        return ldap_sort($this->conn, $this->result, $attr);
    }

    /**
     * Get entries count
     */
    public function count()
    {
        if (!isset($this->count)) {
            $this->count = ldap_count_entries($this->conn, $this->result);
        }

        return $this->count;
    }

    /**
     * Wrapper for ldap_get_entries()
     *
     * @param bool $normalize Optionally normalize the entries to a list of hash arrays
     *
     * @return array List of LDAP entries
     */
    public function entries($normalize = false)
    {
        $entries = ldap_get_entries($this->conn, $this->result);

        if ($normalize) {
            return Net_LDAP3::normalize_result($entries);
        }

        return $entries;
    }

    /**
     * Wrapper for ldap_get_dn() using the current entry pointer
     */
    public function get_dn()
    {
        return $this->current ? ldap_get_dn($this->conn, $this->current) : null;
    }


    /***  Implement PHP 5 Iterator interface to make foreach work  ***/

    function current()
    {
        $attrib       = ldap_get_attributes($this->conn, $this->current);
        $attrib['dn'] = ldap_get_dn($this->conn, $this->current);

        return $attrib;
    }

    function key()
    {
        return $this->iteratorkey;
    }

    function rewind()
    {
        $this->iteratorkey = 0;
        $this->current = ldap_first_entry($this->conn, $this->result);
    }

    function next()
    {
        $this->iteratorkey++;
        $this->current = ldap_next_entry($this->conn, $this->current);
    }

    function valid()
    {
        return (bool)$this->current;
    }

}

<?php

/*
 +-----------------------------------------------------------------------+
 | Roundcube/rcube_ldap_result.php                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2006-2013, The Roundcube Dev Team                       |
 | Copyright (C) 2013, Kolab Systems AG                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Model class that represents an LDAP search result                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/


/**
 * Model class representing an LDAP search result
 *
 * @package    Framework
 * @subpackage LDAP
 */
class rcube_ldap_result implements Iterator
{
    public $conn;
    public $ldap;
    public $base_dn;
    public $filter;

    private $count = null;
    private $current = null;
    private $iteratorkey = 0;

    /**
     * Default constructor
     *
     * @param resource $conn LDAP link identifier
     * @param resource $ldap LDAP result entry identifier
     * @param string   $base_dn   Base DN used to get this result
     * @param string   $filter    Filter query used to get this result
     * @param integer  $count     Record count value (pre-calculated)
     */
    function __construct($conn, $ldap, $base_dn, $filter, $count = null)
    {
        $this->conn = $conn;
        $this->ldap = $ldap;
        $this->base_dn = $base_dn;
        $this->filter = $filter;
        $this->count = $count;
    }

    /**
     * Wrapper for ldap_sort()
     */
    public function sort($attr)
    {
        return ldap_sort($this->conn, $this->ldap, $attr);
    }

    /**
     * Get entries count
     */
    public function count()
    {
        if (!isset($this->count))
            $this->count = ldap_count_entries($this->conn, $this->ldap);

        return $this->count;
    }

    /**
     * Wrapper for ldap_get_entries()
     *
     * @param boolean $normalize Optionally normalize the entries to a list of hash arrays
     * @return array  List of LDAP entries
     */
    public function entries($normalize = false)
    {
        $entries = ldap_get_entries($this->conn, $this->ldap);
        return $normalize ? rcube_ldap_generic::normalize_result($entries) : $entries;
    }

    /**
     * Wrapper for ldap_get_dn() using the current entry pointer
     */
    public function get_dn()
    {
        return $this->current ? ldap_get_dn($this->conn, $this->current) : null;
    }


    /***  Implements the PHP 5 Iterator interface to make foreach work  ***/

    function current()
    {
        $attrib = ldap_get_attributes($this->conn, $this->current);
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
        $this->current = ldap_first_entry($this->conn, $this->ldap);
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

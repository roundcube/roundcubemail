<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_addressbook.php                                 |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2006-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Interface to the local address book database                        |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id:  $

*/


/**
 * Abstract skeleton of an address book/repository
 *
 * @package Addressbook
 */
abstract class rcube_addressbook
{
    /** public properties */
    var $primary_key;
    var $readonly = true;
    var $ready = false;
    var $list_page = 1;
    var $page_size = 10;

    /**
     * Save a search string for future listings
     *
     * @param mixed Search params to use in listing method, obtained by get_search_set()
     */
    abstract function set_search_set($filter);

    /**
     * Getter for saved search properties
     *
     * @return mixed Search properties used by this class
     */
    abstract function get_search_set();

    /**
     * Reset saved results and search parameters
     */
    abstract function reset();

    /**
     * List the current set of contact records
     *
     * @param  array  List of cols to show
     * @param  int    Only return this number of records, use negative values for tail
     * @return array  Indexed list of contact records, each a hash array
     */
    abstract function list_records($cols=null, $subset=0);

    /**
     * Search records
     *
     * @param array   List of fields to search in
     * @param string  Search value
     * @param boolean True if results are requested, False if count only
     * @return Indexed list of contact records and 'count' value
     */
    abstract function search($fields, $value, $strict=false, $select=true);

    /**
     * Count number of available contacts in database
     *
     * @return object rcube_result_set Result set with values for 'count' and 'first'
     */
    abstract function count();

    /**
     * Return the last result set
     *
     * @return object rcube_result_set Current result set or NULL if nothing selected yet
     */
    abstract function get_result();

    /**
     * Get a specific contact record
     *
     * @param mixed record identifier(s)
     * @param boolean True to return record as associative array, otherwise a result set is returned
     * @return mixed Result object with all record fields or False if not found
     */
    abstract function get_record($id, $assoc=false);

    /**
     * Close connection to source
     * Called on script shutdown
     */
    function close() { }

    /**
     * Set internal list page
     *
     * @param  number  Page number to list
     * @access public
     */
    function set_page($page)
    {
      $this->list_page = (int)$page;
    }

    /**
     * Set internal page size
     *
     * @param  number  Number of messages to display on one page
     * @access public
     */
    function set_pagesize($size)
    {
      $this->page_size = (int)$size;
    }

    /**
     * Create a new contact record
     *
     * @param array Assoziative array with save data
     * @param boolean True to check for duplicates first
     * @return The created record ID on success, False on error
     */
    function insert($save_data, $check=false)
    {
      /* empty for read-only address books */
    }

    /**
     * Update a specific contact record
     *
     * @param mixed Record identifier
     * @param array Assoziative array with save data
     * @return True on success, False on error
     */
    function update($id, $save_cols)
    {
      /* empty for read-only address books */
    }

    /**
     * Mark one or more contact records as deleted
     *
     * @param array  Record identifiers
     */
    function delete($ids)
    {
      /* empty for read-only address books */
    }

    /**
     * Remove all records from the database
     */
    function delete_all()
    {
      /* empty for read-only address books */
    }

}
 
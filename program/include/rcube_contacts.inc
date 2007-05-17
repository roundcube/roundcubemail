<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_contacts.inc                                    |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2006-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Interface to the local address book database                        |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: rcube_contacts.inc 328 2006-08-30 17:41:21Z thomasb $

*/

class rcube_contacts
{
  var $db = null;
  var $db_name = '';
  var $user_id = 0;
  var $filter = null;
  var $result = null;
  var $search_fields;
  var $search_string;
  var $table_cols = array('name', 'email', 'firstname', 'surname');
  
  /** public properties */
  var $primary_key = 'contact_id';
  var $readonly = false;
  var $list_page = 1;
  var $page_size = 10;
  var $ready = false;

  
  /**
   * Object constructor
   *
   * @param object  Instance of the rcube_db class
   * @param integer User-ID
   */
  function __construct($dbconn, $user)
  {
    $this->db = $dbconn;
    $this->db_name = get_table_name('contacts');
    $this->user_id = $user;
    $this->ready = $this->db && !$this->db->is_error();
  }

  /**
   * PHP 4 object constructor
   *
   * @see  rcube_contacts::__construct
   */
  function rcube_contacts($dbconn, $user)
  {
    $this->__construct($dbconn, $user);
  }


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
   * Save a search string for future listings
   *
   * @param  string SQL params to use in listing method
   */
  function set_search_set($filter)
  {
    $this->filter = $filter;
  }
  
  
  /**
   * Getter for saved search properties
   *
   * @return mixed Search properties used by this class
   */
  function get_search_set()
  {
    return $this->filter;
  }


  /**
   * Reset all saved results and search parameters
   */
  function reset()
  {
    $this->result = null;
    $this->filter = null;
    $this->search_fields = null;
    $this->search_string = null;
  }
  
  
  /**
   * List the current set of contact records
   *
   * @param  array  List of cols to show
   * @return array  Indexed list of contact records, each a hash array
   */
  function list_records($cols=null, $subset=0)
  {
    // count contacts for this user
    $this->result = $this->count();
    $sql_result = NULL;

    // get contacts from DB
    if ($this->result->count)
    {
      $start_row = $subset < 0 ? $this->result->first + $this->page_size + $subset : $this->result->first;
      $length = $subset != 0 ? abs($subset) : $this->page_size;
      
      $sql_result = $this->db->limitquery(
        "SELECT * FROM ".$this->db_name."
         WHERE  del<>1
         AND    user_id=?" .
        ($this->filter ? " AND (".$this->filter.")" : "") .
        " ORDER BY name",
        $start_row,
        $length,
        $this->user_id);
    }
    
    while ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result)))
    {
      $sql_arr['ID'] = $sql_arr[$this->primary_key];
      // make sure we have a name to display
      if (empty($sql_arr['name']))
        $sql_arr['name'] = $sql_arr['email'];
      $this->result->add($sql_arr);
    }
    
    return $this->result;
  }


  /**
   * Search contacts
   *
   * @param array   List of fields to search in
   * @param string  Search value
   * @param boolean True if results are requested, False if count only
   * @return Indexed list of contact records and 'count' value
   */
  function search($fields, $value, $select=true)
  {
    if (!is_array($fields))
      $fields = array($fields);
      
    $add_where = array();
    foreach ($fields as $col)
    {
      if ($col == 'ID' || $col == $this->primary_key)
      {
        $ids = !is_array($value) ? split(',', $value) : $value;
        $add_where[] = $this->primary_key." IN (".join(',', $ids).")";
      }
      else
        $add_where[] = $this->db->quoteIdentifier($col)." LIKE ".$this->db->quote(strlen($value)>2 ? "%$value%" : "$value%");
    }
    
    if (!empty($add_where))
    {
      $this->set_search_set(join(' OR ', $add_where));
      if ($select)
        $this->list_records();
      else
        $this->result = $this->count();
    }
   
    return $this->result; 
  }


  /**
   * Count number of available contacts in database
   *
   * @return Result array with values for 'count' and 'first'
   */
  function count()
  {
    // count contacts for this user
    $sql_result = $this->db->query(
      "SELECT COUNT(contact_id) AS rows
       FROM ".$this->db_name."
       WHERE  del<>1
       AND    user_id=?".
       ($this->filter ? " AND (".$this->filter.")" : ""),
      $this->user_id);

    $sql_arr = $this->db->fetch_assoc($sql_result);
    return new rcube_result_set($sql_arr['rows'], ($this->list_page-1) * $this->page_size);;
  }


  /**
   * Return the last result set
   *
   * @return Result array or NULL if nothing selected yet
   */
  function get_result($as_res=true)
  {
    return $this->result;
  }
  
  
  /**
   * Get a specific contact record
   *
   * @param mixed record identifier(s)
   * @return Result object with all record fields or False if not found
   */
  function get_record($id, $assoc=false)
  {
    // return cached result
    if ($this->result && ($first = $this->result->first()) && $first[$this->primary_key] == $id)
      return $assoc ? $first : $this->result;
      
    $this->db->query(
      "SELECT * FROM ".$this->db_name."
       WHERE  contact_id=?
       AND    user_id=?
       AND    del<>1",
      $id,
      $this->user_id);

    if ($sql_arr = $this->db->fetch_assoc())
    {
      $sql_arr['ID'] = $sql_arr[$this->primary_key];
      $this->result = new rcube_result_set(1);
      $this->result->add($sql_arr);
    }

    return $assoc && $sql_arr ? $sql_arr : $this->result;
  }
  
  
  /**
   * Create a new contact record
   *
   * @param array Assoziative array with save data
   * @return The created record ID on success, False on error
   */
  function insert($save_data, $check=false)
  {
    if (is_object($save_data) && is_a($save_data, rcube_result_set))
      return $this->insert_recset($save_data, $check);

    $insert_id = $existing = false;

    if ($check)
      $existing = $this->search('email', $save_data['email'], false);

    $a_insert_cols = $a_insert_values = array();
    foreach ($this->table_cols as $col)
      if (isset($save_data[$col]))
      {
        $a_insert_cols[] = $this->db->quoteIdentifier($col);
        $a_insert_values[] = $this->db->quote($save_data[$col]);
      }
    
    if (!$existing->count && !empty($a_insert_cols))
    {
      $this->db->query(
        "INSERT INTO ".$this->db_name."
         (user_id, changed, del, ".join(', ', $a_insert_cols).")
         VALUES (?, ".$this->db->now().", 0, ".join(', ', $a_insert_values).")",
        $this->user_id);
        
      $insert_id = $this->db->insert_id(get_sequence_name('contacts'));
    }
    
    return $insert_id;
  }


  /**
   * Insert new contacts for each row in set
   */
  function insert_recset($result, $check=false)
  {
    $ids = array();
    while ($row = $result->next())
    {
      if ($insert = $this->insert($row, $check))
        $ids[] = $insert;
    }
    return $ids;
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
    $updated = false;
    $write_sql = array();
    foreach ($this->table_cols as $col)
      if (isset($save_cols[$col]))
        $write_sql[] = sprintf("%s=%s", $this->db->quoteIdentifier($col), $this->db->quote($save_cols[$col]));

    if (!empty($write_sql))
    {
      $this->db->query(
        "UPDATE ".$this->db_name."
         SET    changed=".$this->db->now().", ".join(', ', $write_sql)."
         WHERE  contact_id=?
         AND    user_id=?
         AND    del<>1",
        $id,
        $this->user_id);

      $updated = $this->db->affected_rows();
    }
    
    return $updated;
  }
  
  
  /**
   * Mark one or more contact records as deleted
   *
   * @param array  Record identifiers
   */
  function delete($ids)
  {
    if (is_array($ids))
      $ids = join(',', $ids);

    $this->db->query(
      "UPDATE ".$this->db_name."
       SET    del=1
       WHERE  user_id=?
       AND    contact_id IN (".$ids.")",
      $this->user_id);

    return $this->db->affected_rows();
  }

}


/**
 * RoundCube result set class.
 * Representing an address directory result set.
 */
class rcube_result_set
{
  var $count = 0;
  var $first = 0;
  var $current = 0;
  var $records = array();
  
  function __construct($c=0, $f=0)
  {
    $this->count = (int)$c;
    $this->first = (int)$f;
  }
  
  function rcube_result_set($c=0, $f=0)
  {
    $this->__construct($c, $f);
  }
  
  function add($rec)
  {
    $this->records[] = $rec;
  }
  
  function iterate()
  {
    return $this->records[$this->current++];
  }
  
  function first()
  {
    $this->current = 0;
    return $this->records[$this->current++];
  }
  
  // alias
  function next()
  {
    return $this->iterate();
  }
  
  function seek($i)
  {
    $this->current = $i;
  }
  
}


?>
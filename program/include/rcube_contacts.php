<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_contacts.php                                    |
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

 $Id$

*/


/**
 * Model class for the local address book database
 *
 * @package Addressbook
 */
class rcube_contacts extends rcube_addressbook
{
  var $db = null;
  var $db_name = '';
  var $user_id = 0;
  var $filter = null;
  var $result = null;
  var $search_fields;
  var $search_string;
  var $table_cols = array('name', 'email', 'firstname', 'surname', 'vcard');
  
  /** public properties */
  var $primary_key = 'contact_id';
  var $readonly = false;
  var $groups = false;
  var $list_page = 1;
  var $page_size = 10;
  var $group_id = 0;
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
    
    if (in_array('contactgroups', $this->db->list_tables()))
      $this->groups = true;
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
   * Setter for the current group
   * (empty, has to be re-implemented by extending class)
   */
  function set_group($gid)
  {
    $this->group_id = $gid;
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
   * List all active contact groups of this source
   *
   * @param string  Search string to match group name
   * @return array  Indexed list of contact groups, each a hash array
   */
  function list_groups($search = null)
  {
    $results = array();
    
    if (!$this->groups)
      return $results;
      
    $sql_filter = $search ? "AND " . $this->db->ilike('name', '%'.$search.'%') : '';

    $sql_result = $this->db->query(
      "SELECT * FROM ".get_table_name('contactgroups')."
       WHERE  del<>1
       AND    user_id=?
       $sql_filter
       ORDER BY name",
      $this->user_id);

    while ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result))) {
      $sql_arr['ID'] = $sql_arr['contactgroup_id'];
      $results[] = $sql_arr;
    }
    
    return $results;
  }
  
  /**
   * List the current set of contact records
   *
   * @param  array   List of cols to show
   * @param  int     Only return this number of records, use negative values for tail
   * @param  boolean True to skip the count query (select only)
   * @return array  Indexed list of contact records, each a hash array
   */
  function list_records($cols=null, $subset=0, $nocount=false)
  {
    // count contacts for this user
    $this->result = $nocount ? new rcube_result_set(1) : $this->count();
    $sql_result = NULL;

    // get contacts from DB
    if ($this->result->count)
    {
      if ($this->group_id)
        $join = "LEFT JOIN ".get_table_name('contactgroupmembers')." AS m".
          " ON (m.contact_id=c.".$this->primary_key.")";
      
      $start_row = $subset < 0 ? $this->result->first + $this->page_size + $subset : $this->result->first;
      $length = $subset != 0 ? abs($subset) : $this->page_size;
      
      $sql_result = $this->db->limitquery(
        "SELECT * FROM ".$this->db_name." AS c ".$join."
         WHERE  c.del<>1
         AND    c.user_id=?" .
        ($this->group_id ? " AND m.contactgroup_id=?" : "").
        ($this->filter ? " AND (".$this->filter.")" : "") .
        " ORDER BY c.name",
        $start_row,
        $length,
        $this->user_id,
        $this->group_id);
    }
    
    while ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result)))
    {
      $sql_arr['ID'] = $sql_arr[$this->primary_key];
      // make sure we have a name to display
      if (empty($sql_arr['name']))
        $sql_arr['name'] = $sql_arr['email'];
      $this->result->add($sql_arr);
    }
    
    if ($nocount)
      $this->result->count = count($this->result->records);
    
    return $this->result;
  }


  /**
   * Search contacts
   *
   * @param array   List of fields to search in
   * @param string  Search value
   * @param boolean True for strict (=), False for partial (LIKE) matching
   * @param boolean True if results are requested, False if count only
   * @param boolean True to skip the count query (select only)
   * @return Indexed list of contact records and 'count' value
   */
  function search($fields, $value, $strict=false, $select=true, $nocount=false)
  {
    if (!is_array($fields))
      $fields = array($fields);
      
    $add_where = array();
    foreach ($fields as $col)
    {
      if ($col == 'ID' || $col == $this->primary_key)
      {
        $ids = !is_array($value) ? explode(',', $value) : $value;
        $add_where[] = $this->primary_key.' IN ('.join(',', $ids).')';
      }
      else if ($strict)
        $add_where[] = $this->db->quoteIdentifier($col).'='.$this->db->quote($value);
      else
        $add_where[] = $this->db->ilike($col, '%'.$value.'%');
    }
    
    if (!empty($add_where))
    {
      $this->set_search_set(join(' OR ', $add_where));
      if ($select)
        $this->list_records(null, 0, $nocount);
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
    if ($this->group_id)
      $join = "LEFT JOIN ".get_table_name('contactgroupmembers')." AS m".
        " ON (m.contact_id=c.".$this->primary_key.")";
      
    // count contacts for this user
    $sql_result = $this->db->query(
      "SELECT COUNT(c.contact_id) AS rows
       FROM ".$this->db_name." AS c ".$join."
       WHERE  c.del<>1
       AND    c.user_id=?".
       ($this->group_id ? " AND m.contactgroup_id=?" : "").
       ($this->filter ? " AND (".$this->filter.")" : ""),
      $this->user_id,
      $this->group_id);

    $sql_arr = $this->db->fetch_assoc($sql_result);
    return new rcube_result_set($sql_arr['rows'], ($this->list_page-1) * $this->page_size);;
  }


  /**
   * Return the last result set
   *
   * @return Result array or NULL if nothing selected yet
   */
  function get_result()
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
      $existing = $this->search('email', $save_data['email'], true, false);

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
         VALUES (".intval($this->user_id).", ".$this->db->now().", 0, ".join(', ', $a_insert_values).")"
        );
        
      $insert_id = $this->db->insert_id('contacts');
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

    // delete all group members linked with these contacts
    if ($this->groups) {
      $this->db->query(
        "DELETE FROM ".get_table_name('contactgroupmembers')."
         WHERE  contact_id IN (".$ids.")");
    }

    $this->db->query(
      "UPDATE ".$this->db_name."
       SET    del=1
       WHERE  user_id=?
       AND    contact_id IN (".$ids.")",
      $this->user_id);

    return $this->db->affected_rows();
  }
  
  
  /**
   * Remove all records from the database
   */
  function delete_all()
  {
    $this->db->query("DELETE FROM {$this->db_name} WHERE user_id=?", $this->user_id);
    return $this->db->affected_rows();
  }


  /**
   * Create a contact group with the given name
   *
   * @param string The group name
   * @return False on error, array with record props in success
   */
  function create_group($name)
  {
    $result = false;

    // make sure we have a unique name
    $name = $this->unique_groupname($name);
    
    $this->db->query(
      "INSERT INTO ".get_table_name('contactgroups')." (user_id, changed, name)
       VALUES (".intval($this->user_id).", ".$this->db->now().", ".$this->db->quote($name).")"
      );
    
    if ($insert_id = $this->db->insert_id('contactgroups'))
      $result = array('id' => $insert_id, 'name' => $name);
    
    return $result;
  }

  /**
   * Delete the given group and all linked group members
   *
   * @param string Group identifier
   * @return boolean True on success, false if no data was changed
   */
  function delete_group($gid)
  {
    $sql_result = $this->db->query(
      "DELETE FROM ".get_table_name('contactgroupmembers')."
       WHERE  contactgroup_id=?",
      $gid);
    
    $sql_result = $this->db->query(
      "UPDATE ".get_table_name('contactgroups')."
       SET del=1, changed=".$this->db->now()."
       WHERE  contactgroup_id=?",
      $gid);
    
    return $this->db->affected_rows();
  }
  
  /**
   * Rename a specific contact group
   *
   * @param string Group identifier
   * @param string New name to set for this group
   * @return boolean New name on success, false if no data was changed
   */
  function rename_group($gid, $newname)
  {
    // make sure we have a unique name
    $name = $this->unique_groupname($newname);
    
    $sql_result = $this->db->query(
      "UPDATE ".get_table_name('contactgroups')."
       SET name=".$this->db->quote($name).", changed=".$this->db->now()."
       WHERE  contactgroup_id=?",
      $gid);
    
    return $this->db->affected_rows() ? $name : false;
  }

  /**
   * Add the given contact records the a certain group
   *
   * @param string  Group identifier
   * @param array   List of contact identifiers to be added
   * @return int    Number of contacts added 
   */
  function add_to_group($group_id, $ids)
  {
    if (!is_array($ids))
      $ids = explode(',', $ids);
    
    $added = 0;
    
    foreach ($ids as $contact_id) {
      $sql_result = $this->db->query(
        "SELECT 1 FROM ".get_table_name('contactgroupmembers')."
         WHERE  contactgroup_id=?
         AND    contact_id=?",
      $group_id,
      $contact_id);
      
      if (!$this->db->num_rows($sql_result)) {
        $this->db->query(
          "INSERT INTO ".get_table_name('contactgroupmembers')." (contactgroup_id, contact_id, created)
           VALUES (".intval($group_id).", ".intval($contact_id).", ".$this->db->now().")"
        );
        if (!$this->db->db_error)
          $added++;
      }
    }
    
    return $added;
  }
  
  
  /**
   * Remove the given contact records from a certain group
   *
   * @param string  Group identifier
   * @param array   List of contact identifiers to be removed
   * @return int    Number of deleted group members
   */
  function remove_from_group($group_id, $ids)
  {
    if (!is_array($ids))
      $ids = explode(',', $ids);
    
    $sql_result = $this->db->query(
      "DELETE FROM ".get_table_name('contactgroupmembers')."
       WHERE  contactgroup_id=?
       AND    contact_id IN (".join(',', array_map(array($this->db, 'quote'), $ids)).")",
      $group_id);
    
    return $this->db->affected_rows();
  }
  
  /**
   * Check for existing groups with the same name
   *
   * @param string Name to check
   * @return string A group name which is unique for the current use
   */
  private function unique_groupname($name)
  {
    $checkname = $name;
    $num = 2; $hit = false;
    
    do {
      $sql_result = $this->db->query(
        "SELECT 1 FROM ".get_table_name('contactgroups')."
         WHERE  del<>1
         AND    user_id=?
         AND    name LIKE ?",
        $this->user_id,
        $checkname);
    
      // append number to make name unique
      if ($hit = $this->db->num_rows($sql_result))
        $checkname = $name . ' ' . $num++;
    } while ($hit > 0);
    
    return $checkname;
  }
}

<?php
/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_ldap.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2006-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Interface to an LDAP address directory                              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Model class to access an LDAP address directory
 *
 * @package Addressbook
 */
class rcube_ldap
{
  var $conn;
  var $prop = array();
  var $fieldmap = array();
  
  var $filter = '';
  var $result = null;
  var $ldap_result = null;
  var $sort_col = '';
  
  /** public properties */
  var $primary_key = 'ID';
  var $readonly = true;
  var $list_page = 1;
  var $page_size = 10;
  var $ready = false;
  
  
  /**
   * Object constructor
   *
   * @param array LDAP connection properties
   * @param integer User-ID
   */
  function __construct($p)
  {
    $this->prop = $p;
    
    foreach ($p as $prop => $value)
      if (preg_match('/^(.+)_field$/', $prop, $matches))
        $this->fieldmap[$matches[1]] = $value;
    
    $this->connect();
  }

  /**
   * PHP 4 object constructor
   *
   * @see  rcube_ldap::__construct()
   */
  function rcube_ldap($p)
  {
    $this->__construct($p);
  }
  

  /**
   * Establish a connection to the LDAP server
   */
  function connect()
  {
    if (!function_exists('ldap_connect'))
      raise_error(array('type' => 'ldap', 'message' => "No ldap support in this installation of PHP"), true);

    if (is_resource($this->conn))
      return true;
    
    if (!is_array($this->prop['hosts']))
      $this->prop['hosts'] = array($this->prop['hosts']);

    if (empty($this->prop['ldap_version']))
      $this->prop['ldap_version'] = 3;

    foreach ($this->prop['hosts'] as $host)
    {
      if ($lc = @ldap_connect($host, $this->prop['port']))
      {
        ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, $this->prop['ldap_version']);
        $this->prop['host'] = $host;
        $this->conn = $lc;
        break;
      }
    }
    
    if (is_resource($this->conn))
    {
      $this->ready = true;
      if (!empty($this->prop['bind_dn']) && !empty($this->prop['bind_pass']))
        $this->ready = $this->bind($this->prop['bind_dn'], $this->prop['bind_pass']);
    }
    else
      raise_error(array('type' => 'ldap', 'message' => "Could not connect to any LDAP server, tried $host:{$this->prop[port]} last"), true);
  }


  /**
   * Bind connection with DN and password
   *
   * @param string Bind DN
   * @param string Bind password
   * @return boolean True on success, False on error
   */
  function bind($dn, $pass)
  {
    if (!$this->conn) {
      return false;
    }
    
    if (@ldap_bind($this->conn, $dn, $pass)) {
      return true;
    }

    raise_error(array(
        'code' => ldap_errno($this->conn),
        'type' => 'ldap',
        'message' => "Bind failed for dn=$dn: ".ldap_error($this->conn)),
        true);

    return false;
  }


  /**
   * Close connection to LDAP server
   */
  function close()
  {
    if ($this->conn)
    {
      @ldap_unbind($this->conn);
      $this->conn = null;
    }
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
   * @param string Filter string
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
    $this->ldap_result = null;
    $this->filter = '';
  }
  
  
  /**
   * List the current set of contact records
   *
   * @param  array  List of cols to show
   * @param  int    Only return this number of records (not implemented)
   * @return array  Indexed list of contact records, each a hash array
   */
  function list_records($cols=null, $subset=0)
  {
    // add general filter to query
    if (!empty($this->prop['filter']))
    {
      $filter = $this->prop['filter'];
      $this->set_search_set($filter);
    }
    
    // exec LDAP search if no result resource is stored
    if ($this->conn && !$this->ldap_result)
      $this->_exec_search();
    
    // count contacts for this user
    $this->result = $this->count();
    
    // we have a search result resource
    if ($this->ldap_result && $this->result->count > 0)
    {
      if ($this->sort_col && $this->prop['scope'] !== "base")
        @ldap_sort($this->conn, $this->ldap_result, $this->sort_col);
        
      $entries = ldap_get_entries($this->conn, $this->ldap_result);
      for ($i = $this->result->first; $i < min($entries['count'], $this->result->first + $this->page_size); $i++)
        $this->result->add($this->_ldap2result($entries[$i]));
    }

    return $this->result;
  }


  /**
   * Search contacts
   *
   * @param array   List of fields to search in
   * @param string  Search value
   * @param boolean True if results are requested, False if count only
   * @return array  Indexed list of contact records and 'count' value
   */
  function search($fields, $value, $strict=false, $select=true)
  {
    // special treatment for ID-based search
    if ($fields == 'ID' || $fields == $this->primary_key)
    {
      $ids = explode(',', $value);
      $result = new rcube_result_set();
      foreach ($ids as $id)
        if ($rec = $this->get_record($id, true))
        {
          $result->add($rec);
          $result->count++;
        }
      
      return $result;
    }
    
    $filter = '(|';
    $wc = !$strict && $this->prop['fuzzy_search'] ? '*' : '';
    if (is_array($this->prop['search_fields']))
    {
      foreach ($this->prop['search_fields'] as $k => $field)
        $filter .= "($field=$wc" . rcube_ldap::quote_string($value) . "$wc)";
    }
    else
    {
      foreach ((array)$fields as $field)
        if ($f = $this->_map_field($field))
          $filter .= "($f=$wc" . rcube_ldap::quote_string($value) . "$wc)";
    }
    $filter .= ')';
    
    // avoid double-wildcard if $value is empty
    $filter = preg_replace('/\*+/', '*', $filter);
    
    // add general filter to query
    if (!empty($this->prop['filter']))
      $filter = '(&(' . preg_replace('/^\(|\)$/', '', $this->prop['filter']) . ')' . $filter . ')';

    // set filter string and execute search
    $this->set_search_set($filter);
    $this->_exec_search();
    
    if ($select)
      $this->list_records();
    else
      $this->result = $this->count();
   
    return $this->result; 
  }


  /**
   * Count number of available contacts in database
   *
   * @return object rcube_result_set Resultset with values for 'count' and 'first'
   */
  function count()
  {
    $count = 0;
    if ($this->conn && $this->ldap_result)
      $count = ldap_count_entries($this->conn, $this->ldap_result);

    return new rcube_result_set($count, ($this->list_page-1) * $this->page_size);
  }


  /**
   * Return the last result set
   *
   * @return object rcube_result_set Current resultset or NULL if nothing selected yet
   */
  function get_result()
  {
    return $this->result;
  }
  
  
  /**
   * Get a specific contact record
   *
   * @param mixed   Record identifier
   * @param boolean Return as associative array
   * @return mixed  Hash array or rcube_result_set with all record fields
   */
  function get_record($dn, $assoc=false)
  {
    $res = null;
    if ($this->conn && $dn)
    {
      $this->ldap_result = @ldap_read($this->conn, base64_decode($dn), "(objectclass=*)", array_values($this->fieldmap));
      $entry = @ldap_first_entry($this->conn, $this->ldap_result);
      
      if ($entry && ($rec = ldap_get_attributes($this->conn, $entry)))
      {
        $res = $this->_ldap2result($rec);
        $this->result = new rcube_result_set(1);
        $this->result->add($res);
      }
    }

    return $assoc ? $res : $this->result;
  }
  
  
  /**
   * Create a new contact record
   *
   * @param array    Hash array with save data
   * @return boolean The create record ID on success, False on error
   */
  function insert($save_cols)
  {
    // TODO
    return false;
  }
  
  
  /**
   * Update a specific contact record
   *
   * @param mixed Record identifier
   * @param array Hash array with save data
   * @return boolean True on success, False on error
   */
  function update($id, $save_cols)
  {
    // TODO    
    return false;
  }
  
  
  /**
   * Mark one or more contact records as deleted
   *
   * @param array  Record identifiers
   * @return boolean True on success, False on error
   */
  function delete($ids)
  {
    // TODO
    return false;
  }


  /**
   * Execute the LDAP search based on the stored credentials
   *
   * @access private
   */
  function _exec_search()
  {
    if ($this->conn && $this->filter)
    {
      $function = $this->prop['scope'] == 'sub' ? 'ldap_search' : ($this->prop['scope'] == 'base' ? 'ldap_read' : 'ldap_list');
      $this->ldap_result = $function($this->conn, $this->prop['base_dn'], $this->filter, array_values($this->fieldmap), 0, 0);
      return true;
    }
    else
      return false;
  }
  
  
  /**
   * @access private
   */
  function _ldap2result($rec)
  {
    $out = array();
    
    if ($rec['dn'])
      $out[$this->primary_key] = base64_encode($rec['dn']);
    
    foreach ($this->fieldmap as $rf => $lf)
    {
      if ($rec[$lf]['count'])
        $out[$rf] = $rec[$lf][0];
    }
    
    return $out;
  }
  
  
  /**
   * @access private
   */
  function _map_field($field)
  {
    return $this->fieldmap[$field];
  }
  
  
  /**
   * @static
   */
  function quote_string($str)
  {
    return strtr($str, array('*'=>'\2a', '('=>'\28', ')'=>'\29', '\\'=>'\5c'));
  }


}

?>

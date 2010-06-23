<?php
/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_ldap.php                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2006-2009, RoundCube Dev. - Switzerland                 |
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
class rcube_ldap extends rcube_addressbook
{
  var $conn;
  var $prop = array();
  var $fieldmap = array();
  
  var $filter = '';
  var $result = null;
  var $ldap_result = null;
  var $sort_col = '';
  var $mail_domain = '';
  var $debug = false;
  
  /** public properties */
  var $primary_key = 'ID';
  var $readonly = true;
  var $list_page = 1;
  var $page_size = 10;
  var $ready = false;
  
  
  /**
   * Object constructor
   *
   * @param array 	LDAP connection properties
   * @param boolean 	Enables debug mode
   * @param string 	Current user mail domain name
   * @param integer User-ID
   */
  function __construct($p, $debug=false, $mail_domain=NULL)
  {
    $this->prop = $p;

    foreach ($p as $prop => $value)
      if (preg_match('/^(.+)_field$/', $prop, $matches))
        $this->fieldmap[$matches[1]] = $this->_attr_name(strtolower($value));

    // make sure 'required_fields' is an array
    if (!is_array($this->prop['required_fields']))
      $this->prop['required_fields'] = (array) $this->prop['required_fields'];

    foreach ($this->prop['required_fields'] as $key => $val)
      $this->prop['required_fields'][$key] = $this->_attr_name(strtolower($val));

    $this->sort_col = $p['sort'];
    $this->debug = $debug;
    $this->mail_domain = $mail_domain;

    $this->connect();
  }


  /**
   * Establish a connection to the LDAP server
   */
  function connect()
  {
    global $RCMAIL;
    
    if (!function_exists('ldap_connect'))
      raise_error(array('code' => 100, 'type' => 'ldap',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "No ldap support in this installation of PHP"), true);

    if (is_resource($this->conn))
      return true;
    
    if (!is_array($this->prop['hosts']))
      $this->prop['hosts'] = array($this->prop['hosts']);

    if (empty($this->prop['ldap_version']))
      $this->prop['ldap_version'] = 3;

    foreach ($this->prop['hosts'] as $host)
    {
      $host = rcube_parse_host($host);
      $this->_debug("C: Connect [$host".($this->prop['port'] ? ':'.$this->prop['port'] : '')."]");

      if ($lc = @ldap_connect($host, $this->prop['port']))
      {
        if ($this->prop['use_tls']===true)
          if (!ldap_start_tls($lc))
            continue;

        $this->_debug("S: OK");

        ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, $this->prop['ldap_version']);
        $this->prop['host'] = $host;
        $this->conn = $lc;
        break;
      }
      $this->_debug("S: NOT OK");
    }
    
    if (is_resource($this->conn))
    {
      $this->ready = true;

      // User specific access, generate the proper values to use.
      if ($this->prop['user_specific']) {
        // No password set, use the session password
        if (empty($this->prop['bind_pass'])) {
          $this->prop['bind_pass'] = $RCMAIL->decrypt($_SESSION['password']);
        }

        // Get the pieces needed for variable replacement.
        $fu = $RCMAIL->user->get_username();
        list($u, $d) = explode('@', $fu);
        $dc = 'dc='.strtr($d, array('.' => ',dc=')); // hierarchal domain string

        // Replace the bind_dn and base_dn variables.
        $replaces = array('%dc' => $dc, '%d' => $d, '%fu' => $fu, '%u' => $u);
        $this->prop['bind_dn'] = strtr($this->prop['bind_dn'], $replaces);
        $this->prop['base_dn'] = strtr($this->prop['base_dn'], $replaces);
      }

      if (!empty($this->prop['bind_dn']) && !empty($this->prop['bind_pass']))
        $this->ready = $this->bind($this->prop['bind_dn'], $this->prop['bind_pass']);
    }
    else
      raise_error(array('code' => 100, 'type' => 'ldap',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Could not connect to any LDAP server, last tried $host:{$this->prop[port]}"), true);

    // See if the directory is writeable.
    if ($this->prop['writable']) {
      $this->readonly = false;
    } // end if

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
    
    $this->_debug("C: Bind [dn: $dn] [pass: $pass]");
    
    if (@ldap_bind($this->conn, $dn, $pass)) {
      $this->_debug("S: OK");
      return true;
    }

    $this->_debug("S: ".ldap_error($this->conn));

    raise_error(array(
        'code' => ldap_errno($this->conn), 'type' => 'ldap',
	'file' => __FILE__, 'line' => __LINE__,
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
      $this->_debug("C: Close");
      ldap_unbind($this->conn);
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
   * @param  int    Only return this number of records
   * @return array  Indexed list of contact records, each a hash array
   */
  function list_records($cols=null, $subset=0)
  {
    // add general filter to query
    if (!empty($this->prop['filter']) && empty($this->filter))
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
      if ($this->sort_col && $this->prop['scope'] !== 'base')
        ldap_sort($this->conn, $this->ldap_result, $this->sort_col);

      $start_row = $subset < 0 ? $this->result->first + $this->page_size + $subset : $this->result->first;
      $last_row = $this->result->first + $this->page_size;
      $last_row = $subset != 0 ? $start_row + abs($subset) : $last_row;

      $entries = ldap_get_entries($this->conn, $this->ldap_result);
      for ($i = $start_row; $i < min($entries['count'], $last_row); $i++)
        $this->result->add($this->_ldap2result($entries[$i]));
    }

    return $this->result;
  }


  /**
   * Search contacts
   *
   * @param array   List of fields to search in
   * @param string  Search value
   * @param boolean True for strict, False for partial (fuzzy) matching
   * @param boolean True if results are requested, False if count only
   * @param boolean (Not used)
   * @param array   List of fields that cannot be empty
   * @return array  Indexed list of contact records and 'count' value
   */
  function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array())
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

    // add required (non empty) fields filter
    $req_filter = '';
    foreach ((array)$required as $field)
      if ($f = $this->_map_field($field))
        $req_filter .= "($f=*)";

    if (!empty($req_filter))
      $filter = '(&' . $req_filter . $filter . ')';

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
    if ($this->conn && $this->ldap_result) {
      $count = ldap_count_entries($this->conn, $this->ldap_result);
    } // end if
    elseif ($this->conn) {
      // We have a connection but no result set, attempt to get one.
      if (empty($this->filter)) {
        // The filter is not set, set it.
        $this->filter = $this->prop['filter'];
      } // end if
      $this->_exec_search();
      if ($this->ldap_result) {
        $count = ldap_count_entries($this->conn, $this->ldap_result);
      } // end if
    } // end else

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
      $dn = base64_decode($dn);

      $this->_debug("C: Read [dn: $dn] [(objectclass=*)]");
    
      if ($this->ldap_result = @ldap_read($this->conn, $dn, '(objectclass=*)', array_values($this->fieldmap)))
        $entry = ldap_first_entry($this->conn, $this->ldap_result);
      else
        $this->_debug("S: ".ldap_error($this->conn));

      if ($entry && ($rec = ldap_get_attributes($this->conn, $entry)))
      {
        $this->_debug("S: OK");

        $rec = array_change_key_case($rec, CASE_LOWER);

        // Add in the dn for the entry.
        $rec['dn'] = $dn;
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
   * @return encoded record ID on success, False on error
   */
  function insert($save_cols)
  {
    // Map out the column names to their LDAP ones to build the new entry.
    $newentry = array();
    $newentry['objectClass'] = $this->prop['LDAP_Object_Classes'];
    foreach ($save_cols as $col => $val) {
      $fld = $this->_map_field($col);
      if ($fld && $val) {
        // The field does exist, add it to the entry.
        $newentry[$fld] = $val;
      } // end if
    } // end foreach

    // Verify that the required fields are set.
    // We know that the email address is required as a default of rcube, so
    // we will default its value into any unfilled required fields.
    foreach ($this->prop['required_fields'] as $fld) {
      if (!isset($newentry[$fld])) {
        $newentry[$fld] = $newentry[$this->_map_field('email')];
      } // end if
    } // end foreach

    // Build the new entries DN.
    $dn = $this->prop['LDAP_rdn'].'='.rcube_ldap::quote_string($newentry[$this->prop['LDAP_rdn']], true)
      .','.$this->prop['base_dn'];

    $this->_debug("C: Add [dn: $dn]: ".print_r($newentry, true));

    $res = ldap_add($this->conn, $dn, $newentry);
    if ($res === FALSE) {
      $this->_debug("S: ".ldap_error($this->conn));
      return false;
    } // end if

    $this->_debug("S: OK");

    return base64_encode($dn);
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
    $record = $this->get_record($id, true);
    $result = $this->get_result();
    $record = $result->first();

    $newdata = array();
    $replacedata = array();
    $deletedata = array();
    foreach ($save_cols as $col => $val) {
      $fld = $this->_map_field($col);
      if ($fld) {
        // The field does exist compare it to the ldap record.
        if ($record[$col] != $val) {
          // Changed, but find out how.
          if (!isset($record[$col])) {
            // Field was not set prior, need to add it.
            $newdata[$fld] = $val;
          } // end if
          elseif ($val == '') {
            // Field supplied is empty, verify that it is not required.
            if (!in_array($fld, $this->prop['required_fields'])) {
              // It is not, safe to clear.
              $deletedata[$fld] = $record[$col];
            } // end if
          } // end elseif
          else {
            // The data was modified, save it out.
            $replacedata[$fld] = $val;
          } // end else
        } // end if
      } // end if
    } // end foreach

    $dn = base64_decode($id);

    // Update the entry as required.
    if (!empty($deletedata)) {
      // Delete the fields.
      $this->_debug("C: Delete [dn: $dn]: ".print_r($deletedata, true));
      if (!ldap_mod_del($this->conn, $dn, $deletedata)) {
        $this->_debug("S: ".ldap_error($this->conn));
        return false;
      }
      $this->_debug("S: OK");
    } // end if

    if (!empty($replacedata)) {
      // Handle RDN change
      if ($replacedata[$this->prop['LDAP_rdn']]) {
        $newdn = $this->prop['LDAP_rdn'].'='
	  .rcube_ldap::quote_string($replacedata[$this->prop['LDAP_rdn']], true)
	  .','.$this->prop['base_dn']; 
        if ($dn != $newdn) {
          $newrdn = $this->prop['LDAP_rdn'].'='
	    .rcube_ldap::quote_string($replacedata[$this->prop['LDAP_rdn']], true);
          unset($replacedata[$this->prop['LDAP_rdn']]);
        }
      }
      // Replace the fields.
      if (!empty($replacedata)) {
        $this->_debug("C: Replace [dn: $dn]: ".print_r($replacedata, true));
        if (!ldap_mod_replace($this->conn, $dn, $replacedata)) {
          $this->_debug("S: ".ldap_error($this->conn));
          return false;
	}
        $this->_debug("S: OK");
      } // end if
    } // end if

    if (!empty($newdata)) {
      // Add the fields.
      $this->_debug("C: Add [dn: $dn]: ".print_r($newdata, true));
      if (!ldap_mod_add($this->conn, $dn, $newdata)) {
        $this->_debug("S: ".ldap_error($this->conn));
        return false;
      }
      $this->_debug("S: OK");
    } // end if

    // Handle RDN change
    if (!empty($newrdn)) {
      $this->_debug("C: Rename [dn: $dn] [dn: $newrdn]");
      if (@ldap_rename($this->conn, $dn, $newrdn, NULL, TRUE)) {
        $this->_debug("S: ".ldap_error($this->conn));
        return base64_encode($newdn);
      }
      $this->_debug("S: OK");
    }

    return true;
  }
  
  
  /**
   * Mark one or more contact records as deleted
   *
   * @param array  Record identifiers
   * @return boolean True on success, False on error
   */
  function delete($ids)
  {
    if (!is_array($ids)) {
      // Not an array, break apart the encoded DNs.
      $dns = explode(',', $ids);
    } // end if

    foreach ($dns as $id) {
      $dn = base64_decode($id);
      $this->_debug("C: Delete [dn: $dn]");
      // Delete the record.
      $res = ldap_delete($this->conn, $dn);
      if ($res === FALSE) {
        $this->_debug("S: ".ldap_error($this->conn));
        return false;
      } // end if
      $this->_debug("S: OK");
    } // end foreach

    return count($dns);
  }


  /**
   * Execute the LDAP search based on the stored credentials
   *
   * @access private
   */
  private function _exec_search()
  {
    if ($this->ready)
    {
      $filter = $this->filter ? $this->filter : '(objectclass=*)';
      $function = $this->prop['scope'] == 'sub' ? 'ldap_search' : ($this->prop['scope'] == 'base' ? 'ldap_read' : 'ldap_list');

      $this->_debug("C: Search [".$filter."]");

      if ($this->ldap_result = @$function($this->conn, $this->prop['base_dn'], $filter,
          array_values($this->fieldmap), 0, (int) $this->prop['sizelimit'], (int) $this->prop['timelimit'])
      ) {
        $this->_debug("S: ".ldap_count_entries($this->conn, $this->ldap_result)." record(s)");
        return true;
      } else
        $this->_debug("S: ".ldap_error($this->conn));
    }
    
    return false;
  }
  
  
  /**
   * @access private
   */
  private function _ldap2result($rec)
  {
    global $RCMAIL;

    $out = array();
    
    if ($rec['dn'])
      $out[$this->primary_key] = base64_encode($rec['dn']);
    
    foreach ($this->fieldmap as $rf => $lf)
    {
      if ($rec[$lf]['count']) {
        if ($rf == 'email' && $this->mail_domain && !strpos($rec[$lf][0], '@'))
          $out[$rf] = sprintf('%s@%s', $rec[$lf][0], $this->mail_domain);
        else
          $out[$rf] = $rec[$lf][0];
      }
    }
    
    return $out;
  }
  
  
  /**
   * @access private
   */
  private function _map_field($field)
  {
    return $this->fieldmap[$field];
  }
  
  
  /**
   * @access private
   */
  private function _attr_name($name)
  {
    // list of known attribute aliases
    $aliases = array(
      'gn' => 'givenname',
      'rfc822mailbox' => 'email',
      'userid' => 'uid',
      'emailaddress' => 'email',
      'pkcs9email' => 'email',
    );
    return isset($aliases[$name]) ? $aliases[$name] : $name;
  }


  /**
   * @access private
   */
  private function _debug($str)
  {
    if ($this->debug)
      write_log('ldap', $str);
  }
  

  /**
   * @static
   */
  function quote_string($str, $dn=false)
  {
    if ($dn)
      $replace = array(','=>'\2c', '='=>'\3d', '+'=>'\2b', '<'=>'\3c',
        '>'=>'\3e', ';'=>'\3b', '\\'=>'\5c', '"'=>'\22', '#'=>'\23');
    else
      $replace = array('*'=>'\2a', '('=>'\28', ')'=>'\29', '\\'=>'\5c',
        '/'=>'\2f');

    return strtr($str, $replace);
  }

}


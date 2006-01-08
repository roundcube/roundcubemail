<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_ldap.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Manage an LDAP connection                                           |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Jeremy Jongsma <jeremy@jongsma.org>                           |
 +-----------------------------------------------------------------------+

 $Id$

*/

require_once("bugs.inc");

class rcube_ldap
  {
  var $conn;
  var $host;
  var $port;
  var $protocol;
  var $base_dn;
  var $bind_dn;
  var $bind_pass;

  // PHP 5 constructor
  function __construct()
    {
    }

  // PHP 4 constructor
  function rcube_ldap()
    {
    $this->__construct();
    }

  function connect($hosts, $port=389, $protocol=3)
    {
    if (!function_exists('ldap_connect'))
      raise_error(array("type" => "ldap",
                        "message" => "No ldap support in this installation of php."),
                         TRUE);

    if (is_resource($this->conn))
      return TRUE;
    
    if (!is_array($hosts))
      $hosts = array($hosts);

    foreach ($hosts as $host)
      {
      if ($lc = @ldap_connect($host, $port))
        {
        @ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, $protocol);
        $this->host = $host;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->conn = $lc;
        return TRUE;
        }
      }
    
    if (!is_resource($this->conn))
      raise_error(array("type" => "ldap",
                        "message" => "Could not connect to any LDAP server, tried $host:$port last"),
                         TRUE);
    }

  function close()
    {
    if ($this->conn)
      {
      if (@ldap_unbind($this->conn))
        return TRUE;
      else
        raise_error(array("code" => ldap_errno($this->conn),
                          "type" => "ldap",
                          "message" => "Could not close connection to LDAP server: ".ldap_error($this->conn)),
                    TRUE);
      }
    return FALSE;
    }

  // Merge with connect()?
  function bind($dn=null, $pass=null)
    {
    if ($this->conn)
      {
      if ($dn)
        if (@ldap_bind($this->conn, $dn, $pass))
          return TRUE;
        else
          raise_error(array("code" => ldap_errno($this->conn),
                            "type" => "ldap",
                            "message" => "Bind failed for dn=$dn: ".ldap_error($this->conn)),
                      TRUE);
      else
        if (@ldap_bind($this->conn))
          return TRUE;
        else
          raise_error(array("code" => ldap_errno($this->conn),
                            "type" => "ldap",
                            "message" => "Anonymous bind failed: ".ldap_error($this->conn)),
                      TRUE);
      }
    else
      raise_error(array("type" => "ldap",
                        "message" => "Attempted bind on nonexistent connection"), TRUE);
    return FALSE;
    }

  function count($base, $filter=null, $attributes=null, $scope="sub")
    {
    if ($this->conn)
      {
      if ($scope === 'sub')
        $sr = @ldap_search($this->conn, $base, $filter, $attributes, 0, $limit);
      else if ($scope === 'one')
        $sr = @ldap_list($this->conn, $base, $filter, $attributes, 0, $limit);
      else if ($scope === 'base')
        $sr = @ldap_read($this->conn, $base, $filter, $attributes, 0, $limit);
      if ($sr)
        return @ldap_count_entries($this->conn, $sr);
      }
    else
      raise_error(array("type" => "ldap",
                        "message" => "Attempted count search on nonexistent connection"), TRUE);
    return FALSE;
    }

  function search($base, $filter=null, $attributes=null, $scope='sub', $sort=null, $limit=0)
    {
    if ($this->conn)
      {
      if ($scope === 'sub')
        $sr = @ldap_search($this->conn, $base, $filter, $attributes, 0, $limit);
      else if ($scope === 'one')
        $sr = @ldap_list($this->conn, $base, $filter, $attributes, 0, $limit);
      else if ($scope === 'base')
        $sr = @ldap_read($this->conn, $base, $filter, $attributes, 0, $limit);
      if ($sr)
        {
        if ($sort && $scope !== "base")
          {
          if (is_array($sort))
            {
            // Start from the end so first sort field has highest priority
            $sortfields = array_reverse($sort);
            foreach ($sortfields as $sortfield)
              @ldap_sort($this->conn, $sr, $sortfield);
            }
          else
            @ldap_sort($this->conn, $sr, $sort);
          }
        return @ldap_get_entries($this->conn, $sr);
        }
      }
    else
      raise_error(array("type" => "ldap",
                        "message" => "Attempted search on nonexistent connection"), TRUE);
    return FALSE;
    }

  function add($dn, $object)
    {
    if ($this->conn)
      {
      if (@ldap_add($this->conn, $dn, $object))
        return TRUE;
      else
        raise_error(array("code" => ldap_errno($this->conn),
                          "type" => "ldap",
                          "message" => "Add object failed: ".ldap_error($this->conn)),
                    TRUE);
      }
    else
      raise_error(array("type" => "ldap",
                        "message" => "Add object faile: no connection"),
                  TRUE);
    return FALSE;
    }

  function modify($dn, $object)
    {
    if ($this->conn)
      {
      if (@ldap_modify($this->conn, $dn, $object))
        return TRUE;
      else
        raise_error(array("code" => ldap_errno($this->conn),
                          "type" => "ldap",
                          "message" => "Modify object failed: ".ldap_error($this->conn)),
                    TRUE);
      }
    else
      raise_error(array("type" => "ldap",
                        "message" => "Modify object failed: no connection"),
                  TRUE);
    return FALSE;
    }

  function rename($dn, $newrdn, $parentdn)
    {
    if ($this->protocol < 3)
      {
      raise_error(array("type" => "ldap",
                        "message" => "rename() support requires LDAPv3 or above "),
                  TRUE);
      return FALSE;
      }

    if ($this->conn)
      {
      if (@ldap_rename($this->conn, $dn, $newrdn, $parentdn, TRUE))
        return TRUE;
      else
        raise_error(array("code" => ldap_errno($this->conn),
                          "type" => "ldap",
                          "message" => "Rename object failed: ".ldap_error($this->conn)),
                    TRUE);
      }
    else
      raise_error(array("type" => "ldap",
                        "message" => "Rename object failed: no connection"),
                  TRUE);
    return FALSE;
    }

  function delete($dn)
    {
    if ($this->conn)
      {
      if (@ldap_delete($this->conn, $dn))
        return TRUE;
      else
        raise_error(array("code" => ldap_errno($this->conn),
                          "type" => "ldap",
                          "message" => "Delete object failed: ".ldap_error($this->conn)),
                    TRUE);
      }
    else
      raise_error(array("type" => "ldap",
                        "message" => "Delete object failed: no connection"),
                  TRUE);
    return FALSE;
    }

  }

// vi: et ts=2 sw=2
?>

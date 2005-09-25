<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_mysql.inc                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 | PURPOSE:                                                              |
 |   MySQL wrapper class that implements PHP MySQL functions             |
 |   See http://www.php.net/manual/en/ref.mysql.php                      |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


class rcube_mysql
  {
  var $db_link;
  var $db_host = 'localhost';
  var $db_name = '';
  var $db_user = '';
  var $db_pass = '';
  var $a_query_results = array('dummy');
  var $last_res_id = 0;


  // PHP 5 constructor
  function __construct($db_name='', $user='', $pass='', $host='localhost')
    {
    $this->db_host = $host;
    $this->db_name = $db_name;
    $this->db_user = $user;
    $this->db_pass = $pass;
    }

  // PHP 4 compatibility
  function rcube_mysql($db_name='', $user='', $pass='', $host='localhost')
    {
    $this->__construct($db_name, $user, $pass, $host);
    }


  function connect()
    {
    $this->db_link = mysql_connect($this->db_host, $this->db_user, $this->db_pass);
      
    if (!$this->db_link)
      {
      raise_error(array('code' => 500,
                        'type' => 'mysql',
                        'line' => __LINE__,
                        'file' => __FILE__,
                        'message' => "Can't connect to database"), TRUE, FALSE);
      return FALSE;
      }
    
    return TRUE;
    }


  function select_db($name)
    {
    $this->db_name = $name;

    if ($this->db_link)
      mysql_select_db($name, $this->db_link);
    }


  function query($query)
    {
    // establish a connection
    if (!$this->db_link)
      {
      if (!$this->connect())
        return FALSE;
      }

    $sql_result = mysql_db_query($this->db_name, $query, $this->db_link);
    return $this->_add_result($sql_result, $query);
    }


  function num_rows($res_id=NULL)
    {
    if (!$this->db_link)
      return FALSE;

    $sql_result = $this->_get_result($res_id);
    
    if ($sql_result)    
      return mysql_num_rows($sql_result);
    else
      return FALSE;
    }


  function affected_rows()
    {
    if (!$this->db_link)
      return FALSE;

    return mysql_affected_rows($this->db_link);
    }


  function insert_id()
    {
    if (!$this->db_link)
      return FALSE;

    return mysql_insert_id($this->db_link);
    }


  function fetch_assoc($res_id=NULL)
    {
    $sql_result = $this->_get_result($res_id);
    
    if ($sql_result)
      return mysql_fetch_assoc($sql_result);
    else
      return FALSE;
    }


  function seek($res_id=NULL, $row=0)
    {
    $sql_result = $this->_get_result($res_id);
    
    if ($sql_result)
      return mysql_data_seek($sql_result, $row);
    else
      return FALSE;
    }



  function _add_result($res, $query)
    {
    // sql error occured
    if ($res===FALSE)
      {
      $sql_error = mysql_error($this->db_link);
      raise_error(array('code' => 500,
                        'type' => 'mysql',
                        'line' => __LINE__,
                        'file' => __FILE__,
                        'message' => $sql_error."; QUERY: ".preg_replace('/[\r\n]+\s*/', ' ', $query)), TRUE, FALSE);
      
      return FALSE;
      }
    else
      {
      $res_id = sizeof($this->a_query_results);
      $this->a_query_results[$res_id] = $res;
      $this->last_res_id = $res_id;

      return $res_id;
      }
    }


  function _get_result($res_id)
    {
    if ($res_id===NULL)
      $res_id = $this->last_res_id;

    if ($res_id && isset($this->a_query_results[$res_id]))
      return $this->a_query_results[$res_id];
    else
      return FALSE;
    }

  }


?>
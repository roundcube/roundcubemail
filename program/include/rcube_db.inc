<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_db.inc                                          |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   PEAR:DB wrapper class that implements PEAR DB functions             |
 |   See http://pear.php.net/package/DB                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: David Saez Padros <david@ols.es>                              |
 +-----------------------------------------------------------------------+

 $Id$

*/

require_once('DB.php');

class rcube_db
{
    var $db_dsnw;               // DSN for write operations
    var $db_dsnr;               // DSN for read operations
    var $db_connected=false;    // Already connected ?
    var $db_mode='';            // Connection mode
    var $db_handle=0;           // Connection handle

    var $a_query_results = array('dummy');
    var $last_res_id = 0;

    // PHP 5 constructor
    function __construct($db_dsnw,$db_dsnr='')
    {
        if ($db_dsnr=='') $db_dsnr=$db_dsnw;
        
        $this->db_dsnw = $db_dsnw;
        $this->db_dsnr = $db_dsnr;
        
        $dsn_array = DB::parseDSN($db_dsnw);
        $this->db_provider = $dsn_array['phptype'];        
    }

    // PHP 4 compatibility
    function rcube_db($db_dsnw,$db_dsnr='')
    {
        $this->__construct($db_dsnw,$db_dsnr);
    }

    // Connect to specific database 
    function dsn_connect($dsn)
    {
        // Use persistent connections if available
        $dbh = DB::connect($dsn, array('persistent' => $true));
        
        if (DB::isError($dbh))
            raise_error(array('code' => 500,
                        'type' => 'db',
                        'line' => __LINE__,
                        'file' => __FILE__,
                        'message' => $dbh->getMessage()), TRUE, FALSE);

        else if ($this->db_provider=='sqlite')
        {
            $dsn_array = DB::parseDSN($dsn);
            if (!filesize($dsn_array['database']) && !empty($this->sqlite_initials))
                $this->_sqlite_create_database($dbh, $this->sqlite_initials);
        }
        
        return $dbh;
    }

    // Connect to appropiate databse    
    function db_connect ($mode)
    {
        $this->db_mode = $mode;

        // Already connected
        if ($this->db_connected)
            {
            // no replication, current connection is ok
            if ($this->db_dsnw==$this->db_dsnr) return;
            
            // connected to master, current connection is ok
            if ($this->db_mode=='w') return;

            // Same mode, current connection is ok
            if ($this->db_mode==$mode) return;
            }
            
        if ($mode=='r')
            $dsn=$this->db_dsnr;
        else
            $dsn=$this->db_dsnw;

        $this->db_handle = $this->dsn_connect($dsn);
        $this->db_connected = true;
    }

    // Query database
    
    function query()
    {
		$params = func_get_args();
		$query = array_shift($params);
		
		return $this->_query($query, 0, 0, $params);
    }
   
	function limitquery()
    {
		$params = func_get_args();
		$query = array_shift($params);
		$offset = array_shift($params);
		$numrows = array_shift($params);
		
		return $this->_query($query, $offset, $numrows, $params);
    }
    
    function _query($query, $offset, $numrows, $params)
    {
        // Read or write ?
        if (strtolower(trim(substr($query,0,6)))=='select')
            $mode='r';
        else
            $mode='w';
        
        $this->db_connect($mode);

        if ($this->db_provider == 'sqlite')
            $query = $this->_sqlite_prepare_query($query);
        
        if ($numrows || $offset)
			{
			$result = $this->db_handle->limitQuery($query,$offset,$numrows,$params);
			}
        else    
			$result = $this->db_handle->query($query, $params);

        if (DB::isError($result))
			{
            raise_error(array('code' => 500,
                              'type' => 'db',
                              'line' => __LINE__, 
                              'file' => __FILE__, 
                              'message' => $result->getMessage().'; QUERY: '.$query), TRUE, FALSE);
             return false;
            }

        return $this->_add_result($result, $query);
    }
    
    function num_rows($res_id=NULL)
    {
        if (!$this->db_handle)
            return FALSE;

        $result = $this->_get_result($res_id);
    
        if ($result)    
              return $result->numRows();
        else
              return FALSE;
    }

    function affected_rows($res_id=NULL)
    {
        if (!$this->db_handle)
            return FALSE;
    
        return $this->db_handle->affectedRows();
    }

    function insert_id($sequence = '')
    {
        if (!$this->db_handle || $this->db_mode=='r')
            return FALSE;

        switch($this->db_provider)
        {
            case 'pgsql':
                // PostgreSQL uses sequences
                $result =& $this->db_handle->getOne("SELECT CURRVAL('$sequence')");    
                if (DB::isError($result)) {
                    raise_error( array('code' => 500, 'type' => 'db', 'line' => __LINE__, 'file' => __FILE__, 
                    'message' => $result->getMessage()), TRUE, TRUE);
                }
                return $result;
                
            case 'mysql': // This is unfortuneate
                return mysql_insert_id($this->db_handle->connection);

			case 'mysqli':
				return mysqli_insert_id($this->db_handle->connection);
	
            case 'sqlite':
                return sqlite_last_insert_rowid($this->db_handle->connection);

            default:
                die("portability issue with this database, please have the developer fix");
        }
    }


    function fetch_assoc($res_id=NULL)
    {
        $result = $this->_get_result($res_id);

        if (DB::isError($result))
        {
            raise_error( array('code' => 500, 'type' => 'db', 'line' => __LINE__, 'file' => __FILE__,
                         'message' => $this->db_link->getMessage()), TRUE, FALSE);
            return FALSE;
        }
                         
        return $result->fetchRow(DB_FETCHMODE_ASSOC);
    }


    function quote($input, $type=null)
    {
		if (!$this->db_handle)
			$this->db_connect('r');

		return $this->db_handle->quote($input);
    }
    

	function quoteIdentifier($str)
	{
		if (!$this->db_handle)
			$this->db_connect('r');
			
		return $this->db_handle->quoteIdentifier($str);
	}
	
	function quote_identifier($str)
	{
		return $this->quoteIdentifier($str);
	}

	
	function unixtimestamp($field)
	{
		switch($this->db_provider)
			{
			case 'pgsql':
				return "EXTRACT (EPOCH FROM $field)";
				break;
			default:
				return "UNIX_TIMESTAMP($field)";
			}
	}
	
    function _add_result($res, $query)
    {
        // sql error occured
        if (DB::isError($res))
        {
            raise_error(array('code' => 500, 'type' => 'db', 'line' => __LINE__, 'file' => __FILE__, 'message' => $res->getMessage() . " Query: " . substr(preg_replace('/[\r\n]+\s*/', ' ', $query), 0, 1024)), TRUE, FALSE);
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
        if ($res_id==NULL)
            $res_id = $this->last_res_id;
    
        if ($res_id && isset($this->a_query_results[$res_id]))
            return $this->a_query_results[$res_id];
        else
        return FALSE;
    }


    // create a sqlite database from a file
    function _sqlite_create_database($dbh, $fileName)
    {
        if (empty($fileName) || !is_string($fileName))
            return ;

        $data = '';
        if ($fd = fopen($fileName, 'r'))
          {
          $data = fread($fd, filesize($fileName));
          fclose($fd);
          }

        if (strlen($data))
          sqlite_exec($dbh->connection, $data);
    }

    // transform a query so that it is sqlite2 compliant
    function _sqlite_prepare_query($query)
    {
        if (!is_string($query))
            return ($query);
            
        $search = array('/NOW\(\)/i', '/`/');
        $replace = array("datetime('now')", '"');
        $query = preg_replace($search, $replace, $query);

        return ($query);
    }
    
}

?>
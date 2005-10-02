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
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

require_once('DB.php');

class rcube_db
{
    var $db_dsnw;				// DSN for write operations
    var $db_dsnr;				// DSN for read operations
    var $db_connected=false;	// Already connected ?
	var $db_mode='';			// Connection mode
	var $db_handle=0;			// Connection handle

    var $a_query_results = array('dummy');
    var $last_res_id = 0;

    // PHP 5 constructor
    function __construct($db_dsnw,$db_dsnr='')
    {
		if ($db_dsnr=='') $db_dsnr=$db_dsnw;
		
        $this->db_dsnw = $db_dsnw;
        $this->db_dsnr = $db_dsnr;
    }

    // PHP 4 compatibility
    function rcube_db($db_dsnw,$db_dsnr='')
    {
        $this->__construct($db_dsnw,$db_dsnr);
    }

	// Connect to specific database	
	function dsn_connect($dsn)
	{
		$dbh = DB::connect($dsn);

		if (DB::isError($dbh))
			raise_error(array('code' => 500,
                        'type' => 'db',
                        'line' => __LINE__,
                        'file' => __FILE__,
                        'message' => $dbh->getMessage()), TRUE, FALSE);
		return $dbh;
	}

	// Connect to appropiate databse	
	function db_connect ($mode)
	{
		if ($this->db_connected && $this->db_mode=='w') return;

		if ($this->db_connected && $this->db_mode==$mode) return;

		if ($mode=='r')
			$dsn=$this->db_dsnr;
		else
			$dsn=$this->db_dsnw;

		$this->db_handle = $this->dsn_connect($dsn);
		$this->db_connected = true;
		$this->db_mode = $mode;
	}

	// Query database (read operations)
	
    function query($query)
    {
		// Read or write ?
		
		if (strtolower(trim(substr($query,0,6)))=='select')
			$mode='r';
		else
			{
			$mode='w';
			}
		
		$this->db_connect($mode);			
    
        $result = $this->db_handle->query($query);
        
        if (DB::isError($result))
			raise_error( array('code' => 500, 'type' => 'db', 'line' => __LINE__, 
			             'file' => __FILE__, 
			             'message' => $result->getMessage()), TRUE, FALSE);
        
        return $this->_add_result($result, $query);
    }

	function db_execute ($query)
	{
		db_connect('w');

		$result = $this->db_handle->query($query);

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
    
        return $this->last_res_id->affectedRows();
    }

    function insert_id($sequence = '')
    {
        if (!$this->db_link || $this->db_mode=='r')
            return FALSE;

        switch($this->db_provider)
        {
            case 'pgsql':
                // PostgreSQL uses sequences
                $result =& $this->db_handle->getOne("SELECT CURRVAL('$sequence')");    
                if (DB::isError($result))
                    raise_error( array('code' => 500, 'type' => 'db', 'line' => __LINE__, 'file' => __FILE__, 
                    'message' => $result->getMessage()), TRUE, TRUE);
                return $result;
                
            case 'mysql': // This is unfortuneate
                return mysql_insert_id($this->db_handle);
                
            default:
                die("portability issue with this database, please have the developer fix");
        }
    }


    function fetch_assoc($res_id=NULL)
    {
        $result = $this->_get_result($res_id);

        if (DB::isError($result))
            raise_error( array('code' => 500, 'type' => 'db', 'line' => __LINE__, 'file' => __FILE__,
                         'message' => $this->db_link->getMessage()), TRUE, TRUE);
                         
        return $result->fetchRow(DB_FETCHMODE_ASSOC);
    }

    function _add_result($res, $query)
    {
        // sql error occured
        if (DB::isError($res))
        {
            raise_error(array('code' => 500, 'type' => 'db', 'line' => __LINE__, 'file' => __FILE__, 'message' => $res->getMessage() . " Query: " . preg_replace('/[\r\n]+\s*/', ' ', $query)), TRUE, FALSE);
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

}

?>
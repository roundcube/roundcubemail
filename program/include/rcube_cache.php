<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_cache.php                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011, The Roundcube Dev Team                            |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Caching engine                                                      |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Interface class for accessing Roundcube cache
 *
 * @package    Cache
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 * @version    1.0
 */
class rcube_cache
{
    /**
     * Instance of rcube_mdb2 or Memcache class
     *
     * @var rcube_mdb2/Memcache
     */
    private $db;
    private $type;
    private $userid;
    private $prefix;
    private $db_readed;
    private $cache         = array();
    private $cache_keys    = array();
    private $cache_changes = array();
    private $cache_sums    = array();


    /**
     * Object constructor.
     *
     * @param string $type   Engine type ('db' or 'memcache' or 'apc')
     * @param int    $userid User identifier
     * @param string $prefix Key name prefix
     */
    function __construct($type, $userid, $prefix='')
    {
        $rcmail = rcmail::get_instance();
        $type   = strtolower($type);
    
        if ($type == 'memcache') {
            $this->type = 'memcache';
            $this->db   = $rcmail->get_memcache();
        }
        else if ($type == 'apc') {
            $this->type = 'apc';
            $this->db   = function_exists('apc_exists'); // APC 3.1.4 required
        }
        else {
            $this->type = 'db';
            $this->db   = $rcmail->get_dbh();
        }

        $this->userid = (int) $userid;
        $this->prefix = $prefix;
    }


    /**
     * Returns cached value.
     *
     * @param string $key Cache key
     *
     * @return mixed Cached value
     */
    function get($key)
    {
        // read cache (if it was read before)
        if (!$this->db_readed)
            $this->read_cache();

        return $this->cache[$key];
    }


    /**
     * Sets (add/update) value in cache.
     *
     * @param string $key  Cache key
     * @param mixed  $data Data
     */
    function set($key, $data)
    {
        $this->cache[$key]         = $data;
        $this->cache_changed       = true;
        $this->cache_changes[$key] = true;
    }


    /**
     * Clears the cache.
     *
     * @param string  $key         Cache key name or pattern
     * @param boolean $prefix_mode Enable it to clear all keys starting
     *                             with prefix specified in $key
     */
    function remove($key=null, $pattern_mode=false)
    {
        // Remove all keys
        if ($key === null) {
            $this->cache         = array();
            $this->cache_changed = false;
            $this->cache_changes = array();
            $this->cache_keys    = array();
            $where = " AND cache_key LIKE " . $this->db->quote($this->prefix.'.%');
        }
        // Remove keys by name prefix
        else if (!$prefix_mode) {
            $this->cache[$key] = null;
            $this->cache_changes[$key] = false;
            unset($this->cache_keys[$key]);
            $where = " AND cache_key = " . $this->db->quote($this->prefix.'.'.$key);
        }
        // Remove one key by name
        else {
            foreach (array_keys($this->cache) as $k) {
                if (strpos($k, $key) === 0) {
                    $this->cache[$k] = null;
                    $this->cache_changes[$k] = false;
                    unset($this->cache_keys[$k]);
                }
            }
            $where = " AND cache_key LIKE " . $this->db->quote($this->prefix.'.'.$key.'%');
            if (!count($this->cache)) {
                $this->cache_changed = false;
            }
        }

        if (!$this->db) {
            return;
        }

        if ($this->type != 'db') {
            return;
        }

        $this->db->query(
            "DELETE FROM ".get_table_name('cache').
            " WHERE user_id = ?" . $where,
            $this->userid);
    }


    /**
     * Writes the cache back to the DB.
     */
    function close()
    {
        if (!$this->cache_changed) {
            return;
        }

        $this->write_cache();
    }


    /**
     * Reads cache entries.
     * @access private
     */
    private function read_cache()
    {
        if (!$this->db) {
            return null;
        }

        if ($this->type == 'memcache') {
            $data = $this->db->get($this->ckey());
        }
        else if ($this->type == 'apc') {
            $data = apc_fetch($this->ckey());
	    }

        if ($data) {
            $this->cache_sums['data'] = md5($data);
            $data = unserialize($data);
            if (is_array($data)) {
                $this->cache = $data;
            }
        }
        else if ($this->type == 'db') {
            // get cached data from DB
            $sql_result = $this->db->query(
                "SELECT cache_id, data, cache_key".
                " FROM ".get_table_name('cache').
                " WHERE user_id = ?".
                " AND cache_key LIKE " . $this->db->quote($this->prefix.'.%'),
                $this->userid);

            while ($sql_arr = $this->db->fetch_assoc($sql_result)) {
                $key = substr($sql_arr['cache_key'], strlen($this->prefix)+1);
                $md5sum = $sql_arr['data'] ? md5($sql_arr['data']) : null;
                $data   = $sql_arr['data'] ? unserialize($sql_arr['data']) : null;
    	        $this->cache[$key]      = $data;
	            $this->cache_sums[$key] = $md5sum;
                $this->cache_keys[$key] = $sql_arr['cache_id'];
            }
        }

        $this->db_readed = true;        
    }


    /**
     * Writes the cache content into DB.
     *
     * @return boolean Write result
     * @access private
     */
    private function write_cache()
    {
        if (!$this->db) {
            return false;
        }

        if ($this->type == 'memcache' || $this->type == 'apc') {
            // remove nulls
            foreach ($this->cache as $idx => $value) {
                if ($value === null)
                    unset($this->cache[$idx]);
            }

            $data = serialize($this->cache);
            $key  = $this->ckey();

            // Don't save the data if nothing changed
            if ($this->cache_sums['data'] && $this->cache_sums['data'] == md5($data)) {
                return true;
            }

            if ($this->type == 'memcache') {
                $result = $this->db->replace($key, $data, MEMCACHE_COMPRESSED);
                if (!$result)
                    $result = $this->db->set($key, $data, MEMCACHE_COMPRESSED);
                return $result;
            }

            if ($this->type == 'apc') {
                if (apc_exists($key))
                    apc_delete($key);
                return apc_store($key, $data);
            }
        }

        foreach ($this->cache as $key => $data) {
            // The key has been used
            if ($this->cache_changes[$key]) {
                // Make sure we're not going to write unchanged data
                // by comparing current md5 sum with the sum calculated on DB read
                $data = serialize($data);

                if (!$this->cache_sums[$key] || $this->cache_sums[$key] != md5($data)) {
                    $this->write_record($key, $data);
                }
            }
        }

        return true;
    }


    /**
     * Writes single cache record into SQL database.
     *
     * @param string $key  Cache key
     * @param mxied  $data Cache value
     * @access private
     */
    private function write_record($key, $data)
    {
        if (!$this->db) {
            return false;
        }

        // update existing cache record
        if ($this->cache_keys[$key]) {
            $this->db->query(
                "UPDATE ".get_table_name('cache').
                " SET created = ". $this->db->now().", data = ?".
                " WHERE user_id = ?".
                " AND cache_key = ?",
                $data, $this->userid, $this->prefix . '.' . $key);
        }
        // add new cache record
        else {
            $this->db->query(
                "INSERT INTO ".get_table_name('cache').
                " (created, user_id, cache_key, data)".
                " VALUES (".$this->db->now().", ?, ?, ?)",
                $this->userid, $this->prefix . '.' . $key, $data);
        }
    }


    /**
     * Creates per-user cache key (for memcache and apc)
     *
     * @return string Cache key
     * @access private
     */
    private function ckey()
    {
        return sprintf('%d-%s', $this->userid, $this->prefix);
    }
}

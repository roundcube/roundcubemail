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
        $key = $this->prefix.$key;
    
        if ($this->type == 'memcache') {
            return $this->read_cache_record($key);
        }

        // read cache (if it was not read before)
        if (!count($this->cache)) {
            $do_read = true;
        }
        else if (isset($this->cache[$key])) {
            $do_read = false;
        }
        // Find cache prefix, we'll load data for all keys
        // with specified (class) prefix into internal cache (memory)
        else if ($pos = strpos($key, '.')) {
            $prefix = substr($key, 0, $pos);
            $regexp = '/^' . preg_quote($prefix, '/') . '/';
            if (!count(preg_grep($regexp, array_keys($this->cache_keys)))) {
                $do_read = true;
            }
        }

        if ($do_read) {
            return $this->read_cache_record($key);
        }

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
        $key = $this->prefix.$key;

        $this->cache[$key]         = $data;
        $this->cache_changed       = true;
        $this->cache_changes[$key] = true;
    }


    /**
     * Clears the cache.
     *
     * @param string  $key          Cache key name or pattern
     * @param boolean $pattern_mode Enable it to clear all keys with name
     *                              matching PREG pattern in $key
     */
    function remove($key=null, $pattern_mode=false)
    {
        if ($key === null) {
            foreach (array_keys($this->cache) as $key)
                $this->clear_cache_record($key);

            $this->cache         = array();
            $this->cache_changed = false;
            $this->cache_changes = array();
        }
        else if ($pattern_mode) {
            // add cache prefix into PCRE expression
            if (preg_match('/^(.)([^a-z0-9]*).*/i', $key, $matches)) {
                $key = $matches[1] . $matches[2] . preg_quote($this->prefix, $matches[1])
                    . substr($key, strlen($matches[1].$matches[2]));
            }
            else {
                $key = $this->prefix.$key;
            }

            foreach (array_keys($this->cache) as $k) {
                if (preg_match($key, $k)) {
                    $this->clear_cache_record($k);
                    $this->cache_changes[$k] = false;
                    unset($this->cache[$key]);
                }
            }
            if (!count($this->cache)) {
                $this->cache_changed = false;
            }
        }
        else {
            $key = $this->prefix.$key;

            $this->clear_cache_record($key);
            $this->cache_changes[$key] = false;
            unset($this->cache[$key]);
        }
    }


    /**
     * Writes the cache back to the DB.
     */
    function close()
    {
        if (!$this->cache_changed) {
            return;
        }

        foreach ($this->cache as $key => $data) {
            // The key has been used
            if ($this->cache_changes[$key]) {
                // Make sure we're not going to write unchanged data
                // by comparing current md5 sum with the sum calculated on DB read
                $data = serialize($data);
                if (!$this->cache_sums[$key] || $this->cache_sums[$key] != md5($data)) {
                    $this->write_cache_record($key, $data);
                }
            }
        }
    }


    /**
     * Returns cached entry.
     *
     * @param string $key Cache key
     *
     * @return mixed Cached value
     * @access private
     */
    private function read_cache_record($key)
    {
        if (!$this->db) {
            return null;
        }

        if ($this->type == 'memcache') {
            $data = $this->db->get($this->ckey($key));
	        
            if ($data) {
                $this->cache_sums[$key] = md5($data);
                $data = unserialize($data);
            }
            return $this->cache[$key] = $data;
        }

        if ($this->type == 'apc') {
            $data = apc_fetch($this->ckey($key));
	        
            if ($data) {
                $this->cache_sums[$key] = md5($data);
                $data = unserialize($data);
            }
            return $this->cache[$key] = $data;
        }

        // Find cache prefix, we'll load data for all keys
        // with specified (class) prefix into internal cache (memory)
        if ($pos = strpos($key, '.')) {
            $prefix = substr($key, 0, $pos);
            $where = " AND cache_key LIKE '$prefix%'";
        }
        else {
            $where = " AND cache_key = ".$this->db->quote($key);
        }

        // get cached data from DB
        $sql_result = $this->db->query(
            "SELECT cache_id, data, cache_key".
            " FROM ".get_table_name('cache').
            " WHERE user_id = ?".$where,
            $this->userid);

        while ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $sql_key = $sql_arr['cache_key'];
            $this->cache_keys[$sql_key] = $sql_arr['cache_id'];
	        if (!isset($this->cache[$sql_key])) {
                $md5sum = $sql_arr['data'] ? md5($sql_arr['data']) : null;
                $data   = $sql_arr['data'] ? unserialize($sql_arr['data']) : false;
	            $this->cache[$sql_key]      = $data;
	            $this->cache_sums[$sql_key] = $md5sum;
            }
        }

        return $this->cache[$key];
    }


    /**
     * Writes single cache record.
     *
     * @param string $key  Cache key
     * @param mxied  $data Cache value
     * @access private
     */
    private function write_cache_record($key, $data)
    {
        if (!$this->db) {
            return false;
        }

        if ($this->type == 'memcache') {
            $key = $this->ckey($key);
            $result = $this->db->replace($key, $data, MEMCACHE_COMPRESSED);
            if (!$result)
                $result = $this->db->set($key, $data, MEMCACHE_COMPRESSED);
            return $result;
        }

        if ($this->type == 'apc') {
            $key = $this->ckey($key);
            if (apc_exists($key))
                apc_delete($key);
            return apc_store($key, $data);
        }

        // update existing cache record
        if ($this->cache_keys[$key]) {
            $this->db->query(
                "UPDATE ".get_table_name('cache').
                " SET created = ". $this->db->now().", data = ?".
                " WHERE user_id = ?".
                " AND cache_key = ?",
                $data, $this->userid, $key);
        }
        // add new cache record
        else {
            $this->db->query(
                "INSERT INTO ".get_table_name('cache').
                " (created, user_id, cache_key, data)".
                " VALUES (".$this->db->now().", ?, ?, ?)",
                $this->userid, $key, $data);

            // get cache entry ID for this key
            $sql_result = $this->db->query(
                "SELECT cache_id".
                " FROM ".get_table_name('cache').
                " WHERE user_id = ?".
                " AND cache_key = ?",
                $this->userid, $key);

            if ($sql_arr = $this->db->fetch_assoc($sql_result))
                $this->cache_keys[$key] = $sql_arr['cache_id'];
        }
    }


    /**
     * Clears cache for single record.
     *
     * @param string $key Cache key
     * @access private
     */
    private function clear_cache_record($key)
    {
        if (!$this->db) {
            return false;
        }

        if ($this->type == 'memcache') {
            return $this->db->delete($this->ckey($key));
        }

        if ($this->type == 'apc') {
            return apc_delete($this->ckey($key));
        }

        $this->db->query(
            "DELETE FROM ".get_table_name('cache').
            " WHERE user_id = ?".
            " AND cache_key = ?",
            $this->userid, $key);

        unset($this->cache_keys[$key]);
    }


    /**
     * Creates per-user cache key (for memcache and apc)
     *
     * @param string $key Cache key
     * @access private
     */
    private function ckey($key)
    {
        return sprintf('[%d]%s', $this->userid, $key);
    }
}

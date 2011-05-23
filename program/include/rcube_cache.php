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
    private $ttl;
    private $index;
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
     * @param int    $ttl    Expiration time of memcache/apc items in seconds (max.2592000)
     */
    function __construct($type, $userid, $prefix='', $ttl=0)
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
        $this->ttl    = (int) $ttl;
        $this->prefix = $prefix;
    }


    /**
     * Returns cached value.
     *
     * @param string $key Cache key name
     *
     * @return mixed Cached value
     */
    function get($key)
    {
        if (!array_key_exists($key, $this->cache)) {
            return $this->read_record($key);
        }

        return $this->cache[$key];
    }


    /**
     * Sets (add/update) value in cache.
     *
     * @param string $key  Cache key name
     * @param mixed  $data Cache data
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
    function remove($key=null, $prefix_mode=false)
    {
        // Remove all keys
        if ($key === null) {
            $this->cache         = array();
            $this->cache_changed = false;
            $this->cache_changes = array();
            $this->cache_keys    = array();
        }
        // Remove keys by name prefix
        else if ($prefix_mode) {
            foreach (array_keys($this->cache) as $k) {
                if (strpos($k, $key) === 0) {
                    $this->cache[$k] = null;
                    $this->cache_changes[$k] = false;
                    unset($this->cache_keys[$k]);
                }
            }
        }
        // Remove one key by name
        else {
            $this->cache[$key] = null;
            $this->cache_changes[$key] = false;
            unset($this->cache_keys[$key]);
        }

        // Remove record(s) from the backend
        $this->remove_record($key, $prefix_mode);
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
                    $this->write_record($key, $data);
                }
            }
        }

        $this->write_index();
    }


    /**
     * Reads cache entry.
     *
     * @param string $key Cache key name
     *
     * @return mixed Cached value
     */
    private function read_record($key)
    {
        if (!$this->db) {
            return null;
        }

        if ($this->type == 'memcache') {
            $data = $this->db->get($this->ckey($key));
        }
        else if ($this->type == 'apc') {
            $data = apc_fetch($this->ckey($key));
	    }

        if ($data) {
            $this->cache_sums[$key] = md5($data);
            $this->cache[$key]      = unserialize($data);
        }
        else if ($this->type == 'db') {
            $sql_result = $this->db->limitquery(
                "SELECT cache_id, data, cache_key".
                " FROM ".get_table_name('cache').
                " WHERE user_id = ?".
                " AND cache_key = ?".
                // for better performance we allow more records for one key
                // get the newer one
                " ORDER BY created DESC",
                0, 1, $this->userid, $this->prefix.'.'.$key);

            if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
                $key = substr($sql_arr['cache_key'], strlen($this->prefix)+1);
                $md5sum = $sql_arr['data'] ? md5($sql_arr['data']) : null;
                $data   = $sql_arr['data'] ? unserialize($sql_arr['data']) : null;
                $this->cache[$key]      = $data;
	            $this->cache_sums[$key] = $md5sum;
                $this->cache_keys[$key] = $sql_arr['cache_id'];
            }
        }

        return $this->cache[$key];
    }


    /**
     * Writes single cache record into DB.
     *
     * @param string $key  Cache key name
     * @param mxied  $data Serialized cache data 
     */
    private function write_record($key, $data)
    {
        if (!$this->db) {
            return false;
        }

        if ($this->type == 'memcache' || $this->type == 'apc') {
            return $this->add_record($this->ckey($key), $data);
        }

        $key_exists = $this->cache_keys[$key];
        $key        = $this->prefix . '.' . $key;

        // Remove NULL rows (here we don't need to check if the record exist)
        if ($data == 'N;') {
            $this->db->query(
                "DELETE FROM ".get_table_name('cache').
                " WHERE user_id = ?".
                " AND cache_key = ?",
                $this->userid, $key);

            return true;
        }

        // update existing cache record
        if ($key_exists) {
            $this->db->query(
                "UPDATE ".get_table_name('cache').
                " SET created = ". $this->db->now().", data = ?".
                " WHERE user_id = ?".
                " AND cache_key = ?",
                $data, $this->userid, $key);
        }
        // add new cache record
        else {
            // for better performance we allow more records for one key
            // so, no need to check if record exist (see rcube_cache::read_record())
            $this->db->query(
                "INSERT INTO ".get_table_name('cache').
                " (created, user_id, cache_key, data)".
                " VALUES (".$this->db->now().", ?, ?, ?)",
                $this->userid, $key, $data);
        }

        return true;
    }


    /**
     * Deletes the cache record(s).
     *
     * @param string  $key         Cache key name or pattern
     * @param boolean $prefix_mode Enable it to clear all keys starting
     *                             with prefix specified in $key
     *
     */
    private function remove_record($key=null, $prefix_mode=false)
    {
        if (!$this->db) {
            return;
        }

        if ($this->type != 'db') {
            $this->load_index();

            // Remove all keys
            if ($key === null) {
                foreach ($this->index as $key) {
                    $this->delete_record($key, false);
                }
                $this->index = array();
            }
            // Remove keys by name prefix
            else if ($prefix_mode) {
                foreach ($this->index as $k) {
                    if (strpos($k, $key) === 0) {
                        $this->delete_record($k);
                    }
                }
            }
            // Remove one key by name
            else {
                $this->delete_record($key);
            }

            return;
        }

        // Remove all keys (in specified cache)
        if ($key === null) {
            $where = " AND cache_key LIKE " . $this->db->quote($this->prefix.'.%');
        }
        // Remove keys by name prefix
        else if ($prefix_mode) {
            $where = " AND cache_key LIKE " . $this->db->quote($this->prefix.'.'.$key.'%');
        }
        // Remove one key by name
        else {
            $where = " AND cache_key = " . $this->db->quote($this->prefix.'.'.$key);
        }

        $this->db->query(
            "DELETE FROM ".get_table_name('cache').
            " WHERE user_id = ?" . $where,
            $this->userid);
    }


    /**
     * Adds entry into memcache/apc DB.
     */
    private function add_record($key, $data)
    {
        if ($this->type == 'memcache') {
            $result = $this->db->replace($key, $data, MEMCACHE_COMPRESSED, $this->ttl);
            if (!$result)
                $result = $this->db->set($key, $data, MEMCACHE_COMPRESSED, $this->ttl);
            return $result;
        }

        if ($this->type == 'apc') {
            if (apc_exists($key))
                apc_delete($key);
            return apc_store($key, $data, $this->ttl);
        }
    }


    /**
     * Deletes entry from memcache/apc DB.
     */
    private function delete_record($index=true)
    {
        if ($this->type == 'memcache')
            $this->db->delete($this->ckey($key));
        else
            apc_delete($this->ckey($key));

        if ($index) {
            if (($idx = array_search($key, $this->index)) !== false) {
                unset($this->index[$idx]);
            }
        }
    }


    /**
     * Writes the index entry into memcache/apc DB.
     */
    private function write_index()
    {
        if (!$this->db) {
            return;
        }

        if ($this->type == 'db') {
            return;
        }

        $this->load_index();

        // Make sure index contains new keys
        foreach ($this->cache as $key => $value) {
            if ($value !== null) {
                if (array_search($key, $this->index) === false) {
                    $this->index[] = $key;
                }
            }
        }

        $data = serialize($this->index);
        $this->add_record($this->ikey(), $data);
    }


    /**
     * Gets the index entry from memcache/apc DB.
     */
    private function load_index()
    {
        if (!$this->db) {
            return;
        }

        if ($this->index !== null) {
            return;
        }

        $index_key = $this->ikey();
        if ($this->type == 'memcache') {
            $data = $this->db->get($index_key);
        }
        else if ($this->type == 'apc') {
            $data = apc_fetch($index_key);
        }

        $this->index = $data ? unserialize($data) : array();
    }


    /**
     * Creates per-user cache key name (for memcache and apc)
     *
     * @param string $key Cache key name
     *
     * @return string Cache key
     */
    private function ckey($key)
    {
        return sprintf('%d:%s:%s', $this->userid, $this->prefix, $key);
    }


    /**
     * Creates per-user index cache key name (for memcache and apc)
     *
     * @return string Cache key
     */
    private function ikey()
    {
        // This way each cache will have its own index
        return sprintf('%d:%s%s', $this->userid, $this->prefix, 'INDEX');
    }
}

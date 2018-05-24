<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011, The Roundcube Dev Team                            |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Caching engine                                                      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Interface class for accessing Roundcube cache
 *
 * @package    Framework
 * @subpackage Cache
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 */
class rcube_cache
{
    protected $type;
    protected $userid;
    protected $prefix;
    protected $ttl;
    protected $packed;
    protected $index;
    protected $index_changed = false;
    protected $debug         = false;
    protected $cache         = array();
    protected $cache_changes = array();
    protected $cache_sums    = array();
    protected $max_packet    = -1;


    /**
     * Object factory
     *
     * @param string $type   Engine type ('db', 'memcache', 'apc', 'redis')
     * @param int    $userid User identifier
     * @param string $prefix Key name prefix
     * @param string $ttl    Expiration time of memcache/apc items
     * @param bool   $packed Enables/disabled data serialization.
     *                       It's possible to disable data serialization if you're sure
     *                       stored data will be always a safe string
     *
     * @param rcube_cache Cache object
     */
    public static function factory($type, $userid, $prefix = '', $ttl = 0, $packed = true)
    {
        $driver = strtolower($type) ?: 'db';
        $class  = "rcube_cache_$driver";

        if (!$driver || !class_exists($class)) {
            rcube::raise_error(array('code' => 600, 'type' => 'db',
                'line' => __LINE__, 'file' => __FILE__,
                'message' => "Configuration error. Unsupported cache driver: $driver"),
                true, true);
        }

        return new $class($userid, $prefix, $ttl, $packed);
    }

    /**
     * Object constructor.
     *
     * @param int    $userid User identifier
     * @param string $prefix Key name prefix
     * @param string $ttl    Expiration time of memcache/apc items
     * @param bool   $packed Enables/disabled data serialization.
     *                       It's possible to disable data serialization if you're sure
     *                       stored data will be always a safe string
     */
    public function __construct($userid, $prefix = '', $ttl = 0, $packed = true)
    {
        // convert ttl string to seconds
        $ttl = get_offset_sec($ttl);
        if ($ttl > 2592000) $ttl = 2592000;

        $this->userid    = (int) $userid;
        $this->ttl       = $ttl;
        $this->packed    = $packed;
        $this->prefix    = $prefix;
    }

    /**
     * Returns cached value.
     *
     * @param string $key Cache key name
     *
     * @return mixed Cached value
     */
    public function get($key)
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
    public function set($key, $data)
    {
        $this->cache[$key]         = $data;
        $this->cache_changes[$key] = true;
    }

    /**
     * Returns cached value without storing it in internal memory.
     *
     * @param string $key Cache key name
     *
     * @return mixed Cached value
     */
    public function read($key)
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        return $this->read_record($key, true);
    }

    /**
     * Sets (add/update) value in cache and immediately saves
     * it in the backend, no internal memory will be used.
     *
     * @param string $key  Cache key name
     * @param mixed  $data Cache data
     *
     * @param boolean True on success, False on failure
     */
    public function write($key, $data)
    {
        return $this->write_record($key, $this->serialize($data));
    }

    /**
     * Clears the cache.
     *
     * @param string  $key         Cache key name or pattern
     * @param boolean $prefix_mode Enable it to clear all keys starting
     *                             with prefix specified in $key
     */
    public function remove($key=null, $prefix_mode=false)
    {
        // Remove all keys
        if ($key === null) {
            $this->cache         = array();
            $this->cache_changes = array();
            $this->cache_sums    = array();
        }
        // Remove keys by name prefix
        else if ($prefix_mode) {
            foreach (array_keys($this->cache) as $k) {
                if (strpos($k, $key) === 0) {
                    $this->cache[$k] = null;
                    $this->cache_changes[$k] = false;
                    unset($this->cache_sums[$k]);
                }
            }
        }
        // Remove one key by name
        else {
            $this->cache[$key] = null;
            $this->cache_changes[$key] = false;
            unset($this->cache_sums[$key]);
        }

        // Remove record(s) from the backend
        $this->remove_record($key, $prefix_mode);
    }

    /**
     * Remove cache records older than ttl
     */
    public function expunge()
    {
        // to be overwritten by engine class
    }

    /**
     * Remove expired records of all caches
     */
    public static function gc()
    {
        // Only DB cache requires an action to remove expired entries
        rcube_cache_db::gc();
    }

    /**
     * Writes the cache back to the DB.
     */
    public function close()
    {
        foreach ($this->cache as $key => $data) {
            // The key has been used
            if ($this->cache_changes[$key]) {
                // Make sure we're not going to write unchanged data
                // by comparing current md5 sum with the sum calculated on DB read
                $data = $this->serialize($data);

                if (!$this->cache_sums[$key] || $this->cache_sums[$key] != md5($data)) {
                    $this->write_record($key, $data);
                }
            }
        }

        if ($this->index_changed) {
            $this->write_index();
        }

        // reset internal cache index, thanks to this we can force index reload
        $this->index         = null;
        $this->index_changed = false;
        $this->cache         = array();
        $this->cache_sums    = array();
        $this->cache_changes = array();
    }

    /**
     * Reads cache entry.
     *
     * @param string  $key     Cache key name
     * @param boolean $nostore Enable to skip in-memory store
     *
     * @return mixed Cached value
     */
    protected function read_record($key, $nostore = false)
    {
        $this->load_index();

        // Consistency check (#1490390)
        if (!in_array($key, $this->index)) {
            // we always check if the key exist in the index
            // to have data in consistent state. Keeping the index consistent
            // is needed for keys delete operation when we delete all keys or by prefix.
        }
        else {
            $ckey = $this->ckey($key);
            $data = $this->get_item($ckey);
        }

        if ($data !== false) {
            $md5sum = md5($data);
            $data   = $this->unserialize($data);

            if ($nostore) {
                return $data;
            }

            $this->cache_sums[$key] = $md5sum;
            $this->cache[$key]      = $data;
        }
        else {
            $this->cache[$key] = null;
        }

        return $this->cache[$key];
    }

    /**
     * Writes single cache record into DB.
     *
     * @param string $key  Cache key name
     * @param mixed  $data Serialized cache data
     *
     * @param boolean True on success, False on failure
     */
    protected function write_record($key, $data)
    {
        // don't attempt to write too big data sets
        if (strlen($data) > $this->max_packet_size()) {
            trigger_error("rcube_cache: max_packet_size ($this->max_packet) exceeded for key $key. Tried to write " . strlen($data) . " bytes", E_USER_WARNING);
            return false;
        }

        $result = $this->add_item($this->ckey($key), $data);

        // make sure index will be updated
        if ($result) {
            if (!array_key_exists($key, $this->cache_sums)) {
                $this->cache_sums[$key] = true;
            }

            $this->load_index();

            if (!$this->index_changed && !in_array($key, $this->index)) {
                $this->index_changed = true;
            }
        }

        return $result;
    }

    /**
     * Deletes the cache record(s).
     *
     * @param string  $key         Cache key name or pattern
     * @param boolean $prefix_mode Enable it to clear all keys starting
     *                             with prefix specified in $key
     */
    protected function remove_record($key = null, $prefix_mode = false)
    {
        $this->load_index();

        // Remove all keys
        if ($key === null) {
            foreach ($this->index as $key) {
                $this->delete_item($this->ckey($key));
            }

            $this->index = array();
        }
        // Remove keys by name prefix
        else if ($prefix_mode) {
            foreach ($this->index as $idx => $k) {
                if (strpos($k, $key) === 0) {
                    $this->delete_item($this->ckey($k));
                    unset($this->index[$idx]);
                }
            }
        }
        // Remove one key by name
        else {
            $this->delete_item($this->ckey($key));
            if (($idx = array_search($key, $this->index)) !== false) {
                unset($this->index[$idx]);
            }
        }

        $this->index_changed = true;
    }

    /**
     * Fetches cache entry.
     *
     * @param string $key Cache internal key name
     *
     * @return mixed Cached value
     */
    protected function get_item($key)
    {
        // to be overwritten by engine class
    }

    /**
     * Adds entry into memcache/apc/redis DB.
     *
     * @param string $key  Cache internal key name
     * @param mixed  $data Serialized cache data
     *
     * @param boolean True on success, False on failure
     */
    protected function add_item($key, $data)
    {
        // to be overwritten by engine class
    }

    /**
     * Deletes entry from memcache/apc/redis DB.
     *
     * @param string $key Cache internal key name
     *
     * @param boolean True on success, False on failure
     */
    protected function delete_item($key)
    {
        // to be overwritten by engine class
    }

    /**
     * Writes the index entry into memcache/apc/redis DB.
     */
    protected function write_index()
    {
        $this->load_index();

        // Make sure index contains new keys
        foreach ($this->cache as $key => $value) {
            if ($value !== null && !in_array($key, $this->index)) {
                $this->index[] = $key;
            }
        }

        // new keys added using self::write()
        foreach ($this->cache_sums as $key => $value) {
            if ($value === true && !in_array($key, $this->index)) {
                $this->index[] = $key;
            }
        }

        $data = serialize(array_values($this->index));

        $this->add_item($this->ikey(), $data);
    }

    /**
     * Gets the index entry from memcache/apc/redis DB.
     */
    protected function load_index()
    {
        if ($this->index !== null) {
            return;
        }

        $index_key   = $this->ikey();
        $data        = $this->get_item($index_key);
        $this->index = $data ? unserialize($data) : array();
    }

    /**
     * Creates per-user cache key name (for memcache, apc, redis)
     *
     * @param string $key Cache key name
     *
     * @return string Cache key
     */
    protected function ckey($key)
    {
        $key = $this->prefix . ':' . $key;

        if ($this->userid) {
            $key = $this->userid . ':' . $key;
        }

        return $key;
    }

    /**
     * Creates per-user index cache key name (for memcache, apc, redis)
     *
     * @return string Cache key
     */
    protected function ikey()
    {
        $key = $this->prefix . 'INDEX';

        if ($this->userid) {
            $key = $this->userid . ':' . $key;
        }

        return $key;
    }

    /**
     * Serializes data for storing
     */
    protected function serialize($data)
    {
        return $this->packed ? serialize($data) : $data;
    }

    /**
     * Unserializes serialized data
     */
    protected function unserialize($data)
    {
        return $this->packed ? @unserialize($data) : $data;
    }

    /**
     * Determine the maximum size for cache data to be written
     */
    protected function max_packet_size()
    {
        if ($this->max_packet < 0) {
            $config           = rcube::get_instance()->config;
            $max_packet       = $config->get($this->type . '_max_allowed_packet');
            $this->max_packet = parse_bytes($max_packet) ?: 2097152; // default/max is 2 MB
        }

        return $this->max_packet;
    }

    /**
     * Write memcache/apc/redis debug info to the log
     */
    protected function debug($type, $key, $data = null, $result = null)
    {
        $line = strtoupper($type) . ' ' . $key;

        if ($data !== null) {
            $line .= ' ' . ($this->packed ? $data : serialize($data));
        }

        rcube::debug($this->type, $line, $result);
    }
}

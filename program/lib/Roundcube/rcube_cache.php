<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
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
 */
class rcube_cache
{
    protected $type;
    protected $userid;
    protected $prefix;
    protected $ttl;
    protected $packed;
    protected $indexed;
    protected $index;
    protected $index_update;
    protected $cache        = [];
    protected $updates      = [];
    protected $exp_records  = [];
    protected $refresh_time = 0.5; // how often to refresh/save the index and cache entries
    protected $debug        = false;
    protected $max_packet   = -1;

    const MAX_EXP_LEVEL     = 2;
    const DATE_FORMAT       = 'Y-m-d H:i:s.u';
    const DATE_FORMAT_REGEX = '[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{1,6}';


    /**
     * Object factory
     *
     * @param string $type    Engine type ('db', 'memcache', 'apc', 'redis')
     * @param int    $userid  User identifier
     * @param string $prefix  Key name prefix
     * @param string $ttl     Expiration time of memcache/apc items
     * @param bool   $packed  Enables/disabled data serialization.
     *                        It's possible to disable data serialization if you're sure
     *                        stored data will be always a safe string
     * @param bool   $indexed Use indexed cache. Indexed cache is more appropriate for
     *                        storing big data with possibility to remove it by a key prefix.
     *                        Non-indexed cache does not remove data, but flags it for expiration,
     *                        also stores it in memory until close() method is called.
     *
     * @return rcube_cache Cache object
     */
    public static function factory($type, $userid, $prefix = '', $ttl = 0, $packed = true, $indexed = false)
    {
        $driver = strtolower($type) ?: 'db';
        $class  = "rcube_cache_$driver";

        if (!$driver || !class_exists($class)) {
            rcube::raise_error([
                    'code' => 600, 'type' => 'db',
                    'line' => __LINE__, 'file' => __FILE__,
                    'message' => "Configuration error. Unsupported cache driver: $driver"
                ],
                true, true
            );
        }

        return new $class($userid, $prefix, $ttl, $packed, $indexed);
    }

    /**
     * Object constructor.
     *
     * @param int    $userid  User identifier
     * @param string $prefix  Key name prefix
     * @param string $ttl     Expiration time of memcache/apc items
     * @param bool   $packed  Enables/disabled data serialization.
     *                        It's possible to disable data serialization if you're sure
     *                        stored data will be always a safe string
     * @param bool   $indexed Use indexed cache. Indexed cache is more appropriate for
     *                        storing big data with possibility to remove it by key prefix.
     *                        Non-indexed cache does not remove data, but flags it for expiration,
     *                        also stores it in memory until close() method is called.
     */
    public function __construct($userid, $prefix = '', $ttl = 0, $packed = true, $indexed = false)
    {
        $this->userid  = (int) $userid;
        $this->ttl     = min(get_offset_sec($ttl), 2592000);
        $this->prefix  = $prefix;
        $this->packed  = $packed;
        $this->indexed = $indexed;
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
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        return $this->read_record($key);
    }

    /**
     * Sets (add/update) value in cache.
     *
     * @param string $key  Cache key name
     * @param mixed  $data Cache data
     *
     * @return bool True on success, False on failure
     */
    public function set($key, $data)
    {
        return $this->write_record($key, $data);
    }

    /**
     * @deprecated Use self::get()
     */
    public function read($key)
    {
        return $this->get($key);
    }

    /**
     * @deprecated Use self::set()
     */
    public function write($key, $data)
    {
        return $this->set($key, $data);
    }

    /**
     * Clears the cache.
     *
     * @param string $key         Cache key name or pattern
     * @param bool   $prefix_mode Enable it to clear all keys starting
     *                            with prefix specified in $key
     */
    public function remove($key = null, $prefix_mode = false)
    {
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
        $this->write_index(true);
        $this->index   = null;
        $this->cache   = [];
        $this->updates = [];
    }

    /**
     * A helper to build cache key for specified parameters.
     *
     * @param string $prefix Key prefix (Max. length 64 characters)
     * @param array  $params Additional parameters
     *
     * @return string Key name
     */
    public static function key_name($prefix, $params = [])
    {
        $cache_key = $prefix;

        if (!empty($params)) {
            $func = function($v) {
                if (is_array($v)) {
                    sort($v);
                }
                return is_string($v) ? $v : serialize($v);
            };

            $params = array_map($func, $params);
            $cache_key .= '.' . md5(implode(':', $params));
        }

        return $cache_key;
    }

    /**
     * Reads cache entry.
     *
     * @param string $key Cache key name
     *
     * @return mixed Cached value
     */
    protected function read_record($key)
    {
        $this->load_index();

        // Consistency check (#1490390)
        if (is_array($this->index) && !in_array($key, $this->index)) {
            // we always check if the key exist in the index
            // to have data in consistent state. Keeping the index consistent
            // is needed for keys delete operation when we delete all keys or by prefix.
            return;
        }

        $ckey = $this->ckey($key);
        $data = $this->get_item($ckey);

        if ($this->indexed) {
            return $data !== false ? $this->unserialize($data) : null;
        }

        if ($data !== false) {
            $timestamp = 0;
            $utc       = new DateTimeZone('UTC');

            // Extract timestamp from the data entry
            if (preg_match('/^(' . self::DATE_FORMAT_REGEX . '):/', $data, $matches)) {
                try {
                    $timestamp = new DateTime($matches[1], $utc);
                    $data      = substr($data, strlen($matches[1]) + 1);
                }
                catch (Exception $e) {
                    // invalid date = no timestamp
                }
            }

            // Check if the entry is still valid by comparing with EXP timestamps
            // For example for key 'mailboxes.123456789' we check entries:
            // 'EXP:*', 'EXP:mailboxes' and 'EXP:mailboxes.123456789'.
            if ($timestamp) {
                $path     = explode('.', "*.$key");
                $path_len = min(self::MAX_EXP_LEVEL + 1, count($path));

                for ($x = 1; $x <= $path_len; $x++) {
                    $prefix = implode('.', array_slice($path, 0, $x));
                    if ($x > 1) {
                        $prefix = substr($prefix, 2); // remove "*." prefix
                    }

                    if (($ts = $this->get_exp_timestamp($prefix)) && $ts > $timestamp) {
                        $timestamp = 0;
                        break;
                    }
                }
            }

            $data = $timestamp ? $this->unserialize($data) : null;
        }
        else {
            $data = null;
        }

        return $this->cache[$key] = $data;
    }

    /**
     * Writes single cache record into DB.
     *
     * @param string $key  Cache key name
     * @param mixed  $data Serialized cache data
     *
     * @return bool True on success, False on failure
     */
    protected function write_record($key, $data)
    {
        if ($this->indexed) {
            $result = $this->store_record($key, $data);

            if ($result) {
                $this->load_index();
                $this->index[] = $key;

                if (!$this->index_update) {
                    $this->index_update = time();
                }
            }
        }
        else {
            // In this mode we do not save the entry to the database immediately
            // It's because we have cases where the same entry is updated
            // multiple times in one request (e.g. 'messagecount' entry rcube_imap).
            $this->updates[$key] = new DateTime('now', new DateTimeZone('UTC'));
            $this->cache[$key]   = $data;
            $result = true;
        }

        $this->write_index();

        return $result;
    }

    /**
     * Deletes the cache record(s).
     *
     * @param string $key         Cache key name or pattern
     * @param bool   $prefix_mode Enable it to clear all keys starting
     *                            with prefix specified in $key
     */
    protected function remove_record($key = null, $prefix_mode = false)
    {
        if ($this->indexed) {
            return $this->remove_record_indexed($key, $prefix_mode);
        }

        // "Remove" all keys
        if ($key === null) {
            $ts = new DateTime('now', new DateTimeZone('UTC'));
            $this->add_item($this->ekey('*'), $ts->format(self::DATE_FORMAT));
            $this->cache = [];
        }
        // "Remove" keys by name prefix
        else if ($prefix_mode) {
            $ts     = new DateTime('now', new DateTimeZone('UTC'));
            $prefix = implode('.', array_slice(explode('.', trim($key, '. ')), 0, self::MAX_EXP_LEVEL));

            $this->add_item($this->ekey($prefix), $ts->format(self::DATE_FORMAT));

            foreach (array_keys($this->cache) as $k) {
                if (strpos($k, $key) === 0) {
                    $this->cache[$k] = null;
                }
            }
        }
        // Remove one key by name
        else {
            $this->delete_item($this->ckey($key));
            $this->cache[$key] = null;
        }
    }

    /**
     * @see self::remove_record()
     */
    protected function remove_record_indexed($key = null, $prefix_mode = false)
    {
        $this->load_index();

        // Remove all keys
        if ($key === null) {
            foreach ($this->index as $key) {
                $this->delete_item($this->ckey($key));
                if (!$this->index_update) {
                    $this->index_update = time();
                }
            }

            $this->index = [];
        }
        // Remove keys by name prefix
        else if ($prefix_mode) {
            foreach ($this->index as $idx => $k) {
                if (strpos($k, $key) === 0) {
                    $this->delete_item($this->ckey($k));
                    unset($this->index[$idx]);
                    if (!$this->index_update) {
                        $this->index_update = time();
                    }
                }
            }
        }
        // Remove one key by name
        else {
            $this->delete_item($this->ckey($key));
            if (($idx = array_search($key, $this->index)) !== false) {
                unset($this->index[$idx]);
                if (!$this->index_update) {
                    $this->index_update = time();
                }
            }
        }

        $this->write_index();
    }

    /**
     * Writes the index entry as well as updated entries into memcache/apc/redis DB.
     */
    protected function write_index($force = null)
    {
        // Write updated/new entries when needed
        if (!$this->indexed) {
            $need_update = $force === true;

            if (!$need_update && !empty($this->updates)) {
                $now         = new DateTime('now', new DateTimeZone('UTC'));
                $need_update = floatval(min($this->updates)->format('U.u')) < floatval($now->format('U.u')) - $this->refresh_time;
            }

            if ($need_update) {
                foreach ($this->updates as $key => $ts) {
                    if (isset($this->cache[$key])) {
                        $this->store_record($key, $this->cache[$key], $ts);
                    }
                }

                $this->updates = [];
            }
        }
        // Write index entry when needed
        else {
            $need_update = $this->index_update && $this->index !== null
                && ($force === true || $this->index_update > time() - $this->refresh_time);

            if ($need_update) {
                $index = serialize(array_values(array_unique($this->index)));

                $this->add_item($this->ikey(), $index);
                $this->index_update = null;
                $this->index        = null;
            }
        }
    }

    /**
     * Gets the index entry from memcache/apc/redis DB.
     */
    protected function load_index()
    {
        if (!$this->indexed) {
            return;
        }

        if ($this->index !== null) {
            return;
        }

        $data        = $this->get_item($this->ikey());
        $this->index = $data ? unserialize($data) : [];
    }

    /**
     * Write data entry into cache
     */
    protected function store_record($key, $data, $ts = null)
    {
        $value = $this->serialize($data);

        if (!$this->indexed) {
            if (!$ts) {
                $ts = new DateTime('now', new DateTimeZone('UTC'));
            }

            $value = $ts->format(self::DATE_FORMAT) . ':' . $value;
        }

        $size = strlen($value);

        // don't attempt to write too big data sets
        if ($size > $this->max_packet_size()) {
            trigger_error("rcube_cache: max_packet_size ($this->max_packet) exceeded for key $key. Tried to write $size bytes", E_USER_WARNING);
            return false;
        }

        return $this->add_item($this->ckey($key), $value);
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
     * @return bool True on success, False on failure
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
     * @return bool True on success, False on failure
     */
    protected function delete_item($key)
    {
        // to be overwritten by engine class
    }

    /**
     * Get EXP:<key> record value from cache
     */
    protected function get_exp_timestamp($key)
    {
        if (!array_key_exists($key, $this->exp_records)) {
            $data = $this->get_item($this->ekey($key));

            $this->exp_records[$key] = $data ? new DateTime($data, new DateTimeZone('UTC')) : null;
        }

        return $this->exp_records[$key];
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
     * Creates per-user cache key name for expiration time entry
     *
     * @param string $key Cache key name
     *
     * @return string Cache key
     */
    protected function ekey($key, $prefix = null)
    {
        $key = $this->prefix . 'EXP:' . $key;

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

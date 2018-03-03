<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011-2018, The Roundcube Dev Team                       |
 | Copyright (C) 2011-2018, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Caching engine - SQL DB                                             |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Interface class for accessing SQL Database cache
 *
 * @package    Framework
 * @subpackage Cache
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 */
class rcube_cache_db extends rcube_cache
{
    /**
     * Instance of database handler
     *
     * @var rcube_db
     */
    protected $db;

    /**
     * (Escaped) Cache table name (cache or cache_shared)
     *
     * @var string
     */
    protected $table;


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
        parent::__construct($userid, $prefix, $ttl, $packed);

        $rcube = rcube::get_instance();

        $this->type  = 'db';
        $this->db    = $rcube->get_dbh();
        $this->table = $this->db->table_name($userid ? 'cache' : 'cache_shared', true);
    }

    /**
     * Remove cache records older than ttl
     */
    public function expunge()
    {
        if ($this->db && $this->ttl) {
            $this->db->query(
                "DELETE FROM {$this->table} WHERE "
                . ($this->userid ? "`user_id` = {$this->userid} AND " : "")
                . "`cache_key` LIKE ?"
                . " AND `expires` < " . $this->db->now(),
                $this->prefix . '.%');
        }
    }

    /**
     * Remove expired records of all caches
     */
    public static function gc()
    {
        $rcube = rcube::get_instance();
        $db    = $rcube->get_dbh();

        $db->query("DELETE FROM " . $db->table_name('cache', true) . " WHERE `expires` < " . $db->now());
        $db->query("DELETE FROM " . $db->table_name('cache_shared', true) . " WHERE `expires` < " . $db->now());
    }

    /**
     * Reads cache entry.
     *
     * @param string  $key     Cache key name
     * @param boolean $nostore Enable to skip in-memory store
     *
     * @return mixed Cached value
     */
    protected function read_record($key, $nostore=false)
    {
        if (!$this->db) {
            return;
        }

        $sql_result = $this->db->query(
                "SELECT `data`, `cache_key` FROM {$this->table} WHERE "
                . ($this->userid ? "`user_id` = {$this->userid} AND " : "")
                ."`cache_key` = ?",
                $this->prefix . '.' . $key);

        if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            if (strlen($sql_arr['data']) > 0) {
                $md5sum = md5($sql_arr['data']);
                $data   = $this->unserialize($sql_arr['data']);
            }

            $this->db->reset();

            if ($nostore) {
                return $data;
            }

            $this->cache[$key]      = $data;
            $this->cache_sums[$key] = $md5sum;
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
        if (!$this->db) {
            return false;
        }

        // don't attempt to write too big data sets
        if (strlen($data) > $this->max_packet_size()) {
            trigger_error("rcube_cache: max_packet_size ($this->max_packet) exceeded for key $key. Tried to write " . strlen($data) . " bytes", E_USER_WARNING);
            return false;
        }

        $db_key = $this->prefix . '.' . $key;

        // Remove NULL rows (here we don't need to check if the record exist)
        if ($data == 'N;') {
            $result = $this->db->query(
                "DELETE FROM {$this->table} WHERE "
                . ($this->userid ? "`user_id` = {$this->userid} AND " : "")
                ."`cache_key` = ?",
                $db_key);

            return !$this->db->is_error($result);
        }

        $key_exists = array_key_exists($key, $this->cache_sums);
        $expires    = $this->ttl ? $this->db->now($this->ttl) : 'NULL';

        if (!$key_exists) {
            // Try INSERT temporarily ignoring "duplicate key" errors
            $this->db->set_option('ignore_key_errors', true);

            if ($this->userid) {
                $result = $this->db->query(
                    "INSERT INTO {$this->table} (`expires`, `user_id`, `cache_key`, `data`)"
                    . " VALUES ($expires, ?, ?, ?)",
                    $this->userid, $db_key, $data);
            }
            else {
                $result = $this->db->query(
                    "INSERT INTO {$this->table} (`expires`, `cache_key`, `data`)"
                    . " VALUES ($expires, ?, ?)",
                    $db_key, $data);
            }

            $this->db->set_option('ignore_key_errors', false);
        }

        // otherwise try UPDATE
        if (!isset($result) || !($count = $this->db->affected_rows($result))) {
            $result = $this->db->query(
                "UPDATE {$this->table} SET `expires` = $expires, `data` = ? WHERE "
                . ($this->userid ? "`user_id` = {$this->userid} AND " : "")
                . "`cache_key` = ?",
                $data, $db_key);

            $count = $this->db->affected_rows($result);
        }

        return $count > 0;
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
        if (!$this->db) {
            return;
        }

        // Remove all keys (in specified cache)
        if ($key === null) {
            $where = "`cache_key` LIKE " . $this->db->quote($this->prefix . '.%');
        }
        // Remove keys by name prefix
        else if ($prefix_mode) {
            $where = "`cache_key` LIKE " . $this->db->quote($this->prefix . '.' . $key . '%');
        }
        // Remove one key by name
        else {
            $where = "`cache_key` = " . $this->db->quote($this->prefix . '.' . $key);
        }

        $this->db->query(
            "DELETE FROM {$this->table} WHERE "
            . ($this->userid ? "`user_id` = {$this->userid} AND " : "") . $where
        );
    }

    /**
     * Serializes data for storing
     */
    protected function serialize($data)
    {
        return $this->db ? $this->db->encode($data, $this->packed) : false;
    }

    /**
     * Unserializes serialized data
     */
    protected function unserialize($data)
    {
        return $this->db ? $this->db->decode($data, $this->packed) : false;
    }

    /**
     * Determine the maximum size for cache data to be written
     */
    protected function max_packet_size()
    {
        if ($this->max_packet < 0) {
            $this->max_packet = 2097152; // default/max is 2 MB

            if ($value = $this->db->get_variable('max_allowed_packet', $this->max_packet)) {
                $this->max_packet = $value;
            }

            $this->max_packet -= 2000;
        }

        return $this->max_packet;
    }
}

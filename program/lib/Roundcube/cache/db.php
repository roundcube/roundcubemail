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
 |   Caching engine - SQL DB                                             |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Interface implementation class for accessing SQL Database cache
 *
 * @package    Framework
 * @subpackage Cache
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
     * {@inheritdoc}
     */
    public function __construct($userid, $prefix = '', $ttl = 0, $packed = true, $indexed = false)
    {
        parent::__construct($userid, $prefix, $ttl, $packed, $indexed);

        $rcube = rcube::get_instance();

        $this->type  = 'db';
        $this->db    = $rcube->get_dbh();
        $this->table = $this->db->table_name($userid ? 'cache' : 'cache_shared', true);

        $this->refresh_time *= 2;
    }

    /**
     * Remove cache records older than ttl
     */
    public function expunge()
    {
        if ($this->ttl) {
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
     * @param string $key Cache key name
     *
     * @return mixed Cached value
     */
    protected function read_record($key)
    {
        $sql_result = $this->db->query(
                "SELECT `data`, `cache_key` FROM {$this->table} WHERE "
                . ($this->userid ? "`user_id` = {$this->userid} AND " : "")
                ."`cache_key` = ?",
                $this->prefix . '.' . $key);

        $data = null;

        if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            if (strlen($sql_arr['data']) > 0) {
                $data = $this->unserialize($sql_arr['data']);
            }

            $this->db->reset();
        }

        if (!$this->indexed) {
            $this->cache[$key] = $data;
        }

        return $data;
    }

    /**
     * Writes single cache record into DB.
     *
     * @param string   $key  Cache key name
     * @param mixed    $data Serialized cache data
     * @param DateTime $ts   Timestamp
     *
     * @param bool True on success, False on failure
     */
    protected function store_record($key, $data, $ts = null)
    {
        $value = $this->serialize($data);
        $size  = strlen($value);

        // don't attempt to write too big data sets
        if ($size > $this->max_packet_size()) {
            trigger_error("rcube_cache: max_packet_size ($this->max_packet) exceeded for key $key. Tried to write $size bytes", E_USER_WARNING);
            return false;
        }

        $db_key = $this->prefix . '.' . $key;

        // Remove NULL rows (here we don't need to check if the record exist)
        if ($value == 'N;') {
            $result = $this->db->query(
                "DELETE FROM {$this->table} WHERE "
                . ($this->userid ? "`user_id` = {$this->userid} AND " : "")
                ."`cache_key` = ?",
                $db_key);

            return !$this->db->is_error($result);
        }

        $expires = $this->db->param($this->ttl ? $this->db->now($this->ttl) : 'NULL', rcube_db::TYPE_SQL);
        $pkey    = ['cache_key' => $db_key];

        if ($this->userid) {
            $pkey['user_id'] = $this->userid;
        }

        $result = $this->db->insert_or_update(
            $this->table, $pkey, ['expires', 'data'], [$expires, $value]
        );

        $count = $this->db->affected_rows($result);

        return $count > 0;
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
        // Remove all keys (in specified cache)
        if ($key === null) {
            $where = "`cache_key` LIKE " . $this->db->quote($this->prefix . '.%');
            $this->cache = [];
        }
        // Remove keys by name prefix
        else if ($prefix_mode) {
            $where = "`cache_key` LIKE " . $this->db->quote($this->prefix . '.' . $key . '%');
            foreach (array_keys($this->cache) as $k) {
                if (strpos($k, $key) === 0) {
                    $this->cache[$k] = null;
                }
            }
        }
        // Remove one key by name
        else {
            $where = "`cache_key` = " . $this->db->quote($this->prefix . '.' . $key);
            $this->cache[$key] = null;
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
        return $this->db->encode($data, $this->packed);
    }

    /**
     * Unserializes serialized data
     */
    protected function unserialize($data)
    {
        return $this->db->decode($data, $this->packed);
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

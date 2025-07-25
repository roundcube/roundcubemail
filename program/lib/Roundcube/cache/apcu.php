<?php

/*
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
 |   Caching engine - APCu                                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Interface implementation class for accessing APCu cache
 */
class rcube_cache_apcu extends rcube_cache
{
    /**
     * Indicates if APCu module is enabled and in a required version
     *
     * @var bool
     */
    protected $enabled;

    public function __construct($userid, $prefix = '', $ttl = 0, $packed = true, $indexed = false)
    {
        parent::__construct($userid, $prefix, $ttl, $packed, $indexed);

        $rcube = rcube::get_instance();

        $this->type = 'apcu';
        $this->enabled = function_exists('apcu_exists'); // APCu required (pecl install apcu)
        $this->debug = $rcube->config->get('apcu_debug');
    }

    /**
     * Remove cache records older than ttl
     */
    #[\Override]
    public function expunge()
    {
        // No need for GC, entries are expunged automatically
    }

    /**
     * Remove expired records of all caches
     */
    #[\Override]
    public static function gc()
    {
        // No need for GC, entries are expunged automatically
    }

    /**
     * Reads cache entry.
     *
     * @param string $key Cache internal key name
     *
     * @return mixed Cached value
     */
    #[\Override]
    protected function get_item($key)
    {
        if (!$this->enabled) {
            return false;
        }

        $data = apcu_fetch($key);

        if ($this->debug) {
            $this->debug('get', $key, $data);
        }

        return $data;
    }

    /**
     * Adds entry into memcache/apc/apcu/redis DB.
     *
     * @param string $key  Cache internal key name
     * @param mixed  $data Serialized cache data
     *
     * @return bool True on success, False on failure
     */
    #[\Override]
    protected function add_item($key, $data)
    {
        if (!$this->enabled) {
            return false;
        }

        if (apcu_exists($key)) {
            apcu_delete($key);
        }

        $result = apcu_store($key, $data, $this->ttl);

        if ($this->debug) {
            $this->debug('set', $key, $data, $result);
        }

        return $result;
    }

    /**
     * Deletes entry from memcache/apc/apcu/redis DB.
     *
     * @param string $key Cache internal key name
     *
     * @return bool True on success, False on failure
     */
    #[\Override]
    protected function delete_item($key)
    {
        if (!$this->enabled) {
            return false;
        }

        $result = apcu_delete($key);

        if ($this->debug) {
            $this->debug('delete', $key, null, $result);
        }

        return $result;
    }
}

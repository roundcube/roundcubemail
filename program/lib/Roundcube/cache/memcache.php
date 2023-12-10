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
 |   Caching engine - Memcache                                           |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Interface implementation class for accessing Memcache cache
 *
 * @package    Framework
 * @subpackage Cache
 */
class rcube_cache_memcache extends rcube_cache
{
    /**
     * Instance of memcache handler
     *
     * @var Memcache
     */
    protected static $memcache;

    public function __construct($userid, $prefix = '', $ttl = 0, $packed = true, $indexed = false)
    {
        parent::__construct($userid, $prefix, $ttl, $packed, $indexed);

        $this->type  = 'memcache';
        $this->debug = rcube::get_instance()->config->get('memcache_debug');

        self::engine();
    }

    /**
     * Get global handle for memcache access
     *
     * @return object Memcache
     */
    public static function engine()
    {
        if (self::$memcache !== null) {
            return self::$memcache;
        }

        // no memcache support in PHP
        if (!class_exists('Memcache')) {
            self::$memcache = false;

            rcube::raise_error([
                    'code' => 604,
                    'type' => 'memcache',
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => "Failed to find Memcache. Make sure php-memcache is included"
                ],
                true, true);
        }

        // add all configured hosts to pool
        $rcube = rcube::get_instance();
        $pconnect       = $rcube->config->get('memcache_pconnect', true);
        $timeout        = $rcube->config->get('memcache_timeout', 1);
        $retry_interval = $rcube->config->get('memcache_retry_interval', 15);
        $seen           = [];
        $available      = 0;

        // Callback for memcache failure
        $error_callback = function($host, $port) use ($seen, $available) {
            // only report once
            if (!$seen["$host:$port"]++) {
                $available--;
                rcube::raise_error([
                        'code' => 604, 'type' => 'memcache',
                        'line' => __LINE__, 'file' => __FILE__,
                        'message' => "Memcache failure on host $host:$port"
                    ],
                    true, false);
            }
        };

        self::$memcache = new Memcache;

        foreach ((array) $rcube->config->get('memcache_hosts') as $host) {
            if (substr($host, 0, 7) != 'unix://') {
                list($host, $port) = explode(':', $host);
                if (!$port) $port = 11211;
            }
            else {
                $port = 0;
            }

            $available += intval(self::$memcache->addServer(
                $host, $port, $pconnect, 1, $timeout, $retry_interval, false, $error_callback));
        }

        // test connection and failover (will result in $available == 0 on complete failure)
        self::$memcache->increment('__CONNECTIONTEST__', 1);  // NOP if key doesn't exist

        if (!$available) {
            self::$memcache = false;
        }

        return self::$memcache;
    }

    /**
     * Remove cache records older than ttl
     */
    public function expunge()
    {
        // No need for GC, entries are expunged automatically
    }

    /**
     * Remove expired records of all caches
     */
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
    protected function get_item($key)
    {
        if (!self::$memcache) {
            return false;
        }

        $data = self::$memcache->get($key);

        if ($this->debug) {
            $this->debug('get', $key, $data);
        }

        return $data;
    }

    /**
     * Adds entry into the cache.
     *
     * @param string $key  Cache internal key name
     * @param mixed  $data Serialized cache data
     *
     * @return bool True on success, False on failure
     */
    protected function add_item($key, $data)
    {
        if (!self::$memcache) {
            return false;
        }

        $result = self::$memcache->replace($key, $data, MEMCACHE_COMPRESSED, $this->ttl);

        if (!$result) {
            $result = self::$memcache->set($key, $data, MEMCACHE_COMPRESSED, $this->ttl);
        }

        if ($this->debug) {
            $this->debug('set', $key, $data, $result);
        }

        return $result;
    }

    /**
     * Deletes entry from the cache
     *
     * @param string $key Cache internal key name
     *
     * @return bool True on success, False on failure
     */
    protected function delete_item($key)
    {
        if (!self::$memcache) {
            return false;
        }

        // #1488592: use 2nd argument
        $result = self::$memcache->delete($key, 0);

        if ($this->debug) {
            $this->debug('delete', $key, null, $result);
        }

        return $result;
    }
}

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
 * Interface implementation class for accessing Memcached cache
 *
 * @package    Framework
 * @subpackage Cache
 */
class rcube_cache_memcached extends rcube_cache
{
    /**
     * Instance of memcached handler
     *
     * @var Memcached
     */
    protected static $memcache;


    /**
     * {@inheritdoc}
     */
    public function __construct($userid, $prefix = '', $ttl = 0, $packed = true, $indexed = false)
    {
        parent::__construct($userid, $prefix, $ttl, $packed, $indexed);

        $this->type  = 'memcache';
        $this->debug = rcube::get_instance()->config->get('memcache_debug');

        // Maximum TTL is 30 days, bigger values are treated by Memcached
        // as unix timestamp which is not what we want
        if ($this->ttl > 60*60*24*30) {
            $this->ttl = 60*60*24*30;
        }

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
        if (!class_exists('Memcached')) {
            self::$memcache = false;

            rcube::raise_error([
                    'code' => 604, 'type' => 'memcache', 'line' => __LINE__, 'file' => __FILE__,
                    'message' => "Failed to find Memcached. Make sure php-memcached is installed"
                ],
                true, true);
        }

        // add all configured hosts to pool
        $rcube          = rcube::get_instance();
        $pconnect       = $rcube->config->get('memcache_pconnect', true);
        $timeout        = $rcube->config->get('memcache_timeout', 1);
        $retry_interval = $rcube->config->get('memcache_retry_interval', 15);
        $hosts          = $rcube->config->get('memcache_hosts');
        $persistent_id  = $pconnect ? ('rc' . md5(serialize($hosts))) : null;

        self::$memcache = new Memcached($persistent_id);

        self::$memcache->setOptions([
                Memcached::OPT_CONNECT_TIMEOUT => $timeout * 1000,
                Memcached::OPT_RETRY_TIMEOUT   => $timeout,
                Memcached::OPT_DISTRIBUTION    => Memcached::DISTRIBUTION_CONSISTENT,
                Memcached::OPT_COMPRESSION     => true,
        ]);

        if (!$pconnect || !count(self::$memcache->getServerList())) {
            foreach ((array) $hosts as $host) {
                if (substr($host, 0, 7) != 'unix://') {
                    list($host, $port) = explode(':', $host);
                    if (!$port) $port = 11211;
                }
                else {
                    $host = substr($host, 7);
                    $port = 0;
                }

                self::$memcache->addServer($host, $port);
            }
        }

        // test connection
        $result = self::$memcache->increment('__CONNECTIONTEST__');

        if ($result === false && ($res_code = self::$memcache->getResultCode()) !== Memcached::RES_NOTFOUND) {
            self::$memcache = false;

            rcube::raise_error([
                    'code' => 604, 'type' => 'memcache', 'line' => __LINE__, 'file' => __FILE__,
                    'message' => "Memcache connection failure (code: $res_code)."
                ],
                true, false);
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
     * @param bool True on success, False on failure
     */
    protected function add_item($key, $data)
    {
        if (!self::$memcache) {
            return false;
        }

        $result = self::$memcache->set($key, $data, $this->ttl);

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
     * @param bool True on success, False on failure
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

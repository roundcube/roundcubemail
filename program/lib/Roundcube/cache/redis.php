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
 |   Caching engine - Redis                                              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Interface implementation class for accessing Redis cache
 *
 * @package    Framework
 * @subpackage Cache
 */
class rcube_cache_redis extends rcube_cache
{
    /**
     * Instance of Redis object
     *
     * @var Redis
     */
    protected static $redis;

    public function __construct($userid, $prefix = '', $ttl = 0, $packed = true, $indexed = false)
    {
        parent::__construct($userid, $prefix, $ttl, $packed, $indexed);

        $rcube = rcube::get_instance();

        $this->type  = 'redis';
        $this->debug = $rcube->config->get('redis_debug');

        self::engine();
    }

    /**
     * Get global handle for redis access
     *
     * @return object Redis
     */
    public static function engine()
    {
        if (self::$redis !== null) {
            return self::$redis;
        }

        if (!class_exists('Redis')) {
            self::$redis = false;

            rcube::raise_error([
                    'code' => 604,
                    'type' => 'redis',
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => "Failed to find Redis. Make sure php-redis is included"
                ],
                true, true);
        }

        $rcube = rcube::get_instance();
        $hosts = $rcube->config->get('redis_hosts');

        // host config is wrong
        if (!is_array($hosts) || empty($hosts)) {
            rcube::raise_error([
                    'code' => 604,
                    'type' => 'redis',
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => "Redis host not configured"
                ],
                true, true);
        }

        // only allow 1 host for now until we support clustering
        if (count($hosts) > 1) {
            rcube::raise_error([
                    'code' => 604,
                    'type' => 'redis',
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => "Redis cluster not yet supported"
                ],
                true, true);
        }

        self::$redis = new Redis;
        $failures    = 0;

        foreach ($hosts as $redis_host) {
            // explode individual fields
            list($host, $port, $database, $password) = array_pad(explode(':', $redis_host, 4), 4, null);

            if (substr($redis_host, 0, 7) === 'unix://') {
                $host = substr($port, 2);
                $port = 0;
            }
            else {
                // set default values if not set
                $host = $host ?: '127.0.0.1';
                $port = $port ?: 6379;
            }

            try {
                if (self::$redis->connect($host, $port) === false) {
                    throw new Exception("Could not connect to Redis server. Please check host and port.");
                }

                if ($password !== null && self::$redis->auth($password) === false) {
                    throw new Exception("Could not authenticate with Redis server. Please check password.");
                }

                if ($database !== null && self::$redis->select($database) === false) {
                    throw new Exception("Could not select Redis database. Please check database setting.");
                }
            }
            catch (Exception $e) {
                rcube::raise_error($e, true, false);
                $failures++;
            }
        }

        if (count($hosts) === $failures) {
            self::$redis = false;
        }

        if (self::$redis) {
            try {
                $ping = self::$redis->ping();
                if ($ping !== true && $ping !== "+PONG") {
                    throw new Exception("Redis connection failure. Ping failed.");
                }
            }
            catch (Exception $e) {
                self::$redis = false;
                rcube::raise_error($e, true, false);
            }
        }

        return self::$redis;
    }

    /**
     * Remove cache records older than ttl
     */
    public function expunge()
    {
        // No need for GC, entries are expunged automatically
    }

    /**
     * Remove expired records
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
        if (!self::$redis) {
            return false;
        }

        try {
            $data = self::$redis->get($key);
        }
        catch (Exception $e) {
            rcube::raise_error($e, true, false);
            return false;
        }

        if ($this->debug) {
            $this->debug('get', $key, $data);
        }

        return $data;
    }

    /**
     * Adds entry into Redis.
     *
     * @param string $key  Cache internal key name
     * @param mixed  $data Serialized cache data
     *
     * @return bool True on success, False on failure
     */
    protected function add_item($key, $data)
    {
        if (!self::$redis) {
            return false;
        }

        try {
            $result = self::$redis->setEx($key, $this->ttl, $data);
        }
        catch (Exception $e) {
            rcube::raise_error($e, true, false);
            return false;
        }

        if ($this->debug) {
            $this->debug('set', $key, $data, $result);
        }

        return $result;
    }

    /**
     * Deletes entry from Redis.
     *
     * @param string $key Cache internal key name
     *
     * @return bool True on success, False on failure
     */
    protected function delete_item($key)
    {
        if (!self::$redis) {
            return false;
        }

        try {
            $fname  = method_exists(self::$redis, 'del') ? 'del' : 'delete';
            $result = self::$redis->$fname($key);
        }
        catch (Exception $e) {
            rcube::raise_error($e, true, false);
            return false;
        }

        if ($this->debug) {
            $this->debug('delete', $key, null, $result);
        }

        return $result;
    }
}

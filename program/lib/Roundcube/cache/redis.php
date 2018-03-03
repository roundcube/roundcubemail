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
 |   Caching engine - Redis                                              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Interface class for accessing Redis cache
 *
 * @package    Framework
 * @subpackage Cache
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 */
class rcube_cache_redis extends rcube_cache
{
    /**
     * Instance of Redis object
     *
     * @var Redis
     */
    protected static $redis;


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

            rcube::raise_error(array(
                    'code' => 604,
                    'type' => 'redis',
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => "Failed to find Redis. Make sure php-redis is included"
                ),
                true, true);
        }

        $rcube     = rcube::get_instance();
        $hosts     = $rcube->config->get('redis_hosts');

        // host config is wrong
        if (!is_array($hosts) || empty($hosts)) {
            rcube::raise_error(array(
                    'code' => 604,
                    'type' => 'redis',
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => "Redis host not configured"
                ),
                true, true);
        }

        // only allow 1 host for now until we support clustering
        if (count($hosts) > 1) {
            rcube::raise_error(array(
                    'code' => 604,
                    'type' => 'redis',
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => "Redis cluster not yet supported"
                ),
                true, true);
        }

        self::$redis = new Redis;

        foreach ($hosts as $redis_host) {
            // explode individual fields
            list($host, $port, $database, $password) = array_pad(explode(':', $redis_host, 4), 4, null);

            $params = parse_url($redis_host);
            if ($params['scheme'] == 'redis') {
                $host = (isset($params['host'])) ? $params['host'] : null;
                $port = (isset($params['port'])) ? $params['port'] : null;
                $database = (isset($params['database'])) ? $params['database'] : null;
                $password = (isset($params['password'])) ? $params['password'] : null;
            }

            // set default values if not set
            $host = ($host !== null) ? $host : '127.0.0.1';
            $port = ($port !== null) ? $port : 6379;
            $database = ($database !== null) ? $database : 0;

            if (self::$redis->connect($host, $port) === false) {
                rcube::raise_error(array(
                        'code' => 604,
                        'type' => 'redis',
                        'line' => __LINE__,
                        'file' => __FILE__,
                        'message' => "Could not connect to Redis server. Please check host and port"
                    ),
                    true, true);
            }

            if ($password != null && self::$redis->auth($password) === false) {
                rcube::raise_error(array(
                        'code' => 604,
                        'type' => 'redis',
                        'line' => __LINE__,
                        'file' => __FILE__,
                        'message' => "Could not authenticate with Redis server. Please check password."
                    ),
                    true, true);
            }

            if ($database != 0 && self::$redis->select($database) === false) {
                rcube::raise_error(array(
                        'code' => 604,
                        'type' => 'redis',
                        'line' => __LINE__,
                        'file' => __FILE__,
                        'message' => "Could not select Redis database. Please check database setting."
                    ),
                    true, true);
            }
        }

        if (self::$redis->ping() != "+PONG") {
            self::$redis = false;
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

        $data = self::$redis->get($key);

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
     * @param boolean True on success, False on failure
     */
    protected function add_item($key, $data)
    {
        if (!self::$redis) {
            return false;
        }

        $result = self::$redis->setEx($key, $this->ttl, $data);

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
     * @param boolean True on success, False on failure
     */
    protected function delete_item($key)
    {
        if (!self::$redis) {
            return false;
        }

        $result = self::$redis->delete($key);

        if ($this->debug) {
            $this->debug('delete', $key, null, $result);
        }

        return $result;
    }
}

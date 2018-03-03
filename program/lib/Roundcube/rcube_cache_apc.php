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
 |   Caching engine - APC                                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Interface class for accessing APC cache
 *
 * @package    Framework
 * @subpackage Cache
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 */
class rcube_cache_apc extends rcube_cache
{
    /**
     * Indicates if APC module is enabled and in a required version
     *
     * @var bool
     */
    protected $enabled;


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

        $this->type    = 'apc';
        $this->enabled = function_exists('apc_exists'); // APC 3.1.4 required
        $this->debug   = $rcube->config->get('apc_debug');
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
        if (!$this->enabled) {
            return false;
        }

        $data = apc_fetch($key);

        if ($this->debug) {
            $this->debug('get', $key, $data);
        }

        return $data;
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
        if (!$this->enabled) {
            return false;
        }

        if (apc_exists($key)) {
            apc_delete($key);
        }

        $result = apc_store($key, $data, $this->ttl);

        if ($this->debug) {
            $this->debug('set', $key, $data, $result);
        }

        return $result;
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
        if (!$this->enabled) {
            return false;
        }

        $result = apc_delete($key);

        if ($this->debug) {
            $this->debug('delete', $key, null, $result);
        }

        return $result;
    }
}

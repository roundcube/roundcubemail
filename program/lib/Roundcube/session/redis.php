<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide redis supported session management                          |
 +-----------------------------------------------------------------------+
 | Author: Cor Bosman <cor@roundcu.be>                                   |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to provide redis session storage
 *
 * @package    Framework
 * @subpackage Core
 */
class rcube_session_redis extends rcube_session
{
    /** @var Redis The redis engine */
    private $redis;

    /** @var bool Debug state */
    private $debug;


    /**
     * Object constructor
     *
     * @param rcube_config $config Configuration
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $this->redis = rcube::get_instance()->get_redis();
        $this->debug = $config->get('redis_debug');

        if (!$this->redis) {
            rcube::raise_error([
                    'code' => 604, 'type' => 'redis',
                    'line' => __LINE__, 'file' => __FILE__,
                    'message' => "Failed to connect to redis. Please check configuration"
                ],
                true, true);
        }

        // register sessions handler
        $this->register_session_handler();
    }

    /**
     * Opens the session
     *
     * @param string $save_path    Session save path
     * @param string $session_name Session name
     *
     * @return bool True on success, False on failure
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * Close the session
     *
     * @return bool True on success, False on failure
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy the session
     *
     * @param string $key Session identifier
     *
     * @return bool True on success, False on failure
     */
    public function destroy($key)
    {
        if ($key) {
            try {
                $fname  = method_exists($this->redis, 'del') ? 'del' : 'delete';
                $result = $this->redis->$fname($key);
            }
            catch (Exception $e) {
                rcube::raise_error($e, true, true);
            }

            if ($this->debug) {
                $this->debug('delete', $key, null, $result ?? false);
            }
        }

        return true;
    }

    /**
     * Read data from redis store
     *
     * @param string $key Session identifier
     *
     * @return string Serialized data string
     */
    public function read($key)
    {
        $value = null;

        try {
            $value = $this->redis->get($key);
        }
        catch (Exception $e) {
            rcube::raise_error($e, true, true);
        }

        if ($this->debug) {
            $this->debug('get', $key, $value);
        }

        if ($value) {
            $arr = unserialize($value);
            $this->changed = $arr['changed'];
            $this->ip      = $arr['ip'];
            $this->vars    = $arr['vars'];
            $this->key     = $key;
        }

        return $this->vars ?: '';
    }

    /**
     * Write data to redis store
     *
     * @param string $key     Session identifier
     * @param string $newvars New session data string
     * @param string $oldvars Old session data string
     *
     * @return bool True on success, False on failure
     */
    public function update($key, $newvars, $oldvars)
    {
        $ts = microtime(true);

        if ($newvars !== $oldvars || $ts - $this->changed > $this->lifetime / 3) {
            $data   = serialize(['changed' => time(), 'ip' => $this->ip, 'vars' => $newvars]);
            $result = false;

            try {
                $result = $this->redis->setex($key, $this->lifetime + 60, $data);
            }
            catch (Exception $e) {
                rcube::raise_error($e, true, true);
            }

            if ($this->debug) {
                $this->debug('set', $key, $data, $result);
            }

            return $result;
        }

        return true;
    }

    /**
     * Write data to redis store
     *
     * @param string $key  Session identifier
     * @param array  $vars Session data
     *
     * @return bool True on success, False on failure
     */
    public function write($key, $vars)
    {
        if ($this->ignore_write) {
            return true;
        }

        $result = false;
        $data   = null;

        try {
            $data   = serialize(['changed' => time(), 'ip' => $this->ip, 'vars' => $vars]);
            $result = $this->redis->setex($key, $this->lifetime + 60, $data);
        }
        catch (Exception $e) {
            rcube::raise_error($e, true, true);
        }

        if ($this->debug) {
            $this->debug('set', $key, $data, $result);
        }

        return $result;
    }

    /**
     * Write memcache debug info to the log
     *
     * @param string $type   Operation type
     * @param string $key    Session identifier
     * @param string $data   Data to log
     * @param bool   $result Operation result
     */
    protected function debug($type, $key, $data = null, $result = null)
    {
        $line = strtoupper($type) . ' ' . $key;

        if ($data !== null) {
            $line .= ' ' . $data;
        }

        rcube::debug('redis', $line, $result);
    }
}

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
 |   Provide memcached supported session management                      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 | Author: Cor Bosman <cor@roundcu.bet>                                  |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to provide memcached session storage
 *
 * @package    Framework
 * @subpackage Core
 */
class rcube_session_memcached extends rcube_session
{
    /** @var Memcached The memcache driver */
    private $memcache;

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

        $this->memcache = rcube::get_instance()->get_memcached();
        $this->debug    = $config->get('memcache_debug');

        if (!$this->memcache) {
            rcube::raise_error([
                    'code' => 604, 'type' => 'memcache',
                    'line' => __LINE__, 'file' => __FILE__,
                    'message' => "Failed to connect to memcached. Please check configuration"
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
            // #1488592: use 2nd argument
            $result = $this->memcache->delete($key, 0);

            if ($this->debug) {
                $this->debug('delete', $key, null, $result);
            }
        }

        return true;
    }

    /**
     * Read session data from memcache
     *
     * @param string $key Session identifier
     *
     * @return string Serialized data string
     */
    public function read($key)
    {
        if ($arr = $this->memcache->get($key)) {
            $this->changed = $arr['changed'];
            $this->ip      = $arr['ip'];
            $this->vars    = $arr['vars'];
            $this->key     = $key;
        }

        if ($this->debug) {
            $this->debug('get', $key, $arr ? serialize($arr) : '');
        }

        return $this->vars ?: '';
    }

    /**
     * Write data to memcache storage
     *
     * @param string $key  Session identifier
     * @param string $vars Session data string
     *
     * @return bool True on success, False on failure
     */
    public function write($key, $vars)
    {
        if ($this->ignore_write) {
            return true;
        }

        $data   = ['changed' => time(), 'ip' => $this->ip, 'vars' => $vars];
        $result = $this->memcache->set($key, $data, $this->lifetime + 60);

        if ($this->debug) {
            $this->debug('set', $key, serialize($data), $result);
        }

        return $result;
    }

    /**
     * Update memcache session data
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
            $data   = ['changed' => time(), 'ip' => $this->ip, 'vars' => $newvars];
            $result = $this->memcache->set($key, $data, $this->lifetime + 60);

            if ($this->debug) {
                $this->debug('set', $key, serialize($data), $result);
            }

            return $result;
        }

        return true;
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

        rcube::debug('memcache', $line, $result);
    }
}

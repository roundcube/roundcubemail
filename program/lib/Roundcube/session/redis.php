<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2018, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide redis supported session management                          |
 +-----------------------------------------------------------------------+
 | Author: Cor Bosman <cor@roundcu.be>                                   |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to provide redis session storage
 *
 * @package    Framework
 * @subpackage Core
 * @author     Cor Bosman <cor@roundcu.be>
 * @author     Aleksander Machniak <alec@alec.pl>
 */
class rcube_session_redis extends rcube_session {

    private $redis;
    private $debug;

    /**
     * @param rcube_config $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $this->redis = rcube::get_instance()->get_redis();
        $this->debug = $config->get('redis_debug');

        // register sessions handler
        $this->register_session_handler();
    }

    /**
     * @param $save_path
     * @param $session_name
     * @return bool
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * remove data from store
     *
     * @param $key
     * @return bool
     */
    public function destroy($key)
    {
        if ($key) {
            $result = $this->redis->del($key);

            if ($this->debug) {
                $this->debug('delete', $key, null, $result);
            }
        }

        return true;
    }

    /**
     * read data from redis store
     *
     * @param $key
     * @return string
     */
    public function read($key)
    {
        if ($value = $this->redis->get($key)) {
            $arr = unserialize($value);
            $this->changed = $arr['changed'];
            $this->ip      = $arr['ip'];
            $this->vars    = $arr['vars'];
            $this->key     = $key;
        }

        if ($this->debug) {
            $this->debug('get', $key, $value);
        }

        return $this->vars ?: '';
    }

    /**
     * write data to redis store
     *
     * @param $key
     * @param $newvars
     * @param $oldvars
     * @return bool
     */
    public function update($key, $newvars, $oldvars)
    {
        $ts = microtime(true);

        if ($newvars !== $oldvars || $ts - $this->changed > $this->lifetime / 3) {
            $data   = serialize(array('changed' => time(), 'ip' => $this->ip, 'vars' => $newvars));
            $result = $this->redis->setex($key, $this->lifetime + 60, $data);

            if ($this->debug) {
                $this->debug('set', $key, $data, $result);
            }

            return $result;
        }

        return true;
    }

    /**
     * write data to redis store
     *
     * @param $key
     * @param $vars
     * @return bool
     */
    public function write($key, $vars)
    {
        if ($this->ignore_write) {
            return true;
        }

        $data   = serialize(array('changed' => time(), 'ip' => $this->ip, 'vars' => $vars));
        $result = $this->redis->setex($key, $this->lifetime + 60, $data);

        if ($this->debug) {
            $this->debug('set', $key, $data, $result);
        }

        return $result;
    }

    /**
     * Write memcache debug info to the log
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

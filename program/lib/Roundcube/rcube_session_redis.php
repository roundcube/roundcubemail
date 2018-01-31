<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2014, The Roundcube Dev Team                       |
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
 */
class rcube_session_redis extends rcube_session {

    private $redis;

    /**
     * @param Object $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $this->redis = rcube::get_instance()->get_redis();

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
            $this->redis->del($key);
        }

        return true;
    }

    /**
     * read data from redis store
     *
     * @param $key
     * @return null
     */
    public function read($key)
    {
        if ($value = $this->redis->get($key)) {
            $arr = unserialize($value);
            $this->changed = $arr['changed'];
            $this->ip      = $arr['ip'];
            $this->vars    = $arr['vars'];
            $this->key     = $key;

            return !empty($this->vars) ? (string) $this->vars : '';
        }

        return '';
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
            $data = serialize(array('changed' => time(), 'ip' => $this->ip, 'vars' => $newvars));
            $this->redis->setex($key, $this->lifetime + 60, $data);
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

        $data = serialize(array('changed' => time(), 'ip' => $this->ip, 'vars' => $vars));

        return $this->redis->setex($key, $this->lifetime + 60, $data);
    }
}

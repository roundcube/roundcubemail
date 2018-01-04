<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2014, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide database supported session management                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 | Author: Cor Bosman <cor@roundcu.bet>                                  |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to provide memcache session storage
 *
 * @package    Framework
 * @subpackage Core
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 * @author     Cor Bosman <cor@roundcu.be>
 */
class rcube_session_memcache extends rcube_session implements SessionHandlerInterface
{
    private $memcache;
    private $debug;

    /**
     * rcube_session_memcache constructor.
     * @param rcube_config $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (!class_exists('Memcached')) {
            rcube::raise_error(array(
                'code' => 604,
                'type' => 'db',
                'line' => __LINE__,
                'file' => __FILE__,
                'message' => 'Please enable memcached extension for php',
            ), true, true);
        }

        if ($config->get('memcache_pconnect', true)) {
            $this->memcache = new Memcached('roundcube_session');
        }
        else {
            $this->memcache = new Memcached();
        }

        $this->memcache->setOptions(array(
            Memcached::OPT_CONNECT_TIMEOUT => $config->get('memcache_timeout', 1),
            Memcached::OPT_RETRY_TIMEOUT => $config->get('memcache_retry_interval', 15),
        ));

        $this->debug = $config->get('memcache_debug');

        foreach ($config->get('memcache_hosts', array()) as $host) {
            $this->add_server($host);
        }

        if ($this->memcache->getVersion() === false) {
            rcube::raise_error(array(
                'code' => 604,
                'type' => 'db',
                'line' => __LINE__,
                'file' => __FILE__,
                'message' => 'Failed to connect to memcached. Please check configuration',
            ), true, true);
        }

        // register sessions handler
        $this->register_session_handler();
    }

    /**
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $name The session name.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function destroy($session_id)
    {
        $result = $this->memcache->delete($session_id);
        $this->debug('delete', $session_id, null, $result);

        return $result;
    }

    /**
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function read($session_id)
    {
        $value = $this->memcache->get($session_id);
        $this->debug('get', $session_id, $value);

        if ($value === false) {
            return '';
        }

        $data = unserialize($value);

        $this->changed = $data['changed'];
        $this->ip = $data['ip'];
        $this->vars = $data['vars'];
        $this->key = $session_id;

        return $this->vars ?: '';
    }

    /**
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function write($session_id, $session_data)
    {
        // ignore write is handled by sess_write from rcube_session
        $data = serialize(array('changed' => time(), 'ip' => $this->ip, 'vars' => $session_data));

        $result = $this->memcache->set($session_id, $data, $this->lifetime + 60);
        $this->debug('set', $session_id, $data, $result);

        return $result;
    }

    /**
     * Update session data
     *
     * @param string $session_id The session id.
     * @param string $session_data
     * @param string $old_session_data
     *
     * @return bool
     */
    public function update($session_id, $session_data, $old_session_data)
    {
        // write and update is the same for memcache
        return $this->write($session_id, $session_data);
    }

    /**
     * Write memcache debug info to the log
     *
     * @param $type
     * @param $key
     * @param null $data
     * @param null $result
     */
    private function debug($type, $key, $data = null, $result = null)
    {
        if ($this->debug) {
            $line = strtoupper($type) . ' ' . $key;

            if ($data !== null) {
                $line = $line . ' ' . $data;
            }

            rcube::debug('memcache', $line, $result);
        }
    }

    /**
     * Connect to memcache instance by unix socket or tcp
     *
     * @param $host
     * @return bool
     */
    private function add_server($host)
    {
        // connect to server by unix socket
        if (substr($host, 0, 7) === 'unix://') {
            return $this->memcache->addServer($host, 0);
        }

        // connect to server by tcp
        $configuration = explode(':', $host);

        if (count($configuration) === 2) {
            return $this->memcache->addServer($configuration[0], (int)$configuration[1]);
        }

        if (count($configuration) === 3) {
            return $this->memcache->addServer($configuration[0], (int)$configuration[1], (int)$configuration[2]);
        }

        return false;
    }
}

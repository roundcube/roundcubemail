<?php

/**
 *  Classes for managesieve operations (using PEAR::Net_Sieve)
 *
 * Copyright (C) 2008-2011, The Roundcube Dev Team
 * Copyright (C) 2011, Kolab Systems AG
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

// Managesieve Protocol: RFC5804

define('SIEVE_ERROR_CONNECTION', 1);
define('SIEVE_ERROR_LOGIN', 2);
define('SIEVE_ERROR_NOT_EXISTS', 3);    // script not exists
define('SIEVE_ERROR_INSTALL', 4);       // script installation
define('SIEVE_ERROR_ACTIVATE', 5);      // script activation
define('SIEVE_ERROR_DELETE', 6);        // script deletion
define('SIEVE_ERROR_INTERNAL', 7);      // internal error
define('SIEVE_ERROR_DEACTIVATE', 8);    // script activation
define('SIEVE_ERROR_OTHER', 255);       // other/unknown error


class rcube_sieve
{
    private $sieve;                 // Net_Sieve object
    private $error = false;         // error flag
    private $list = array();        // scripts list

    public $script;                 // rcube_sieve_script object
    public $current;                // name of currently loaded script
    private $exts;                  // array of supported extensions


    /**
     * Object constructor
     *
     * @param string  Username (for managesieve login)
     * @param string  Password (for managesieve login)
     * @param string  Managesieve server hostname/address
     * @param string  Managesieve server port number
     * @param string  Managesieve authentication method 
     * @param boolean Enable/disable TLS use
     * @param array   Disabled extensions
     * @param boolean Enable/disable debugging
     * @param string  Proxy authentication identifier
     * @param string  Proxy authentication password
     */
    public function __construct($username, $password='', $host='localhost', $port=2000,
        $auth_type=null, $usetls=true, $disabled=array(), $debug=false,
        $auth_cid=null, $auth_pw=null)
    {
        $this->sieve = new Net_Sieve();

        if ($debug) {
            $this->sieve->setDebug(true, array($this, 'debug_handler'));
        }

        if (PEAR::isError($this->sieve->connect($host, $port, null, $usetls))) {
            return $this->_set_error(SIEVE_ERROR_CONNECTION);
        }

        if (!empty($auth_cid)) {
            $authz    = $username;
            $username = $auth_cid;
            $password = $auth_pw;
        }

        if (PEAR::isError($this->sieve->login($username, $password,
            $auth_type ? strtoupper($auth_type) : null, $authz))
        ) {
            return $this->_set_error(SIEVE_ERROR_LOGIN);
        }

        $this->exts = $this->get_extensions();

        // disable features by config
        if (!empty($disabled)) {
            // we're working on lower-cased names
            $disabled = array_map('strtolower', (array) $disabled);
            foreach ($disabled as $ext) {
                if (($idx = array_search($ext, $this->exts)) !== false) {
                    unset($this->exts[$idx]);
                }
            }
        }
    }

    public function __destruct() {
        $this->sieve->disconnect();
    }

    /**
     * Getter for error code
     */
    public function error()
    {
        return $this->error ? $this->error : false;
    }

    /**
     * Saves current script into server
     */
    public function save($name = null)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$this->script)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$name)
            $name = $this->current;

        $script = $this->script->as_text();

        if (!$script)
            $script = '/* empty script */';

        if (PEAR::isError($this->sieve->installScript($name, $script)))
            return $this->_set_error(SIEVE_ERROR_INSTALL);

        return true;
    }

    /**
     * Saves text script into server
     */
    public function save_script($name, $content = null)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$content)
            $content = '/* empty script */';

        if (PEAR::isError($this->sieve->installScript($name, $content)))
            return $this->_set_error(SIEVE_ERROR_INSTALL);

        return true;
    }

    /**
     * Activates specified script
     */
    public function activate($name = null)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$name)
            $name = $this->current;

        if (PEAR::isError($this->sieve->setActive($name)))
            return $this->_set_error(SIEVE_ERROR_ACTIVATE);

        return true;
    }

    /**
     * De-activates specified script
     */
    public function deactivate()
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (PEAR::isError($this->sieve->setActive('')))
            return $this->_set_error(SIEVE_ERROR_DEACTIVATE);

        return true;
    }

    /**
     * Removes specified script
     */
    public function remove($name = null)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$name)
            $name = $this->current;

        // script must be deactivated first
        if ($name == $this->sieve->getActive())
            if (PEAR::isError($this->sieve->setActive('')))
                return $this->_set_error(SIEVE_ERROR_DELETE);

        if (PEAR::isError($this->sieve->removeScript($name)))
            return $this->_set_error(SIEVE_ERROR_DELETE);

        if ($name == $this->current)
            $this->current = null;

        return true;
    }

    /**
     * Gets list of supported by server Sieve extensions
     */
    public function get_extensions()
    {
        if ($this->exts)
            return $this->exts;

        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        $ext = $this->sieve->getExtensions();
        // we're working on lower-cased names
        $ext = array_map('strtolower', (array) $ext);

        if ($this->script) {
            $supported = $this->script->get_extensions();
            foreach ($ext as $idx => $ext_name)
                if (!in_array($ext_name, $supported))
                    unset($ext[$idx]);
        }

        return array_values($ext);
    }

    /**
     * Gets list of scripts from server
     */
    public function get_scripts()
    {
        if (!$this->list) {

            if (!$this->sieve)
                return $this->_set_error(SIEVE_ERROR_INTERNAL);

            $list = $this->sieve->listScripts();

            if (PEAR::isError($list))
                return $this->_set_error(SIEVE_ERROR_OTHER);

            $this->list = $list;
        }

        return $this->list;
    }

    /**
     * Returns active script name
     */
    public function get_active()
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        return $this->sieve->getActive();
    }

    /**
     * Loads script by name
     */
    public function load($name)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if ($this->current == $name)
            return true;

        $script = $this->sieve->getScript($name);

        if (PEAR::isError($script))
            return $this->_set_error(SIEVE_ERROR_OTHER);

        // try to parse from Roundcube format
        $this->script = $this->_parse($script);

        $this->current = $name;

        return true;
    }

    /**
     * Loads script from text content
     */
    public function load_script($script)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        // try to parse from Roundcube format
        $this->script = $this->_parse($script);
    }

    /**
     * Creates rcube_sieve_script object from text script
     */
    private function _parse($txt)
    {
        // parse
        $script = new rcube_sieve_script($txt, $this->exts);

        // fix/convert to Roundcube format
        if (!empty($script->content)) {
            // replace all elsif with if+stop, we support only ifs
            foreach ($script->content as $idx => $rule) {
                if (empty($rule['type']) || !preg_match('/^(if|elsif|else)$/', $rule['type'])) {
                    continue;
                }

                $script->content[$idx]['type'] = 'if';

                // 'stop' not found?
                foreach ($rule['actions'] as $action) {
                    if (preg_match('/^(stop|vacation)$/', $action['type'])) {
                        continue 2;
                    }
                }
                if (!empty($script->content[$idx+1]) && $script->content[$idx+1]['type'] != 'if') {
                    $script->content[$idx]['actions'][] = array('type' => 'stop');
                }
            }
        }

        return $script;
    }

    /**
     * Gets specified script as text
     */
    public function get_script($name)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        $content = $this->sieve->getScript($name);

        if (PEAR::isError($content))
            return $this->_set_error(SIEVE_ERROR_OTHER);

        return $content;
    }

    /**
     * Creates empty script or copy of other script
     */
    public function copy($name, $copy)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if ($copy) {
            $content = $this->sieve->getScript($copy);

            if (PEAR::isError($content))
                return $this->_set_error(SIEVE_ERROR_OTHER);
        }

        return $this->save_script($name, $content);
    }

    private function _set_error($error)
    {
        $this->error = $error;
        return false;
    }

    /**
     * This is our own debug handler for connection
     */
    public function debug_handler(&$sieve, $message)
    {
        rcube::write_log('sieve', preg_replace('/\r\n$/', '', $message));
    }
}

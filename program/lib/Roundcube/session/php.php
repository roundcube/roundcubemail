<?php

/*
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
 |   Provide database supported session management                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 | Author: Cor Bosman <cor@roundcu.be>                                   |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to provide native php session storage
 */
class rcube_session_php extends rcube_session
{
    /**
     * Native php sessions don't need a save handler.
     * We do need to define abstract function implementations but they are not used.
     */
    #[Override]
    public function open($save_path, $session_name)
    {
        return true;
    }

    #[Override]
    public function close()
    {
        return true;
    }

    #[Override]
    public function destroy($key)
    {
        return true;
    }

    #[Override]
    public function read($key)
    {
        return '';
    }

    #[Override]
    protected function save($key, $vars)
    {
        return true;
    }

    #[Override]
    protected function update($key, $newvars, $oldvars)
    {
        return true;
    }

    /**
     * Object constructor
     *
     * @param rcube_config $config Configuration
     */
    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Wrapper for session_write_close()
     */
    #[Override]
    public function write_close()
    {
        $_SESSION['__IP'] = $this->ip;
        $_SESSION['__MTIME'] = time();

        parent::write_close();
    }

    /**
     * Wrapper for session_start()
     */
    #[Override]
    public function start()
    {
        parent::start();

        $this->key = session_id();
        $this->ip = $_SESSION['__IP'] ?? null;
        $this->changed = $_SESSION['__MTIME'] ?? null;
    }
}

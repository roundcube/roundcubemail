<?php

namespace Roundcube\WIP;

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
 |   Framework base class providing core functions and holding           |
 |   instances of all 'global' objects like db- and storage-connections  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Lightweight plugin API class serving as a dummy if plugins are not enabled
 */
class rcube_dummy_plugin_api
{
    /**
     * Triggers a plugin hook.
     *
     * @param string       $hook Hook name
     * @param array|string $args Hook arguments
     *
     * @return array Hook arguments
     *
     * @see rcube_plugin_api::exec_hook()
     */
    public function exec_hook($hook, $args = [])
    {
        if (!is_array($args)) {
            $args = ['arg' => $args];
        }

        return $args += ['abort' => false];
    }
}

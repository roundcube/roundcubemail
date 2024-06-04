<?php

namespace Roundcube\Tests;

/*
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
 |   A class for easier testing of code utilizing rcube_storage          |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * A class for easier testing of code utilizing rcube_storage
 */
class StorageMock
{
    public $methodCalls = [];

    protected $mocks = [];

    public function registerFunction($name, $result = null)
    {
        $this->mocks[] = [$name, $result];

        return $this;
    }

    public function __call($name, $arguments)
    {
        foreach ($this->mocks as $idx => $mock) {
            if ($mock[0] == $name) {
                $result = $mock[1];
                $this->methodCalls[] = ['name' => $name, 'args' => $arguments];
                unset($this->mocks[$idx]);
                return $result;
            }
        }

        throw new \Exception("Unhandled function call for '{$name}'");
    }

    /**
     * Close connection. Usually done on script shutdown
     */
    public function close()
    {
        // do nothing
    }

    public function get_hierarchy_delimiter()
    {
        return '/';
    }

    public function get_namespace()
    {
        return null;
    }

    /**
     * Check if specified folder is a special folder
     */
    public function is_special_folder($name)
    {
        return $name == 'INBOX' || in_array($name, $this->get_special_folders());
    }

    /**
     * Return configured special folders
     */
    public function get_special_folders($forced = false)
    {
        $rcube = \rcube::get_instance();
        $folders = [];

        foreach (\rcube_storage::$folder_types as $type) {
            if ($folder = $rcube->config->get($type . '_mbox')) {
                $folders[$type] = $folder;
            }
        }

        return $folders;
    }
}

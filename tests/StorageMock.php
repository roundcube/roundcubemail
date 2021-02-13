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
 |   A class for easier testing of code utilizing rcube_storage          |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * A class for easier testing of code utilizing rcube_storage
 *
 * @package Tests
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

        throw new Exception("Unhandled function call for '$name' in StorageMock");
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
}

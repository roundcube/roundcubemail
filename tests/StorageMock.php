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
    protected $mocks = [];

    public function registerFunction($name, $result = null)
    {
        $this->mocks[] = [$name, $result];
    }

    public function __call($name, $arguments)
    {
        foreach ($this->mocks as $idx => $mock) {
            if ($mock[0] == $name) {
                $result = $mock[1];
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
}

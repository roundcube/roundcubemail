<?php
/*
 +-------------------------------------------------------------------------+
 | Error class for the Enigma Plugin                                       |
 |                                                                         |
 | This program is free software; you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License version 2          |
 | as published by the Free Software Foundation.                           |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License along |
 | with this program; if not, write to the Free Software Foundation, Inc., |
 | 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

class enigma_error
{
    private $code;
    private $message;
    private $data = array();

    // error codes
    const E_OK = 0;
    const E_INTERNAL = 1;
    const E_NODATA = 2;
    const E_KEYNOTFOUND = 3;
    const E_DELKEY = 4;
    const E_BADPASS = 5;
    const E_EXPIRED = 6;
    const E_UNVERIFIED = 7;

    function __construct($code = null, $message = '', $data = array())
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    function getCode()
    {
        return $this->code;
    }

    function getMessage()
    {
        return $this->message;
    }

    function getData($name)
    {
        if ($name)
            return $this->data[$name];
        else
            return $this->data;
    }
}

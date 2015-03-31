<?php
/*
 +-------------------------------------------------------------------------+
 | Error class for the Enigma Plugin                                       |
 |                                                                         |
 | Copyright (C) 2010-2015 The Roundcube Dev Team                          |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
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
    const E_OK          = 0;
    const E_INTERNAL    = 1;
    const E_NODATA      = 2;
    const E_KEYNOTFOUND = 3;
    const E_DELKEY      = 4;
    const E_BADPASS     = 5;
    const E_EXPIRED     = 6;
    const E_UNVERIFIED  = 7;


    function __construct($code = null, $message = '', $data = array())
    {
        $this->code    = $code;
        $this->message = $message;
        $this->data    = $data;
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
        if ($name) {
            return $this->data[$name];
        }
        else {
            return $this->data;
        }
    }
}

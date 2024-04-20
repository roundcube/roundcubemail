<?php

/*
 +-------------------------------------------------------------------------+
 | Error class for the Enigma Plugin                                       |
 |                                                                         |
 | Copyright (C) The Roundcube Dev Team                                    |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

class enigma_error
{
    private $code;
    private $message;
    private $data = [];

    // error codes
    public const OK = 0;
    public const INTERNAL = 1;
    public const NODATA = 2;
    public const KEYNOTFOUND = 3;
    public const DELKEY = 4;
    public const BADPASS = 5;
    public const EXPIRED = 6;
    public const UNVERIFIED = 7;
    public const NOMDC = 8;

    public function __construct($code = null, $message = '', $data = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getData($name = null)
    {
        if ($name) {
            return $this->data[$name] ?? null;
        }

        return $this->data;
    }
}

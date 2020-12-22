<?php

/**
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
 |   Class representing a message part                                   |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Class representing a message part
 *
 * @package    Framework
 * @subpackage Storage
 */
class rcube_message_part
{
    /**
     * Part MIME identifier
     *
     * @var string
     */
    public $mime_id = '';

    /**
     * Content main type
     *
     * @var string
     */
    public $ctype_primary = 'text';

    /**
     * Content subtype
     *
     * @var string
     */
    public $ctype_secondary = 'plain';

    /**
     * Complete content type
     *
     * @var string
     */
    public $mimetype = 'text/plain';

    /**
     * Part size in bytes
     *
     * @var int
     */
    public $size = 0;

    /**
     * Part body
     *
     * @var string|null
     */
    public $body;

    /**
     * Part headers
     *
     * @var array
     */
    public $headers = [];

    /**
     * Sub-Parts
     *
     * @var array
     */
    public $parts = [];

    public $type;
    public $replaces     = [];
    public $disposition  = '';
    public $filename     = '';
    public $encoding     = '8bit';
    public $charset      = '';
    public $d_parameters = [];
    public $ctype_parameters = [];


    /**
     * Clone handler.
     */
    function __clone()
    {
        if (isset($this->parts)) {
            foreach ($this->parts as $idx => $part) {
                if (is_object($part)) {
                    $this->parts[$idx] = clone $part;
                }
            }
        }
    }
}

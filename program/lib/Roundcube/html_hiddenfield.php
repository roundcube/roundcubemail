<?php

namespace Roundcube\WIP;

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
 |   Helper class to create valid XHTML code                             |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to create an hidden HTML input field
 */
class html_hiddenfield extends \html
{
    protected $tagname = 'input';
    protected $type = 'hidden';
    protected $allowed = ['type', 'name', 'value', 'onchange', 'disabled', 'readonly'];
    protected $fields = [];

    /**
     * Constructor
     *
     * @param array $attrib Named tag attributes
     */
    public function __construct($attrib = null)
    {
        parent::__construct();

        if (is_array($attrib)) {
            $this->add($attrib);
        }
    }

    /**
     * Add a hidden field to this instance
     *
     * @param array $attrib Named tag attributes
     */
    public function add($attrib)
    {
        $this->fields[] = $attrib;
    }

    /**
     * Create HTML code for the hidden fields
     *
     * @return string Final HTML code
     */
    #[\Override]
    public function show()
    {
        $out = '';

        foreach ($this->fields as $attrib) {
            $out .= self::tag($this->tagname, ['type' => $this->type] + $attrib);
        }

        return $out;
    }
}

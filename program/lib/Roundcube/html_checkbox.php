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
 * Class to create HTML checkboxes
 */
class html_checkbox extends \html_inputfield
{
    protected $type = 'checkbox';

    /**
     * Get HTML code for this object
     *
     * @param string $value  Value of the checked field
     * @param array  $attrib Additional attributes to override
     *
     * @return string HTML output
     */
    #[\Override]
    public function show($value = '', $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        // set 'checked' attribute
        $this->attrib['checked'] = (string) $value === (string) ($this->attrib['value'] ?? '');

        return parent::show();
    }
}

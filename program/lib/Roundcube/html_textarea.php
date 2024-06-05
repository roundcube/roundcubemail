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
 * Class to create an HTML textarea
 */
class html_textarea extends \html
{
    protected $tagname = 'textarea';
    protected $allowed = ['name', 'rows', 'cols', 'wrap', 'tabindex',
        'onchange', 'disabled', 'readonly', 'spellcheck'];

    /**
     * Get HTML code for this object
     *
     * @param string $value  Textbox value
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

        // take value attribute as content
        if (empty($value) && !empty($this->attrib['value'])) {
            $value = $this->attrib['value'];
        }

        // make shure we don't print the value attribute
        if (isset($this->attrib['value'])) {
            unset($this->attrib['value']);
        }

        if (!empty($value) && empty($this->attrib['is_escaped'])) {
            $value = self::quote($value);
        }

        return self::tag($this->tagname, $this->attrib, $value,
            array_merge(self::$common_attrib, $this->allowed));
    }
}

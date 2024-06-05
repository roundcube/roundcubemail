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
 * Class to create an HTML input field
 */
class html_inputfield extends \html
{
    protected $tagname = 'input';
    protected $type = 'text';
    protected $allowed = [
        'type', 'name', 'value', 'size', 'tabindex', 'autocapitalize', 'required',
        'autocomplete', 'checked', 'onchange', 'onclick', 'disabled', 'readonly',
        'spellcheck', 'results', 'maxlength', 'src', 'multiple', 'accept',
        'placeholder', 'autofocus', 'pattern', 'oninput',
    ];

    /**
     * Object constructor
     *
     * @param array $attrib Associative array with tag attributes
     */
    public function __construct($attrib = [])
    {
        parent::__construct($attrib);

        if (!empty($attrib['type'])) {
            $this->type = $attrib['type'];
        }
    }

    /**
     * Compose input tag
     *
     * @param string $value  Field value
     * @param array  $attrib Additional attributes to override
     *
     * @return string HTML output
     */
    #[\Override]
    public function show($value = null, $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        // set value attribute
        if ($value !== null) {
            $this->attrib['value'] = $value;
        }
        // set type
        $this->attrib['type'] = $this->type;

        return parent::show();
    }
}

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
 * Builder for HTML drop-down menus.
 */
class html_select extends \html
{
    protected $tagname = 'select';
    protected $options = [];
    protected $allowed = ['name', 'size', 'tabindex', 'autocomplete',
        'multiple', 'onchange', 'disabled', 'rel'];

    /**
     * Add a new option to this drop-down
     *
     * @param mixed $names  Option name or array with option names
     * @param mixed $values Option value or array with option values
     * @param array $attrib Additional attributes for the option entry
     */
    public function add($names, $values = null, $attrib = [])
    {
        if (is_array($names)) {
            foreach ($names as $i => $text) {
                $this->options[] = [
                    'text' => $text,
                    'value' => $values[$i] ?? $i,
                ] + $attrib;
            }
        } else {
            $this->options[] = ['text' => $names, 'value' => $values] + $attrib;
        }
    }

    /**
     * Get HTML code for this object
     *
     * @param string|array $select Value of the selection option
     * @param ?array       $attrib Additional attributes to override
     *
     * @return string HTML output
     */
    #[\Override]
    public function show($select = [], $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        $this->content = "\n";
        $select = (array) $select;
        foreach ($this->options as $option) {
            $attr = [
                'value' => $option['value'],
                'selected' => (in_array($option['value'], $select, true)
                    || in_array($option['text'], $select, true)) ? 1 : null,
            ];

            $option_content = $option['text'];
            if (empty($this->attrib['is_escaped'])) {
                $option_content = self::quote($option_content);
            }

            $allowed = ['value', 'label', 'class', 'style', 'title', 'disabled', 'selected'];

            $this->content .= self::tag('option', $attr + $option, $option_content, $allowed);
        }

        return parent::show();
    }
}

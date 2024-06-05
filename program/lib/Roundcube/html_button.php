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
 * Class to create HTML button
 */
class html_button extends \html_inputfield
{
    protected $tagname = 'button';
    protected $type = 'button';

    /**
     * Get HTML code for this object
     *
     * @param string $content Text Content of the button
     * @param array  $attrib  Additional attributes to override
     *
     * @return string HTML output
     */
    #[\Override]
    public function show($content = '', $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        $this->content = $content;

        return parent::show();
    }
}

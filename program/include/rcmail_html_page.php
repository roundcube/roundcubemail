<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcmail_html_page.php                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2006-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Render a simple HTML page with the given contents                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/


/**
 * Class to create HTML page output using a skin template
 *
 * @package    Core
 * @subpackage View
 */
class rcmail_html_page extends rcmail_output_html
{
    public function write($contents = '')
    {
        self::reset();
        parent::write($contents);
    }
}
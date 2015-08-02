<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/utils/html2text.inc                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2015, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Convert HTML message to plain text                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

$html = stream_get_contents(fopen('php://input', 'r'));

// strip slashes if magic_quotes enabled
if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
    $html = stripslashes($html);
}

// Replace emoticon images with its text representation
$html = $RCMAIL->replace_emoticons($html);

$do_links = (bool) rcube_utils::get_input_value('_do_links', rcube_utils::INPUT_GET);
$width    = (int) rcube_utils::get_input_value('_width', rcube_utils::INPUT_GET);

// Convert to text
$converter = new rcube_html2text($html, false, $do_links, $width);

header('Content-Type: text/plain; charset=UTF-8');
print rtrim($converter->get_text());
exit;

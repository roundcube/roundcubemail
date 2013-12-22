<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/utils/html2text.inc                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
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

$html = $HTTP_RAW_POST_DATA;

// Replace emoticon images with its text representation
$html = $RCMAIL->replace_emoticons($html);

$converter = new rcube_html2text($html, false, true, 0);

header('Content-Type: text/plain; charset=UTF-8');
print rtrim($converter->get_text());
exit;

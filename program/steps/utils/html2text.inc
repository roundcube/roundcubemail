<?php
/*

 +-----------------------------------------------------------------------+
 | program/steps/utils/html2text.inc                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Convert HTML message to plain text                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$html = $HTTP_RAW_POST_DATA;

// Replace emoticon images with its text representation
$html = rcmail_replace_emoticons($html);

$converter = new html2text($html);

header('Content-Type: text/plain; charset=UTF-8');
print rtrim($converter->get_text());
exit;



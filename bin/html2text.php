<?php
/*

 +-----------------------------------------------------------------------+
 | bin/html2text.php                                                     |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
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

define('INSTALL_PATH', realpath('./../') . '/');
require INSTALL_PATH.'program/include/iniset.php';

$converter = new html2text(html_entity_decode($HTTP_RAW_POST_DATA, ENT_COMPAT, 'UTF-8'));

header('Content-Type: text/plain; charset=UTF-8');
print trim($converter->get_text());

?>

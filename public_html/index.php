<?php

/*
 +-----------------------------------------------------------------------+
 | Roundcube Webmail IMAP Client                                         |
 | Version 1.6-git                                                       |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   This is the public entry point for all HTTP requests to the         |
 |   Roundcube webmail application.                                      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/');

// include index.php from application root directory
include INSTALL_PATH . 'index.php';

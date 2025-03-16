<?php

/*
 +-----------------------------------------------------------------------+
 | Roundcube Webmail IMAP Client                                         |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   This is the public entry point for all HTTP requests to the         |
 |   Roundcube Installer.                                                |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

if (!file_exists(__DIR__ . '/../installer/index.php')) {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../installer/index.php';

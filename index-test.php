<?php

/*
 +-----------------------------------------------------------------------+
 | Roundcube Webmail Selenium Tests Entry Point                          |
 |                                                                       |
 | Copyright (C) 2005-2014, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   This is the public entry point for all HTTP requests to the         |
 |   Roundcube webmail application loading the 'tests' environment.      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <thomas@roundcube.net>                       |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__) . '/');

$GLOBALS['env'] = 'test';

// include index.php from application root directory
include INSTALL_PATH . 'index.php';


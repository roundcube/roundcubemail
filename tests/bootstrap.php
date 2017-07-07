<?php

/*
 +-----------------------------------------------------------------------+
 | tests/bootstrap.php                                                   |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2009-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Environment initialization script for unit tests                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

if (php_sapi_name() != 'cli')
  die("Not in shell mode (php-cli)");

if (!defined('INSTALL_PATH')) define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );

define('TESTS_DIR', __DIR__ . '/');

if (@is_dir(TESTS_DIR . 'config')) {
    define('RCUBE_CONFIG_DIR', TESTS_DIR . 'config');
}

require_once(INSTALL_PATH . 'program/include/iniset.php');

rcmail::get_instance(0, 'test')->config->set('devel_mode', false);

// Extend include path so some plugin test won't fail
$include_path = ini_get('include_path') . PATH_SEPARATOR . TESTS_DIR . '..';
if (set_include_path($include_path) === false) {
    die("Fatal error: ini_set/set_include_path does not work.");
}

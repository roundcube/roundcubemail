<?php

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
 |   Environment initialization script for unit tests                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

error_reporting(\E_ALL);

if (\PHP_SAPI != 'cli') {
    exit('Not in shell mode (php-cli)');
}

if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', realpath(__DIR__ . '/../../') . '/');
}

define('ROUNDCUBE_TEST_MODE', true);
define('ROUNDCUBE_TEST_SESSION', microtime(true));
define('TESTS_DIR', __DIR__ . '/');

if (@is_dir(TESTS_DIR . 'config')) {
    define('RCUBE_CONFIG_DIR', TESTS_DIR . 'config');
}

// Some tests depend on the way phpunit is executed
$_SERVER['SCRIPT_NAME'] = 'vendor/bin/phpunit';

require_once INSTALL_PATH . 'program/include/iniset.php';

rcmail::get_instance(0, 'test')->config->set('devel_mode', false);

#!/usr/bin/env php
<?php

/*
 +-----------------------------------------------------------------------+
 | tests/runtests.sh                                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2009, The Roundcube Dev Team                            |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Run-script for unit tests based on http://simpletest.org            |
 |   All .php files in this folder will be treated as tests              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id:  $

*/

if (php_sapi_name() != 'cli')
  die("Not in shell mode (php-cli)");

if (!defined('SIMPLETEST'))   define('SIMPLETEST', '/www/simpletest/');
if (!defined('INSTALL_PATH')) define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );

define('TESTS_DIR', dirname(__FILE__) . '/');
define('RCMAIL_CONFIG_DIR', TESTS_DIR . 'config');

require_once(SIMPLETEST . 'unit_tester.php');
require_once(SIMPLETEST . 'reporter.php');
require_once(INSTALL_PATH . 'program/include/iniset.php');

if (count($_SERVER['argv']) > 1) {
  $testfiles = array();
  for ($i=1; $i < count($_SERVER['argv']); $i++)
    $testfiles[] = realpath('./' . $_SERVER['argv'][$i]);
}
else {
  $testfiles = glob(TESTS_DIR . '*.php');
}

$test = new TestSuite('Roundcube unit tests');
$reporter = new TextReporter();

foreach ($testfiles as $fn) {
  $test->addTestFile($fn);
}

$test->run($reporter);

?>
<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/iniset.php                                            |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Setup the application envoronment required to process               |
 |   any request.                                                        |
 +-----------------------------------------------------------------------+
 | Author: Till Klampaeckel <till@php.net>                               |
 |         Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// Some users are not using Installer, so we'll check some
// critical PHP settings here. Only these, which doesn't provide
// an error/warning in the logs later. See (#1486307).
$crit_opts = array(
    'mbstring.func_overload' => 0,
    'suhosin.session.encrypt' => 0,
    'session.auto_start' => 0,
    'file_uploads' => 1,
    'magic_quotes_runtime' => 0,
);
foreach ($crit_opts as $optname => $optval) {
    if ($optval != ini_get($optname)) {
        die("ERROR: Wrong '$optname' option value. Read REQUIREMENTS section in INSTALL file or use Roundcube Installer, please!");
    }
}

// application constants
define('RCMAIL_VERSION', '0.8-svn');
define('RCMAIL_CHARSET', 'UTF-8');
define('JS_OBJECT_NAME', 'rcmail');
define('RCMAIL_START', microtime(true));

if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');
}

if (!defined('RCMAIL_CONFIG_DIR')) {
    define('RCMAIL_CONFIG_DIR', INSTALL_PATH . 'config');
}

// make sure path_separator is defined
if (!defined('PATH_SEPARATOR')) {
    define('PATH_SEPARATOR', (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? ';' : ':');
}

// RC include folders MUST be included FIRST to avoid other
// possible not compatible libraries (i.e PEAR) to be included
// instead the ones provided by RC
$include_path = INSTALL_PATH . 'program/lib' . PATH_SEPARATOR;
$include_path.= ini_get('include_path');

if (set_include_path($include_path) === false) {
    die("Fatal error: ini_set/set_include_path does not work.");
}

ini_set('error_reporting', E_ALL&~E_NOTICE);

// increase maximum execution time for php scripts
// (does not work in safe mode)
@set_time_limit(120);

// set internal encoding for mbstring extension
if (extension_loaded('mbstring')) {
    mb_internal_encoding(RCMAIL_CHARSET);
    @mb_regex_encoding(RCMAIL_CHARSET);
}

/**
 * Use PHP5 autoload for dynamic class loading
 * 
 * @todo Make Zend, PEAR etc play with this
 * @todo Make our classes conform to a more straight forward CS.
 */
function rcube_autoload($classname)
{
    $filename = preg_replace(
        array(
            '/MDB2_(.+)/',
            '/Mail_(.+)/',
            '/Net_(.+)/',
            '/Auth_(.+)/',
            '/^html_.+/',
            '/^utf8$/',
        ),
        array(
            'MDB2/\\1',
            'Mail/\\1',
            'Net/\\1',
            'Auth/\\1',
            'html',
            'utf8.class',
        ),
        $classname
    );

    if ($fp = @fopen("$filename.php", 'r', true)) {
        fclose($fp);
        include_once("$filename.php");
        return true;
    }

    return false;
}

spl_autoload_register('rcube_autoload');

/**
 * Local callback function for PEAR errors
 */
function rcube_pear_error($err)
{
    error_log(sprintf("%s (%s): %s",
        $err->getMessage(),
        $err->getCode(),
        $err->getUserinfo()), 0);
}

// set PEAR error handling (will also load the PEAR main class)
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'rcube_pear_error');

// include global functions
require_once INSTALL_PATH . 'program/include/main.inc';
require_once INSTALL_PATH . 'program/include/rcube_shared.inc';

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
define('RCMAIL_VERSION', '0.9-git');
define('RCMAIL_CHARSET', 'UTF-8');
define('RCMAIL_START', microtime(true));

if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');
}

if (!defined('RCMAIL_CONFIG_DIR')) {
    define('RCMAIL_CONFIG_DIR', INSTALL_PATH . 'config');
}

// RC include folders MUST be included FIRST to avoid other
// possible not compatible libraries (i.e PEAR) to be included
// instead the ones provided by RC
$include_path = INSTALL_PATH . 'program/lib' . PATH_SEPARATOR;
$include_path.= ini_get('include_path');

if (set_include_path($include_path) === false) {
    die("Fatal error: ini_set/set_include_path does not work.");
}

ini_set('error_reporting', E_ALL &~ (E_NOTICE | E_STRICT));

// increase maximum execution time for php scripts
// (does not work in safe mode)
@set_time_limit(120);

// set internal encoding for mbstring extension
if (extension_loaded('mbstring')) {
    mb_internal_encoding(RCMAIL_CHARSET);
    @mb_regex_encoding(RCMAIL_CHARSET);
}

// include global functions
require_once INSTALL_PATH . 'program/include/rcube_shared.inc';

// Register autoloader
spl_autoload_register('rcube_autoload');

// set PEAR error handling (will also load the PEAR main class)
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'rcube_pear_error');

// backward compatybility (to be removed)
require_once INSTALL_PATH . 'program/include/rcube_bc.inc';

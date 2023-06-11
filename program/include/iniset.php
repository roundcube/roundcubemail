<?php

/**
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
 |   Setup the application environment required to process               |
 |   any request.                                                        |
 +-----------------------------------------------------------------------+
 | Author: Till Klampaeckel <till@php.net>                               |
 |         Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

if (PHP_VERSION_ID < 70300) {
    die("Unsupported PHP version. Required PHP >= 7.3.");
}

// application constants
define('RCMAIL_VERSION', '1.6-git');
define('RCMAIL_START', microtime(true));

if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');
}

if (!defined('RCMAIL_CONFIG_DIR')) {
    define('RCMAIL_CONFIG_DIR', getenv('ROUNDCUBE_CONFIG_DIR') ?: (INSTALL_PATH . 'config'));
}

if (!defined('RCUBE_LOCALIZATION_DIR')) {
    define('RCUBE_LOCALIZATION_DIR', INSTALL_PATH . 'program/localization/');
}

define('RCUBE_INSTALL_PATH', INSTALL_PATH);
define('RCUBE_CONFIG_DIR',  RCMAIL_CONFIG_DIR.'/');

// Show basic error message on fatal PHP error
register_shutdown_function('rcmail_error_handler');

// RC include folders MUST be included FIRST to avoid other
// possible not compatible libraries (i.e PEAR) to be included
// instead the ones provided by RC
$include_path = INSTALL_PATH . 'program/lib' . PATH_SEPARATOR;
$include_path.= ini_get('include_path');

if (set_include_path($include_path) === false) {
    die("Fatal error: ini_set/set_include_path does not work.");
}

// increase maximum execution time for php scripts
// (does not work in safe mode)
@set_time_limit(120);

// include composer autoloader (if available)
if (@file_exists(INSTALL_PATH . 'vendor/autoload.php')) {
    require INSTALL_PATH . 'vendor/autoload.php';
}

// translate PATH_INFO to _task and _action GET parameters
if (!empty($_SERVER['PATH_INFO']) && preg_match('!^/([a-z]+)/([a-z]+)$!', $_SERVER['PATH_INFO'], $m)) {
    if (!isset($_GET['_task'])) {
        $_GET['_task'] = $m[1];
    }
    if (!isset($_GET['_action'])) {
        $_GET['_action'] = $m[2];
    }
}

// include Roundcube Framework
require_once 'Roundcube/bootstrap.php';

// register autoloader for rcmail app classes
spl_autoload_register('rcmail_autoload');

/**
 * PHP5 autoloader routine for dynamic class loading
 */
function rcmail_autoload($classname)
{
    if (strpos($classname, 'rcmail') === 0) {
        if (preg_match('/^rcmail_action_([^_]+)_(.*)$/', $classname, $matches)) {
            $filepath = INSTALL_PATH . "program/actions/{$matches[1]}/{$matches[2]}.php";
        }
        else {
            $filepath = INSTALL_PATH . "program/include/$classname.php";
        }

        if (is_readable($filepath)) {
            include_once $filepath;
            return true;
        }
    }

    return false;
}

/**
 * Show a generic error message on fatal PHP error
 */
function rcmail_error_handler()
{
    $error = error_get_last();

    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        rcmail_fatal_error();
    }
}

/**
 * Raise a generic error message on error
 */
function rcmail_fatal_error()
{
    if (php_sapi_name() === 'cli') {
        echo "Fatal error: Please check the Roundcube error log and/or server error logs for more information.\n";
    }
    elseif (!empty($_REQUEST['_remote'])) {
        // Ajax request from UI
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['code' => 500, 'message' => "Internal Server Error"]);
    }
    else {
        if (!defined('RCUBE_FATAL_ERROR_MSG')) {
            define('RCUBE_FATAL_ERROR_MSG', INSTALL_PATH . 'program/resources/error.html');
        }

        echo file_get_contents(RCUBE_FATAL_ERROR_MSG);
    }

    exit;
}

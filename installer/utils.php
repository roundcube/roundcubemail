<?php
/*
 +-------------------------------------------------------------------------+
 | Roundcube Webmail installer utilities                                   |
 |                                                                         |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                         |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                          |
 +-------------------------------------------------------------------------+

 $Id: index.php 2696 2009-07-02 06:38:26Z thomasb $

*/

/**
 * Use PHP5 autoload for dynamic class loading
 * (copy from program/include/iniset.php)
 */
function __autoload($classname)
{
    $filename = preg_replace(
        array(
            '/MDB2_(.+)/',
            '/Mail_(.+)/',
            '/Net_(.+)/',
            '/Auth_(.+)/',
            '/^html_.+/',
            '/^utf8$/'
        ),
        array(
            'MDB2/\\1',
            'Mail/\\1',
            'Net/\\1',
            'Auth/\\1',
            'html',
            'utf8.class'
        ),
        $classname
    );
    include_once $filename. '.php';
}


/**
 * Fake internal error handler to catch errors
 */
function raise_error($p)
{
    $rci = rcube_install::get_instance();
    $rci->raise_error($p);
}

/**
 * Local callback function for PEAR errors
 */
function rcube_pear_error($err)
{
    raise_error(array(
        'code' => $err->getCode(),
        'message' => $err->getMessage(),
    ));
}

// set PEAR error handling (will also load the PEAR main class)
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'rcube_pear_error');

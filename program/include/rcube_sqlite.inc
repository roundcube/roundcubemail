<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_sqlite.inc                                      |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide callback functions for sqlite that will emulate             |
 |   sone MySQL functions                                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

/**
 * Callback functions for sqlite database interface
 *
 * @package Database
 */


function rcube_sqlite_from_unixtime($timestamp)
{
    $timestamp = trim($timestamp);
    if (!preg_match('/^[0-9]+$/is', $timestamp))
        $ret = strtotime($timestamp);
    else
        $ret = $timestamp;
    
    $ret = date('Y-m-d H:i:s', $ret);
    rcube_sqlite_debug("FROM_UNIXTIME ($timestamp) = $ret");
    return $ret;
}


function rcube_sqlite_unix_timestamp($timestamp='')
{
    $timestamp = trim($timestamp);
    if (!$timestamp)
        $ret = time();
    else if (!preg_match('/^[0-9]+$/is', $timestamp))
        $ret = strtotime($timestamp);
    else
        $ret = $timestamp;

    rcube_sqlite_debug("UNIX_TIMESTAMP ($timestamp) = $ret");
    return $ret;
}


function rcube_sqlite_now()
{
    rcube_sqlite_debug("NOW() = ".date("Y-m-d H:i:s"));
    return date("Y-m-d H:i:s");
}


function rcube_sqlite_md5($str)
{
    return md5($str);
}


function rcube_sqlite_debug($str)
{
    //console($str);
}


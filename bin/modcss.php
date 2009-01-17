<?php

/*
 +-----------------------------------------------------------------------+
 | bin/modcss.php                                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2007-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Modify CSS source from a URL                                        |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/');
require INSTALL_PATH . 'program/include/iniset.php';

$RCMAIL = rcmail::get_instance();

$source = '';
$error  = 'Requires a valid user session and source url';

if (empty($RCMAIL->user->ID)) {
    header('HTTP/1.1 403 Forbidden');
    echo $error;
    exit;
}

$url = preg_replace('/[^a-z0-9.-_\?\$&=%]/i', '', $_GET['u']);
if ($url === null) {
    header('HTTP/1.1 403 Forbidden');
    echo $error;
    exit;
}

$a_uri = parse_url($url);
$port  = $a_uri['port'] ? $a_uri['port'] : 80;
$host  = $a_uri['host'];
$path  = $a_uri['path'] . ($a_uri['query'] ? '?'.$a_uri['query'] : '');

if (!($fp = fsockopen($host, $port, $errno, $errstr, 30))) {
    header('HTTP/1.1 500 Internal Server Error');
    echo $error;
    exit;
}

$out  = "GET $path HTTP/1.0\r\n";
$out .= "Host: $host\r\n";
$out .= "Connection: Close\r\n\r\n";
fwrite($fp, $out);

$header = true;
while (!feof($fp)) {
    $line = trim(fgets($fp, 4048));

    if ($header
        && preg_match('/^HTTP\/1\..\s+(\d+)/', $line, $regs)
        && intval($regs[1]) != 200) {
	break;
    } else if (empty($line) && $header) {
        $header = false;
    } else if (!$header) {
        $source .= "$line\n";
    }
    fclose($fp);
}

if (!empty($source)) {
    header('Content-Type: text/css');
    echo rcmail_mod_css_styles(
        $source,
        preg_replace('/[^a-z0-9]/i', '', $_GET['c']),
        $url
    );
    exit;
}

header('HTTP/1.0 404 Not Found');
echo $error;
exit;

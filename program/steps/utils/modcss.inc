<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/utils/modcss.inc                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2007-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Modify CSS source from a URL                                        |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

$url = preg_replace('![^a-z0-9.-]!i', '', $_GET['_u']);

if ($url === null || !($realurl = $_SESSION['modcssurls'][$url])) {
    header('HTTP/1.1 403 Forbidden');
    exit("Unauthorized request");
}

// don't allow any other connections than http(s)
if (!preg_match('~^(https?)://~i', $realurl, $matches)) {
    header('HTTP/1.1 403 Forbidden');
    exit("Invalid URL");
}

if (!ini_get('allow_url_fopen')) {
    header('HTTP/1.1 403 Forbidden');
    exit("HTTP connections disabled");
}

$scheme  = strtolower($matches[1]);
$options = array(
    $scheme => array(
        'method' => 'GET',
        'timeout' => 15,
    )
);

$context = stream_context_create($options);
$source  = @file_get_contents($realurl, false, $context);

// php.net/manual/en/reserved.variables.httpresponseheader.php
$headers = implode("\n", (array)$http_response_header);
$ctype   = '~Content-Type:\s+text/(css|plain)~i';

if ($source !== false && preg_match($ctype, $headers)) {
    header('Content-Type: text/css');
    echo rcmail_mod_css_styles($source, preg_replace('/[^a-z0-9]/i', '', $_GET['_c']));
    exit;
}

header('HTTP/1.0 404 Not Found');
exit("Invalid response returned by server");

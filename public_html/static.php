<?php

/*
 +-----------------------------------------------------------------------+
 | Roundcube Webmail IMAP Client                                         |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   This is the public entry point for HTTP requests regarding static   |
 |   content.                                                            |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * @var array Supported file types/extensions. If file type is not on the list
 *            it will have to be served by custom code in your plugin.
 */
const SUPPORTED_TYPES = [
    'avif' => 'image/avif',
    'css' => 'text/css',
    'gif' => 'image/gif',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'html' => 'text/html',
    'ico' => 'image/x-icon',
    'js' => 'text/javascript',
    'json' => 'application/json',
    'less' => 'text/less',
    'mp3' => 'audio/mpeg',
    'png' => 'image/png',
    'pdf' => 'application/pdf',
    'svg' => 'image/svg+xml',
    'tiff' => 'image/tiff',
    'wav' => 'audio/wav',
    'webp' => 'image/webp',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
];

/**
 * @var array Path prefixes to look for the requested files
 */
const ALLOWED_PATHS = [
    'installer/',
    'plugins/',
    'program/',
    'skins/',
];

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/');

$path = validateStaticFile($_SERVER['PATH_INFO']);

if (!$path) {
    http_response_code(404);
    exit;
}

serveStaticFile($path);

/**
 * Validate that the file exists and can be served
 *
 * @param string $path File location
 *
 * @return ?string Verified and resolved file location
 */
function validateStaticFile(string $path): ?string
{
    $path = trim($path, "/ \t\r\n");

    // Remove query params from the path (e.g. cache buster)
    $path = preg_replace('/[?&].*$/', '', $path);

    // Potential hack attempts, don't allow ".."
    if (str_contains($path, '..')) {
        return null;
    }

    $ext = pathinfo($path, \PATHINFO_EXTENSION);

    // Only supported file types
    if (empty($ext) || !isset(SUPPORTED_TYPES[strtolower($ext)])) {
        return null;
    }

    // Ignore some sensitive files
    if (preg_match('/(README.*|CHANGELOG.*|SECURITY.*|meta\.json|composer\..*)/', $path)) {
        return null;
    }

    $found = false;
    foreach (ALLOWED_PATHS as $prefix) {
        if (str_starts_with($path, $prefix) && !preg_match('~skins/.+/templates/~', $path)) {
            $found = true;
            break;
        }
    }

    if (!$found) {
        return null;
    }

    $path = realpath(INSTALL_PATH . $path);

    if ($path === false) {
        return null;
    }

    return $path;
}

/**
 * Serve a file.
 *
 * @param string $path File location
 */
function serveStaticFile($path): void
{
    $lastModifiedTime = filemtime($path);

    header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $lastModifiedTime));
    if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModifiedTime) {
        http_response_code(304); // "Not Modified"
        return;
    }

    $size = filesize($path);
    $fp = fopen($path, 'r');
    $range = [0, $size - 1];

    if (isset($_SERVER['HTTP_RANGE'])) {
        // $valid = preg_match('^bytes=\d*-\d*(,\d*-\d*)*$', $_SERVER['HTTP_RANGE']);
        if (!str_starts_with($_SERVER['HTTP_RANGE'], 'bytes=')) {
            http_response_code(416); // "Range Not Satisfiable"
            header('Content-Range: bytes */' . $size); // Required in 416.
            return;
        }

        $ranges = explode(',', substr($_SERVER['HTTP_RANGE'], 6));
        $range = explode('-', $ranges[0]); // TODO: only support the first range now.

        if ($range[0] === '') {
            $range[0] = 0;
        }
        if ($range[1] === '') {
            $range[1] = $size - 1;
        }

        if ($range[0] >= 0 && ($range[1] <= $size - 1) && $range[0] <= $range[1]) {
            http_response_code(206); // "Partial Content"
            header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
        } else {
            http_response_code(416); // "Range Not Satisfiable"
            header('Content-Range: bytes */' . $size);
            return;
        }
    }

    $contentLength = $range[1] - $range[0] + 1;
    $ext = pathinfo($path, \PATHINFO_EXTENSION);

    $headers = [
        'Accept-Ranges' => 'bytes',
        'Content-Length' => $contentLength,
        'Content-Type' => SUPPORTED_TYPES[strtolower($ext)],
        // 'Content-Disposition: attachment; filename="xxxxx"',
        'Cache-Control' => 'public, max-age=604800',
        'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 30 * 86400),
    ];

    foreach ($headers as $k => $v) {
        header("{$k}: {$v}", true);
    }

    if ($range[0] > 0) {
        fseek($fp, $range[0]);
    }

    $sentSize = 0;

    while (!feof($fp) && (connection_status() === \CONNECTION_NORMAL)) {
        $readingSize = $contentLength - $sentSize;
        $readingSize = min($readingSize, 512 * 1024);

        if ($readingSize <= 0) {
            break;
        }

        $data = fread($fp, $readingSize);
        if ($data === false) {
            break;
        }

        $sentSize += strlen($data);
        echo $data;
        flush();
    }

    fclose($fp);
}

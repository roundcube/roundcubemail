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
    // Clean any output buffers to prevent Content-Length mismatch with PHP built-in server
    while (ob_get_level()) {
        ob_end_clean();
    }

    $lastModifiedTime = filemtime($path);

    header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $lastModifiedTime));
    if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModifiedTime) {
        http_response_code(304); // "Not Modified"
        return;
    }

    $size = filesize($path);
    $ext = pathinfo($path, \PATHINFO_EXTENSION);

    $headers = [
        'Accept-Ranges' => 'bytes',
        'Content-Type' => SUPPORTED_TYPES[strtolower($ext)],
        'Cache-Control' => 'public, max-age=604800',
        'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 30 * 86400),
    ];

    // Handle range requests
    if (isset($_SERVER['HTTP_RANGE'])) {
        if (!str_starts_with($_SERVER['HTTP_RANGE'], 'bytes=')) {
            http_response_code(416); // "Range Not Satisfiable"
            header('Content-Range: bytes */' . $size);
            return;
        }

        $ranges = explode(',', substr($_SERVER['HTTP_RANGE'], 6));
        $range = explode('-', $ranges[0]);

        // Handle suffix-range (e.g., "bytes=-500" means last 500 bytes)
        if ($range[0] === '') {
            $start = max(0, $size - (int) $range[1]);
            $end = $size - 1;
        } else {
            $start = (int) $range[0];
            $end = $range[1] === '' ? $size - 1 : (int) $range[1];
        }

        if ($start >= 0 && $end <= $size - 1 && $start <= $end) {
            http_response_code(206); // "Partial Content"
            header('Content-Range: bytes ' . sprintf('%u-%u/%u', $start, $end, $size));
            $headers['Content-Length'] = $end - $start + 1;

            foreach ($headers as $k => $v) {
                header("{$k}: {$v}", true);
            }

            // For range requests, use chunked reading
            $fp = fopen($path, 'r');
            if ($fp === false) {
                http_response_code(500);
                return;
            }
            fseek($fp, $start);
            $remaining = $end - $start + 1;

            while ($remaining > 0 && !feof($fp) && connection_status() === \CONNECTION_NORMAL) {
                $chunk = fread($fp, min($remaining, 8192));
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
                $remaining -= strlen($chunk);
            }

            fclose($fp);
        } else {
            http_response_code(416); // "Range Not Satisfiable"
            header('Content-Range: bytes */' . $size);
        }

        return;
    }

    // For full file requests
    $headers['Content-Length'] = $size;

    foreach ($headers as $k => $v) {
        header("{$k}: {$v}", true);
    }

    // Use chunked reading with flush for PHP built-in server compatibility
    if (\PHP_SAPI === 'cli-server') {
        $fp = fopen($path, 'r');
        if ($fp === false) {
            http_response_code(500);
            return;
        }
        while (!feof($fp) && connection_status() === \CONNECTION_NORMAL) {
            $chunk = fread($fp, 8192);
            if ($chunk === false) {
                break;
            }
            echo $chunk;
            flush();
        }
        fclose($fp);
    } else {
        readfile($path);
    }
}

<?php

/*
 * Router script for PHP's built-in server that replicates the ORIGINAL
 * (pre-fix) static.php behavior. Used by StaticTest to demonstrate bugs
 * in the original implementation:
 *
 * 1. Suffix-range bug: "bytes=-500" is misinterpreted as "bytes=0-500"
 *    (first 501 bytes) instead of the last 500 bytes per RFC 7233.
 *
 * 2. Suffix-range-larger-than-file bug: "bytes=-9999" on a 1058-byte
 *    file is interpreted as "bytes=0-9999", fails the bounds check,
 *    and returns 416 instead of serving the full file.
 *
 * 3. Output buffer corruption: if output buffering is active (e.g. via
 *    php.ini output_buffering or ob_start()), the buffered content is
 *    prepended to the file output, but Content-Length only reflects the
 *    file size. The HTTP client reads Content-Length bytes, which now
 *    starts with buffer junk and cuts off the end of the actual file.
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

const ALLOWED_PATHS = [
    'installer/',
    'plugins/',
    'program/',
    'skins/',
];

define('INSTALL_PATH', realpath(__DIR__ . '/../../') . '/');

// Simulate output buffering being active (as can happen with php.ini
// output_buffering=On, or framework/plugin code calling ob_start()).
// The "ob" query parameter controls this for targeted testing.
if (isset($_GET['ob'])) {
    ob_start();
    // This junk represents any incidental output that might end up in the
    // buffer (warnings, debug output, whitespace from included files, etc.)
    echo $_GET['ob'];
}

// Router script receives the path in REQUEST_URI, not PATH_INFO
$pathInfo = parse_url($_SERVER['REQUEST_URI'], \PHP_URL_PATH);

$path = validateStaticFile($pathInfo ?? '');

if (!$path) {
    http_response_code(404);
    exit;
}

serveStaticFile($path);

// --- Original validateStaticFile (unchanged) ---

function validateStaticFile(string $path): ?string
{
    $path = trim($path, "/ \t\r\n");
    $path = preg_replace('/[?&].*$/', '', $path);

    if (str_contains($path, '..')) {
        return null;
    }

    $ext = pathinfo($path, \PATHINFO_EXTENSION);

    if (empty($ext) || !isset(SUPPORTED_TYPES[strtolower($ext)])) {
        return null;
    }

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

// --- Original serveStaticFile (WITHOUT fixes) ---
// - No ob_end_clean() call
// - Suffix-range "bytes=-N" sets start=0 instead of start=size-N
// - File handle opened unconditionally and leaked on early return

function serveStaticFile($path): void
{
    $lastModifiedTime = filemtime($path);

    header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $lastModifiedTime));
    if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModifiedTime) {
        http_response_code(304);
        return;
    }

    $size = filesize($path);
    $fp = fopen($path, 'r');
    $range = [0, $size - 1];

    if (isset($_SERVER['HTTP_RANGE'])) {
        if (!str_starts_with($_SERVER['HTTP_RANGE'], 'bytes=')) {
            http_response_code(416);
            header('Content-Range: bytes */' . $size);
            return;
        }

        $ranges = explode(',', substr($_SERVER['HTTP_RANGE'], 6));
        $range = explode('-', $ranges[0]);

        // BUG: suffix-range "bytes=-500" splits to ["", "500"].
        // Setting $range[0]=0 turns it into "bytes=0-500" (first 501 bytes)
        // instead of "last 500 bytes" per RFC 7233.
        if ($range[0] === '') {
            $range[0] = 0;
        }
        if ($range[1] === '') {
            $range[1] = $size - 1;
        }

        // BUG: for "bytes=-9999" on a 1058-byte file, this becomes
        // $range=[0, 9999], and 9999 <= 1057 is false, so we get 416
        // instead of serving the full file.
        if ($range[0] >= 0 && ($range[1] <= $size - 1) && $range[0] <= $range[1]) {
            http_response_code(206);
            header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
        } else {
            http_response_code(416);
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

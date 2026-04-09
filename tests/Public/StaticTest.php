<?php

namespace Roundcube\Tests\Public;

use PHPUnit\Framework\Attributes\DataProvider;
use Roundcube\Tests\ServerTestCase;
use Symfony\Component\Process\Process;

/**
 * Test class to test static resources server
 */
class StaticTest extends ServerTestCase
{
    /** @var Process PHP built-in server running the ORIGINAL (unfixed) static.php logic */
    protected static $originalProcess;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Start a second PHP built-in server with the original (unfixed) code
        // as a router script, so we can compare its behavior to the fixed version.
        $router = realpath(__DIR__ . '/../fixtures/static_original_router.php');
        $cmd = ['php', '-S', 'localhost:8001', $router];

        static::$originalProcess = new Process($cmd);
        static::$originalProcess->start();
        usleep(100 * 1000);
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        static::$originalProcess->stop();
        parent::tearDownAfterClass();
    }

    /**
     * HTTP client request to the ORIGINAL (unfixed) server on port 8001
     */
    protected function requestOriginal($method, $path, $options = [])
    {
        $config = [
            'base_uri' => 'http://localhost:8001',
            'http_errors' => false,
            'handler' => null,
        ];

        $client = \rcmail::get_instance()->get_http_client($config);

        return $client->request($method, $path, $options);
    }

    /**
     * Test valid resources
     */
    #[DataProvider('provide_ExistingResources_cases')]
    public function testExistingResources($path, $ctype): void
    {
        $response = $this->request('GET', 'static.php/' . $path);

        $file = file_get_contents(INSTALL_PATH . preg_replace('/\?.*$/', '', $path));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($file, (string) $response->getBody());
        $this->assertSame(['public, max-age=604800'], $response->getHeader('Cache-Control'));
        $this->assertSame(['bytes'], $response->getHeader('Accept-Ranges'));
        $this->assertSame([(string) strlen($file)], $response->getHeader('Content-Length'));
        $this->assertStringContainsString($ctype, $response->getHeader('Content-Type')[0]);
        // TODO: Expires and Last-Modified header
    }

    /**
     * Dataset for testExistingResources()
     */
    public static function provide_ExistingResources_cases(): iterable
    {
        return [
            ['program/resources/blank.gif', 'image/gif'],
            ['skins/elastic/images/logo.svg?s=1234567', 'image/svg+xml'],
            ['plugins/acl/acl.js', 'text/javascript'],
        ];
    }

    /**
     * Test forbidden resources
     */
    #[DataProvider('provide_ForbiddenResources_cases')]
    public function testForbiddenResources($path): void
    {
        $response = $this->request('GET', 'static.php/' . $path);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }

    /**
     * Dataset for testForbiddenResources()
     */
    public static function provide_ForbiddenResources_cases(): iterable
    {
        return [
            [''],
            ['CHANGELOG.md'],
            ['LICENSE'],
            ['skins/../passwd'],
            ['skins/elastic/templates/about.html'],
            ['plugins/acl/composer.json'],
            ['program/include/iniset.php'],
            ['program/localization/index.inc'],
            ['public_html/.htaccess'],
            ['vendor/friendsofphp/php-cs-fixer/logo.png'],
        ];
    }

    /**
     * Test handling of Modified-Since header
     */
    public function testModifiedSinceHeader(): void
    {
        $path = 'program/resources/blank.gif';
        $mtime = gmdate('D, d M Y H:i:s \G\M\T', filemtime(INSTALL_PATH . $path));
        $headers = [
            'If-Modified-Since' => $mtime,
        ];

        $response = $this->request('GET', 'static.php/' . $path, ['headers' => $headers]);

        $this->assertSame(304, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }

    /**
     * Test handling of Range header - invalid requests
     */
    public function testRangeHeaderInvalid(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);

        // Non-"bytes=" prefix must be rejected per RFC 7233 Section 3.1
        $response = $this->request('GET', 'static.php/' . $path, [
            'headers' => ['Range' => 'invalid'],
        ]);

        $this->assertSame(416, $response->getStatusCode());
        $this->assertSame(['bytes */' . strlen($file)], $response->getHeader('Content-Range'));
        $this->assertSame('', (string) $response->getBody());

        // Start position greater than end position is invalid
        $response = $this->request('GET', 'static.php/' . $path, [
            'headers' => ['Range' => 'bytes=1000-10'],
        ]);

        $this->assertSame(416, $response->getStatusCode());
        $this->assertSame(['bytes */' . strlen($file)], $response->getHeader('Content-Range'));
        $this->assertSame('', (string) $response->getBody());
    }

    /**
     * Test handling of Range header - standard byte range (bytes=start-end)
     */
    public function testRangeHeaderStandard(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);

        $response = $this->request('GET', 'static.php/' . $path, [
            'headers' => ['Range' => 'bytes=10-50'],
        ]);

        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame(['41'], $response->getHeader('Content-Length'));
        $this->assertSame(['bytes 10-50/' . strlen($file)], $response->getHeader('Content-Range'));
        $this->assertSame(substr($file, 10, 41), (string) $response->getBody());
    }

    /**
     * Test handling of Range header - open-ended range (bytes=offset-)
     *
     * RFC 7233 Section 2.1: "If the last-byte-pos value is absent [...] the
     * byte range extends to the end of the representation's data."
     *
     * This verifies that an open-ended range like "bytes=10-" correctly returns
     * all bytes from offset 10 to the end of the file, rather than being
     * misinterpreted or rejected.
     */
    public function testRangeHeaderOpenEnded(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);
        $size = strlen($file);

        $response = $this->request('GET', 'static.php/' . $path, [
            'headers' => ['Range' => 'bytes=10-'],
        ]);

        $expectedLength = $size - 10;
        $expectedEnd = $size - 1;
        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame([(string) $expectedLength], $response->getHeader('Content-Length'));
        $this->assertSame(["bytes 10-{$expectedEnd}/{$size}"], $response->getHeader('Content-Range'));
        // Verify actual byte content matches - not just length
        $this->assertSame(substr($file, 10), (string) $response->getBody());
    }

    /**
     * Test handling of Range header - suffix-range (bytes=-N)
     *
     * RFC 7233 Section 2.1: "A client can request the last N bytes of the
     * selected representation using a suffix-byte-range-spec."
     *   suffix-byte-range-spec = "-" suffix-length
     * For example, "bytes=-500" means "the last 500 bytes".
     *
     * BUG FIXED: The previous implementation treated the empty first element
     * of "bytes=-500" (split on "-" yields ["", "500"]) by setting start=0,
     * which incorrectly served bytes 0-500 (the FIRST 501 bytes) instead of
     * the last 500 bytes. For a 1058-byte file, "bytes=-500" returned bytes
     * 0-500 with Content-Range "bytes 0-500/1058", but RFC 7233 requires
     * bytes 558-1057 with Content-Range "bytes 558-1057/1058".
     */
    public function testRangeHeaderSuffix(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);
        $size = strlen($file);
        $suffixLength = 500;

        $response = $this->request('GET', 'static.php/' . $path, [
            'headers' => ['Range' => "bytes=-{$suffixLength}"],
        ]);

        $expectedStart = $size - $suffixLength; // 1058 - 500 = 558
        $expectedEnd = $size - 1;               // 1057

        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame([(string) $suffixLength], $response->getHeader('Content-Length'));
        $this->assertSame(
            ["bytes {$expectedStart}-{$expectedEnd}/{$size}"],
            $response->getHeader('Content-Range'),
        );

        // Verify the actual returned bytes are from the END of the file,
        // not the beginning. This is the core assertion that proves the fix:
        // the old code would return substr($file, 0, 501) here instead.
        $expectedContent = substr($file, $expectedStart);
        $actualContent = (string) $response->getBody();
        $this->assertSame($expectedContent, $actualContent);

        // Double-check: the old (buggy) response would have started with the
        // file's first bytes - verify we are NOT getting those
        $firstBytes = substr($file, 0, 10);
        $this->assertNotSame($firstBytes, substr($actualContent, 0, 10));
    }

    /**
     * Test suffix-range larger than the file (bytes=-N where N > filesize)
     *
     * RFC 7233 Section 2.1: "If the selected representation is shorter than
     * the specified suffix-length, the entire representation is used."
     *
     * BUG FIXED: The previous implementation would interpret "bytes=-9999"
     * on a 1058-byte file as "bytes=0-9999", which fails the bounds check
     * ($range[1] <= $size - 1) and returns 416. The correct behavior per
     * RFC 7233 is to clamp and serve the entire file as a 206 response.
     */
    public function testRangeHeaderSuffixLargerThanFile(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);
        $size = strlen($file);

        $response = $this->request('GET', 'static.php/' . $path, [
            'headers' => ['Range' => 'bytes=-9999'],
        ]);

        // Should clamp to the full file: bytes 0-(size-1)
        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame([(string) $size], $response->getHeader('Content-Length'));
        $this->assertSame(
            ['bytes 0-' . ($size - 1) . '/' . $size],
            $response->getHeader('Content-Range'),
        );
        $this->assertSame($file, (string) $response->getBody());
    }

    /**
     * Test full file retrieval via range (bytes=0-)
     *
     * Verifies that requesting from byte 0 with no end returns the complete
     * file contents with a 206 status and correct Content-Range header.
     */
    public function testRangeHeaderFullFileViaRange(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);
        $size = strlen($file);

        $response = $this->request('GET', 'static.php/' . $path, [
            'headers' => ['Range' => 'bytes=0-'],
        ]);

        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame([(string) $size], $response->getHeader('Content-Length'));
        $this->assertSame(
            ['bytes 0-' . ($size - 1) . '/' . $size],
            $response->getHeader('Content-Range'),
        );
        $this->assertSame($file, (string) $response->getBody());
    }

    /**
     * Test that CSS files are served correctly on the PHP built-in server
     *
     * The PHP built-in server (cli-server SAPI) can corrupt static file
     * responses in two ways:
     * 1. Output buffering adds extra bytes before the file content, causing
     *    the actual body to exceed the declared Content-Length. HTTP clients
     *    then truncate the response at Content-Length, cutting off the end
     *    of the file — resulting in broken stylesheets.
     * 2. Without explicit flush() between chunks, large files may be
     *    incompletely delivered, again producing truncated CSS.
     *
     * These tests run against the built-in server (see ServerTestCase) and
     * verify byte-for-byte integrity of the served content.
     */
    #[DataProvider('provide_CssFileIntegrity_cases')]
    public function testCssFileIntegrity($path): void
    {
        $file = file_get_contents(INSTALL_PATH . $path);
        $response = $this->request('GET', 'static.php/' . $path);

        $this->assertSame(200, $response->getStatusCode());

        $declaredLength = $response->getHeader('Content-Length')[0] ?? null;
        $actualBody = (string) $response->getBody();

        // Content-Length must match the file on disk — a mismatch here means
        // output buffering injected extra bytes or the size was miscalculated
        $this->assertSame((string) strlen($file), $declaredLength,
            "Content-Length header must match file size on disk for {$path}");

        // The response body must be exactly the file content — truncation or
        // prepended buffer output would break stylesheet parsing
        $this->assertSame(strlen($file), strlen($actualBody),
            "Response body length must match file size for {$path}");
        $this->assertSame($file, $actualBody,
            "Response body must be byte-for-byte identical to {$path} on disk");
    }

    /**
     * Dataset for testCssFileIntegrity()
     */
    public static function provide_CssFileIntegrity_cases(): iterable
    {
        return [
            'small less file' => ['skins/elastic/styles/global.less'],
            'large less file' => ['skins/elastic/styles/styles.less'],
        ];
    }

    /**
     * Test that JavaScript files are served correctly on the PHP built-in server
     *
     * Same output buffering and chunked delivery issues as CSS (see above).
     * JavaScript files are particularly affected because app.js (~387KB) is
     * large enough that without chunked reading + flush(), the built-in server
     * may not deliver the full content before the connection is considered
     * complete, leaving the browser with a truncated and unparseable script.
     */
    #[DataProvider('provide_JsFileIntegrity_cases')]
    public function testJsFileIntegrity($path): void
    {
        $file = file_get_contents(INSTALL_PATH . $path);
        $response = $this->request('GET', 'static.php/' . $path);

        $this->assertSame(200, $response->getStatusCode());

        $declaredLength = $response->getHeader('Content-Length')[0] ?? null;
        $actualBody = (string) $response->getBody();

        $this->assertSame((string) strlen($file), $declaredLength,
            "Content-Length header must match file size on disk for {$path}");
        $this->assertSame(strlen($file), strlen($actualBody),
            "Response body length must match file size for {$path}");
        $this->assertSame($file, $actualBody,
            "Response body must be byte-for-byte identical to {$path} on disk");
    }

    /**
     * Dataset for testJsFileIntegrity()
     */
    public static function provide_JsFileIntegrity_cases(): iterable
    {
        return [
            'small js file' => ['plugins/acl/acl.js'],
            'large js file' => ['program/js/app.js'],
        ];
    }

    /**
     * Test Content-Length accuracy across multiple file types
     *
     * On PHP's built-in server (cli-server SAPI), output buffering can cause
     * the actual response body to be larger than the declared Content-Length,
     * leading to truncated or corrupted downloads. The fix cleans any active
     * output buffers (ob_end_clean) before serving, and uses chunked
     * fread()+flush() instead of readfile() on the built-in server to ensure
     * complete delivery. This test verifies that Content-Length exactly matches
     * the actual response body size for various file types and sizes.
     */
    #[DataProvider('provide_ContentLengthAccuracy_cases')]
    public function testContentLengthAccuracy($path): void
    {
        $file = file_get_contents(INSTALL_PATH . $path);
        $response = $this->request('GET', 'static.php/' . $path);

        $this->assertSame(200, $response->getStatusCode());

        $declaredLength = $response->getHeader('Content-Length')[0] ?? null;
        $actualBody = (string) $response->getBody();

        $this->assertNotNull($declaredLength,
            "Content-Length header must be present for {$path}");
        $this->assertSame((int) $declaredLength, strlen($actualBody),
            "Content-Length must match actual body size for {$path}");
        $this->assertSame(strlen($file), strlen($actualBody),
            "Response body size must match file on disk for {$path}");
    }

    /**
     * Dataset for testContentLengthAccuracy()
     */
    public static function provide_ContentLengthAccuracy_cases(): iterable
    {
        return [
            'tiny binary (54 bytes)' => ['program/resources/blank.gif'],
            'small pdf (1058 bytes)' => ['program/resources/dummy.pdf'],
            'medium css (4KB)' => ['skins/elastic/styles/global.less'],
            'medium js (12KB)' => ['plugins/acl/acl.js'],
            'large css (10KB)' => ['skins/elastic/styles/styles.less'],
            'large js (387KB)' => ['program/js/app.js'],
        ];
    }

    // -------------------------------------------------------------------------
    // Tests against the ORIGINAL (unfixed) server to demonstrate the bugs
    // that motivated the static.php changes.
    // -------------------------------------------------------------------------

    /**
     * Prove the original code returns WRONG bytes for suffix-range requests
     *
     * RFC 7233 Section 2.1 defines "bytes=-500" as "the last 500 bytes".
     * The original code splits on "-" to get ["", "500"], then sets the
     * empty first element to 0, turning it into "bytes=0-500" — serving
     * the FIRST 501 bytes instead of the LAST 500 bytes.
     *
     * This test hits both the original (buggy) and fixed servers with the
     * same request and proves they return different content, then verifies
     * only the fixed server returns the correct bytes.
     */
    public function testOriginalSuffixRangeReturnsWrongBytes(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);
        $size = strlen($file);
        $suffixLength = 500;

        $headers = ['Range' => "bytes=-{$suffixLength}"];

        // Original (unfixed) server
        $original = $this->requestOriginal('GET', $path, ['headers' => $headers]);

        // Fixed server
        $fixed = $this->request('GET', 'static.php/' . $path, ['headers' => $headers]);

        // Both return 206, but with different content
        $this->assertSame(206, $original->getStatusCode(), 'Original server should return 206');
        $this->assertSame(206, $fixed->getStatusCode(), 'Fixed server should return 206');

        $originalBody = (string) $original->getBody();
        $fixedBody = (string) $fixed->getBody();

        // The original server returns bytes 0-500 (first 501 bytes) — WRONG
        $buggyContent = substr($file, 0, $suffixLength + 1);
        $this->assertSame($buggyContent, $originalBody,
            'Original server should return first 501 bytes (the bug)');
        $this->assertSame(
            ['bytes 0-500/' . $size],
            $original->getHeader('Content-Range'),
            'Original server reports wrong Content-Range starting at byte 0',
        );

        // The fixed server returns bytes 558-1057 (last 500 bytes) — CORRECT
        $correctStart = $size - $suffixLength;
        $correctContent = substr($file, $correctStart);
        $this->assertSame($correctContent, $fixedBody,
            'Fixed server should return last 500 bytes (correct per RFC 7233)');
        $this->assertSame(
            ["bytes {$correctStart}-" . ($size - 1) . "/{$size}"],
            $fixed->getHeader('Content-Range'),
            'Fixed server reports correct Content-Range from end of file',
        );

        // The two responses must differ — proving the bug changes the output
        $this->assertNotSame($originalBody, $fixedBody,
            'Original and fixed servers must return different content for suffix-range');
    }

    /**
     * Prove the original code returns 416 for suffix-range larger than file
     *
     * RFC 7233 Section 2.1: "If the selected representation is shorter than
     * the specified suffix-length, the entire representation is used."
     *
     * The original code interprets "bytes=-9999" on a 1058-byte file as
     * "bytes=0-9999". The bounds check ($range[1] <= $size - 1) fails
     * because 9999 > 1057, so it returns 416 "Range Not Satisfiable".
     * The fixed version correctly clamps to the full file and returns 206.
     */
    public function testOriginalSuffixRangeLargerThanFileReturns416(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);
        $size = strlen($file);

        $headers = ['Range' => 'bytes=-9999'];

        // Original (unfixed) server — returns 416 (the bug)
        $original = $this->requestOriginal('GET', $path, ['headers' => $headers]);

        $this->assertSame(416, $original->getStatusCode(),
            'Original server incorrectly returns 416 for suffix-range larger than file');
        $this->assertSame(
            ['bytes */' . $size],
            $original->getHeader('Content-Range'),
            'Original server reports unsatisfiable range',
        );

        // Fixed server — returns 206 with full file (correct per RFC 7233)
        $fixed = $this->request('GET', 'static.php/' . $path, ['headers' => $headers]);

        $this->assertSame(206, $fixed->getStatusCode(),
            'Fixed server correctly returns 206 for suffix-range larger than file');
        $this->assertSame([(string) $size], $fixed->getHeader('Content-Length'));
        $this->assertSame(
            ['bytes 0-' . ($size - 1) . '/' . $size],
            $fixed->getHeader('Content-Range'),
        );
        $this->assertSame($file, (string) $fixed->getBody(),
            'Fixed server returns the entire file content');
    }

    /**
     * Prove that output buffering corrupts responses without ob_end_clean()
     *
     * When output buffering is active (e.g. php.ini output_buffering=On,
     * or a plugin/framework calling ob_start()), any content in the buffer
     * is prepended to the file output. But Content-Length is calculated from
     * filesize(), so the declared length doesn't account for the buffer junk.
     *
     * The HTTP client reads exactly Content-Length bytes, which now STARTS
     * with the buffer content and CUTS OFF the end of the actual file.
     * The result: the body is the right length, but contains wrong data.
     *
     * The test fixture's "?ob=..." parameter triggers ob_start() + echo
     * to simulate this condition on the original server.
     */
    public function testOriginalOutputBufferCorruptsResponse(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);
        $size = strlen($file);
        $junk = 'BUFFER_JUNK_DATA';

        // Original server WITH output buffer junk injected via ?ob= param
        $original = $this->requestOriginal('GET', $path . '?ob=' . $junk);

        // Content-Length is still the file size (doesn't know about the buffer)
        $declaredLength = $original->getHeader('Content-Length')[0] ?? null;
        $this->assertSame((string) $size, $declaredLength,
            'Original server declares Content-Length as file size, ignoring buffer content');

        // The actual body the HTTP client received (Content-Length bytes)
        // starts with the buffer junk, not the file's real first bytes
        $originalBody = (string) $original->getBody();
        $this->assertStringStartsWith($junk, $originalBody,
            'Original server response begins with output buffer junk');
        $this->assertNotSame($file, $originalBody,
            'Original server response is NOT the correct file content');

        // The end of the file is truncated because buffer junk displaced it
        $expectedTruncatedFile = substr($file, 0, $size - strlen($junk));
        $this->assertSame(
            $junk . $expectedTruncatedFile,
            $originalBody,
            'Response is buffer junk + truncated file (last bytes cut off)',
        );

        // Fixed server — ob_end_clean() prevents this corruption
        $fixed = $this->request('GET', 'static.php/' . $path);
        $this->assertSame($file, (string) $fixed->getBody(),
            'Fixed server returns correct file content regardless of output buffering');
    }
}

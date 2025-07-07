<?php

namespace Roundcube\Tests\Public;

use PHPUnit\Framework\Attributes\DataProvider;
use Roundcube\Tests\ServerTestCase;

/**
 * Test class to test static resources server
 */
class StaticTest extends ServerTestCase
{
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
     * Test handling of Range header
     */
    public function testRangeHeader(): void
    {
        $path = 'program/resources/dummy.pdf';
        $file = file_get_contents(INSTALL_PATH . $path);

        // Invalid header
        $headers = [
            'Range' => 'invalid',
        ];

        $response = $this->request('GET', 'static.php/' . $path, ['headers' => $headers]);

        $this->assertSame(416, $response->getStatusCode());
        $this->assertSame(['bytes */' . strlen($file)], $response->getHeader('Content-Range'));
        $this->assertSame('', (string) $response->getBody());

        // Invalid header
        $headers = [
            'Range' => 'bytes=1000-10',
        ];

        $response = $this->request('GET', 'static.php/' . $path, ['headers' => $headers]);

        $this->assertSame(416, $response->getStatusCode());
        $this->assertSame(['bytes */' . strlen($file)], $response->getHeader('Content-Range'));
        $this->assertSame('', (string) $response->getBody());

        // Valid request
        $headers = [
            'Range' => 'bytes=10-50',
        ];

        $response = $this->request('GET', 'static.php/' . $path, ['headers' => $headers]);

        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame(['41'], $response->getHeader('Content-Length'));
        $this->assertSame(['bytes 10-50/' . strlen($file)], $response->getHeader('Content-Range'));
        $this->assertSame(substr($file, 10, 41), (string) $response->getBody());
    }
}

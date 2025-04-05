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
     * Dataset for testExistingResources()
     */
    public static function provideExistingResources(): iterable
    {
        return [
            ['program/resources/blank.gif', 'image/gif'],
            ['skins/elastic/images/logo.svg?s=1234567', 'image/svg+xml'],
            ['plugins/acl/acl.js', 'text/javascript'],
        ];
    }

    /**
     * Test valid resources
     */
    #[DataProvider('provideExistingResources')]
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
        // TODO: Expires header
    }

    /**
     * Dataset for testForbiddenResources()
     */
    public static function provideForbiddenResources(): iterable
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
     * Test forbidden resources
     */
    #[DataProvider('provideForbiddenResources')]
    public function testForbiddenResources($path): void
    {
        $response = $this->request('GET', 'static.php/' . $path);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }

    /**
     * Test handling of Modified-Since header
     */
    public function testModifiedSinceHeader(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * Test handling of Range header
     */
    public function testRangeHeader(): void
    {
        $this->markTestIncomplete();
    }
}

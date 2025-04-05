<?php

namespace Roundcube\Tests\Public;

use Roundcube\Tests\ServerTestCase;

/**
 * Test class to test installer.php
 */
class InstallerTest extends ServerTestCase
{
    /**
     * Test installer.php
     */
    public function testInstaller(): void
    {
        $response = $this->request('GET', 'installer.php/');

        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(str_starts_with($body, '<!DOCTYPE html>'));
    }
}

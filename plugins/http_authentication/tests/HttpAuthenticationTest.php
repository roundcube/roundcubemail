<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class HttpAuthenticationTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \http_authentication($rcube->plugins);

        $this->assertInstanceOf('http_authentication', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

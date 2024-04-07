<?php

use PHPUnit\Framework\TestCase;

class HttpAuthentication_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new http_authentication($rcube->plugins);

        self::assertInstanceOf('http_authentication', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

<?php

class HttpAuthentication_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../http_authentication.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new http_authentication($rcube->plugins);

        $this->assertInstanceOf('http_authentication', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


<?php

class Zipdownload_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../zipdownload.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new zipdownload($rcube->plugins);

        $this->assertInstanceOf('zipdownload', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

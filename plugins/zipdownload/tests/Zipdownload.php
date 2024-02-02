<?php

use PHPUnit\Framework\TestCase;

class Zipdownload_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../zipdownload.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new zipdownload($rcube->plugins);

        $this->assertInstanceOf('zipdownload', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

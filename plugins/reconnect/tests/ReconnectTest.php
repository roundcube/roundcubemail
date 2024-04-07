<?php

use PHPUnit\Framework\TestCase;

class Reconnect_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../reconnect.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new reconnect($rcube->plugins);

        $this->assertInstanceOf('reconnect', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

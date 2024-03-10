<?php

use PHPUnit\Framework\TestCase;

class Autologon_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../autologon.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new autologon($rcube->plugins);

        self::assertInstanceOf('autologon', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);

        // TODO
        $plugin->startup([]);
        $plugin->authenticate([]);
    }
}

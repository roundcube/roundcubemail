<?php

use PHPUnit\Framework\TestCase;

class DebugLogger_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new debug_logger($rcube->plugins);

        self::assertInstanceOf('debug_logger', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

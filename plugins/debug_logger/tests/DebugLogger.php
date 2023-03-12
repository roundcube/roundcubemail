<?php

class DebugLogger_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../debug_logger.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new debug_logger($rcube->plugins);

        $this->assertInstanceOf('debug_logger', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


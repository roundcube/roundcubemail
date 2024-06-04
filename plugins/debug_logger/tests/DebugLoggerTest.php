<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class DebugLoggerTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \debug_logger($rcube->plugins);

        $this->assertInstanceOf('debug_logger', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

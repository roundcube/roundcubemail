<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class AutologoutTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \autologout($rcube->plugins);

        $this->assertInstanceOf('autologout', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        // TODO
        $plugin->startup([]);
    }
}

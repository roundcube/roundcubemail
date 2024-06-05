<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class VirtuserFileTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \virtuser_file($rcube->plugins);

        $this->assertInstanceOf('virtuser_file', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

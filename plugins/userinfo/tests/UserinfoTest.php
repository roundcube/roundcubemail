<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class UserinfoTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \userinfo($rcube->plugins);

        $this->assertInstanceOf('userinfo', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

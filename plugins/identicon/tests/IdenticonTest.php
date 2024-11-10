<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class IdenticonTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \identicon($rcube->plugins);

        $this->assertInstanceOf('identicon', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

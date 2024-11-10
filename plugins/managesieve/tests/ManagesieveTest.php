<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class ManagesieveTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \managesieve($rcube->plugins);

        $this->assertInstanceOf('managesieve', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

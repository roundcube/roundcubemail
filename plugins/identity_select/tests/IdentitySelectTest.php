<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class IdentitySelectTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \identity_select($rcube->plugins);

        $this->assertInstanceOf('identity_select', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

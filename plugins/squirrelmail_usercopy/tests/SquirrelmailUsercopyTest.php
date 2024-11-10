<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class SquirrelmailUsercopyTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \squirrelmail_usercopy($rcube->plugins);

        $this->assertInstanceOf('squirrelmail_usercopy', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

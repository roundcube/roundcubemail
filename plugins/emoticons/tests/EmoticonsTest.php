<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EmoticonsTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \emoticons($rcube->plugins);

        $this->assertInstanceOf('emoticons', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

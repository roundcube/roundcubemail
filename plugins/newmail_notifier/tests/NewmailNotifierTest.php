<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class NewmailNotifierTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \newmail_notifier($rcube->plugins);

        $this->assertInstanceOf('newmail_notifier', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

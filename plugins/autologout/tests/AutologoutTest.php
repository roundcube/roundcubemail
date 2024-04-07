<?php

use PHPUnit\Framework\TestCase;

class Autologout_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new autologout($rcube->plugins);

        self::assertInstanceOf('autologout', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);

        // TODO
        $plugin->startup([]);
    }
}

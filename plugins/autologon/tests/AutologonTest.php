<?php

use PHPUnit\Framework\TestCase;

class Autologon_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new autologon($rcube->plugins);

        self::assertInstanceOf('autologon', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);

        // TODO
        $plugin->startup([]);
        $plugin->authenticate([]);
    }
}

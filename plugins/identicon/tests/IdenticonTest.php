<?php

use PHPUnit\Framework\TestCase;

class Identicon_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new identicon($rcube->plugins);

        self::assertInstanceOf('identicon', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

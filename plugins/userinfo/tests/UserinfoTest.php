<?php

use PHPUnit\Framework\TestCase;

class Userinfo_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new userinfo($rcube->plugins);

        self::assertInstanceOf('userinfo', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

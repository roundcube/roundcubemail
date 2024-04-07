<?php

use PHPUnit\Framework\TestCase;

class Emoticons_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new emoticons($rcube->plugins);

        self::assertInstanceOf('emoticons', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

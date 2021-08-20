<?php

class Emoticons_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../emoticons.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new emoticons($rcube->plugins);

        $this->assertInstanceOf('emoticons', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

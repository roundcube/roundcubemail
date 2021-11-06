<?php

class Autologout_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../autologout.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new autologout($rcube->plugins);

        $this->assertInstanceOf('autologout', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        // TODO
        $plugin->startup([]);
    }
}


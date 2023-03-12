<?php

class Userinfo_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../userinfo.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new userinfo($rcube->plugins);

        $this->assertInstanceOf('userinfo', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


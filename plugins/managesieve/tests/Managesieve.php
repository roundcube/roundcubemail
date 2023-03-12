<?php

class Managesieve_Plugin extends PHPUnit\Framework\TestCase
{

    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../managesieve.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new managesieve($rcube->plugins);

        $this->assertInstanceOf('managesieve', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

<?php

class Enigma_Plugin extends PHPUnit\Framework\TestCase
{

    function setUp(): void
    {
        include_once __DIR__ . '/../enigma.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new enigma($rcube->plugins);

        $this->assertInstanceOf('enigma', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


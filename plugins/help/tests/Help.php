<?php

class Help_Plugin extends PHPUnit\Framework\TestCase
{

    function setUp()
    {
        include_once __DIR__ . '/../help.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new help($rcube->plugins);

        $this->assertInstanceOf('help', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


<?php

class Archive_Plugin extends PHPUnit\Framework\TestCase
{

    function setUp()
    {
        include_once __DIR__ . '/../archive.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new archive($rcube->plugins);

        $this->assertInstanceOf('archive', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


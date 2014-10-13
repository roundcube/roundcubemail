<?php

class Password_Plugin extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        include_once __DIR__ . '/../password.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new password($rcube->api);

        $this->assertInstanceOf('password', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


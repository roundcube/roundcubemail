<?php

class Userinfo_Plugin extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        include_once __DIR__ . '/../account_details.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new account_details($rcube->api);

        $this->assertInstanceOf('account_details', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


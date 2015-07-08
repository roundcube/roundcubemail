<?php

class Legacy_Browser_Plugin extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        include_once __DIR__ . '/../legacy_browser.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new legacy_browser($rcube->api);

        $this->assertInstanceOf('legacy_browser', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


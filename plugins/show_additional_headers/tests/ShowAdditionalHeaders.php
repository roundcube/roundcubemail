<?php

class ShowAdditionalHeaders_Plugin extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        include_once dirname(__FILE__) . '/../show_additional_headers.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new show_additional_headers($rcube->api);

        $this->assertInstanceOf('show_additional_headers', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


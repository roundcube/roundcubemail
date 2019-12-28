<?php

class AdditionalMessageHeaders_Plugin extends PHPUnit\Framework\TestCase
{

    function setUp()
    {
        include_once __DIR__ . '/../additional_message_headers.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new additional_message_headers($rcube->plugins);

        $this->assertInstanceOf('additional_message_headers', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


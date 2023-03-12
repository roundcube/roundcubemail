<?php

class ShowAdditionalHeaders_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../show_additional_headers.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new show_additional_headers($rcube->plugins);

        $this->assertInstanceOf('show_additional_headers', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

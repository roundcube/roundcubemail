<?php

use PHPUnit\Framework\TestCase;

class ShowAdditionalHeaders_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new show_additional_headers($rcube->plugins);

        $this->assertInstanceOf('show_additional_headers', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

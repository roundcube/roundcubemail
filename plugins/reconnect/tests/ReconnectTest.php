<?php

use PHPUnit\Framework\TestCase;

class Reconnect_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new reconnect($rcube->plugins);

        $this->assertInstanceOf('reconnect', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

<?php

use PHPUnit\Framework\TestCase;

class VirtuserQuery_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new virtuser_query($rcube->plugins);

        $this->assertInstanceOf('virtuser_query', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

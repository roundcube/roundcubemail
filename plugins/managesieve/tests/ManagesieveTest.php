<?php

use PHPUnit\Framework\TestCase;

class Managesieve_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \managesieve($rcube->plugins);

        $this->assertInstanceOf('managesieve', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

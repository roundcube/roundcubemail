<?php

use PHPUnit\Framework\TestCase;

class Jqueryui_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \jqueryui($rcube->plugins);

        $this->assertInstanceOf('jqueryui', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

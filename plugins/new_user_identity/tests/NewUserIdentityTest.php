<?php

use PHPUnit\Framework\TestCase;

class NewUserIdentity_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \new_user_identity($rcube->plugins);

        $this->assertInstanceOf('new_user_identity', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

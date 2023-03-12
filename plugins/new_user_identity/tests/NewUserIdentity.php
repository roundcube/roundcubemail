<?php

class NewUserIdentity_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../new_user_identity.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new new_user_identity($rcube->plugins);

        $this->assertInstanceOf('new_user_identity', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


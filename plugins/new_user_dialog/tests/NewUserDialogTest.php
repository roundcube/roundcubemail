<?php

use PHPUnit\Framework\TestCase;

class NewUserDialog_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new new_user_dialog($rcube->plugins);

        self::assertInstanceOf('new_user_dialog', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

<?php

class SquirrelmailUsercopy_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../squirrelmail_usercopy.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new squirrelmail_usercopy($rcube->plugins);

        $this->assertInstanceOf('squirrelmail_usercopy', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

<?php

use PHPUnit\Framework\TestCase;

class SquirrelmailUsercopy_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../squirrelmail_usercopy.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new squirrelmail_usercopy($rcube->plugins);

        self::assertInstanceOf('squirrelmail_usercopy', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

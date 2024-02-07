<?php

use PHPUnit\Framework\TestCase;

class Emoticons_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../emoticons.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new emoticons($rcube->plugins);

        self::assertInstanceOf('emoticons', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

<?php

use PHPUnit\Framework\TestCase;

class VirtuserFile_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../virtuser_file.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new virtuser_file($rcube->plugins);

        self::assertInstanceOf('virtuser_file', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

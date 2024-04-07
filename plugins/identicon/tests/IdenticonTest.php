<?php

use PHPUnit\Framework\TestCase;

class Identicon_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../identicon.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new identicon($rcube->plugins);

        $this->assertInstanceOf('identicon', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

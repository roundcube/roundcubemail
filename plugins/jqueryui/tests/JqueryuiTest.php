<?php

use PHPUnit\Framework\TestCase;

class Jqueryui_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../jqueryui.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new jqueryui($rcube->plugins);

        $this->assertInstanceOf('jqueryui', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

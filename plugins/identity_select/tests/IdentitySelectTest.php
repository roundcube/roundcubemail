<?php

use PHPUnit\Framework\TestCase;

class IdentitySelect_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../identity_select.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new identity_select($rcube->plugins);

        $this->assertInstanceOf('identity_select', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

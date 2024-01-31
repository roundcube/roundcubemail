<?php

use PHPUnit\Framework\TestCase;

class Enigma_Plugin extends TestCase
{
    protected function setUp(): void
    {
        include_once __DIR__ . '/../enigma.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new enigma($rcube->plugins);

        $this->assertInstanceOf('enigma', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

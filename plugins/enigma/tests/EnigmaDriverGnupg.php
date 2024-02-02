<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaDriverGnupg extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../lib/enigma_driver.php';
        include_once __DIR__ . '/../lib/enigma_driver_gnupg.php';
    }

    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new enigma_driver_gnupg($rcube->user);

        $this->assertInstanceOf('enigma_driver', $plugin);
    }
}

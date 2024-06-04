<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EnigmaDriverGnupgTest extends TestCase
{
    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \enigma_driver_gnupg($rcube->user);

        $this->assertInstanceOf('enigma_driver', $plugin);
    }
}

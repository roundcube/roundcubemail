<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaSubkey extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../lib/enigma_subkey.php';
    }

    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new enigma_subkey();

        $this->assertInstanceOf('enigma_subkey', $error);
    }
}

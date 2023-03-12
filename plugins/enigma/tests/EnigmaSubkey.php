<?php

class Enigma_EnigmaSubkey extends PHPUnit\Framework\TestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../lib/enigma_subkey.php';
    }

    /**
     * Test constructor
     */
    function test_constructor()
    {
        $error = new enigma_subkey();

        $this->assertInstanceOf('enigma_subkey', $error);
    }
}


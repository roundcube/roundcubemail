<?php

class Enigma_EnigmaSignature extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../lib/enigma_signature.php';
    }

    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new enigma_signature();

        $this->assertInstanceOf('enigma_signature', $error);
    }
}

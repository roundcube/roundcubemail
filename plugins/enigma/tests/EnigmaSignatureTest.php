<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaSignature extends TestCase
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

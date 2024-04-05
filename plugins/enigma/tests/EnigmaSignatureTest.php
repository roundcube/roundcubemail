<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaSignature extends TestCase
{
    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new enigma_signature();

        $this->assertInstanceOf('enigma_signature', $error);
    }
}

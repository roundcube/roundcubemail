<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaSubkey extends TestCase
{
    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new enigma_subkey();

        $this->assertInstanceOf('enigma_subkey', $error);
    }
}

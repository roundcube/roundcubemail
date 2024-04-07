<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaUserid extends TestCase
{
    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new enigma_userid();

        self::assertInstanceOf('enigma_userid', $error);
    }
}

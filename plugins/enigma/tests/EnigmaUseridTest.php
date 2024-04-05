<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaUserid extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../lib/enigma_userid.php';
    }

    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new enigma_userid();

        $this->assertInstanceOf('enigma_userid', $error);
    }
}

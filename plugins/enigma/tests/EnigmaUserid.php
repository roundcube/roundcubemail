<?php

class Enigma_EnigmaUserid extends PHPUnit\Framework\TestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../lib/enigma_userid.php';
    }

    /**
     * Test constructor
     */
    function test_constructor()
    {
        $error = new enigma_userid();

        $this->assertInstanceOf('enigma_userid', $error);
    }
}


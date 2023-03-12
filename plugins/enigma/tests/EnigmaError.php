<?php

class Enigma_EnigmaError extends PHPUnit\Framework\TestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../lib/enigma_error.php';
    }

    /**
     * Test constructor
     */
    function test_constructor()
    {
        $error = new enigma_error(enigma_error::EXPIRED, 'message', ['test1' => 'test2']);

        $this->assertInstanceOf('enigma_error', $error);
        $this->assertSame(enigma_error::EXPIRED, $error->getCode());
        $this->assertSame('message', $error->getMessage());
        $this->assertSame('test2', $error->getData('test1'));
        $this->assertSame(['test1' => 'test2'], $error->getData());
    }
}


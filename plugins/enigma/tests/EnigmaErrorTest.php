<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaError extends TestCase
{
    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new enigma_error(enigma_error::EXPIRED, 'message', ['test1' => 'test2']);

        self::assertInstanceOf('enigma_error', $error);
        self::assertSame(enigma_error::EXPIRED, $error->getCode());
        self::assertSame('message', $error->getMessage());
        self::assertSame('test2', $error->getData('test1'));
        self::assertSame(['test1' => 'test2'], $error->getData());
    }
}

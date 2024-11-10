<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EnigmaErrorTest extends TestCase
{
    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new \enigma_error(\enigma_error::EXPIRED, 'message', ['test1' => 'test2']);

        $this->assertInstanceOf('enigma_error', $error);
        $this->assertSame(\enigma_error::EXPIRED, $error->getCode());
        $this->assertSame('message', $error->getMessage());
        $this->assertSame('test2', $error->getData('test1'));
        $this->assertSame(['test1' => 'test2'], $error->getData());
    }
}

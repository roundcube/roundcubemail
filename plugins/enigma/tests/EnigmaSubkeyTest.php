<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EnigmaSubkeyTest extends TestCase
{
    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new \enigma_subkey();

        $this->assertInstanceOf('enigma_subkey', $error);
    }
}

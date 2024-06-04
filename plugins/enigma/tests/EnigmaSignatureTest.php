<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EnigmaSignatureTest extends TestCase
{
    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new \enigma_signature();

        $this->assertInstanceOf('enigma_signature', $error);
    }
}

<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EnigmaUseridTest extends TestCase
{
    /**
     * Test constructor
     */
    public function test_constructor()
    {
        $error = new \enigma_userid();

        $this->assertInstanceOf('enigma_userid', $error);
    }
}

<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EnigmaKeyTest extends TestCase
{
    /**
     * Test "empty" key
     */
    public function test_empty_key()
    {
        $key = new \enigma_key();

        $this->assertInstanceOf('enigma_key', $key);
        $this->assertSame(\enigma_key::TYPE_UNKNOWN, $key->get_type());
        $this->assertFalse($key->is_revoked());
        $this->assertFalse($key->is_valid());
        $this->assertFalse($key->is_private());
        $this->assertNull($key->find_subkey('test@domain.com', \enigma_key::CAN_SIGN));

        $this->assertSame('89E037A5', $key::format_id('04622F2089E037A5'));
        // TODO: format_fingerprint();
    }
}

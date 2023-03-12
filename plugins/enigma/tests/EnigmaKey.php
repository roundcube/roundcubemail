<?php

class Enigma_EnigmaKey extends PHPUnit\Framework\TestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../lib/enigma_key.php';
    }

    /**
     * Test "empty" key
     */
    function test_empty_key()
    {
        $key = new enigma_key();

        $this->assertInstanceOf('enigma_key', $key);
        $this->assertSame(enigma_key::TYPE_UNKNOWN, $key->get_type());
        $this->assertSame(false, $key->is_revoked());
        $this->assertSame(false, $key->is_valid());
        $this->assertSame(false, $key->is_private());
        $this->assertSame(null, $key->find_subkey('test@domain.com', enigma_key::CAN_SIGN));

        $this->assertSame('89E037A5', $key::format_id('04622F2089E037A5'));
        // TODO: format_fingerprint();
    }
}


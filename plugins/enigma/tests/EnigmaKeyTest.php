<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaKey extends TestCase
{
    /**
     * Test "empty" key
     */
    public function test_empty_key()
    {
        $key = new enigma_key();

        self::assertInstanceOf('enigma_key', $key);
        self::assertSame(enigma_key::TYPE_UNKNOWN, $key->get_type());
        self::assertFalse($key->is_revoked());
        self::assertFalse($key->is_valid());
        self::assertFalse($key->is_private());
        self::assertNull($key->find_subkey('test@domain.com', enigma_key::CAN_SIGN));

        self::assertSame('89E037A5', $key::format_id('04622F2089E037A5'));
        // TODO: format_fingerprint();
    }
}

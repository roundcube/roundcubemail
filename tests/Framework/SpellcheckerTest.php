<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_spellchecker class
 */
class Framework_Spellchecker extends TestCase
{
    /**
     * Test is_exception() method
     */
    public function test_is_exception()
    {
        $object = new rcube_spellchecker();

        self::assertFalse($object->is_exception('test'));

        self::assertTrue($object->is_exception('9'));

        // TODO: Test other cases and dictionary
    }

    /**
     * Test add_word() method
     */
    public function test_add_word()
    {
        self::markTestIncomplete();
    }

    /**
     * Test remove_word() method
     */
    public function test_remove_word()
    {
        self::markTestIncomplete();
    }
}

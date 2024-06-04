<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_spellchecker class
 */
class SpellcheckerTest extends TestCase
{
    /**
     * Test is_exception() method
     */
    public function test_is_exception()
    {
        $object = new \rcube_spellchecker();

        $this->assertFalse($object->is_exception('test'));

        $this->assertTrue($object->is_exception('9'));

        // TODO: Test other cases and dictionary
    }

    /**
     * Test add_word() method
     */
    public function test_add_word()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test remove_word() method
     */
    public function test_remove_word()
    {
        $this->markTestIncomplete();
    }
}

<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_spellcheck_googie class
 */
class Framework_SpellcheckerGoogie extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_spellchecker_googie(null, 'en');

        $this->assertInstanceOf('rcube_spellchecker_googie', $object, 'Class constructor');
        $this->assertInstanceOf('rcube_spellchecker_engine', $object, 'Class constructor');
    }
}

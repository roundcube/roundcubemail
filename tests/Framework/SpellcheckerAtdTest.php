<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_spellcheck_atd class
 */
class Framework_SpellcheckerAtd extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_spellchecker_atd(null, 'en');

        self::assertInstanceOf('rcube_spellchecker_atd', $object, 'Class constructor');
        self::assertInstanceOf('rcube_spellchecker_engine', $object, 'Class constructor');
    }
}

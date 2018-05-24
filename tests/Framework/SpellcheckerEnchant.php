<?php

/**
 * Test class to test rcube_spellcheck_enchant class
 *
 * @package Tests
 */
class Framework_SpellcheckerEnchant extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_spellchecker_enchant(null, 'en');

        $this->assertInstanceOf('rcube_spellchecker_enchant', $object, "Class constructor");
        $this->assertInstanceOf('rcube_spellchecker_engine', $object, "Class constructor");
    }
}

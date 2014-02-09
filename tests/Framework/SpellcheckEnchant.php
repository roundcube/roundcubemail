<?php

/**
 * Test class to test rcube_spellcheck_enchant class
 *
 * @package Tests
 */
class Framework_SpellcheckEnchant extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_spellcheck_enchant(null, 'en');

        $this->assertInstanceOf('rcube_spellcheck_enchant', $object, "Class constructor");
        $this->assertInstanceOf('rcube_spellcheck_engine', $object, "Class constructor");
    }
}

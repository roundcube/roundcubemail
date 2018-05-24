<?php

/**
 * Test class to test rcube_spellcheck_googie class
 *
 * @package Tests
 */
class Framework_SpellcheckerGoogie extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_spellchecker_googie(null, 'en');

        $this->assertInstanceOf('rcube_spellchecker_googie', $object, "Class constructor");
        $this->assertInstanceOf('rcube_spellchecker_engine', $object, "Class constructor");
    }
}

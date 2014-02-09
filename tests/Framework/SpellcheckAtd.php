<?php

/**
 * Test class to test rcube_spellcheck_atd class
 *
 * @package Tests
 */
class Framework_SpellcheckAtd extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_spellcheck_atd(null, 'en');

        $this->assertInstanceOf('rcube_spellcheck_atd', $object, "Class constructor");
        $this->assertInstanceOf('rcube_spellcheck_engine', $object, "Class constructor");
    }
}

<?php

/**
 * Test class to test rcube_spellcheck_pspell class
 *
 * @package Tests
 */
class Framework_SpellcheckerPspell extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_spellchecker_pspell(null, 'en');

        $this->assertInstanceOf('rcube_spellchecker_pspell', $object, "Class constructor");
        $this->assertInstanceOf('rcube_spellchecker_engine', $object, "Class constructor");
    }
}

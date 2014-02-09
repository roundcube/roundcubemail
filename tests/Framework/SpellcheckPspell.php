<?php

/**
 * Test class to test rcube_spellcheck_pspell class
 *
 * @package Tests
 */
class Framework_SpellcheckPspell extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_spellcheck_pspell(null, 'en');

        $this->assertInstanceOf('rcube_spellcheck_pspell', $object, "Class constructor");
        $this->assertInstanceOf('rcube_spellcheck_engine', $object, "Class constructor");
    }
}

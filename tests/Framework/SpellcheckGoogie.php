<?php

/**
 * Test class to test rcube_spellcheck_googie class
 *
 * @package Tests
 */
class Framework_SpellcheckGoogie extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_spellcheck_googie(null, 'en');

        $this->assertInstanceOf('rcube_spellcheck_googie', $object, "Class constructor");
        $this->assertInstanceOf('rcube_spellcheck_engine', $object, "Class constructor");
    }
}

<?php

/**
 * Test class to test rcube_spellchecker class
 *
 * @package Tests
 */
class Framework_Spellchecker extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_spellchecker;

        $this->assertInstanceOf('rcube_spellchecker', $object, "Class constructor");
    }
}

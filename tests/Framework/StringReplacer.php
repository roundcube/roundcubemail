<?php

/**
 * Test class to test rcube_string_replacer class
 *
 * @package Tests
 */
class Framework_StringReplacer extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $sr = new rcube_string_replacer;

        $this->assertInstanceOf('rcube_string_replacer', $sr, "Class constructor");
    }
}

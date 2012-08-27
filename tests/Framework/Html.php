<?php

/**
 * Test class to test rcube_html class
 *
 * @package Tests
 */
class Framework_Html extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new html;

        $this->assertInstanceOf('html', $object, "Class constructor");
    }
}

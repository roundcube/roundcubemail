<?php

/**
 * Test class to test rcube_smtp class
 *
 * @package Tests
 */
class Framework_Smtp extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_smtp;

        $this->assertInstanceOf('rcube_smtp', $object, "Class constructor");
    }
}

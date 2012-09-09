<?php

/**
 * Test class to test rcube class
 *
 * @package Tests
 */
class Framework_Rcube extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = rcube::get_instance();

        $this->assertInstanceOf('rcube', $object, "Class singleton");
    }
}

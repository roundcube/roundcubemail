<?php

/**
 * Test class to test rcube class
 *
 * @package Tests
 */
class Framework_Rcube extends PHPUnit\Framework\TestCase
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

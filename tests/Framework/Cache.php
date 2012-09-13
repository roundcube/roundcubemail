<?php

/**
 * Test class to test rcube_cache class
 *
 * @package Tests
 */
class Framework_Cache extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_cache('db', 1);

        $this->assertInstanceOf('rcube_cache', $object, "Class constructor");
    }
}

<?php

/**
 * Test class to test rcube_db class
 *
 * @package Tests
 */
class Framework_DB extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_db('test');

        $this->assertInstanceOf('rcube_db', $object, "Class constructor");
    }
}

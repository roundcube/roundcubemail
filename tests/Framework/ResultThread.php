<?php

/**
 * Test class to test rcube_result_thread class
 *
 * @package Tests
 */
class Framework_ResultThread extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_result_thread;

        $this->assertInstanceOf('rcube_result_thread', $object, "Class constructor");
    }
}

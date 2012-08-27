<?php

/**
 * Test class to test rcube_result_index class
 *
 * @package Tests
 */
class Framework_ResultIndex extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_result_index;

        $this->assertInstanceOf('rcube_result_index', $object, "Class constructor");
    }
}

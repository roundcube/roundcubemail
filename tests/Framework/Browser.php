<?php

/**
 * Test class to test rcube_browser class
 *
 * @package Tests
 */
class Framework_Browser extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_browser();

        $this->assertInstanceOf('rcube_browser', $object, "Class constructor");
    }
}

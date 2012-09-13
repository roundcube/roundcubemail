<?php

/**
 * Test class to test rcube_image class
 *
 * @package Tests
 */
class Framework_Image extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_image('test');

        $this->assertInstanceOf('rcube_image', $object, "Class constructor");
    }
}

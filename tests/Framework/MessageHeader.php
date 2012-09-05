<?php

/**
 * Test class to test rcube_message_header class
 *
 * @package Tests
 */
class Framework_MessageHeader extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_message_header;

        $this->assertInstanceOf('rcube_message_header', $object, "Class constructor");
    }
}

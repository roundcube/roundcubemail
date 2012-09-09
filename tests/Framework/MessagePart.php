<?php

/**
 * Test class to test rcube_message_part class
 *
 * @package Tests
 */
class Framework_MessagePart extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_message_part;

        $this->assertInstanceOf('rcube_message_part', $object, "Class constructor");
    }
}

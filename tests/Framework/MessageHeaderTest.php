<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_message_header class
 */
class Framework_MessageHeader extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_message_header();

        $this->assertInstanceOf('rcube_message_header', $object, 'Class constructor');
    }
}

<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_message_part class
 */
class Framework_MessagePart extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_message_part();

        $this->assertInstanceOf('rcube_message_part', $object, 'Class constructor');
    }
}

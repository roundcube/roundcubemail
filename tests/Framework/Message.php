<?php

/**
 * Test class to test rcube_message class
 *
 * @package Tests
 */
class Framework_Message extends PHPUnit\Framework\TestCase
{
    /**
     * Test format_part_body() method
     */
    function test_format_part_body()
    {
        $part   = new rcube_message_part();
        $body   = 'test';
        $result = rcube_message::format_part_body($body, $part);

        $this->assertSame('test', $result);
    }
}

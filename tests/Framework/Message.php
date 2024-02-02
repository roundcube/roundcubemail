<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_message class
 */
class Framework_Message extends TestCase
{
    /**
     * Test format_part_body() method
     */
    public function test_format_part_body()
    {
        $part = new rcube_message_part();
        $body = 'test';
        $result = rcube_message::format_part_body($body, $part);

        $this->assertSame('test', $result);
    }
}

<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_message_header class
 */
class MessageHeaderTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_message_header();

        $this->assertInstanceOf(\rcube_message_header::class, $object, 'Class constructor');
    }
}

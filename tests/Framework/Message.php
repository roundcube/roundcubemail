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

    /**
     * Test tnef_decode() method
     */
    public function test_tnef_decode()
    {
        $message = new rcube_message_test(123);
        $part = new rcube_message_part();
        $part->mime_id = 1;

        $message->set_part_body(1, '');
        $result = $message->tnef_decode($part);

        $this->assertSame([], $result);

        $message->set_part_body(1, file_get_contents(TESTS_DIR . 'src/body.tnef'));
        $result = $message->tnef_decode($part);

        $this->assertCount(1, $result);
        $this->assertInstanceOf('rcube_message_part', $result[0]);
        $this->assertSame('winmail.1.html', $result[0]->mime_id);
        $this->assertSame('text/html', $result[0]->mimetype);
        $this->assertSame(5360, $result[0]->size);
        $this->assertStringStartsWith('<!DOCTYPE HTML', $result[0]->body);
        $this->assertSame([], $result[0]->parts);

        $message->set_part_body(1, file_get_contents(TESTS_DIR . 'src/one-file.tnef'));
        $result = $message->tnef_decode($part);

        $this->assertCount(1, $result);
        $this->assertInstanceOf('rcube_message_part', $result[0]);
        $this->assertSame('winmail.1.0', $result[0]->mime_id);
        $this->assertSame('application/octet-stream', $result[0]->mimetype);
        $this->assertSame(244, $result[0]->size);
        $this->assertStringContainsString(' Authors of', $result[0]->body);
        $this->assertSame([], $result[0]->parts);
    }

    /**
     * Test uu_decode() method
     */
    public function test_uu_decode()
    {
        $message = new rcube_message_test(123);
        $part = new rcube_message_part();
        $part->mime_id = 1;

        $message->set_part_body(1, '');
        $result = $message->uu_decode($part);

        $this->assertSame([], $result);

        $content = "begin 644 /dev/stdout\n" . convert_uuencode('test') . "end";
        $message->set_part_body(1, $content);

        $result = $message->uu_decode($part);

        $this->assertCount(1, $result);
        $this->assertInstanceOf('rcube_message_part', $result[0]);
        $this->assertSame('uu.1.0', $result[0]->mime_id);
        $this->assertSame('text/plain', $result[0]->mimetype);
        $this->assertSame(4, $result[0]->size);
        $this->assertSame('test', $result[0]->body);
        $this->assertSame([], $result[0]->parts);
    }
}

/**
 * rcube_message wrapper for easier testing (without accessing IMAP)
 */
class rcube_message_test extends rcube_message
{
    private $part_bodies = [];

    public function __construct($uid, $folder = null, $is_safe = false)
    {
    }

    public function get_part_body($mime_id, $formatted = false, $max_bytes = 0, $mode = null)
    {
        return $this->part_bodies[$mime_id] ?? null;
    }

    public function set_part_body($mime_id, $body)
    {
        $this->part_bodies[$mime_id] = $body;
    }
}

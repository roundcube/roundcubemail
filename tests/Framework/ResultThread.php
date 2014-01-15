<?php

/**
 * Test class to test rcube_result_thread class
 *
 * @package Tests
 */
class Framework_ResultThread extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_result_thread;

        $this->assertInstanceOf('rcube_result_thread', $object, "Class constructor");
    }

    /**
     * thread parser test
     */
    function test_parse_thread()
    {
        $text   = file_get_contents(__DIR__ . '/../src/imap_thread.txt');
        $object = new rcube_result_thread('INBOX', $text);

        $this->assertSame(false, $object->is_empty(), "Object is empty");
        $this->assertSame(false, $object->is_error(), "Object is error");
        $this->assertSame(1721, $object->max(), "Max message UID");
        $this->assertSame(1, $object->min(), "Min message UID");
        $this->assertSame(1721, $object->count_messages(), "Messages count");
        $this->assertSame(1691, $object->exists(1720, true), "Message exists");
        $this->assertSame(true, $object->exists(1720), "Message exists (bool)");
        $this->assertSame(1, $object->get_element('FIRST'), "Get first element");
        $this->assertSame(1719, $object->get_element('LAST'), "Get last element");
        $this->assertSame(14, (int) $object->get_element(2), "Get specified element");

        $object->filter(array(784));
        $this->assertSame(118, $object->count_messages(), "Messages filter");
        $this->assertSame(1, $object->count(), "Messages filter (count)");
    }
}

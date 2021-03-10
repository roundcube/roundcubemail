<?php

/**
 * Test class to test rcube_result_thread class
 *
 * @package Tests
 */
class Framework_ResultThread extends PHPUnit\Framework\TestCase
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
        $this->assertSame(731, $object->count(), "Threads count");
        $this->assertSame(1721, $object->count_messages(), "Messages count");
        $this->assertSame(1691, $object->exists(1720, true), "Message exists");
        $this->assertSame(true, $object->exists(1720), "Message exists (bool)");
        $this->assertSame(1, $object->get_element('FIRST'), "Get first element");
        $this->assertSame(1719, $object->get_element('LAST'), "Get last element");
        $this->assertSame(14, (int) $object->get_element(2), "Get specified element");

        $tree = $object->get_tree();
        $expected = [
            4 => [
                18 => [
                    39 => [
                        100 => []
                    ]
                ]
            ],
            5 => [
                6 => [],
                8 => [
                    11 => [],
                    13 => [
                        15 => []
                    ],
                    465 => []
                ],
                209 => []
            ],
            19 => [
                314 => []
            ]
        ];

        $this->assertSame([], $tree[1]);
        $this->assertSame([], $tree[2]);
        $this->assertSame([], $tree[14]);
        $this->assertSame([], $tree[3]);
        $this->assertSame($expected[4], $tree[4]);
        $this->assertSame($expected[5], $tree[5]);
        $this->assertSame($expected[19], $tree[19]);

        $clone = clone $object;
        $clone->filter([7]);
        $clone = $clone->get_tree();

        $this->assertSame(1, count($clone), "Structure check");
        $this->assertSame(3, count($clone[7]), "Structure check");
        $this->assertSame(0, count($clone[7][12]), "Structure check");
        $this->assertSame(1, count($clone[7][167]), "Structure check");
        $this->assertSame(0, count($clone[7][167][197]), "Structure check");
        $this->assertSame(2, count($clone[7][458]), "Structure check");
        $this->assertSame(1, count($clone[7][458][460]), "Structure check");
        $this->assertSame(0, count($clone[7][458][460][463]), "Structure check");
        $this->assertSame(1, count($clone[7][458][464]), "Structure check");
        $this->assertSame(0, count($clone[7][458][464][471]), "Structure check");

        $object->filter([784]);
        $this->assertSame(118, $object->count_messages(), "Messages filter");
        $this->assertSame(1, $object->count(), "Messages filter (count)");
    }

    /**
     * thread parser test (empty result)
     */
    function test_parse_empty()
    {
        $object = new rcube_result_thread('INBOX', "* THREAD");

        $this->assertSame(true, $object->is_empty(), "Object is empty");
        $this->assertSame(false, $object->is_error(), "Object is error");
        $this->assertSame(null, $object->max(), "Max message UID");
        $this->assertSame(null, $object->min(), "Min message UID");
        $this->assertSame(0, $object->count(), "Threads count");
        $this->assertSame(0, $object->count_messages(), "Messages count");
        $this->assertSame(false, $object->exists(1720, true), "Message exists");
        $this->assertSame(false, $object->exists(1720), "Message exists (bool)");
        $this->assertSame(null, $object->get_element('FIRST'), "Get first element");
        $this->assertSame(null, $object->get_element('LAST'), "Get last element");
        $this->assertSame(null, $object->get_element(2), "Get specified element");
    }
}

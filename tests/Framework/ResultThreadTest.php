<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_result_thread class
 */
class Framework_ResultThread extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_result_thread();

        self::assertInstanceOf('rcube_result_thread', $object, 'Class constructor');
    }

    /**
     * thread parser test
     */
    public function test_parse_thread()
    {
        $text = file_get_contents(__DIR__ . '/../src/imap_thread.txt');
        $object = new rcube_result_thread('INBOX', $text);

        self::assertFalse($object->is_empty(), 'Object is empty');
        self::assertFalse($object->is_error(), 'Object is error');
        self::assertSame(1721, $object->max(), 'Max message UID');
        self::assertSame(1, $object->min(), 'Min message UID');
        self::assertSame(731, $object->count(), 'Threads count');
        self::assertSame(1721, $object->count_messages(), 'Messages count');
        self::assertSame(1691, $object->exists(1720, true), 'Message exists');
        self::assertTrue($object->exists(1720), 'Message exists (bool)');
        self::assertSame(1, $object->get_element('FIRST'), 'Get first element');
        self::assertSame(1719, $object->get_element('LAST'), 'Get last element');
        self::assertSame(14, (int) $object->get_element(2), 'Get specified element');

        $tree = $object->get_tree();
        $expected = [
            4 => [
                18 => [
                    39 => [
                        100 => [],
                    ],
                ],
            ],
            5 => [
                6 => [],
                8 => [
                    11 => [],
                    13 => [
                        15 => [],
                    ],
                    465 => [],
                ],
                209 => [],
            ],
            19 => [
                314 => [],
            ],
        ];

        self::assertSame([], $tree[1]);
        self::assertSame([], $tree[2]);
        self::assertSame([], $tree[14]);
        self::assertSame([], $tree[3]);
        self::assertSame($expected[4], $tree[4]);
        self::assertSame($expected[5], $tree[5]);
        self::assertSame($expected[19], $tree[19]);

        $clone = clone $object;
        $clone->filter([7]);
        $clone = $clone->get_tree();

        self::assertSame(1, count($clone), 'Structure check');
        self::assertSame(3, count($clone[7]), 'Structure check');
        self::assertSame(0, count($clone[7][12]), 'Structure check');
        self::assertSame(1, count($clone[7][167]), 'Structure check');
        self::assertSame(0, count($clone[7][167][197]), 'Structure check');
        self::assertSame(2, count($clone[7][458]), 'Structure check');
        self::assertSame(1, count($clone[7][458][460]), 'Structure check');
        self::assertSame(0, count($clone[7][458][460][463]), 'Structure check');
        self::assertSame(1, count($clone[7][458][464]), 'Structure check');
        self::assertSame(0, count($clone[7][458][464][471]), 'Structure check');

        $object->filter([784]);
        self::assertSame(118, $object->count_messages(), 'Messages filter');
        self::assertSame(1, $object->count(), 'Messages filter (count)');
    }

    /**
     * thread parser test (empty result)
     */
    public function test_parse_empty()
    {
        $object = new rcube_result_thread('INBOX', '* THREAD');

        self::assertTrue($object->is_empty(), 'Object is empty');
        self::assertFalse($object->is_error(), 'Object is error');
        self::assertNull($object->max(), 'Max message UID');
        self::assertNull($object->min(), 'Min message UID');
        self::assertSame(0, $object->count(), 'Threads count');
        self::assertSame(0, $object->count_messages(), 'Messages count');
        self::assertFalse($object->exists(1720, true), 'Message exists');
        self::assertFalse($object->exists(1720), 'Message exists (bool)');
        self::assertNull($object->get_element('FIRST'), 'Get first element');
        self::assertNull($object->get_element('LAST'), 'Get last element');
        self::assertNull($object->get_element(2), 'Get specified element');
    }
}

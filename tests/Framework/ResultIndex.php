<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_result_index class
 */
class Framework_ResultIndex extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_result_index();

        self::assertInstanceOf('rcube_result_index', $object, 'Class constructor');
    }

    /**
     * SORT result parsing test
     */
    public function test_parse_sort()
    {
        $text = '* SORT 2001 2002 2035 2036 2037 2038 2044 2046 2043 2045 2226 2225 2224 2223';
        $object = new rcube_result_index('INBOX', $text);

        self::assertFalse($object->is_empty(), 'Object is empty');
        self::assertFalse($object->is_error(), 'Object is error');
        self::assertSame(2226, $object->max(), 'Max message UID');
        self::assertSame(2001, $object->min(), 'Min message UID');
        self::assertSame(14, $object->count_messages(), 'Messages count');
        self::assertSame(14, $object->count(), 'Messages count');
        self::assertSame(1, $object->exists(2002, true), 'Message exists');
        self::assertTrue($object->exists(2002), 'Message exists (bool)');
        self::assertSame(2001, $object->get_element('FIRST'), 'Get first element');
        self::assertSame(2223, $object->get_element('LAST'), 'Get last element');
        self::assertSame(2035, $object->get_element(2), 'Get specified element');
        self::assertSame('2001:2002,2035:2038,2043:2046,2223:2226', $object->get_compressed(), 'Get compressed index');
        self::assertSame('INBOX', $object->get_parameters('MAILBOX'), 'Get parameter');

        $clone = clone $object;
        $clone->filter([2035, 2002]);

        self::assertSame(2, $clone->count(), 'Messages count (filtered)');
        self::assertSame(2002, $clone->get_element('FIRST'), 'Get first element (filtered)');

        $clone = clone $object;
        $clone->revert();

        self::assertSame(14, $clone->count(), 'Messages count (reverted)');
        self::assertSame(12, $clone->exists(2002, true), 'Message exists (reverted)');
        self::assertTrue($clone->exists(2002), 'Message exists (bool) (reverted)');
        self::assertSame(2223, $clone->get_element('FIRST'), 'Get first element (reverted)');
        self::assertSame(2001, $clone->get_element('LAST'), 'Get last element (reverted)');
        self::assertSame(2225, $clone->get_element(2), 'Get specified element (reverted)');

        $clone = clone $object;
        $clone->slice(2, 3);

        self::assertSame(3, $clone->count(), 'Messages count (sliced)');
        self::assertSame(2035, $clone->get_element('FIRST'), 'Get first element (sliced)');
        self::assertSame(2037, $clone->get_element('LAST'), 'Get last element (sliced)');
    }

    /**
     * ESEARCH result parsing test
     */
    public function test_parse_esearch()
    {
        $text = '* ESEARCH (TAG "A282") MIN 2 COUNT 3 ALL 2,10:11';
        $object = new rcube_result_index('INBOX', $text);

        self::assertFalse($object->is_empty(), 'Object is empty');
        self::assertFalse($object->is_error(), 'Object is error');
        self::assertSame(11, $object->max(), 'Max message UID');
        self::assertSame(2, $object->min(), 'Min message UID');
        self::assertSame(3, $object->count_messages(), 'Messages count');
        self::assertSame(3, $object->count(), 'Messages count');
        self::assertSame(1, $object->exists(10, true), 'Message exists');
        self::assertTrue($object->exists(10), 'Message exists (bool)');
        self::assertSame(2, $object->get_element('FIRST'), 'Get first element');
        self::assertSame(11, $object->get_element('LAST'), 'Get last element');
        self::assertSame(10, $object->get_element(1), 'Get specified element');
        self::assertSame('2,10:11', $object->get_compressed(), 'Get compressed index');
        self::assertSame('INBOX', $object->get_parameters('MAILBOX'), 'Get parameter');

        // A case without 'ALL' response
        $text = '* ESEARCH (TAG "A282") UID MAX 721 COUNT 3';
        $object = new rcube_result_index('INBOX', $text);

        self::assertFalse($object->is_empty(), 'Object is empty');
        self::assertFalse($object->is_error(), 'Object is error');
        self::assertSame(721, $object->max(), 'Max message UID');
        self::assertNull($object->min(), 'Min message UID');
        self::assertSame(3, $object->count_messages(), 'Messages count');
        self::assertSame(3, $object->count(), 'Messages count');
        self::assertFalse($object->exists(10, true), 'Message exists');
        self::assertFalse($object->exists(10), 'Message exists (bool)');
        self::assertNull($object->get_element('FIRST'), 'Get first element');
        self::assertNull($object->get_element('LAST'), 'Get last element');
        self::assertNull($object->get_element(1), 'Get specified element');
        self::assertSame('', $object->get_compressed(), 'Get compressed index');
        self::assertSame('INBOX', $object->get_parameters('MAILBOX'), 'Get parameter');
    }

    /**
     * Empty SORT result parsing test
     */
    public function test_parse_empty()
    {
        $object = new rcube_result_index('INBOX', '* SORT');

        self::assertTrue($object->is_empty(), 'Object is empty');
        self::assertFalse($object->is_error(), 'Object is error');
        self::assertNull($object->max(), 'Max message UID');
        self::assertNull($object->min(), 'Min message UID');
        self::assertSame(0, $object->count_messages(), 'Messages count');
        self::assertSame(0, $object->count(), 'Messages count');
        self::assertFalse($object->exists(10, true), 'Message exists');
        self::assertFalse($object->exists(10), 'Message exists (bool)');
        self::assertNull($object->get_element('FIRST'), 'Get first element');
        self::assertNull($object->get_element('LAST'), 'Get last element');
        self::assertNull($object->get_element(1), 'Get specified element');
        self::assertSame('', $object->get_compressed(), 'Get compressed index');
        self::assertSame('INBOX', $object->get_parameters('MAILBOX'), 'Get parameter');
    }
}

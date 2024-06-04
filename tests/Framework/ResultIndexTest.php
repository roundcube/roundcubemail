<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_result_index class
 */
class ResultIndexTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_result_index();

        $this->assertInstanceOf(\rcube_result_index::class, $object, 'Class constructor');
    }

    /**
     * SORT result parsing test
     */
    public function test_parse_sort()
    {
        $text = '* SORT 2001 2002 2035 2036 2037 2038 2044 2046 2043 2045 2226 2225 2224 2223';
        $object = new \rcube_result_index('INBOX', $text);

        $this->assertFalse($object->is_empty(), 'Object is empty');
        $this->assertFalse($object->is_error(), 'Object is error');
        $this->assertSame(2226, $object->max(), 'Max message UID');
        $this->assertSame(2001, $object->min(), 'Min message UID');
        $this->assertSame(14, $object->count_messages(), 'Messages count');
        $this->assertSame(14, $object->count(), 'Messages count');
        $this->assertSame(1, $object->exists(2002, true), 'Message exists');
        $this->assertTrue($object->exists(2002), 'Message exists (bool)');
        $this->assertSame(2001, $object->get_element('FIRST'), 'Get first element');
        $this->assertSame(2223, $object->get_element('LAST'), 'Get last element');
        $this->assertSame(2035, $object->get_element(2), 'Get specified element');
        $this->assertSame('2001:2002,2035:2038,2043:2046,2223:2226', $object->get_compressed(), 'Get compressed index');
        $this->assertSame('INBOX', $object->get_parameters('MAILBOX'), 'Get parameter');

        $clone = clone $object;
        $clone->filter([2035, 2002]);

        $this->assertSame(2, $clone->count(), 'Messages count (filtered)');
        $this->assertSame(2002, $clone->get_element('FIRST'), 'Get first element (filtered)');

        $clone = clone $object;
        $clone->revert();

        $this->assertSame(14, $clone->count(), 'Messages count (reverted)');
        $this->assertSame(12, $clone->exists(2002, true), 'Message exists (reverted)');
        $this->assertTrue($clone->exists(2002), 'Message exists (bool) (reverted)');
        $this->assertSame(2223, $clone->get_element('FIRST'), 'Get first element (reverted)');
        $this->assertSame(2001, $clone->get_element('LAST'), 'Get last element (reverted)');
        $this->assertSame(2225, $clone->get_element(2), 'Get specified element (reverted)');

        $clone = clone $object;
        $clone->slice(2, 3);

        $this->assertSame(3, $clone->count(), 'Messages count (sliced)');
        $this->assertSame(2035, $clone->get_element('FIRST'), 'Get first element (sliced)');
        $this->assertSame(2037, $clone->get_element('LAST'), 'Get last element (sliced)');
    }

    /**
     * ESEARCH result parsing test
     */
    public function test_parse_esearch()
    {
        $text = '* ESEARCH (TAG "A282") MIN 2 COUNT 3 ALL 2,10:11';
        $object = new \rcube_result_index('INBOX', $text);

        $this->assertFalse($object->is_empty(), 'Object is empty');
        $this->assertFalse($object->is_error(), 'Object is error');
        $this->assertSame(11, $object->max(), 'Max message UID');
        $this->assertSame(2, $object->min(), 'Min message UID');
        $this->assertSame(3, $object->count_messages(), 'Messages count');
        $this->assertSame(3, $object->count(), 'Messages count');
        $this->assertSame(1, $object->exists(10, true), 'Message exists');
        $this->assertTrue($object->exists(10), 'Message exists (bool)');
        $this->assertSame(2, $object->get_element('FIRST'), 'Get first element');
        $this->assertSame(11, $object->get_element('LAST'), 'Get last element');
        $this->assertSame(10, $object->get_element(1), 'Get specified element');
        $this->assertSame('2,10:11', $object->get_compressed(), 'Get compressed index');
        $this->assertSame('INBOX', $object->get_parameters('MAILBOX'), 'Get parameter');

        // A case without 'ALL' response
        $text = '* ESEARCH (TAG "A282") UID MAX 721 COUNT 3';
        $object = new \rcube_result_index('INBOX', $text);

        $this->assertFalse($object->is_empty(), 'Object is empty');
        $this->assertFalse($object->is_error(), 'Object is error');
        $this->assertSame(721, $object->max(), 'Max message UID');
        $this->assertNull($object->min(), 'Min message UID');
        $this->assertSame(3, $object->count_messages(), 'Messages count');
        $this->assertSame(3, $object->count(), 'Messages count');
        $this->assertFalse($object->exists(10, true), 'Message exists');
        $this->assertFalse($object->exists(10), 'Message exists (bool)');
        $this->assertNull($object->get_element('FIRST'), 'Get first element');
        $this->assertNull($object->get_element('LAST'), 'Get last element');
        $this->assertNull($object->get_element(1), 'Get specified element');
        $this->assertSame('', $object->get_compressed(), 'Get compressed index');
        $this->assertSame('INBOX', $object->get_parameters('MAILBOX'), 'Get parameter');
    }

    /**
     * Empty SORT result parsing test
     */
    public function test_parse_empty()
    {
        $object = new \rcube_result_index('INBOX', '* SORT');

        $this->assertTrue($object->is_empty(), 'Object is empty');
        $this->assertFalse($object->is_error(), 'Object is error');
        $this->assertNull($object->max(), 'Max message UID');
        $this->assertNull($object->min(), 'Min message UID');
        $this->assertSame(0, $object->count_messages(), 'Messages count');
        $this->assertSame(0, $object->count(), 'Messages count');
        $this->assertFalse($object->exists(10, true), 'Message exists');
        $this->assertFalse($object->exists(10), 'Message exists (bool)');
        $this->assertNull($object->get_element('FIRST'), 'Get first element');
        $this->assertNull($object->get_element('LAST'), 'Get last element');
        $this->assertNull($object->get_element(1), 'Get specified element');
        $this->assertSame('', $object->get_compressed(), 'Get compressed index');
        $this->assertSame('INBOX', $object->get_parameters('MAILBOX'), 'Get parameter');
    }
}

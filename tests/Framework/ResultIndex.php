<?php

/**
 * Test class to test rcube_result_index class
 *
 * @package Tests
 */
class Framework_ResultIndex extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_result_index;

        $this->assertInstanceOf('rcube_result_index', $object, "Class constructor");
    }

    /**
     * thread parser test
     */
    function test_parse()
    {
        $text = "* SORT 2001 2002 2035 2036 2037 2038 2044 2046 2043 2045 2226 2225 2224 2223";
        $object = new rcube_result_index('INBOX', $text);

        $this->assertSame(false, $object->is_empty(), "Object is empty");
        $this->assertSame(false, $object->is_error(), "Object is error");
        $this->assertSame(2226, $object->max(), "Max message UID");
        $this->assertSame(2001, $object->min(), "Min message UID");
        $this->assertSame(14, $object->count_messages(), "Messages count");
        $this->assertSame(14, $object->count(), "Messages count");
        $this->assertSame(1, $object->exists(2002, true), "Message exists");
        $this->assertSame(true, $object->exists(2002), "Message exists (bool)");
        $this->assertSame(2001, $object->get_element('FIRST'), "Get first element");
        $this->assertSame(2223, $object->get_element('LAST'), "Get last element");
        $this->assertSame(2035, (int) $object->get_element(2), "Get specified element");
        $this->assertSame("2001:2002,2035:2038,2043:2046,2223:2226", $object->get_compressed(), "Get compressed index");
        $this->assertSame('INBOX', $object->get_parameters('MAILBOX'), "Get parameter");

        $clone = clone $object;
        $clone->filter(array(2035, 2002));

        $this->assertSame(2, $clone->count(), "Messages count (filtered)");
        $this->assertSame(2002, $clone->get_element('FIRST'), "Get first element (filtered)");

        $clone = clone $object;
        $clone->revert();

        $this->assertSame(14, $clone->count(), "Messages count (reverted)");
        $this->assertSame(12, $clone->exists(2002, true), "Message exists (reverted)");
        $this->assertSame(true, $clone->exists(2002), "Message exists (bool) (reverted)");
        $this->assertSame(2223, $clone->get_element('FIRST'), "Get first element (reverted)");
        $this->assertSame(2001, $clone->get_element('LAST'), "Get last element (reverted)");
        $this->assertSame(2225, (int) $clone->get_element(2), "Get specified element (reverted)");

        $clone = clone $object;
        $clone->slice(2, 3);

        $this->assertSame(3, $clone->count(), "Messages count (sliced)");
        $this->assertSame(2035, $clone->get_element('FIRST'), "Get first element (sliced)");
        $this->assertSame(2037, $clone->get_element('LAST'), "Get last element (sliced)");
    }

}

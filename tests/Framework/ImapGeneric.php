<?php

/**
 * Test class to test rcube_imap_generic class
 *
 * @package Tests
 */
class Framework_ImapGeneric extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_imap_generic;

        $this->assertInstanceOf('rcube_imap_generic', $object, "Class constructor");
    }

    /**
     * Test for uncompressMessageSet
     */
    function test_uncompressMessageSet()
    {
        $result = rcube_imap_generic::uncompressMessageSet(null);
        $this->assertSame(array(), $result);
        $this->assertCount(0, $result);

        $result = rcube_imap_generic::uncompressMessageSet('1');
        $this->assertSame(array(1), $result);
        $this->assertCount(1, $result);

        $result = rcube_imap_generic::uncompressMessageSet('1:3');
        $this->assertSame(array(1, 2, 3), $result);
        $this->assertCount(3, $result);
    }

    /**
     * Test for tokenizeResponse
     */
    function test_tokenizeResponse()
    {
        $response = "test brack[et] {1}\r\na {0}\r\n (item1 item2)";

        $result = rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame("test", $result);

        $result = rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame("brack[et]", $result);

        $result = rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame("a", $result);

        $result = rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame("", $result);

        $result = rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame(array('item1', 'item2'), $result);
    }
}

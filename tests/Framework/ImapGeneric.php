<?php

/**
 * Test class to test rcube_imap_generic class
 *
 * @package Tests
 */
class Framework_ImapGeneric extends PHPUnit\Framework\TestCase
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
     * Test for escape()
     */
    function test_escape()
    {
        $this->assertSame('NIL', rcube_imap_generic::escape(null));
        $this->assertSame('""', rcube_imap_generic::escape(''));
        $this->assertSame('abc', rcube_imap_generic::escape('abc'));
        $this->assertSame('"abc"', rcube_imap_generic::escape('abc', true));
        $this->assertSame('"abc\"def"', rcube_imap_generic::escape('abc"def'));
        $this->assertSame("{3}\r\na\nb", rcube_imap_generic::escape("a\nb"));
    }

    /**
     * Test for sortHeaders()
     */
    function test_sortHeaders()
    {
        $headers = [
            rcube_message_header::from_array([
                'subject' => 'Test1',
                'timestamp' => time() - 100,
            ]),
            rcube_message_header::from_array([
                'subject' => 'Re: Test2',
                'timestamp' => time(),
            ]),
        ];

        $result = rcube_imap_generic::sortHeaders($headers, 'subject');

        $this->assertSame('Test1', $result[0]->subject);
        $this->assertSame('Re: Test2', $result[1]->subject);

        $result = rcube_imap_generic::sortHeaders($headers, 'subject', 'DESC');

        $this->assertSame('Re: Test2', $result[0]->subject);
        $this->assertSame('Test1', $result[1]->subject);

        $result = rcube_imap_generic::sortHeaders($headers, 'date', 'DESC');

        $this->assertSame('Re: Test2', $result[0]->subject);
        $this->assertSame('Test1', $result[1]->subject);
    }

    /**
     * Test for compressMessageSet()
     */
    function test_compressMessageSet()
    {
        $result = rcube_imap_generic::compressMessageSet([5,4,3]);
        $this->assertSame('3:5', $result);

        $result = rcube_imap_generic::compressMessageSet([5,4,3,10,12,13]);
        $this->assertSame('3:5,10,12:13', $result);

        $result = rcube_imap_generic::compressMessageSet('1');
        $this->assertSame('1', $result);

        $result = rcube_imap_generic::compressMessageSet('-1');
        $this->assertSame('INVALID', $result);
    }

    /**
     * Test for uncompressMessageSet
     */
    function test_uncompressMessageSet()
    {
        $result = rcube_imap_generic::uncompressMessageSet(null);
        $this->assertSame([], $result);
        $this->assertCount(0, $result);

        $result = rcube_imap_generic::uncompressMessageSet('1');
        $this->assertSame([1], $result);
        $this->assertCount(1, $result);

        $result = rcube_imap_generic::uncompressMessageSet('1:3');
        $this->assertSame([1, 2, 3], $result);
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
        $this->assertSame(['item1', 'item2'], $result);
    }
}

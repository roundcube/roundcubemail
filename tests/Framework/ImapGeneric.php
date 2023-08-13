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
     * Test for uncompressMessageSet()
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
     * Test for tokenizeResponse()
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

    /**
     * Test for decodeContent() with no encoding
     */
    function test_decode_content_plain()
    {
        $content = "test uuencode encoded content\ntest uuencode encoded content";

        $this->runDecodeContent($content, $content, 0);
    }

    /**
     * Test for decodeContent() with base64 encoding
     */
    function test_decode_content_base64()
    {
        $content = "test base64 encoded content\ntest base64 encoded content";
        $encoded = chunk_split(base64_encode($content), 10, "\r\n");

        $this->runDecodeContent($content, $encoded, 1);

        // Test some real-life example
        $content = file_get_contents(TESTS_DIR . "src/test.pdf");
        $encoded = file_get_contents(TESTS_DIR . "src/test.base64");

        $this->runDecodeContent($content, $encoded, 1, 2000);
        $this->runDecodeContent($content, $encoded, 1, 4000);
        $this->runDecodeContent($content, $encoded, 1, 6000);
    }

    /**
     * Test for decodeContent() with quoted-printable encoding
     */
    function test_decode_content_qp()
    {
        $content = "test quoted-printable\n\n żąśźć encoded content\ntest quoted-printable żąśźć encoded content";
        $encoded = Mail_mimePart::quotedPrintableEncode($content, 12);

        $this->runDecodeContent($content, $encoded, 2);
    }

    /**
     * Test for decodeContent() with x-uuencode encoding
     */
    function test_decode_content_uuencode()
    {
        $content = "test uuencode encoded content\ntest uuencode encoded content";
        $encoded = "begin 664 test.txt\r\n" . convert_uuencode($content) . "end";

        $this->runDecodeContent($content, $encoded, 3);

        // Test some real-life example
        $content = file_get_contents(TESTS_DIR . "src/test.pdf");
        $encoded = file_get_contents(TESTS_DIR . "src/test.uuencode");

        $this->runDecodeContent($content, $encoded, 3, 2000);
        $this->runDecodeContent($content, $encoded, 3, 4000);
    }

    /**
     * Test for decodeContent() with no encoding, but formatted output
     */
    function test_decode_content_formatted()
    {
        $content = "test \r\n plain text\tcontent\t\r\n test plain text content\t";
        $expected = "test \n plain text\tcontent\n test plain text content";

        $this->runDecodeContent($expected, $content, 4);
    }

    /**
     * Helper to execute decodeCOntent() method in multiple variations of an input
     * and assert with the expected output
     */
    function runDecodeContent($expected, $encoded, $mode, $size = null)
    {
        $method = new ReflectionMethod('rcube_imap_generic', 'decodeContent');
        $method->setAccessible(true);

        // Make sure the method works with any chunk size
        for ($x = 1; $x <= strlen($encoded); $x++) {
            if ($size && $size != $x) {
                continue;
            }

            $decoded = $prev = '';
            $chunks = str_split($encoded, $x);

            foreach ($chunks as $idx => $chunk) {
                $decoded .= $method->invokeArgs(null, [$chunk, $mode, $idx == count($chunks)-1, &$prev]);
            }

            $this->assertSame($expected, $decoded, "Failed on chunk size of $x");
        }
    }
}

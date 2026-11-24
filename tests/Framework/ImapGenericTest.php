<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_imap_generic class
 */
class ImapGenericTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_imap_generic();

        $this->assertInstanceOf(\rcube_imap_generic::class, $object, 'Class constructor');
    }

    /**
     * Test for escape()
     */
    public function test_escape()
    {
        $this->assertSame('NIL', \rcube_imap_generic::escape(null));
        $this->assertSame('""', \rcube_imap_generic::escape(''));
        $this->assertSame('abc', \rcube_imap_generic::escape('abc'));
        $this->assertSame('"abc"', \rcube_imap_generic::escape('abc', true));
        $this->assertSame('"abc\"def"', \rcube_imap_generic::escape('abc"def'));
        $this->assertSame("{3}\r\na\nb", \rcube_imap_generic::escape("a\nb"));
    }

    /**
     * Test for sortHeaders()
     */
    public function test_sortHeaders()
    {
        $headers = [
            \rcube_message_header::from_array([
                'subject' => 'Test1',
                'timestamp' => time() - 100,
            ]),
            \rcube_message_header::from_array([
                'subject' => 'Re: Test2',
                'timestamp' => time(),
            ]),
        ];

        $result = \rcube_imap_generic::sortHeaders($headers, 'subject');

        $this->assertSame('Test1', $result[0]->subject);
        $this->assertSame('Re: Test2', $result[1]->subject);

        $result = \rcube_imap_generic::sortHeaders($headers, 'subject', 'DESC');

        $this->assertSame('Re: Test2', $result[0]->subject);
        $this->assertSame('Test1', $result[1]->subject);

        $result = \rcube_imap_generic::sortHeaders($headers, 'date', 'DESC');

        $this->assertSame('Re: Test2', $result[0]->subject);
        $this->assertSame('Test1', $result[1]->subject);
    }

    /**
     * Test for compressMessageSet()
     */
    public function test_compressMessageSet()
    {
        $result = \rcube_imap_generic::compressMessageSet([5, 4, 3]);
        $this->assertSame('3:5', $result);

        $result = \rcube_imap_generic::compressMessageSet([5, 4, 3, 10, 12, 13]);
        $this->assertSame('3:5,10,12:13', $result);

        $result = \rcube_imap_generic::compressMessageSet('1');
        $this->assertSame('1', $result);

        $result = \rcube_imap_generic::compressMessageSet('-1');
        $this->assertSame('INVALID', $result);
    }

    /**
     * Test for uncompressMessageSet()
     */
    public function test_uncompressMessageSet()
    {
        $result = \rcube_imap_generic::uncompressMessageSet(null);
        $this->assertSame([], $result);
        $this->assertCount(0, $result);

        $result = \rcube_imap_generic::uncompressMessageSet('1');
        $this->assertSame([1], $result);
        $this->assertCount(1, $result);

        $result = \rcube_imap_generic::uncompressMessageSet('1:3');
        $this->assertSame([1, 2, 3], $result);
        $this->assertCount(3, $result);
    }

    /**
     * Test for tokenizeResponse()
     */
    public function test_tokenizeResponse()
    {
        $response = "test brack[et] {1}\r\na {0}\r\n (item1 item2)";

        $result = \rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame('test', $result);

        $result = \rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame('brack[et]', $result);

        $result = \rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame('a', $result);

        $result = \rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame('', $result);

        $result = \rcube_imap_generic::tokenizeResponse($response, 1);
        $this->assertSame(['item1', 'item2'], $result);
    }

    /**
     * Test for fetchMIMEHeaders() splitting a long request
     *
     * @dataProvider provide_fetchMIMEHeaders_cases
     */
    #[DataProvider('provide_fetchMIMEHeaders_cases')]
    public function test_fetchMIMEHeaders($parts)
    {
        // A connection that records the commands and answers each part with a header of its own,
        // except the parts in $missing, which it leaves out
        $conn = new class extends \rcube_imap_generic {
            public $commands = [];
            public $response = [];
            public $missing = [];

            /**
             * @return int
             */
            #[\Override]
            protected function putLine($string, $endln = true, $anonymized = false)
            {
                $this->commands[] = $string;

                preg_match_all('/BODY\.PEEK\[([0-9.]+)\.MIME\]/', $string, $matches);
                $prefix = '* 1 FETCH (UID 10 ';
                foreach (array_diff($matches[1], $this->missing) as $part) {
                    $this->response[] = "{$prefix}BODY[{$part}.MIME] X-Part: {$part}\r\n";
                    $prefix = ' ';
                }
                $this->response[] = ")\r\n";

                return strlen($string);
            }

            #[\Override]
            protected function readLine($size = 1024)
            {
                return (string) array_shift($this->response);
            }
        };
        $conn->selected = 'INBOX';
        $conn->missing = array_slice($parts, 1, 1);

        $result = $conn->fetchMIMEHeaders('INBOX', 10, $parts);

        // every command stays within the length RFC 7162 recommends, and each part is asked for once
        $requested = [];
        foreach ($conn->commands as $command) {
            $this->assertLessThanOrEqual(8192, strlen($command));
            preg_match_all('/BODY\.PEEK\[([0-9.]+)\.MIME\]/', $command, $matches);
            $requested = array_merge($requested, $matches[1]);
        }
        $this->assertSame(array_map('strval', $parts), $requested);

        // every part gets its own headers, and the parts left out get none
        $expected = array_combine($parts, array_map(static fn ($id) => "X-Part: {$id}", $parts));
        $this->assertSame(array_diff_key($expected, array_flip($conn->missing)), $result);
    }

    /**
     * Data for test_fetchMIMEHeaders(): part identifiers
     */
    public static function provide_fetchMIMEHeaders_cases(): iterable
    {
        return [
            'no parts' => [[]],
            'many parts' => [range(1, 1000)],
            'deep parts' => [array_map(static fn ($i) => str_repeat('1.', 20) . $i, range(1, 200))],
        ];
    }

    /**
     * Test for fetchMIMEHeaders() with empty sections, answered as "" or NIL (e.g. Cyrus)
     */
    public function test_fetchMIMEHeaders_empty()
    {
        $conn = new class extends \rcube_imap_generic {
            public $response = [
                "* 1 FETCH (UID 10 BODY[1.MIME] \"\" BODY[2.MIME] {11}\r\n",
                "X-Part: 2\r\n",
                " BODY[3.MIME] NIL BODY[4.MIME] {11}\r\n",
                "X-Part: 4\r\n",
                ")\r\n",
                "A0001 OK Completed\r\n",
            ];

            /**
             * @return int
             */
            #[\Override]
            protected function putLine($string, $endln = true, $anonymized = false)
            {
                return strlen($string);
            }

            #[\Override]
            protected function readLine($size = 1024)
            {
                return (string) array_shift($this->response);
            }

            #[\Override]
            protected function readBytes($bytes)
            {
                return (string) array_shift($this->response);
            }
        };
        $conn->selected = 'INBOX';

        $result = $conn->fetchMIMEHeaders('INBOX', 10, [1, 2, 3, 4]);

        $this->assertSame(['1' => '', '2' => 'X-Part: 2', '3' => '', '4' => 'X-Part: 4'], $result);
    }

    /**
     * Test for decodeContent() with no encoding
     */
    public function test_decode_content_plain()
    {
        $content = "test uuencode encoded content\ntest uuencode encoded content";

        $this->runDecodeContent($content, $content, 0);
    }

    /**
     * Test for decodeContent() with base64 encoding
     */
    public function test_decode_content_base64()
    {
        $content = "test base64 encoded content\ntest base64 encoded content";
        $encoded = chunk_split(base64_encode($content), 10, "\r\n");

        $this->runDecodeContent($content, $encoded, 1);

        // Test some real-life example
        $content = file_get_contents(TESTS_DIR . 'src/test.pdf');
        $encoded = file_get_contents(TESTS_DIR . 'src/test.base64');

        $this->runDecodeContent($content, $encoded, 1, 2000);
        $this->runDecodeContent($content, $encoded, 1, 4000);
        $this->runDecodeContent($content, $encoded, 1, 6000);
    }

    /**
     * Test for decodeContent() with base64 encoding (multiple bodies)
     */
    public function test_decode_content_base64_multiple()
    {
        $expected = $content = str_repeat('a', 100);
        $encoded = chunk_split(base64_encode($content), 72, "\r\n");

        $expected .= ($content = str_repeat('b', 101));
        $encoded .= chunk_split(base64_encode($content), 72, "\r\n");

        $expected .= ($content = str_repeat('c', 102));
        $encoded .= chunk_split(base64_encode($content), 72, "\r\n");

        $expected .= ($content = str_repeat('d', 103));
        $encoded .= chunk_split(base64_encode($content), 72, "\r\n");

        $this->runDecodeContent($expected, $encoded, 1);
    }

    /**
     * Test for decodeContent() with quoted-printable encoding
     */
    public function test_decode_content_qp()
    {
        $content = "test quoted-printable\r\n\r\n żąśźć encoded content\r\ntest quoted-printable żąśźć encoded content";
        $encoded = \Mail_mimePart::quotedPrintableEncode($content, 12, "\r\n");

        $this->runDecodeContent($content, $encoded, 2);

        $content = file_get_contents(TESTS_DIR . '/src/test.pdf');
        $encoded = \Mail_mimePart::quotedPrintableEncode($content, 76, "\r\n");

        $this->runDecodeContent($content, $encoded, 2, strlen($encoded));
        $this->runDecodeContent($content, $encoded, 2, strlen($encoded) / 2);
    }

    /**
     * Test for decodeContent() with x-uuencode encoding
     */
    public function test_decode_content_uuencode()
    {
        $content = "test uuencode encoded content\ntest uuencode encoded content";
        $encoded = "begin 664 test.txt\r\n" . convert_uuencode($content) . 'end';

        $this->runDecodeContent($content, $encoded, 3);

        // Test some real-life example
        $content = file_get_contents(TESTS_DIR . 'src/test.pdf');
        $encoded = file_get_contents(TESTS_DIR . 'src/test.uuencode');

        $this->runDecodeContent($content, $encoded, 3, 2000);
        $this->runDecodeContent($content, $encoded, 3, 4000);
    }

    /**
     * Test for decodeContent() with no encoding, but formatted output
     */
    public function test_decode_content_formatted()
    {
        $content = "test \r\n plain text\tcontent\t\r\n test plain text content\t";

        $this->runDecodeContent(rtrim($content), $content, 4, true);
    }

    /**
     * Helper to execute decodeCOntent() method in multiple variations of an input
     * and assert with the expected output
     */
    public function runDecodeContent($expected, $encoded, $mode, $size = null, $formatted = false)
    {
        $method = new \ReflectionMethod('rcube_imap_generic', 'decodeContent');

        // Make sure the method works with any chunk size
        for ($x = 1; $x <= strlen($encoded); $x++) {
            if ($size && $size != $x) {
                continue;
            }

            $decoded = $prev = '';
            $chunks = str_split($encoded, $x);

            foreach ($chunks as $idx => $chunk) {
                $decoded .= $method->invokeArgs(null, [$chunk, $mode, $idx == count($chunks) - 1, &$prev, $formatted]);
            }

            $this->assertSame($expected, $decoded, "Failed on chunk size of {$x}");
        }
    }
}

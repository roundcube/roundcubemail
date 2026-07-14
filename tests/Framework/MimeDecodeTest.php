<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_mime_decode class
 */
class MimeDecodeTest extends TestCase
{
    /**
     * Test mail decode
     */
    public function test_decode()
    {
        $mail = file_get_contents(TESTS_DIR . 'src/mail0.eml');

        $decoder = new \rcube_mime_decode();

        $result = $decoder->decode($mail);

        $this->assertInstanceOf(\rcube_message_part::class, $result);
        $this->assertSame('multipart/mixed', $result->mimetype);
        $this->assertSame('=_8853bfb47b7da1852ac882e69cc724f3', $result->ctype_parameters['boundary']);
        $this->assertSame('8bit', $result->encoding);
        $this->assertSame(1413, $result->size);

        $this->assertCount(13, $result->headers);
        $this->assertSame('thomas@roundcube.net', $result->headers['x-sender']);

        $this->assertSame('=_8853bfb47b7da1852ac882e69cc724f3', $result->ctype_parameters['boundary']);

        $this->assertCount(3, $result->parts);
        $this->assertSame(11, $result->parts[2]->size);
        $this->assertSame('text/plain', $result->parts[2]->mimetype);
        $this->assertSame('lines_lf.txt', $result->parts[2]->filename);
    }

    /**
     * Test decoding of a multi-segment RFC2231 extended (encoded) filename.
     * Both the first segment (with charset'lang' prefix) and the continuation
     * segments must be rawurldecode()'d before concatenation.
     */
    public function test_decode_rfc2231_extended_continuation()
    {
        $mail = "Content-Type: text/plain\r\n"
            . "Content-Disposition: attachment;\r\n"
            . " filename*0*=UTF-8''%e2%82%ac;\r\n"
            . " filename*1*=%e2%82%ac\r\n"
            . "\r\n"
            . "body\r\n";

        $decoder = new \rcube_mime_decode();
        $result = $decoder->decode($mail);

        // Two euro signs, fully decoded (not left percent-encoded)
        $this->assertSame("\xe2\x82\xac\xe2\x82\xac", $result->filename);
    }

    /**
     * Test that plain (non-extended) RFC2231 continuation segments are kept
     * literal and not rawurldecode()'d.
     */
    public function test_decode_rfc2231_plain_continuation()
    {
        $mail = "Content-Type: text/plain\r\n"
            . "Content-Disposition: attachment;\r\n"
            . " filename*0=a%20;\r\n"
            . " filename*1=b\r\n"
            . "\r\n"
            . "body\r\n";

        $decoder = new \rcube_mime_decode();
        $result = $decoder->decode($mail);

        // Plain continuations are literal; the "%20" must NOT be decoded
        $this->assertSame('a%20b', $result->filename);
    }
}

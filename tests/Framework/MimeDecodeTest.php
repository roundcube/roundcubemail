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

    /**
     * Test parameter names with a hyphen and quoted parameter values with a semicolon
     */
    public function test_decode_parameters()
    {
        $mail = "Content-Type: application/pkcs7-mime; smime-type=signed-data;\r\n"
            . " name=\"a;b.p7m\"\r\n"
            . "Content-Disposition: attachment; filename=\"a;b.p7m\"\r\n"
            . "\r\n"
            . "body\r\n";

        $decoder = new \rcube_mime_decode();
        $result = $decoder->decode($mail);

        $this->assertSame(['smime-type' => 'signed-data', 'name' => 'a;b.p7m'], $result->ctype_parameters);
        $this->assertSame(['filename' => 'a;b.p7m'], $result->d_parameters);
        $this->assertSame('a;b.p7m', $result->filename);
    }

    /**
     * Test that a broken part (multipart without boundary) does not break the other parts
     */
    public function test_decode_broken_part()
    {
        $mail = "Content-Type: multipart/mixed; boundary=\"b1\"\r\n"
            . "\r\n"
            . "--b1\r\n"
            . "Content-Type: text/plain\r\n"
            . "\r\n"
            . "text\r\n"
            . "--b1\r\n"
            . "Content-Type: multipart/alternative\r\n"
            . "\r\n"
            . "no boundary\r\n"
            . "--b1\r\n"
            . "Content-Type: application/pdf; name=\"a.pdf\"\r\n"
            . "\r\n"
            . "pdf\r\n"
            . "--b1--\r\n";

        $warnings = [];
        set_error_handler(static function ($no, $str) use (&$warnings) {
            $warnings[] = $str;

            return true;
        });

        $decoder = new \rcube_mime_decode();
        $result = $decoder->decode($mail);

        restore_error_handler();

        $this->assertSame([], $warnings);
        $this->assertCount(3, $result->parts);
        $this->assertSame('2', $result->parts[1]->mime_id);
        $this->assertSame('text/plain', $result->parts[1]->mimetype);
        $this->assertSame('', $result->parts[1]->body);
        $this->assertSame('3', $result->parts[2]->mime_id);
        $this->assertSame('a.pdf', $result->parts[2]->filename);
    }
}

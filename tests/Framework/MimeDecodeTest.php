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
}

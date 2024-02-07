<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_mime_decode class
 */
class Framework_MimeDecode extends TestCase
{
    /**
     * Test mail decode
     */
    public function test_decode()
    {
        $mail = file_get_contents(TESTS_DIR . 'src/mail0.eml');

        $decoder = new rcube_mime_decode();

        $result = $decoder->decode($mail);

        self::assertInstanceOf('rcube_message_part', $result);
        self::assertSame('multipart/mixed', $result->mimetype);
        self::assertSame('=_8853bfb47b7da1852ac882e69cc724f3', $result->ctype_parameters['boundary']);
        self::assertSame('8bit', $result->encoding);
        self::assertSame(1413, $result->size);

        self::assertCount(13, $result->headers);
        self::assertSame('thomas@roundcube.net', $result->headers['x-sender']);

        self::assertSame('=_8853bfb47b7da1852ac882e69cc724f3', $result->ctype_parameters['boundary']);

        self::assertCount(3, $result->parts);
        self::assertSame(11, $result->parts[2]->size);
        self::assertSame('text/plain', $result->parts[2]->mimetype);
        self::assertSame('lines_lf.txt', $result->parts[2]->filename);
    }
}

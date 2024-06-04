<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_message_part class
 */
class MessagePartTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_message_part();

        $this->assertInstanceOf(\rcube_message_part::class, $object, 'Class constructor');
    }

    /**
     * Test for normalize() method
     */
    public function test_normalize()
    {
        $conf = [
            'include_bodies' => false,
            'decode_bodies' => false,
            'decode_headers' => true,
        ];

        $mime = new \rcube_mime_decode($conf);
        $message = $mime->decode(file_get_contents(TESTS_DIR . 'src/filename.eml'));

        foreach ($message->parts as $part) {
            $part->filename = '';
        }

        // Test some basic cases
        $this->assertSame('A011.txt', $message->parts[0]->normalize());
        $this->assertSame('A012.txt', $message->parts[1]->normalize());
        $this->assertSame('A014.txt', $message->parts[2]->normalize());

        // Test RFC2047 encoding (note: the decoding was done in rcube_mime_decode)
        $this->assertSame('żółć.png', $message->parts[3]->normalize());
        $this->assertSame(
            'very very very very long very very very very long ćććććć very very very long name.txt',
            $message->parts[5]->normalize()
        );

        // Test RFC2231 encoding (note: the decoding was done in rcube_mime_decode)
        $this->assertSame('żółć.png', $message->parts[4]->normalize());

        // Test the decoding in normalize() itself
        $part = new \rcube_message_part();

        $headers = "Content-Type: image/png; charset=UTF-16LE; name=A016.txt\r\n"
            . "Content-Disposition: attachment;\r\n filename*=UTF-8''%C5%BC%C3%B3%C5%82%C4%87.png\r\n";
        $this->assertSame('żółć.png', $part->normalize($headers));

        $headers = "Content-Type: text/plain; charset=ISO-8859-1;\r\n"
            . " name=\"=?UTF-8?Q?very_very_very_very_long_very_very_very_very_long_=C4=87?=\r\n"
            . " =?UTF-8?Q?=C4=87=C4=87=C4=87=C4=87_very_very_very_long_name=2Etxt?=\r\n"
            . " =?UTF-8?Q??=\"\r\n"
            . "Content-Disposition: attachment;\r\n"
            . " filename*0*=UTF-8''very%20very%20very%20very%20long%20very%20very%20very;\r\n"
            . " filename*1*=%20very%20long%20%C4%87%C4%87%C4%87%C4%87%C4%87%C4%87%20very;\r\n"
            . " filename*2*=%20very%20very%20long%20name.txt;\r\n";
        $this->assertSame(
            'very very very very long very very very very long ćććććć very very very long name.txt',
            $part->normalize($headers)
        );

        // TODO: Test some more corner cases
    }
}

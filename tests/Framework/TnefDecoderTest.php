<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_tnef_decoder class
 */
class TnefDecoderTest extends TestCase
{
    /**
     * Test TNEF decoding
     */
    public function test_decompress()
    {
        $body = file_get_contents(TESTS_DIR . 'src/one-file.tnef');
        $tnef = new \rcube_tnef_decoder();
        $result = $tnef->decompress($body);

        $this->assertSame('one-file', trim($result['message']['name']));
        $this->assertCount(1, $result['attachments']);
        $this->assertSame('application', $result['attachments'][0]['type']);
        $this->assertSame('octet-stream', $result['attachments'][0]['subtype']);
        $this->assertSame('AUTHORS', $result['attachments'][0]['name']);
        $this->assertSame(244, $result['attachments'][0]['size']);
        $this->assertMatchesRegularExpression('/Mark Simpson/', $result['attachments'][0]['stream']);
    }

    /**
     * Test TNEF decoding (#10193)
     */
    public function test_decompress_10193()
    {
        $body = file_get_contents(TESTS_DIR . 'src/winmail-10193.tnef');
        $tnef = new \rcube_tnef_decoder();
        $result = $tnef->decompress($body);

        $this->assertSame("..Re: Vyzva_Teplotka_Ordinace_perioperacni_pece\0.html", trim($result['message']['name']));
        $this->assertSame('text', $result['message']['type']);
        $this->assertSame('html', $result['message']['subtype']);
        $this->assertSame(16664, $result['message']['size']);
        $this->assertSame(16664, strlen($result['message']['stream']));
        $this->assertCount(3, $result['attachments']);
        $this->assertSame(34965, $result['attachments'][0]['size']);
        $this->assertSame(34965, strlen($result['attachments'][0]['stream']));
        $this->assertTrue(str_starts_with($result['attachments'][0]['stream'], "\x89PNG"));
        $this->assertSame('73471187-19a4-4a80-ac0b-6e776ff823fc', $result['attachments'][0]['content-id']);
        $this->assertSame(39201, $result['attachments'][1]['size']);
        $this->assertSame(39201, strlen($result['attachments'][1]['stream']));
        $this->assertTrue(str_starts_with($result['attachments'][1]['stream'], "\x89PNG"));
        $this->assertSame('32c2ea33-0a82-4941-9843-9b5bbd7e9cb2', $result['attachments'][1]['content-id']);
        $this->assertSame(37205, $result['attachments'][2]['size']);
        $this->assertSame(37205, strlen($result['attachments'][2]['stream']));
        $this->assertTrue(str_starts_with($result['attachments'][2]['stream'], "\x89PNG"));
        $this->assertSame('2be4b893-2b78-4442-81c0-8ddac66487c5', $result['attachments'][2]['content-id']);
    }

    /**
     * Test TNEF decoding
     */
    public function test_decompress_body()
    {
        $body = file_get_contents(TESTS_DIR . 'src/body.tnef');
        $tnef = new \rcube_tnef_decoder();
        $result = $tnef->decompress($body);

        $this->assertSame('Untitled.html', trim($result['message']['name']));
        $this->assertCount(0, $result['attachments']);
        $this->assertSame('text', $result['message']['type']);
        $this->assertSame('html', $result['message']['subtype']);
        $this->assertSame(5360, $result['message']['size']);
        $this->assertMatchesRegularExpression('/^<\!DOCTYPE HTML/', $result['message']['stream']);

        $tnef = new \rcube_tnef_decoder();
        $result = $tnef->decompress($body, true);

        $this->assertCount(0, $result['attachments']);
        $this->assertSame(5360, strlen($result['message']));
        $this->assertMatchesRegularExpression('/^<\!DOCTYPE HTML/', $result['message']);
    }

    /**
     * Test that a truncated compressed-RTF payload does not cause
     * out-of-bounds string reads (PHP warnings) in _decompressRTF().
     */
    public function test_decompressRTF_truncated()
    {
        $tnef = new \rcube_tnef_decoder();

        $method = new \ReflectionMethod('rcube_tnef_decoder', '_decompressRTF');

        // First byte is a flags byte with bit 0 set, so the back-reference
        // branch is taken. The branch needs two more bytes (offset + length),
        // but the payload is truncated right after the flags byte. On
        // unmodified code this triggers "Uninitialized string offset" warnings.
        $truncated = [
            "\x01",           // flags byte only, no data follows
            "\x01\x00",       // flags + offset, length byte missing
            "\x00\x00",       // literal branch, then out of data mid-loop
        ];

        // Turn PHP warnings/notices into exceptions so we can assert cleanly.
        set_error_handler(static function ($errno, $errstr) {
            throw new \ErrorException($errstr, 0, $errno);
        });

        try {
            foreach ($truncated as $data) {
                // A large $size forces the loop to keep consuming input.
                $result = $method->invoke($tnef, $data, 1000);
                $this->assertIsString($result);
            }
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Test rtf2text()
     */
    public function test_rtf2text()
    {
        $body = file_get_contents(TESTS_DIR . 'src/sample.rtf');
        $text = \rcube_tnef_decoder::rtf2text($body);

        $this->assertMatchesRegularExpression('/^[a-zA-Z1-6!&<,> \n\r\.]+$/', $text);
        $this->assertTrue(str_contains($text, 'Alex Skolnick'));
        $this->assertTrue(str_contains($text, 'Heading 1'));
        $this->assertTrue(str_contains($text, 'Heading 2'));
        $this->assertTrue(str_contains($text, 'Heading 3'));
        $this->assertTrue(str_contains($text, 'Heading 4'));
        $this->assertTrue(str_contains($text, 'Heading 5'));
        $this->assertTrue(str_contains($text, 'Heading 6'));
        $this->assertTrue(str_contains($text, 'This is the first normal paragraph!'));
        $this->assertTrue(str_contains($text, 'This is a chunk of normal text.'));
        $this->assertTrue(str_contains($text, 'This is a chunk of normal text with specials, &, <, and >.'));
        $this->assertTrue(str_contains($text, 'This is a second paragraph.'));
        $this->assertTrue(str_contains($text, 'This is text with embedded  bold,  italic, and  underline styles.'));
        $this->assertTrue(str_contains($text, 'Here is the  anchor style. And here is the  Image style.'));
    }
}

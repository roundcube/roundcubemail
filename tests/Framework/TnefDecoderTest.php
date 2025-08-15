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

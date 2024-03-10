<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_tnef_decoder class
 */
class Framework_TnefDecoder extends TestCase
{
    /**
     * Test TNEF decoding
     */
    public function test_decompress()
    {
        $body = file_get_contents(TESTS_DIR . 'src/one-file.tnef');
        $tnef = new rcube_tnef_decoder();
        $result = $tnef->decompress($body);

        self::assertSame('one-file', trim($result['message']['name']));
        self::assertCount(1, $result['attachments']);
        self::assertSame('application', $result['attachments'][0]['type']);
        self::assertSame('octet-stream', $result['attachments'][0]['subtype']);
        self::assertSame('AUTHORS', $result['attachments'][0]['name']);
        self::assertSame(244, $result['attachments'][0]['size']);
        self::assertMatchesRegularExpression('/Mark Simpson/', $result['attachments'][0]['stream']);
    }

    /**
     * Test TNEF decoding
     */
    public function test_decompress_body()
    {
        $body = file_get_contents(TESTS_DIR . 'src/body.tnef');
        $tnef = new rcube_tnef_decoder();
        $result = $tnef->decompress($body);

        self::assertSame('Untitled.html', trim($result['message']['name']));
        self::assertCount(0, $result['attachments']);
        self::assertSame('text', $result['message']['type']);
        self::assertSame('html', $result['message']['subtype']);
        self::assertSame(5360, $result['message']['size']);
        self::assertMatchesRegularExpression('/^<\!DOCTYPE HTML/', $result['message']['stream']);

        $tnef = new rcube_tnef_decoder();
        $result = $tnef->decompress($body, true);

        self::assertCount(0, $result['attachments']);
        self::assertSame(5360, strlen($result['message']));
        self::assertMatchesRegularExpression('/^<\!DOCTYPE HTML/', $result['message']);
    }

    /**
     * Test rtf2text()
     */
    public function test_rtf2text()
    {
        $body = file_get_contents(TESTS_DIR . 'src/sample.rtf');
        $text = rcube_tnef_decoder::rtf2text($body);

        self::assertMatchesRegularExpression('/^[a-zA-Z1-6!&<,> \n\r\.]+$/', $text);
        self::assertTrue(strpos($text, 'Alex Skolnick') !== false);
        self::assertTrue(strpos($text, 'Heading 1') !== false);
        self::assertTrue(strpos($text, 'Heading 2') !== false);
        self::assertTrue(strpos($text, 'Heading 3') !== false);
        self::assertTrue(strpos($text, 'Heading 4') !== false);
        self::assertTrue(strpos($text, 'Heading 5') !== false);
        self::assertTrue(strpos($text, 'Heading 6') !== false);
        self::assertTrue(strpos($text, 'This is the first normal paragraph!') !== false);
        self::assertTrue(strpos($text, 'This is a chunk of normal text.') !== false);
        self::assertTrue(strpos($text, 'This is a chunk of normal text with specials, &, <, and >.') !== false);
        self::assertTrue(strpos($text, 'This is a second paragraph.') !== false);
        self::assertTrue(strpos($text, 'This is text with embedded  bold,  italic, and  underline styles.') !== false);
        self::assertTrue(strpos($text, 'Here is the  anchor style. And here is the  Image style.') !== false);
    }
}

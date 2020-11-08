<?php

/**
 * Test class to test rcube_tnef_decoder class
 *
 * @package Tests
 */
class Framework_TnefDecoder extends PHPUnit\Framework\TestCase
{

    /**
     * Test TNEF decoding
     */
    function test_decompress()
    {
        $body   = file_get_contents(TESTS_DIR . 'src/one-file.tnef');
        $tnef   = new rcube_tnef_decoder;
        $result = $tnef->decompress($body);

        $this->assertSame('one-file', trim($result['message']['name']));
        $this->assertCount(1, $result['attachments']);
        $this->assertSame('application', $result['attachments'][0]['type']);
        $this->assertSame('octet-stream', $result['attachments'][0]['subtype']);
        $this->assertSame('AUTHORS', $result['attachments'][0]['name']);
        $this->assertSame(244, $result['attachments'][0]['size']);
        $this->assertRegExp('/Mark Simpson/', $result['attachments'][0]['stream']);
    }
}

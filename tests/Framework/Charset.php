<?php

/**
 * Test class to test rcube_charset class
 *
 * @package Tests
 * @group mbstring
 */
class Framework_Charset extends PHPUnit\Framework\TestCase
{
    /**
     * Data for test_clean()
     */
    function data_clean()
    {
        return [
            ['', ''],
            ["\xC1", ""],
            ["Οὐχὶ ταὐτὰ παρίσταταί μοι γιγνώσκειν", "Οὐχὶ ταὐτὰ παρίσταταί μοι γιγνώσκειν"],
            ["сим\xD0вол", "символ"],
            [["сим\xD0вол"], ["символ"]],
            [["a\x8cb" => "a\x8cb"], ["ab" => "ab"]],
            [["a\x8cb" => "a\x8cb", "ab" => "12"], ["ab" => "12"]],
        ];
    }

    /**
     * @dataProvider data_clean
     */
    function test_clean($input, $output)
    {
        $this->assertSame($output, rcube_charset::clean($input));
    }

    /**
     * Data for test_is_valid()
     */
    function data_is_valid()
    {
        $list = [];
        foreach (mb_list_encodings() as $charset) {
            $list[] = [$charset, true];
        }

        return array_merge($list, [
            ['', false],
            ['a', false],
            ['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', false],
            [null, false],

            ['TCVN5712-1:1993', true],
            ['JUS_I.B1.002', true],
        ]);
    }

    /**
     * @dataProvider data_is_valid
     */
    function test_is_valid($input, $result)
    {
        $this->assertSame($result, rcube_charset::is_valid($input));
    }

    /**
     * Data for test_parse_charset()
     */
    function data_parse_charset()
    {
        return [
            ['UTF8', 'UTF-8'],
            ['WIN1250', 'WINDOWS-1250'],
        ];
    }

    /**
     * @dataProvider data_parse_charset
     */
    function test_parse_charset($input, $output)
    {
        $this->assertEquals($output, rcube_charset::parse_charset($input));
    }

    /**
     * Data for test_convert()
     */
    function data_convert()
    {
        $data = [
            ['ö', 'ö', 'UTF-8', 'UTF-8'],
            ['ö', '', 'UTF-8', 'ASCII'],
            ['aż', 'a', 'UTF-8', 'US-ASCII'],
            ['&BCAEMARBBEEESwQ7BDoEOA-', 'Рассылки', 'UTF7-IMAP', 'UTF-8'],
            ['Рассылки', '&BCAEMARBBEEESwQ7BDoEOA-', 'UTF-8', 'UTF7-IMAP'],
            [base64_decode('GyRCLWo7M3l1OSk2SBsoQg=='), '㈱山﨑工業', 'ISO-2022-JP', 'UTF-8'],
            ['㈱山﨑工業', base64_decode('GyRCLWo7M3l1OSk2SBsoQg=='), 'UTF-8', 'ISO-2022-JP'],
            // try some invalid encodings, to make sure no error/exception is thrown
            ['test', 'test', 'WIN1253', 'INVALID'],
        ];

        if (extension_loaded('iconv')) {
            // Windows-1253 is not supported by mbstring, we're testing fallback to iconv
            $data[] = ['ε', chr(hexdec(('E5'))), 'UTF-8', 'WINDOWS-1253'];
            // Windows-874 is also not supported by mbstring
            $in = quoted_printable_decode('=B5=CD=BA=A1=C5=D1=BA');
            $data[] = [$in, 'ตอบกลับ', 'WINDOWS-874', 'UTF-8'];
        }

        return $data;
    }

    /**
     * @dataProvider data_convert
     */
    function test_convert($input, $output, $from, $to)
    {
        $this->assertEquals($output, rcube_charset::convert($input, $from, $to));
    }

    /**
     * Data for test_utf7_to_utf8()
     */
    function data_utf7_to_utf8()
    {
        return [
            ['+BCAEMARBBEEESwQ7BDoEOA-', 'Рассылки'],
        ];
    }

    /**
     * @dataProvider data_utf7_to_utf8
     */
    function test_utf7_to_utf8($input, $output)
    {
        $this->assertEquals($output, rcube_charset::utf7_to_utf8($input));
    }

    /**
     * Data for test_utf7imap_to_utf8()
     */
    function data_utf7imap_to_utf8()
    {
        return [
            ['&BCAEMARBBEEESwQ7BDoEOA-', 'Рассылки'],
        ];
    }

    /**
     * @dataProvider data_utf7imap_to_utf8
     */
    function test_utf7imap_to_utf8($input, $output)
    {
        $this->assertEquals($output, rcube_charset::utf7imap_to_utf8($input));
    }

    /**
     * Data for test_utf8_to_utf7imap()
     */
    function data_utf8_to_utf7imap()
    {
        return [
            ['Рассылки', '&BCAEMARBBEEESwQ7BDoEOA-'],
        ];
    }

    /**
     * @dataProvider data_utf8_to_utf7imap
     */
    function test_utf8_to_utf7imap($input, $output)
    {
        $this->assertEquals($output, rcube_charset::utf8_to_utf7imap($input));
    }

    /**
     * Data for test_utf16_to_utf8()
     */
    function data_utf16_to_utf8()
    {
        return [
            [base64_decode('BCAEMARBBEEESwQ7BDoEOA=='), 'Рассылки'],
        ];
    }

    /**
     * @dataProvider data_utf16_to_utf8
     */
    function test_utf16_to_utf8($input, $output)
    {
        $this->assertEquals($output, rcube_charset::utf16_to_utf8($input));
    }

    /**
     * Data for test_detect()
     */
    function data_detect()
    {
        return [
            ['', '', 'UTF-8'],
            ['a', 'UTF-8', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider data_detect
     */
    function test_detect($input, $fallback, $output)
    {
        $this->assertEquals($output, rcube_charset::detect($input, $fallback));
    }

    /**
     * Data for test_detect()
     */
    function data_detect_with_lang()
    {
        return [
            [base64_decode('xeOl3KZXutkspUStbg=='), 'zh_TW', 'BIG-5'],
        ];
    }

    /**
     * @dataProvider data_detect_with_lang
     */
    function test_detect_with_lang($input, $lang, $output)
    {
        $this->assertEquals($output, rcube_charset::detect($input, $output, $lang));
    }
}

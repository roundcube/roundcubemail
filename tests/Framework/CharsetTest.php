<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_charset class
 *
 * @group mbstring
 */
#[Group('mbstring')]
class CharsetTest extends TestCase
{
    /**
     * Data for test_clean()
     */
    public static function provide_clean_cases(): iterable
    {
        return [
            ['', ''],
            ["\xC1", ''],
            ['Οὐχὶ ταὐτὰ παρίσταταί μοι γιγνώσκειν', 'Οὐχὶ ταὐτὰ παρίσταταί μοι γιγνώσκειν'],
            ["сим\xD0вол", 'символ'],
            [["сим\xD0вол"], ['символ']],
            [["a\x8cb" => "a\x8cb"], ['ab' => 'ab']],
            [["a\x8cb" => "a\x8cb", 'ab' => '12'], ['ab' => '12']],
        ];
    }

    /**
     * @dataProvider provide_clean_cases
     */
    #[DataProvider('provide_clean_cases')]
    public function test_clean($input, $output)
    {
        $this->assertSame($output, \rcube_charset::clean($input));
    }

    /**
     * Data for test_is_valid()
     */
    public static function provide_is_valid_cases(): iterable
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
     * @dataProvider provide_is_valid_cases
     */
    #[DataProvider('provide_is_valid_cases')]
    public function test_is_valid($input, $result)
    {
        $this->assertSame($result, \rcube_charset::is_valid($input));
    }

    /**
     * Data for test_parse_charset()
     */
    public static function provide_parse_charset_cases(): iterable
    {
        return [
            ['UTF8', 'UTF-8'],
            ['WIN1250', 'WINDOWS-1250'],
        ];
    }

    /**
     * @dataProvider provide_parse_charset_cases
     */
    #[DataProvider('provide_parse_charset_cases')]
    public function test_parse_charset($input, $output)
    {
        $this->assertSame($output, \rcube_charset::parse_charset($input));
    }

    /**
     * Data for test_convert()
     */
    public static function provide_convert_cases(): iterable
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
            $data[] = ['ε', chr(hexdec('E5')), 'UTF-8', 'WINDOWS-1253'];
            // Windows-874 is also not supported by mbstring
            $in = quoted_printable_decode('=B5=CD=BA=A1=C5=D1=BA');
            $data[] = [$in, 'ตอบกลับ', 'WINDOWS-874', 'UTF-8'];
        }

        return $data;
    }

    /**
     * @dataProvider provide_convert_cases
     */
    #[DataProvider('provide_convert_cases')]
    public function test_convert($input, $output, $from, $to)
    {
        $this->assertSame($output, \rcube_charset::convert($input, $from, $to));
    }

    /**
     * Data for test_utf7_to_utf8()
     */
    public static function provide_utf7_to_utf8_cases(): iterable
    {
        return [
            ['+BCAEMARBBEEESwQ7BDoEOA-', 'Рассылки'],
        ];
    }

    /**
     * @dataProvider provide_utf7_to_utf8_cases
     */
    #[DataProvider('provide_utf7_to_utf8_cases')]
    public function test_utf7_to_utf8($input, $output)
    {
        // @phpstan-ignore-next-line
        $this->assertSame($output, \rcube_charset::utf7_to_utf8($input));
    }

    /**
     * Data for test_utf7imap_to_utf8()
     */
    public static function provide_utf7imap_to_utf8_cases(): iterable
    {
        return [
            ['&BCAEMARBBEEESwQ7BDoEOA-', 'Рассылки'],
        ];
    }

    /**
     * @dataProvider provide_utf7imap_to_utf8_cases
     */
    #[DataProvider('provide_utf7imap_to_utf8_cases')]
    public function test_utf7imap_to_utf8($input, $output)
    {
        // @phpstan-ignore-next-line
        $this->assertSame($output, \rcube_charset::utf7imap_to_utf8($input));
    }

    /**
     * Data for test_utf8_to_utf7imap()
     */
    public static function provide_utf8_to_utf7imap_cases(): iterable
    {
        return [
            ['Рассылки', '&BCAEMARBBEEESwQ7BDoEOA-'],
        ];
    }

    /**
     * @dataProvider provide_utf8_to_utf7imap_cases
     */
    #[DataProvider('provide_utf8_to_utf7imap_cases')]
    public function test_utf8_to_utf7imap($input, $output)
    {
        // @phpstan-ignore-next-line
        $this->assertSame($output, \rcube_charset::utf8_to_utf7imap($input));
    }

    /**
     * Data for test_utf16_to_utf8()
     */
    public static function provide_utf16_to_utf8_cases(): iterable
    {
        return [
            [base64_decode('BCAEMARBBEEESwQ7BDoEOA=='), 'Рассылки'],
        ];
    }

    /**
     * @dataProvider provide_utf16_to_utf8_cases
     */
    #[DataProvider('provide_utf16_to_utf8_cases')]
    public function test_utf16_to_utf8($input, $output)
    {
        // @phpstan-ignore-next-line
        $this->assertSame($output, \rcube_charset::utf16_to_utf8($input));
    }

    /**
     * Data for test_detect()
     */
    public static function provide_detect_cases(): iterable
    {
        return [
            ['', '', 'UTF-8'],
            ['a', 'UTF-8', 'UTF-8'],
        ];
    }

    /**
     * @dataProvider provide_detect_cases
     */
    #[DataProvider('provide_detect_cases')]
    public function test_detect($input, $fallback, $output)
    {
        // @phpstan-ignore-next-line
        $this->assertSame($output, \rcube_charset::detect($input, $fallback));
    }

    /**
     * Data for test_detect()
     */
    public static function provide_detect_with_lang_cases(): iterable
    {
        return [
            [base64_decode('xeOl3KZXutkspUStbg=='), 'zh_TW', 'BIG-5'],
        ];
    }

    /**
     * @dataProvider provide_detect_with_lang_cases
     */
    #[DataProvider('provide_detect_with_lang_cases')]
    public function test_detect_with_lang($input, $lang, $output)
    {
        // @phpstan-ignore-next-line
        $this->assertSame($output, \rcube_charset::detect($input, $output, $lang));
    }
}

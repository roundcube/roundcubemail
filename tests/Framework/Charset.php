<?php

/**
 * Test class to test rcube_charset class
 *
 * @package Tests
 * @group iconv
 * @group mbstring
 */
class Framework_Charset extends PHPUnit_Framework_TestCase
{

    /**
     * Data for test_clean()
     */
    function data_clean()
    {
        return array(
            array('', ''),
            array("\xC1", ""),
            array("Οὐχὶ ταὐτὰ παρίσταταί μοι γιγνώσκειν", "Οὐχὶ ταὐτὰ παρίσταταί μοι γιγνώσκειν"),
        );
    }

    /**
     * @dataProvider data_clean
     */
    function test_clean($input, $output)
    {
        $this->assertEquals($output, rcube_charset::clean($input));
    }

    /**
     * Just check for faulty byte-sequence, regardless of the actual cleaning results
     */
    function test_clean_2()
    {
        $bogus = "сим\xD0вол";
        $this->assertRegExp('/\xD0\xD0/', $bogus);
        $this->assertNotRegExp('/\xD0\xD0/', rcube_charset::clean($bogus));
    }

    /**
     * Data for test_parse_charset()
     */
    function data_parse_charset()
    {
        return array(
            array('UTF8', 'UTF-8'),
            array('WIN1250', 'WINDOWS-1250'),
        );
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
        return array(
            array('ö', 'ö', 'UTF-8', 'UTF-8'),
            array('ö', '', 'UTF-8', 'US-ASCII'),
            array('aż', 'a', 'UTF-8', 'US-ASCII'),
            array('&BCAEMARBBEEESwQ7BDoEOA-', 'Рассылки', 'UTF7-IMAP', 'UTF-8'),
            array('Рассылки', '&BCAEMARBBEEESwQ7BDoEOA-', 'UTF-8', 'UTF7-IMAP'),
            array(base64_decode('GyRCLWo7M3l1OSk2SBsoQg=='), '㈱山﨑工業', 'ISO-2022-JP', 'UTF-8'),
        );
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
        return array(
            array('+BCAEMARBBEEESwQ7BDoEOA-', 'Рассылки'),
        );
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
        return array(
            array('&BCAEMARBBEEESwQ7BDoEOA-', 'Рассылки'),
        );
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
        return array(
            array('Рассылки', '&BCAEMARBBEEESwQ7BDoEOA-'),
        );
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
        return array(
            array(base64_decode('BCAEMARBBEEESwQ7BDoEOA=='), 'Рассылки'),
        );
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
        return array(
            array('', '', 'UTF-8'),
            array('a', 'UTF-8', 'UTF-8'),
        );
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
        return array(
            array(base64_decode('xeOl3KZXutkspUStbg=='), 'zh_TW', 'BIG-5'),
        );
    }

    /**
     * @dataProvider data_detect_with_lang
     */
    function test_detect_with_lang($input, $lang, $output)
    {
        $this->assertEquals($output, rcube_charset::detect($input, $output, $lang));
    }

}

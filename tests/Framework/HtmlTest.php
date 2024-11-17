<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test html class
 */
class HtmlTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \html();

        $this->assertInstanceOf(\html::class, $object, 'Class constructor');
    }

    /**
     * Data for test_attrib_string()
     */
    public static function provide_attrib_string_cases(): iterable
    {
        return [
            [
                [], null, '',
            ],
            [
                ['test' => 'test'], null, ' test="test"',
            ],
            [
                ['test' => 'test'], ['test'], ' test="test"',
            ],
            [
                ['test' => 'test'], ['other'], '',
            ],
            [
                ['checked' => true], null, ' checked="checked"',
            ],
            [
                ['checked' => ''], null, '',
            ],
            [
                ['onclick' => ''], null, '',
            ],
            [
                ['size' => 5], null, ' size="5"',
            ],
            [
                ['size' => 'test'], null, '',
            ],
            [
                ['data-test' => 'test'], null, ' data-test="test"',
            ],
        ];
    }

    /**
     * Test for attrib_string()
     *
     * @dataProvider provide_attrib_string_cases
     */
    #[DataProvider('provide_attrib_string_cases')]
    public function test_attrib_string($arg1, $arg2, $expected)
    {
        $this->assertSame($expected, \html::attrib_string($arg1, $arg2));
    }

    /**
     * Data for test_quote()
     */
    public static function provide_quote_cases(): iterable
    {
        return [
            ['abc', 'abc'],
            ['?', '?'],
            ['"', '&quot;'],
            ['<', '&lt;'],
            ['>', '&gt;'],
            ['&', '&amp;'],
            ['&amp;', '&amp;amp;'],
        ];
    }

    /**
     * Test for quote()
     *
     * @dataProvider provide_quote_cases
     */
    #[DataProvider('provide_quote_cases')]
    public function test_quote($str, $expected)
    {
        $this->assertSame($expected, \html::quote($str));
    }

    /**
     * Data for test_parse_attrib_string()
     */
    public static function provide_parse_attrib_string_cases(): iterable
    {
        return [
            [
                '',
                [],
            ],
            [
                'test="test1-val"',
                ['test' => 'test1-val'],
            ],
            [
                'test1="test1-val"    test2=test2-val',
                ['test1' => 'test1-val', 'test2' => 'test2-val'],
            ],
            [
                '   test1="test1\'val"    test2=\'test2"val\'   ',
                ['test1' => 'test1\'val', 'test2' => 'test2"val'],
            ],
            [
                'expression="test == true ? \' test\' : \'\'" ',
                ['expression' => 'test == true ? \' test\' : \'\''],
            ],
            [
                'href="http://domain.tld/страница"',
                ['href' => 'http://domain.tld/страница'],
            ],
        ];
    }

    /**
     * Test for parse_attrib_string()
     *
     * @dataProvider provide_parse_attrib_string_cases
     */
    #[DataProvider('provide_parse_attrib_string_cases')]
    public function test_parse_attrib_string($arg1, $expected)
    {
        $this->assertSame($expected, \html::parse_attrib_string($arg1));
    }
}

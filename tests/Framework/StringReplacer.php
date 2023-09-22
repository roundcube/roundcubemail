<?php

/**
 * Test class to test rcube_string_replacer class
 *
 * @package Tests
 */
class Framework_StringReplacer extends PHPUnit\Framework\TestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $sr = new rcube_string_replacer;

        $this->assertInstanceOf('rcube_string_replacer', $sr, "Class constructor");
    }

    /**
     * Data for test_replace()
     */
    function data_replace()
    {
        return [
            ['http://domain.tld/path*path2', '<a href="http://domain.tld/path*path2">http://domain.tld/path*path2</a>'],
            ["Click this link:\nhttps://mail.xn--brderli-o2a.ch/rc/ EOF", "Click this link:\n<a href=\"https://mail.xn--brderli-o2a.ch/rc/\">https://mail.xn--brderli-o2a.ch/rc/</a> EOF"],
            ['Start http://localhost/?foo End', 'Start <a href="http://localhost/?foo">http://localhost/?foo</a> End'],
            ['http://localhost/?foo=bar. Period', '<a href="http://localhost/?foo=bar">http://localhost/?foo=bar</a>. Period'],
            ['www.domain.tld', '<a href="http://www.domain.tld">www.domain.tld</a>'],
            ['WWW.DOMAIN.TLD', '<a href="http://WWW.DOMAIN.TLD">WWW.DOMAIN.TLD</a>'],
            ['[http://link.com]', '[<a href="http://link.com">http://link.com</a>]'],
            ['http://link.com?a[]=1', '<a href="http://link.com?a[]=1">http://link.com?a[]=1</a>'],
            ['http://link.com?a[]', '<a href="http://link.com?a[]">http://link.com?a[]</a>'],
            ['(http://link.com)', '(<a href="http://link.com">http://link.com</a>)'],
            ['http://link.com?a(b)c', '<a href="http://link.com?a(b)c">http://link.com?a(b)c</a>'],
            ['http://link.com?(link)', '<a href="http://link.com?(link)">http://link.com?(link)</a>'],
            ['https://github.com/a/b/compare/3a0f82...1f4b2a after', '<a href="https://github.com/a/b/compare/3a0f82...1f4b2a">https://github.com/a/b/compare/3a0f82...1f4b2a</a> after'],
            ['http://<test>', 'http://<test>'],
            ['http://', 'http://'],
            ['test test@www.test test', 'test <a href="mailto:test@www.test">test@www.test</a> test'],
            ["test 'test@www.test' test", "test '<a href=\"mailto:test@www.test\">test@www.test</a>' test"],
            ['test "test@www.test" test', 'test "<a href="mailto:test@www.test">test@www.test</a>" test'],
            ['a 1@1.com www.domain.tld', 'a <a href="mailto:1@1.com">1@1.com</a> <a href="http://www.domain.tld">www.domain.tld</a>'],
            [' www.domain.tld ', ' <a href="http://www.domain.tld">www.domain.tld</a> '],
            [' www.domain.tld/#!download|856p1|2 ', ' <a href="http://www.domain.tld/#!download|856p1|2">www.domain.tld/#!download|856p1|2</a> '],
            // #1489898: allow some unicode characters
            ['https://www.google.com/maps/place/New+York,+État+de+New+York/@40.7056308,-73.9780035,11z/data=!3m1!4b1!4m2!3m1!1s0x89c24fa5d33f083b:0xc80b8f06e177fe62',
                '<a href="https://www.google.com/maps/place/New+York,+État+de+New+York/@40.7056308,-73.9780035,11z/data=!3m1!4b1!4m2!3m1!1s0x89c24fa5d33f083b:0xc80b8f06e177fe62">https://www.google.com/maps/place/New+York,+État+de+New+York/@40.7056308,-73.9780035,11z/data=!3m1!4b1!4m2!3m1!1s0x89c24fa5d33f083b:0xc80b8f06e177fe62</a>'
            ],
        ];
    }

    /**
     * @dataProvider data_replace
     */
    function test_replace($input, $output)
    {
        $replacer = new rcube_string_replacer;
        $result = $replacer->replace($input);
        $result = $replacer->resolve($result);

        $this->assertEquals($output, $result);
    }

    /**
     * Test link references
     */
    function test_linkrefs()
    {
        $input = "This is a sample message [1] to test the linkref [ref0] replacement feature of [Roundcube].[ref<0]\n"
            . "[1] http://en.wikipedia.org/wiki/Email\n"
            . "[ref0] www.link-ref.com\n";

        $replacer = new rcube_string_replacer;
        $result = $replacer->replace($input);
        $result = $replacer->resolve($result);

        $this->assertStringContainsString('[<a href="http://en.wikipedia.org/wiki/Email">1</a>] to', $result, "Numeric linkref replacements");
        $this->assertStringContainsString('[<a href="http://www.link-ref.com">ref0</a>] repl', $result, "Alphanum linkref replacements");
        $this->assertStringContainsString('of [Roundcube].[ref<0]', $result, "Don't touch strings without an index entry");
    }
}

<?php

/**
 * Test class to test rcube_string_replacer class
 *
 * @package Tests
 */
class Framework_StringReplacer extends PHPUnit_Framework_TestCase
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
        return array(
            array('http://domain.tld/path*path2', '<a href="http://domain.tld/path*path2">http://domain.tld/path*path2</a>'),
            array("Click this link:\nhttps://mail.xn--brderli-o2a.ch/rc/ EOF", "Click this link:\n<a href=\"https://mail.xn--brderli-o2a.ch/rc/\">https://mail.xn--brderli-o2a.ch/rc/</a> EOF"),
            array('Start http://localhost/?foo End', 'Start <a href="http://localhost/?foo">http://localhost/?foo</a> End'),
            array('http://localhost/?foo=bar. Period', '<a href="http://localhost/?foo=bar">http://localhost/?foo=bar</a>. Period'),
            array('www.domain.tld', '<a href="http://www.domain.tld">www.domain.tld</a>'),
            array('WWW.DOMAIN.TLD', '<a href="http://WWW.DOMAIN.TLD">WWW.DOMAIN.TLD</a>'),
            array('[http://link.com]', '[<a href="http://link.com">http://link.com</a>]'),
            array('http://link.com?a[]=1', '<a href="http://link.com?a[]=1">http://link.com?a[]=1</a>'),
            array('http://link.com?a[]', '<a href="http://link.com?a[]">http://link.com?a[]</a>'),
            array('(http://link.com)', '(<a href="http://link.com">http://link.com</a>)'),
            array('http://link.com?a(b)c', '<a href="http://link.com?a(b)c">http://link.com?a(b)c</a>'),
            array('http://link.com?(link)', '<a href="http://link.com?(link)">http://link.com?(link)</a>'),
            array('https://github.com/a/b/compare/3a0f82...1f4b2a after', '<a href="https://github.com/a/b/compare/3a0f82...1f4b2a">https://github.com/a/b/compare/3a0f82...1f4b2a</a> after'),
            array('http://<test>', 'http://<test>'),
            array('http://', 'http://'),
            array('test@www.test', '<a href="mailto:test@www.test">test@www.test</a>'),
            array('1@1.com www.domain.tld', '<a href="mailto:1@1.com">1@1.com</a> <a href="http://www.domain.tld">www.domain.tld</a>'),
            array(' www.domain.tld ', ' <a href="http://www.domain.tld">www.domain.tld</a> '),
            array(' www.domain.tld/#!download|856p1|2 ', ' <a href="http://www.domain.tld/#!download|856p1|2">www.domain.tld/#!download|856p1|2</a> '),
            // #1489898: allow some unicode characters
            array('https://www.google.com/maps/place/New+York,+État+de+New+York/@40.7056308,-73.9780035,11z/data=!3m1!4b1!4m2!3m1!1s0x89c24fa5d33f083b:0xc80b8f06e177fe62',
                '<a href="https://www.google.com/maps/place/New+York,+État+de+New+York/@40.7056308,-73.9780035,11z/data=!3m1!4b1!4m2!3m1!1s0x89c24fa5d33f083b:0xc80b8f06e177fe62">https://www.google.com/maps/place/New+York,+État+de+New+York/@40.7056308,-73.9780035,11z/data=!3m1!4b1!4m2!3m1!1s0x89c24fa5d33f083b:0xc80b8f06e177fe62</a>'
            ),
        );
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

    function test_linkrefs()
    {
        $input = "This is a sample message [1] to test the new linkref [ref0] replacement feature of [Roundcube].\n";
        $input.= "\n";
        $input.= "[1] http://en.wikipedia.org/wiki/Email\n";
        $input.= "[ref0] www.link-ref.com\n";

        $replacer = new rcube_string_replacer;
        $result = $replacer->replace($input);
        $result = $replacer->resolve($result);

        $this->assertContains('[<a href="http://en.wikipedia.org/wiki/Email">1</a>] to', $result, "Numeric linkref replacements");
        $this->assertContains('[<a href="http://www.link-ref.com">ref0</a>] repl', $result, "Alphanum linkref replacements");
        $this->assertContains('of [Roundcube].', $result, "Don't touch strings wihtout an index entry");
    }
}

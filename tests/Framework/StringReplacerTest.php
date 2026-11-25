<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_string_replacer class
 */
class StringReplacerTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $sr = new \rcube_string_replacer();

        $this->assertInstanceOf(\rcube_string_replacer::class, $sr, 'Class constructor');
    }

    /**
     * @dataProvider provide_replace_cases
     */
    #[DataProvider('provide_replace_cases')]
    public function test_replace($input, $output)
    {
        $replacer = new \rcube_string_replacer();
        $result = $replacer->replace($input);
        $result = $replacer->resolve($result);

        $this->assertSame($output, $result);
    }

    /**
     * Data for test_replace()
     */
    public static function provide_replace_cases(): iterable
    {
        return [
            ['http://domain.tld/path*path2', '<a href="http://domain.tld/path*path2">http://domain.tld/path*path2</a>'],
            ["Click this link:\nhttps://mail.xn--brderli-o2a.ch/rc/ EOF", "Click this link:\n<a href=\"https://mail.xn--brderli-o2a.ch/rc/\">https://mail.xn--brderli-o2a.ch/rc/</a> EOF"],
            ['Start http://localhost/?foo End', 'Start <a href="http://localhost/?foo">http://localhost/?foo</a> End'],
            ['http://localhost/?foo=bar. Period', '<a href="http://localhost/?foo=bar">http://localhost/?foo=bar</a>. Period'],
            ['www.domain.tld', '<a href="http://www.domain.tld">www.domain.tld</a>'],
            ['WWW.DOMAIN.TLD', '<a href="http://WWW.DOMAIN.TLD">WWW.DOMAIN.TLD</a>'],
            ['[http://link.com]', '[<a href="http://link.com">http://link.com</a>]'],
            ['http://link.com.', '<a href="http://link.com">http://link.com</a>.'],
            ["http://link.com\ttest", "<a href=\"http://link.com\">http://link.com</a>\ttest"],
            ['http://link.com:test', '<a href="http://link.com">http://link.com</a>:test'],
            ['http://link.com#test End', '<a href="http://link.com#test">http://link.com#test</a> End'],
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
                '<a href="https://www.google.com/maps/place/New+York,+État+de+New+York/@40.7056308,-73.9780035,11z/data=!3m1!4b1!4m2!3m1!1s0x89c24fa5d33f083b:0xc80b8f06e177fe62">https://www.google.com/maps/place/New+York,+État+de+New+York/@40.7056308,-73.9780035,11z/data=!3m1!4b1!4m2!3m1!1s0x89c24fa5d33f083b:0xc80b8f06e177fe62</a>',
            ],
            ['https://192.168.56.1.', '<a href="https://192.168.56.1">https://192.168.56.1</a>.'],
            ['http://192.168.56.1/.', '<a href="http://192.168.56.1/">http://192.168.56.1/</a>.'],
            ['ftp://1.1.1.101/test.', '<a href="ftp://1.1.1.101/test">ftp://1.1.1.101/test</a>.'],
            ['http://[::1]:8000/test.', '<a href="http://[::1]:8000/test">http://[::1]:8000/test</a>.'],
            // Port number in URL with domain name
            ['http://example.com:8080/path', '<a href="http://example.com:8080/path">http://example.com:8080/path</a>'],
            ['https://example.com:3000', '<a href="https://example.com:3000">https://example.com:3000</a>'],
            ['https://example.com:8443/path?q=1 end', '<a href="https://example.com:8443/path?q=1">https://example.com:8443/path?q=1</a> end'],
            // Non-unicode characters should be supported
            ['http://link.com ' . chr(206) . 'a', '<a href="http://link.com">http://link.com</a> ' . chr(206) . 'a'],
            // #9538: unicode Fullwidth Left Parenthesis (U+FF08)
            // ['http://www.domain.tld/abc（哇哇）', '<a href="http://www.domain.tld/abc">http://www.domain.tld/abc</a>（哇哇）'],
            // HTML tag characters should not be consumed as part of URL path
            ['<a href="https://example.com/">click here</a>', '<a href="<a href="https://example.com/">https://example.com/</a>">click here</a>'],
            ['<img src="https://example.com/img.png"/>', '<img src="<a href="https://example.com/img.png">https://example.com/img.png</a>"/>'],
            ['<https://example.com/>', '<<a href="https://example.com/">https://example.com/</a>>'],
            // #10428: an e-mail address does not take in a query, a link after it is a link of its own
            ['user@example.com?body=http://example.com/x', '<a href="mailto:user@example.com">user@example.com</a>?body=<a href="http://example.com/x">http://example.com/x</a>'],
            ['user@example.com?http://example.com/x', '<a href="mailto:user@example.com">user@example.com</a>?<a href="http://example.com/x">http://example.com/x</a>'],
            ['user@example.com?subject=Hi', '<a href="mailto:user@example.com">user@example.com</a>?subject=Hi'],
            ['git clone https://user@example.com/team/repo.git', 'git clone <a href="https://user">https://user</a>@example.com/team/repo.git'],
            // #10428: a mailto: URI with its query is one link, the query ends like a link
            ['mailto:user@example.com?body=http://example.com/x', '<a href="mailto:user@example.com?body=http://example.com/x">mailto:user@example.com?body=http://example.com/x</a>'],
            ['MAILTO:user@example.com?subject=Hi', '<a href="mailto:user@example.com?subject=Hi">MAILTO:user@example.com?subject=Hi</a>'],
            ['mailto:user@example.com?cc=other@example.org&body=http://example.com/x',
                '<a href="mailto:user@example.com?cc=other@example.org&amp;body=http://example.com/x">mailto:user@example.com?cc=other@example.org&amp;body=http://example.com/x</a>',
            ],
            ['Write to mailto:user@example.com.', 'Write to <a href="mailto:user@example.com">mailto:user@example.com</a>.'],
            ['mailto:user@example.com?subject=Hi.', '<a href="mailto:user@example.com?subject=Hi">mailto:user@example.com?subject=Hi</a>.'],
            ['mailto:user@example.com?subject=Hi, end', '<a href="mailto:user@example.com?subject=Hi">mailto:user@example.com?subject=Hi</a>, end'],
            ['mailto:user@example.com?subject=Hi; end', '<a href="mailto:user@example.com?subject=Hi">mailto:user@example.com?subject=Hi</a>; end'],
            ['mailto:user@example.com?subject=Hi: end', '<a href="mailto:user@example.com?subject=Hi">mailto:user@example.com?subject=Hi</a>: end'],
            ['mailto:user@example.com?', '<a href="mailto:user@example.com">mailto:user@example.com</a>?'],
            ['(mailto:user@example.com?subject=Hi)', '(<a href="mailto:user@example.com?subject=Hi">mailto:user@example.com?subject=Hi</a>)'],
            ['[mailto:user@example.com?subject=Hi]', '[<a href="mailto:user@example.com?subject=Hi">mailto:user@example.com?subject=Hi</a>]'],
            ['(mailto:user@example.com?body=http://example.com/x)', '(<a href="mailto:user@example.com?body=http://example.com/x">mailto:user@example.com?body=http://example.com/x</a>)'],
            ['mailto:user@example.com?subject=a)b+https://example.com/x',
                '<a href="mailto:user@example.com?subject=a">mailto:user@example.com?subject=a</a>)b+<a href="https://example.com/x">https://example.com/x</a>',
            ],
            ['mailto:user@example.com?body=www.example.com]www.example.org',
                '<a href="mailto:user@example.com?body=www.example.com">mailto:user@example.com?body=www.example.com</a>]<a href="http://www.example.org">www.example.org</a>',
            ],
            ['mailto:user@example.com;https://example.com/x', '<a href="mailto:user@example.com">mailto:user@example.com</a>;<a href="https://example.com/x">https://example.com/x</a>'],
            ['mailto:user+www.example.com@example.org', '<a href="mailto:user+www.example.com@example.org">mailto:user+www.example.com@example.org</a>'],
            ['http://example.com/mailto:user@example.com', '<a href="http://example.com/mailto:user@example.com">http://example.com/mailto:user@example.com</a>'],
        ];
    }

    /**
     * An e-mail address next to a link must not take in the replacement string of the link (#10428)
     *
     * @dataProvider provide_replace_mailto_next_to_link_cases
     */
    #[DataProvider('provide_replace_mailto_next_to_link_cases')]
    public function test_replace_mailto_next_to_link($input)
    {
        $replacer = new \rcube_string_replacer();
        $result = $replacer->resolve($replacer->replace($input));

        $this->assertDoesNotMatchRegularExpression($replacer->pattern, $result);
        $this->assertStringNotContainsString('##', $result);
    }

    /**
     * Data for test_replace_mailto_next_to_link()
     */
    public static function provide_replace_mailto_next_to_link_cases(): iterable
    {
        return [
            ['user@example.com?body=http://example.com/x'],
            ['user@example.com?subject=www.example.com'],
            ['git clone https://user@example.com/team/repo.git'],
            ['user+www.example.com@example.org'],
            ['mailto:user@example.com?body=http://example.com/x'],
            ['mailto:user@example.com?cc=other@example.org&body=www.example.com'],
            ['mailto:user+www.example.com@example.org'],
        ];
    }

    /**
     * Test link references
     */
    public function test_linkrefs()
    {
        $input = "This is a sample message [1] to test the linkref [ref0] replacement feature of [Roundcube].[ref<0]\n"
            . "[1] http://en.wikipedia.org/wiki/Email\n"
            . "[ref0] www.link-ref.com\n";

        $replacer = new \rcube_string_replacer();
        $result = $replacer->replace($input);
        $result = $replacer->resolve($result);

        $this->assertStringContainsString('[<a href="http://en.wikipedia.org/wiki/Email">1</a>] to', $result, 'Numeric linkref replacements');
        $this->assertStringContainsString('[<a href="http://www.link-ref.com">ref0</a>] repl', $result, 'Alphanum linkref replacements');
        $this->assertStringContainsString('of [Roundcube].[ref<0]', $result, "Don't touch strings without an index entry");
    }
}

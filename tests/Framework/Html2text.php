<?php

/**
 * Test class to test rcube_html2text class
 *
 * @package Tests
 */
class rc_html2text extends PHPUnit\Framework\TestCase
{

    function data_html2text()
    {
        return [
            0 => [
                'title' => 'Test entry',
                'in'    => '',
                'out'   => '',
            ],
            1 => [
                'title' => 'Basic HTML entities',
                'in'    => '&quot;&amp;',
                'out'   => '"&',
            ],
            2 => [
                'title' => 'HTML entity string',
                'in'    => '&amp;quot;',
                'out'   => '&quot;',
            ],
            3 => [
                'title' => 'HTML entity in H1 tag',
                'in'    => '<h1>&#347;</h1>', // ś
                'out'   => "Ś\n\n", // upper ś
            ],
            4 => [
                'title' => 'H1 tag to upper-case conversion',
                'in'    => '<h1>ś</h1>',
                'out'   => "Ś\n\n",
            ],
            5 => [
                'title' => 'H1 inside B tag',
                'in'    => '<b><h1>&#347;</h1></b>',
                'out'   => "Ś\n\n",
            ],
            6 => [
                'title' => 'Don\'t remove non-printable chars',
                'in'    => chr(0x002).chr(0x003),
                'out'   => chr(0x002).chr(0x003),
            ],
            7 => [
                'title' => 'Remove spaces after <br>',
                'in'    => 'test<br>  test',
                'out'   => "test\ntest",
            ],
            8 => [
                'title' => '&nbsp; handling test',
                'in'    => '<div>eye: &nbsp;&nbsp;test<br /> test: &nbsp;&nbsp;test</div>',
                'out'   => "eye:   test\ntest:   test",
            ],
            9 => [
                'title' => 'HTML entity in STRONG tag',
                'in'    => '<strong>&#347;</strong>', // ś
                'out'   => 'ś',
            ],
            10 => [
                'title' => 'STRONG tag to upper-case conversion',
                'in'    => '<strong>ś</strong>',
                'out'   => 'ś',
            ],
            11 => [
                'title' => 'STRONG inside B tag',
                'in'    => '<b><strong>&#347;</strong></b>',
                'out'   => 'ś',
            ],
            12 => [
                'title' => 'Full HTML handling (html tag only)',
                'in'    => "<html>\n<p>test</p></html>",
                'out'   => 'test',
            ],
            13 => [
                'title' => 'Full HTML handling (html+head tags)',
                'in'    => '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>'
                    . "\n<p>test</p></html>\n",
                'out'   => 'test',
            ],
            14 => [
                'title' => 'Full HTML handling (html+head+body tags)',
                'in'    => '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>' . "\n"
                    . '<body style="font-size: 10pt; font-family: Verdana,Geneva,sans-serif">' . "\n"
                    . '<p>test</p>'
                    . '</body></html>',
                'out'   => 'test',
            ],
        ];
    }

    /**
     * @dataProvider data_html2text
     */
    function test_html2text($title, $in, $out)
    {
        $ht = new rcube_html2text(null, false, rcube_html2text::LINKS_NONE);

        $ht->set_html($in);
        $res = $ht->get_text();

        $this->assertEquals($out, $res, $title);
    }

    /**
     * Test blockquote tags handling
     */
    function test_multiple_blockquotes()
    {
        $html = <<<EOF
<br>Begin<br><blockquote>OUTER BEGIN<blockquote>INNER 1<br></blockquote><div><br></div><div>Par 1</div>
<blockQuote>INNER 2</blockquote><div><br></div><div>Par 2</div>
<div><br></div><div>Par 3</div><div><br></div>
<blockquote>INNER 3</blockquote>OUTER END</blockquote>
EOF;
        $ht = new rcube_html2text($html, false, rcube_html2text::LINKS_NONE);
        $res = $ht->get_text();

        $this->assertStringContainsString('>> INNER 1', $res, 'Quote inner');
        $this->assertStringContainsString('>> INNER 3', $res, 'Quote inner');
        $this->assertStringContainsString('> OUTER END', $res, 'Quote outer');
    }

    function test_broken_blockquotes()
    {
        // no end tag
        $html = <<<EOF
Begin<br>
<blockquote>QUOTED TEXT
<blockquote>
NO END TAG FOUND
EOF;
        $ht = new rcube_html2text($html, false, rcube_html2text::LINKS_NONE);
        $res = $ht->get_text();

        $this->assertStringContainsString('QUOTED TEXT NO END TAG FOUND', $res, 'No quoting on invalid html');

        // with some (nested) end tags
        $html = <<<EOF
Begin<br>
<blockquote>QUOTED TEXT
<blockquote>INNER 1</blockquote>
<blockquote>INNER 2</blockquote>
NO END TAG FOUND
EOF;
        $ht = new rcube_html2text($html, false, rcube_html2text::LINKS_NONE);
        $res = $ht->get_text();

        $this->assertStringContainsString('QUOTED TEXT INNER 1 INNER 2 NO END', $res, 'No quoting on invalid html');
    }

    /**
     * Test links handling
     */
    function test_links()
    {
        $html     = '<a href="http://test.com">content</a>';
        $expected = 'content [1]

Links:
------
[1] http://test.com
';

        $ht = new rcube_html2text($html, false, rcube_html2text::LINKS_END);
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Links list');

        // href == content (#1490434)
        $html     = '<a href="http://test.com">http://test.com</a>';
        $expected = 'http://test.com';

        $ht = new rcube_html2text($html, false, rcube_html2text::LINKS_END);
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Skip link with href == content');

        // HTML entities in links
        $html     = '<a href="http://test.com?test1&amp;test2">test3&amp;test4</a>';
        $expected = 'test3&test4 [1]

Links:
------
[1] http://test.com?test1&test2
';

        $ht = new rcube_html2text($html, false, rcube_html2text::LINKS_END);
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Links with HTML entities');
    }

    /**
     * Test links handling with backward compatibility boolean flag
     */
    function test_links_bc_with_boolean()
    {
        $html     = '<a href="http://test.com">content</a>';
        $expected = 'content [1]

Links:
------
[1] http://test.com
';

        $ht = new rcube_html2text($html, false, true);
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Links list');

        // href == content (#1490434)
        $html     = '<a href="http://test.com">http://test.com</a>';
        $expected = 'http://test.com';

        $ht = new rcube_html2text($html, false, true);
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Skip link with href == content');
    }

    /**
     * Test links inline handling
     */
    function test_links_inline()
    {
        $html     = '<a href="http://test.com">content</a>';
        $expected = 'content <http://test.com>';

        $ht = new rcube_html2text($html, false, rcube_html2text::LINKS_INLINE);
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Links Inline');

        // href == content (#1490434)
        $html     = '<a href="http://test.com">http://test.com</a>';
        $expected = 'http://test.com';

        $ht = new rcube_html2text($html, false, rcube_html2text::LINKS_INLINE);
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Skip link with href == content');
    }

    /**
     * Test <a> links handling when not using link list (#5795)
     *
     * @dataProvider data_links_no_list
     */
    function test_links_no_list($input, $output)
    {
        $h2t = new rcube_html2text($input, false, rcube_html2text::LINKS_NONE);
        $res = $h2t->get_text();

        $this->assertSame($output, $res, 'Links handling');
    }

    /**
     * Test <a> links handling when not using link list (#5795) with backward compatibility boolean flag
     *
     * @dataProvider data_links_no_list
     */
    function test_links_no_list_bc_with_boolean($input, $output)
    {
        $h2t = new rcube_html2text($input, false, false);
        $res = $h2t->get_text();

        $this->assertSame($output, $res, 'Links handling');
    }

    function data_links_no_list()
    {
        return [
            [
                'this is <a href="http://test.com">content</a>',
                'this is content',
            ],
            [
                'this is <a href="#test">content&amp;&nbsp;test</a>',
                'this is content& test',
            ],
            [
                'this is <a href="">content</a>',
                'this is content',
            ],
            [
                'this is <a href="http://test.com"><img src=http://test.com/image" alt="image" /></a>',
                'this is http://test.com',
            ],
        ];
    }

    /**
     * Test links fallback to default handling
     */
    function test_links_fallback_to_default_link_list()
    {
        $html     = '<a href="http://test.com">content</a>';
        $expected = 'content [1]

Links:
------
[1] http://test.com
';

        $ht = new rcube_html2text($html, false);
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Links list as default (doLinks not set)');

        $ht = new rcube_html2text($html, false, mt_rand(3, 9999));
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Links list as default (doLinks greater than 3)');

        $ht = new rcube_html2text($html, false, mt_rand(-9999, -1));
        $res = $ht->get_text();

        $this->assertSame($expected, $res, 'Links list as default (doLinks lower than 0)');
    }

    /**
     * Test huge HTML content (#8137)
     */
    function test_memory_fix_8137()
    {
        // create >1MB input
        $src = 'data:image/png;base64,' . str_repeat('1234567890abcdefghijklmnopqrstuvwxyz', 50000);
        $input = 'test<body><p>test1</p><p>test2</p><img src="' . $src . '" /><p>test3</p>';

        $h2t = new rcube_html2text($input, false, rcube_html2text::LINKS_NONE);
        $res = $h2t->get_text();

        $this->assertSame("test1\n\ntest2\n\ntest3", $res, 'Huge input');
    }
}

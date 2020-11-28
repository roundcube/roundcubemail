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
                'in'    => '<div>eye: &nbsp;&nbsp;test<br /> tes: &nbsp;&nbsp;test</div>',
                'out'   => "eye:   test\ntes:   test",
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
        ];
    }

    /**
     * @dataProvider data_html2text
     */
    function test_html2text($title, $in, $out)
    {
        $ht = new rcube_html2text(null, false, false);

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
        $ht = new rcube_html2text($html, false, false);
        $res = $ht->get_text();

        $this->assertContains('>> INNER 1', $res, 'Quote inner');
        $this->assertContains('>> INNER 3', $res, 'Quote inner');
        $this->assertContains('> OUTER END', $res, 'Quote outer');
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
        $ht = new rcube_html2text($html, false, false);
        $res = $ht->get_text();

        $this->assertContains('QUOTED TEXT NO END TAG FOUND', $res, 'No quoating on invalid html');

        // with some (nested) end tags
        $html = <<<EOF
Begin<br>
<blockquote>QUOTED TEXT
<blockquote>INNER 1</blockquote>
<blockquote>INNER 2</blockquote>
NO END TAG FOUND
EOF;
        $ht = new rcube_html2text($html, false, false);
        $res = $ht->get_text();

        $this->assertContains('QUOTED TEXT INNER 1 INNER 2 NO END', $res, 'No quoating on invalid html');
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
     * Test <a> links handling when not using link list (#5795)
     *
     * @dataProvider data_links_no_list
     */
    function test_links_no_list($input, $output)
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
}

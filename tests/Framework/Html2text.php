<?php

/**
 * Test class to test rcube_html2text class
 *
 * @package Tests
 */
class rc_html2text extends PHPUnit_Framework_TestCase
{

    function data_html2text()
    {
        return array(
            0 => array(
                'title' => 'Test entry',
                'in'    => '',
                'out'   => '',
            ),
            1 => array(
                'title' => 'Basic HTML entities',
                'in'    => '&quot;&amp;',
                'out'   => '"&',
            ),
            2 => array(
                'title' => 'HTML entity string',
                'in'    => '&amp;quot;',
                'out'   => '&quot;',
            ),
            3 => array(
                'title' => 'HTML entity in STRONG tag',
                'in'    => '<strong>&#347;</strong>', // ś
                'out'   => 'Ś', // upper ś
            ),
            4 => array(
                'title' => 'STRONG tag to upper-case conversion',
                'in'    => '<strong>ś</strong>',
                'out'   => 'Ś',
            ),
            5 => array(
                'title' => 'STRONG inside B tag',
                'in'    => '<b><strong>&#347;</strong></b>',
                'out'   => 'Ś',
            ),
        );
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
     *
     */
    function test_multiple_blockquotes()
    {
        $html = <<<EOF
<br>Begin<br><blockquote>OUTER BEGIN<blockquote>INNER 1<br></blockquote><div><br></div><div>Par 1</div>
<blockquote>INNER 2</blockquote><div><br></div><div>Par 2</div>
<div><br></div><div>Par 3</div><div><br></div>
<blockquote>INNER 3</blockquote>OUTER END</blockquote>
EOF;
        $ht = new rcube_html2text($html, false, false);
        $res = $ht->get_text();

        $this->assertContains('>> INNER 1', $res, 'Quote inner');
        $this->assertContains('>> INNER 3', $res, 'Quote inner');
        $this->assertContains('> OUTER END', $res, 'Quote outer');
    }
}

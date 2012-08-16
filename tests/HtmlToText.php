<?php

/**
 * Test class to test html2text class
 *
 * @package Tests
 */
class HtmlToText extends PHPUnit_Framework_TestCase
{

    function data()
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
     * @dataProvider data
     */
    function test_html2text($title, $in, $out)
    {
        $ht = new html2text(null, false, false);

        $ht->set_html($in);
        $res = $ht->get_text();

        $this->assertEquals($out, $res, $title);
    }
}

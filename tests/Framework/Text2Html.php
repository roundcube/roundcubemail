<?php

/**
 * Test class to test rcube_text2html class
 *
 * @package Tests
 */
class Framework_Text2Html extends PHPUnit_Framework_TestCase
{

    /**
     * Data for test_text2html()
     */
    function data_text2html()
    {
        $options = array(
            'begin'  => '',
            'end'    => '',
            'break'  => '<br>',
            'links'  => false,
            'flowed' => false,
            'wrap'   => false,
            'space'  => '_', // replace UTF-8 non-breaking space for simpler testing
        );

        $data[] = array(" aaaa", "_aaaa", $options);
        $data[] = array("aaaa aaaa", "aaaa_aaaa", $options);
        $data[] = array("aaaa  aaaa", "aaaa__aaaa", $options);
        $data[] = array("aaaa   aaaa", "aaaa___aaaa", $options);
        $data[] = array("aaaa\taaaa", "aaaa____aaaa", $options);
        $data[] = array("aaaa\naaaa", "aaaa<br>aaaa", $options);
        $data[] = array("aaaa\n aaaa", "aaaa<br>_aaaa", $options);
        $data[] = array("aaaa\n  aaaa", "aaaa<br>__aaaa", $options);
        $data[] = array("aaaa\n   aaaa", "aaaa<br>___aaaa", $options);
        $data[] = array("\taaaa", "____aaaa", $options);
        $data[] = array("\naaaa", "<br>aaaa", $options);
        $data[] = array("\n aaaa", "<br>_aaaa", $options);
        $data[] = array("\n  aaaa", "<br>__aaaa", $options);
        $data[] = array("\n   aaaa", "<br>___aaaa", $options);
        $data[] = array("aaaa\n\nbbbb", "aaaa<br><br>bbbb", $options);
        $data[] = array(">aaaa \n>aaaa", "<blockquote>aaaa_<br>aaaa</blockquote>", $options);
        $data[] = array(">aaaa\n>aaaa", "<blockquote>aaaa<br>aaaa</blockquote>", $options);
        $data[] = array(">aaaa \n>bbbb\ncccc dddd", "<blockquote>aaaa_<br>bbbb</blockquote>cccc_dddd", $options);

        $options['flowed'] = true;

        $data[] = array(" aaaa", "aaaa", $options);
        $data[] = array("aaaa aaaa", "aaaa_aaaa", $options);
        $data[] = array("aaaa  aaaa", "aaaa__aaaa", $options);
        $data[] = array("aaaa   aaaa", "aaaa___aaaa", $options);
        $data[] = array("aaaa\taaaa", "aaaa____aaaa", $options);
        $data[] = array("aaaa\naaaa", "aaaa<br>aaaa", $options);
        $data[] = array("aaaa\n aaaa", "aaaa<br>aaaa", $options);
        $data[] = array("aaaa\n  aaaa", "aaaa<br>_aaaa", $options);
        $data[] = array("aaaa\n   aaaa", "aaaa<br>__aaaa", $options);
        $data[] = array("\taaaa", "____aaaa", $options);
        $data[] = array("\naaaa", "<br>aaaa", $options);
        $data[] = array("\n aaaa", "<br>aaaa", $options);
        $data[] = array("\n  aaaa", "<br>_aaaa", $options);
        $data[] = array("\n   aaaa", "<br>__aaaa", $options);
        $data[] = array("aaaa\n\nbbbb", "aaaa<br><br>bbbb", $options);
        $data[] = array(">aaaa \n>aaaa", "<blockquote>aaaa aaaa</blockquote>", $options);
        $data[] = array(">aaaa\n>aaaa", "<blockquote>aaaa<br>aaaa</blockquote>", $options);
        $data[] = array(">aaaa \n>bbbb\ncccc dddd", "<blockquote>aaaa bbbb</blockquote>cccc_dddd", $options);

        return $data;
    }

    /**
     * Test text to html conversion
     *
     * @dataProvider data_text2html
     */
    function test_text2html($input, $output, $options)
    {
        $t2h = new rcube_text2html($input, false, $options);

        $html = $t2h->get_html();

        $this->assertEquals($output, $html);
    }
}

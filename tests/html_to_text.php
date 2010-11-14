<?php

/**
 * Test class to test html2text class
 *
 * @package Tests
 */
class rcube_test_html2text extends UnitTestCase
{

    function __construct()
    {
        $this->UnitTestCase("HTML-to-Text conversion tests");

    }

    function test_html2text()
    {
        $data = array(
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
        );

        $ht = new html2text(null, false, false);

        foreach ($data as $item) {
            $ht->set_html($item['in']);
            $res = $ht->get_text();
            $this->assertEqual($item['out'], $res, $item['title']);
        }
    }

}

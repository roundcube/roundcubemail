<?php

/**
 * Test class to test rcube_html class
 *
 * @package Tests
 */
class Framework_Html extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new html;

        $this->assertInstanceOf('html', $object, "Class constructor");
    }

    /**
     * Data for test_quote()
     */
    function data_quote()
    {
        return array(
            array('abc', 'abc'),
            array('?', '?'),
            array('"', '&quot;'),
            array('<', '&lt;'),
            array('>', '&gt;'),
            array('&', '&amp;'),
            array('&amp;', '&amp;amp;'),
        );
    }

    /**
     * Test for quote()
     * @dataProvider data_quote
     */
    function test_quote($str, $result)
    {
        $this->assertEquals(html::quote($str), $result);
    }
}

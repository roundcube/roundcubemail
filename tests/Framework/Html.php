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
     * Data for test_attrib_string()
     */
    function data_attrib_string()
    {
        return array(
            array(
                array(), null, '',
            ),
            array(
                array('test' => 'test'), null, ' test="test"',
            ),
            array(
                array('test' => 'test'), array('test'), ' test="test"',
            ),
            array(
                array('test' => 'test'), array('other'), '',
            ),
            array(
                array('checked' => true), null, ' checked="checked"',
            ),
            array(
                array('checked' => ''), null, '',
            ),
            array(
                array('onclick' => ''), null, '',
            ),
            array(
                array('size' => 5), null, ' size="5"',
            ),
            array(
                array('size' => 'test'), null, '',
            ),
            array(
                array('data-test' => 'test'), null, ' data-test="test"',
            ),
        );
    }

    /**
     * Test for attrib_string()
     * @dataProvider data_attrib_string
     */
    function test_attrib_string($arg1, $arg2, $result)
    {
        $this->assertEquals(html::attrib_string($arg1, $arg2), $result);
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

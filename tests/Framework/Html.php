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
    function test_attrib_string($arg1, $arg2, $expected)
    {
        $this->assertEquals($expected, html::attrib_string($arg1, $arg2));
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
    function test_quote($str, $expected)
    {
        $this->assertEquals($expected, html::quote($str));
    }

    /**
     * Data for test_parse_attrib_string()
     */
    function data_parse_attrib_string()
    {
        return array(
            array(
                '',
                array(),
            ),
            array(
                'test="test1-val"',
                array('test' => 'test1-val'),
            ),
            array(
                'test1="test1-val"    test2=test2-val',
                array('test1' => 'test1-val', 'test2' => 'test2-val'),
            ),
            array(
                '   test1="test1\'val"    test2=\'test2"val\'   ',
                array('test1' => 'test1\'val', 'test2' => 'test2"val'),
            ),
            array(
                'expression="test == true ? \' test\' : \'\'" ',
                array('expression' => 'test == true ? \' test\' : \'\''),
            ),
            array(
                'href="http://domain.tld/страница"',
                array('href' => 'http://domain.tld/страница'),
            ),
        );
    }

    /**
     * Test for parse_attrib_string()
     * @dataProvider data_parse_attrib_string
     */
    function test_parse_attrib_string($arg1, $expected)
    {
        $this->assertEquals($expected, html::parse_attrib_string($arg1));
    }
}

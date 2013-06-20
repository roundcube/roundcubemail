<?php

/**
 * Test class to test rcube_enriched class
 *
 * @package Tests
 */
class Framework_Enriched extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_enriched();

        $this->assertInstanceOf('rcube_enriched', $object, "Class constructor");
    }

    /**
     * Test to_html()
     */
    function test_to_html()
    {
        $enriched = '<bold><italic>the-text</italic></bold>';
        $expected = '<b><i>the-text</i></b>';
        $result   = rcube_enriched::to_html($enriched);

        $this->assertSame($expected, $result);
    }

    /**
     * Data for test_formatting()
     */
    function data_formatting()
    {
        return array(
            array('<bold>', '<b>'),
            array('</bold>', '</b>'),
            array('<italic>', '<i>'),
            array('</italic>', '</i>'),
            array('<fixed>', '<tt>'),
            array('</fixed>', '</tt>'),
            array('<smaller>', '<font size=-1>'),
            array('</smaller>', '</font>'),
            array('<bigger>', '<font size=+1>'),
            array('</bigger>', '</font>'),
            array('<underline>', '<span style="text-decoration: underline">'),
            array('</underline>', '</span>'),
            array('<flushleft>', '<span style="text-align: left">'),
            array('</flushleft>', '</span>'),
            array('<flushright>', '<span style="text-align: right">'),
            array('</flushright>', '</span>'),
            array('<flushboth>', '<span style="text-align: justified">'),
            array('</flushboth>', '</span>'),
            array('<indent>', '<span style="padding-left: 20px">'),
            array('</indent>', '</span>'),
            array('<indentright>', '<span style="padding-right: 20px">'),
            array('</indentright>', '</span>'),
        );
    }

    /**
     * Test formatting conversion
     * @dataProvider data_formatting
     */
    function test_formatting($enriched, $expected)
    {
        $result = rcube_enriched::to_html($enriched);

        $this->assertSame($expected, $result);
    }
}

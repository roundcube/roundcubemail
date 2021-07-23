<?php

/**
 * Test class to test rcube_enriched class
 *
 * @package Tests
 */
class Framework_Enriched extends PHPUnit\Framework\TestCase
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
        return [
            ['<bold>', '<b>'],
            ['</bold>', '</b>'],
            ['<italic>', '<i>'],
            ['</italic>', '</i>'],
            ['<fixed>', '<tt>'],
            ['</fixed>', '</tt>'],
            ['<smaller>', '<font size=-1>'],
            ['</smaller>', '</font>'],
            ['<bigger>', '<font size=+1>'],
            ['</bigger>', '</font>'],
            ['<underline>', '<span style="text-decoration: underline">'],
            ['</underline>', '</span>'],
            ['<flushleft>', '<span style="text-align: left">'],
            ['</flushleft>', '</span>'],
            ['<flushright>', '<span style="text-align: right">'],
            ['</flushright>', '</span>'],
            ['<flushboth>', '<span style="text-align: justified">'],
            ['</flushboth>', '</span>'],
            ['<indent>', '<span style="padding-left: 20px">'],
            ['</indent>', '</span>'],
            ['<indentright>', '<span style="padding-right: 20px">'],
            ['</indentright>', '</span>'],
        ];
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

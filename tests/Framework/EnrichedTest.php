<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_enriched class
 */
class EnrichedTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_enriched();

        $this->assertInstanceOf(\rcube_enriched::class, $object, 'Class constructor');
    }

    /**
     * Test to_html()
     */
    public function test_to_html()
    {
        $enriched = '<bold><italic>the-text</italic></bold>';
        $expected = '<b><i>the-text</i></b>';
        $result = \rcube_enriched::to_html($enriched);

        $this->assertSame($expected, $result);
    }

    /**
     * Data for test_formatting()
     */
    public static function provide_formatting_cases(): iterable
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
     *
     * @dataProvider provide_formatting_cases
     */
    #[DataProvider('provide_formatting_cases')]
    public function test_formatting($enriched, $expected)
    {
        $result = \rcube_enriched::to_html($enriched);

        $this->assertSame($expected, $result);
    }
}

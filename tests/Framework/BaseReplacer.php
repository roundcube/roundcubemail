<?php

/**
 * Test class to test rcube_base_replacer class
 *
 * @package Tests
 */
class Framework_BaseReplacer extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_base_replacer('test');

        $this->assertInstanceOf('rcube_base_replacer', $object, "Class constructor");
    }

    /**
     * Test replace()
     */
    function test_replace()
    {
        $base = 'http://thisshouldntbetheurl.bob.com/';
        $html = '<A href=http://shouldbethislink.com>Test URL</A>';

        $replacer = new rcube_base_replacer($base);
        $response = $replacer->replace($html);

        $this->assertSame('<A href="http://shouldbethislink.com">Test URL</A>', $response);
    }

    /**
     * Data for absolute_url() test
     */
    function data_absolute_url()
    {
        return [
            ['', 'http://test', 'http://test/'],
            ['http://test', 'http://anything', 'http://test'],
            ['cid:test', 'http://anything', 'cid:test'],
            ['/test', 'http://test', 'http://test/test'],
            ['./test', 'http://test', 'http://test/test'],
            ['../test1', 'http://test/test2', 'http://test1'],
            ['../test1', 'http://test/test2/', 'http://test/test1'],
        ];
    }

    /**
     * Test absolute_url()
     * @dataProvider data_absolute_url
     */
    function test_absolute_url($path, $base, $expected)
    {
        $replacer = new rcube_base_replacer('test');
        $result   = $replacer->absolute_url($path, $base);

        $this->assertSame($expected, $result);
    }
}

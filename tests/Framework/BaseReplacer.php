<?php

/**
 * Test class to test rcube_base_replacer class
 *
 * @package Tests
 */
class Framework_BaseReplacer extends PHPUnit_Framework_TestCase
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
}

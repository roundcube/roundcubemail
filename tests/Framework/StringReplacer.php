<?php

/**
 * Test class to test rcube_string_replacer class
 *
 * @package Tests
 */
class Framework_StringReplacer extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $sr = new rcube_string_replacer;

        $this->assertInstanceOf('rcube_string_replacer', $sr, "Class constructor");
    }

    /**
     * Data for test_replace()
     */
    function data_replace()
    {
        return array(
            array('http://domain.tld/path*path2', '<a href="http://domain.tld/path*path2" target="_blank">http://domain.tld/path*path2</a>'),
            array('www.domain.tld', '<a href="http://www.domain.tld" target="_blank">www.domain.tld</a>'),
            array('WWW.DOMAIN.TLD', '<a href="http://WWW.DOMAIN.TLD" target="_blank">WWW.DOMAIN.TLD</a>'),
        );
    }

    /**
     * @dataProvider data_replace
     */
    function test_replace($input, $output)
    {
        $replacer = new rcube_string_replacer;
        $result = $replacer->replace($input);
        $result = $replacer->resolve($result);

        $this->assertEquals($output, $result);
    }
}

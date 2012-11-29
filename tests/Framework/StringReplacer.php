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
            array("Click this link:\nhttps://mail.xn--brderli-o2a.ch/rc/ EOF", "Click this link:\n<a href=\"https://mail.xn--brderli-o2a.ch/rc/\" target=\"_blank\">https://mail.xn--brderli-o2a.ch/rc/</a> EOF"),
            array('Start http://localhost/?foo End', 'Start <a href="http://localhost/?foo" target="_blank">http://localhost/?foo</a> End'),
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

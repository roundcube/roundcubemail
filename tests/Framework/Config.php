<?php

/**
 * Test class to test rcube_config class
 *
 * @package Tests
 */
class Framework_Config extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_config();

        $this->assertInstanceOf('rcube_config', $object, "Class constructor");
    }

    /**
     * Test resolve_timezone_alias()
     */
    function test_resolve_timezone_alias()
    {
        $this->assertSame('UTC', rcube_config::resolve_timezone_alias('Etc/GMT'));
        $this->assertSame('UTC', rcube_config::resolve_timezone_alias('Etc/Zulu'));
    }
}

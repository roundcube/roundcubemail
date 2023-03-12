<?php

/**
 * Test class to test rcube_config class
 *
 * @package Tests
 */
class Framework_Config extends PHPUnit\Framework\TestCase
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

    /**
     * Test get() and set()
     */
    function test_get_and_set()
    {
        $object = new rcube_config();

        $this->assertSame(null, $object->get('test'));
        $this->assertSame('def', $object->get('test', 'def'));

        $object->set('test', 'val');

        $this->assertSame('val', $object->get('test'));

        putenv('ROUNDCUBE_TEST_INT=4190');

        $this->assertSame(4190, $object->get('test_int'));

        // TODO: test more code paths in get() and set()
    }

    /**
     * Test guess_type()
     */
    function test_guess_type()
    {
        $object = new rcube_config();

        $this->assertSame('bool', invokeMethod($object, 'guess_type', ['true']));
        $this->assertSame('bool', invokeMethod($object, 'guess_type', ['false']));
        $this->assertSame('bool', invokeMethod($object, 'guess_type', ['t']));
        $this->assertSame('bool', invokeMethod($object, 'guess_type', ['f']));
        $this->assertSame('bool', invokeMethod($object, 'guess_type', ['TRUE']));
        $this->assertSame('bool', invokeMethod($object, 'guess_type', ['FALSE']));
        $this->assertSame('bool', invokeMethod($object, 'guess_type', ['T']));
        $this->assertSame('bool', invokeMethod($object, 'guess_type', ['F']));

        $this->assertSame('float', invokeMethod($object, 'guess_type', ['1.5']));
        $this->assertSame('float', invokeMethod($object, 'guess_type', ['1.0']));
        $this->assertSame('float', invokeMethod($object, 'guess_type', ['1.2e3']));
        $this->assertSame('float', invokeMethod($object, 'guess_type', ['7E-10']));

        $this->assertSame('int', invokeMethod($object, 'guess_type', ['1']));
        $this->assertSame('int', invokeMethod($object, 'guess_type', ['123456789']));

        $this->assertSame('string', invokeMethod($object, 'guess_type', ['ON']));
        $this->assertSame('string', invokeMethod($object, 'guess_type', ['1-0']));
    }

    /**
     * Test parse_env()
     */
    function test_parse_env()
    {
        $object = new rcube_config();

        $this->assertSame(true, invokeMethod($object, 'parse_env', ['true']));
        $this->assertSame(1, invokeMethod($object, 'parse_env', ['1']));
        $this->assertSame(1.5, invokeMethod($object, 'parse_env', ['1.5']));
        $this->assertSame(true, invokeMethod($object, 'parse_env', ['1', 'bool']));
        $this->assertSame(1.0, invokeMethod($object, 'parse_env', ['1', 'float']));
        $this->assertSame(1, invokeMethod($object, 'parse_env', ['1', 'int']));
        $this->assertSame('1', invokeMethod($object, 'parse_env', ['1', 'string']));
        $this->assertSame([1], invokeMethod($object, 'parse_env', ['[1]', 'array']));
        $this->assertSame(['test' => 1], (array) invokeMethod($object, 'parse_env', ['{"test":1}', 'object']));
    }
}

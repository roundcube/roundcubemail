<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_config class
 */
class Framework_Config extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_config();

        self::assertInstanceOf('rcube_config', $object, 'Class constructor');
    }

    /**
     * Test resolve_timezone_alias()
     */
    public function test_resolve_timezone_alias()
    {
        self::assertSame('UTC', rcube_config::resolve_timezone_alias('Etc/GMT'));
        self::assertSame('UTC', rcube_config::resolve_timezone_alias('Etc/Zulu'));
    }

    /**
     * Test get() and set()
     */
    public function test_get_and_set()
    {
        $object = new rcube_config();

        self::assertNull($object->get('test'));
        self::assertSame('def', $object->get('test', 'def'));

        $object->set('test', 'val');

        self::assertSame('val', $object->get('test'));

        putenv('ROUNDCUBE_TEST_INT=4190');

        self::assertSame(4190, $object->get('test_int'));

        // TODO: test more code paths in get() and set()
    }

    /**
     * Test guess_type()
     */
    public function test_guess_type()
    {
        $object = new rcube_config();

        self::assertSame('bool', invokeMethod($object, 'guess_type', ['true']));
        self::assertSame('bool', invokeMethod($object, 'guess_type', ['false']));
        self::assertSame('bool', invokeMethod($object, 'guess_type', ['t']));
        self::assertSame('bool', invokeMethod($object, 'guess_type', ['f']));
        self::assertSame('bool', invokeMethod($object, 'guess_type', ['TRUE']));
        self::assertSame('bool', invokeMethod($object, 'guess_type', ['FALSE']));
        self::assertSame('bool', invokeMethod($object, 'guess_type', ['T']));
        self::assertSame('bool', invokeMethod($object, 'guess_type', ['F']));

        self::assertSame('float', invokeMethod($object, 'guess_type', ['1.5']));
        self::assertSame('float', invokeMethod($object, 'guess_type', ['1.0']));
        self::assertSame('float', invokeMethod($object, 'guess_type', ['1.2e3']));
        self::assertSame('float', invokeMethod($object, 'guess_type', ['7E-10']));

        self::assertSame('int', invokeMethod($object, 'guess_type', ['1']));
        self::assertSame('int', invokeMethod($object, 'guess_type', ['123456789']));

        self::assertSame('string', invokeMethod($object, 'guess_type', ['ON']));
        self::assertSame('string', invokeMethod($object, 'guess_type', ['1-0']));
    }

    /**
     * Test parse_env()
     */
    public function test_parse_env()
    {
        $object = new rcube_config();

        self::assertTrue(invokeMethod($object, 'parse_env', ['true']));
        self::assertSame(1, invokeMethod($object, 'parse_env', ['1']));
        self::assertSame(1.5, invokeMethod($object, 'parse_env', ['1.5']));
        self::assertTrue(invokeMethod($object, 'parse_env', ['1', 'bool']));
        self::assertSame(1.0, invokeMethod($object, 'parse_env', ['1', 'float']));
        self::assertSame(1, invokeMethod($object, 'parse_env', ['1', 'int']));
        self::assertSame('1', invokeMethod($object, 'parse_env', ['1', 'string']));
        self::assertSame([1], invokeMethod($object, 'parse_env', ['[1]', 'array']));
        self::assertSame(['test' => 1], (array) invokeMethod($object, 'parse_env', ['{"test":1}', 'object']));
    }
}

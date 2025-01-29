<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\invokeMethod;

/**
 * Test class to test rcube_config class
 */
class ConfigTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_config();

        $this->assertInstanceOf(\rcube_config::class, $object, 'Class constructor');
    }

    /**
     * Test resolve_timezone_alias()
     */
    public function test_resolve_timezone_alias()
    {
        $this->assertSame('UTC', \rcube_config::resolve_timezone_alias('Etc/GMT'));
        $this->assertSame('UTC', \rcube_config::resolve_timezone_alias('Etc/Zulu'));
    }

    /**
     * Test get() and set()
     */
    public function test_get_and_set()
    {
        $object = new \rcube_config();

        $this->assertNull($object->get('test'));
        $this->assertSame('def', $object->get('test', 'def'));

        $object->set('test', 'val');

        $this->assertSame('val', $object->get('test'));

        putenv('ROUNDCUBE_TEST_INT=4190');

        $this->assertSame(4190, $object->get('test_int'));

        // If the configured value is `null`, expect the fallback value (which is `null` by default).
        $object->set('test', null);
        $this->assertNull($object->get('test'));
        $this->assertSame('fallback', $object->get('test', 'fallback'));

        // If the configured value is `false`, expect `false`, regardless of the fallback value.
        $object->set('test', false);
        $this->assertFalse($object->get('test'));
        $this->assertFalse($object->get('test', 'wrong'));

        // TODO: test more code paths in get() and set()
    }

    /**
     * Test guess_type()
     */
    public function test_guess_type()
    {
        $object = new \rcube_config();

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
    public function test_parse_env()
    {
        $object = new \rcube_config();

        $this->assertTrue(invokeMethod($object, 'parse_env', ['true']));
        $this->assertSame(1, invokeMethod($object, 'parse_env', ['1']));
        $this->assertSame(1.5, invokeMethod($object, 'parse_env', ['1.5']));
        $this->assertTrue(invokeMethod($object, 'parse_env', ['1', 'bool']));
        $this->assertSame(1.0, invokeMethod($object, 'parse_env', ['1', 'float']));
        $this->assertSame(1, invokeMethod($object, 'parse_env', ['1', 'int']));
        $this->assertSame('1', invokeMethod($object, 'parse_env', ['1', 'string']));
        $this->assertSame([1], invokeMethod($object, 'parse_env', ['[1]', 'array']));
        $this->assertSame(['test' => 1], (array) invokeMethod($object, 'parse_env', ['{"test":1}', 'object']));
    }

    // Test if values in defaults.inc.php and values in rcube_config::DEFAULTS match
    public function test_defaults_values(): void
    {
        // Initialize the variable to make the static analysis happy.
        $config = null;
        // Load the values from defaults.inc.php manually (we don't want to
        // test the whole loading mechanics of `rcube_config` here).
        ob_start();
        require realpath(RCUBE_INSTALL_PATH . 'config/defaults.inc.php');
        ob_end_clean();

        $this->assertIsArray($config);

        foreach (\rcube_config::DEFAULTS as $name => $hardcoded_value) {
            $this->assertSame($hardcoded_value, $config[$name], "The value for '{$name}' in defaults.inc.php does not match the hardcoded default in rcube_config!");
        }
    }
}

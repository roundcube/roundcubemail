<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_plugin_api class
 */
class PluginApiTest extends TestCase
{
    /**
     * Test get_info()
     */
    public function test_get_info()
    {
        $api = \rcube_plugin_api::get_instance();

        $info = $api->get_info('acl');

        $this->assertSame('roundcube', $info['vendor']);
        $this->assertSame('acl', $info['name']);
        $this->assertSame([], $info['require']);
        $this->assertSame('GPL-3.0+', $info['license']);
    }

    /**
     * Test hooks registration, execution and unregistration
     */
    public function test_hooks()
    {
        $api = \rcube_plugin_api::get_instance();

        $var = 0;
        $hook_handler = static function ($args) use (&$var) { $var++; };

        $api->register_hook('test', $hook_handler);

        $api->exec_hook('test', []);

        $this->assertSame(1, $var);

        $api->unregister_hook('test', $hook_handler);

        $api->exec_hook('test', []);

        $this->assertSame(1, $var);
        $this->assertFalse($api->is_processing());
    }

    /**
     * Test tasks registration
     */
    public function test_tasks()
    {
        $api = \rcube_plugin_api::get_instance();

        $this->assertTrue($api->register_task('test', 'test'));
        $this->assertTrue($api->is_plugin_task('test'));
    }
}

<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_plugin_api class
 */
class Framework_PluginApi extends TestCase
{
    /**
     * Test get_info()
     */
    public function test_get_info()
    {
        $api = rcube_plugin_api::get_instance();

        $info = $api->get_info('acl');

        self::assertSame('roundcube', $info['vendor']);
        self::assertSame('acl', $info['name']);
        self::assertSame([], $info['require']);
        self::assertSame('GPL-3.0+', $info['license']);
    }

    /**
     * Test hooks registration, execution and unregistration
     */
    public function test_hooks()
    {
        $api = rcube_plugin_api::get_instance();

        $var = 0;
        $hook_handler = static function ($args) use (&$var) { $var++; };

        $api->register_hook('test', $hook_handler);

        $api->exec_hook('test', []);

        self::assertSame(1, $var);

        $api->unregister_hook('test', $hook_handler);

        $api->exec_hook('test', []);

        self::assertSame(1, $var);
        self::assertFalse($api->is_processing());
    }

    /**
     * Test tasks registration
     */
    public function test_tasks()
    {
        $api = rcube_plugin_api::get_instance();

        self::assertTrue($api->register_task('test', 'test'));
        self::assertTrue($api->is_plugin_task('test'));
    }
}

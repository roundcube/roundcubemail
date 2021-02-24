<?php

/**
 * Test class to test rcube_plugin_api class
 *
 * @package Tests
 */
class Framework_PluginApi extends PHPUnit\Framework\TestCase
{
    /**
     * Test get_info()
     */
    function test_get_info()
    {
        $api = rcube_plugin_api::get_instance();

        $info = $api->get_info('acl');

        $this->assertSame('roundcube', $info['vendor']);
        $this->assertSame('acl', $info['name']);
        $this->assertSame([], $info['require']);
        $this->assertSame('GPL-3.0+', $info['license']);
    }

    /**
     * Test hooks registration, execution and unregistration
     */
    function test_hooks()
    {
        $api = rcube_plugin_api::get_instance();

        $var = 0;
        $hook_handler = function($args) use (&$var) { $var++; };

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
    function test_tasks()
    {
        $api = rcube_plugin_api::get_instance();

        $this->assertTrue($api->register_task('test', 'test'));
        $this->assertTrue($api->is_plugin_task('test'));
    }
}

<?php

class SubscriptionsOption_Plugin extends ActionTestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new subscriptions_option($rcube->plugins);

        self::assertInstanceOf('subscriptions_option', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test prefs_list() method
     */
    public function test_prefs_list()
    {
        $rcube = rcube::get_instance();
        $plugin = new subscriptions_option($rcube->plugins);

        html::$doctype = 'html5';

        $args = ['section' => 'server', 'blocks' => ['main' => ['options' => []]]];

        $result = $plugin->prefs_list($args);

        self::assertSame(
            '<label for="rcmfd_use_subscriptions">Use IMAP Subscriptions</label>',
            $result['blocks']['main']['options']['use_subscriptions']['title']
        );

        self::assertSame(
            '<input name="_use_subscriptions" id="rcmfd_use_subscriptions" value="1" checked type="checkbox">',
            $result['blocks']['main']['options']['use_subscriptions']['content']
        );
    }

    /**
     * Test prefs_save() method
     */
    public function test_prefs_save()
    {
        $rcube = rcube::get_instance();
        $plugin = new subscriptions_option($rcube->plugins);

        $_POST = ['_use_subscriptions' => 1];
        $args = ['section' => 'server', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        self::assertTrue($result['prefs']['use_subscriptions']);

        $storage = self::mockStorage()->registerFunction('clear_cache', true);

        $_POST = [];
        $args = ['section' => 'server', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        self::assertFalse($result['prefs']['use_subscriptions']);
        self::assertCount(1, $storage->methodCalls);
        self::assertSame('clear_cache', $storage->methodCalls[0]['name']);
        self::assertSame(['mailboxes'], $storage->methodCalls[0]['args']);
    }
}

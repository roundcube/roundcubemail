<?php

class SubscriptionsOption_Plugin extends ActionTestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../subscriptions_option.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new subscriptions_option($rcube->plugins);

        $this->assertInstanceOf('subscriptions_option', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test prefs_list() method
     */
    function test_prefs_list()
    {
        $rcube  = rcube::get_instance();
        $plugin = new subscriptions_option($rcube->plugins);

        html::$doctype = 'html5';

        $args = ['section' => 'server', 'blocks' => ['main' => ['options' => []]]];

        $result = $plugin->prefs_list($args);

        $this->assertSame(
            '<label for="rcmfd_use_subscriptions">Use IMAP Subscriptions</label>',
            $result['blocks']['main']['options']['use_subscriptions']['title']
        );

        $this->assertSame(
            '<input name="_use_subscriptions" id="rcmfd_use_subscriptions" value="1" checked type="checkbox">',
            $result['blocks']['main']['options']['use_subscriptions']['content']
        );
    }

    /**
     * Test prefs_save() method
     */
    function test_prefs_save()
    {
        self::initStorage();

        $rcube  = rcube::get_instance();
        $plugin = new subscriptions_option($rcube->plugins);

        $_POST = ['_use_subscriptions' => 1];
        $args  = ['section' => 'server', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertSame(true, $result['prefs']['use_subscriptions']);

        $storage = $rcube->storage;
        $storage->registerFunction('clear_cache', true);

        $_POST = [];
        $args  = ['section' => 'server', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertSame(false, $result['prefs']['use_subscriptions']);
        $this->assertCount(1, $storage->methodCalls);
        $this->assertSame('clear_cache', $storage->methodCalls[0]['name']);
        $this->assertSame(['mailboxes'], $storage->methodCalls[0]['args']);
    }
}

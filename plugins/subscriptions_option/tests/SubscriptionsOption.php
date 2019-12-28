<?php

class SubscriptionsOption_Plugin extends PHPUnit\Framework\TestCase
{

    function setUp()
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
}


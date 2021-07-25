<?php

class NewmailNotifier_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../newmail_notifier.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new newmail_notifier($rcube->plugins);

        $this->assertInstanceOf('newmail_notifier', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


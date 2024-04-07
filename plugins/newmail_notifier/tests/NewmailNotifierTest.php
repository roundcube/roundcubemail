<?php

use PHPUnit\Framework\TestCase;

class NewmailNotifier_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new newmail_notifier($rcube->plugins);

        self::assertInstanceOf('newmail_notifier', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

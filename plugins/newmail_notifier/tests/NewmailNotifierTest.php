<?php

use PHPUnit\Framework\TestCase;

class NewmailNotifier_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../newmail_notifier.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new newmail_notifier($rcube->plugins);

        $this->assertInstanceOf('newmail_notifier', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\getProperty;
use function Roundcube\Tests\invokeMethod;
use function Roundcube\Tests\setProperty;

class MarkasjunkTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \markasjunk($rcube->plugins);

        $this->assertInstanceOf('markasjunk', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test driver loading
     */
    public function test_init_driver()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \markasjunk($rcube->plugins);

        $drivers = ['amavis_blacklist', 'cmd_learn', 'dir_learn', 'edit_headers', 'email_learn',
            'jsevent', 'sa_blacklist', 'sa_detach',
        ];

        setProperty($plugin, 'rcube', $rcube);

        foreach ($drivers as $driver_name) {
            $rcube->config->set('markasjunk_learning_driver', $driver_name);

            invokeMethod($plugin, '_init_driver');

            $driver = getProperty($plugin, 'driver');
            $this->assertInstanceOf("markasjunk_{$driver_name}", $driver);
        }
    }

    /**
     * Test move decision in all-folders learning mode
     */
    public function test_move_decision()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \markasjunk($rcube->plugins);

        setProperty($plugin, 'spam_mbox', 'Junk');
        setProperty($plugin, 'ham_mbox', 'INBOX');

        // Spam is learned everywhere, but only moved when not already in Junk
        $this->assertTrue(invokeMethod($plugin, '_is_move_required', ['INBOX', true]));
        $this->assertTrue(invokeMethod($plugin, '_is_move_required', ['Archive', true]));
        $this->assertFalse(invokeMethod($plugin, '_is_move_required', ['Junk', true]));

        // Ham is only moved from Junk
        $this->assertTrue(invokeMethod($plugin, '_is_move_required', ['Junk', false]));
        $this->assertFalse(invokeMethod($plugin, '_is_move_required', ['INBOX', false]));
        $this->assertFalse(invokeMethod($plugin, '_is_move_required', ['Archive', false]));
    }

    /**
     * Test messages selected for moving in legacy and all-folders modes
     */
    public function test_move_messageset()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \markasjunk($rcube->plugins);

        setProperty($plugin, 'spam_mbox', 'Junk');
        setProperty($plugin, 'ham_mbox', 'INBOX');

        $messageset = [
            'INBOX' => [1, 2],
            'Junk' => [3],
            'Archive' => [4],
        ];

        // Legacy mode: move everything unless already in the destination
        $this->assertSame($messageset, invokeMethod($plugin, '_get_move_messageset', [$messageset, true, 'INBOX', true, false]));
        $this->assertSame([], invokeMethod($plugin, '_get_move_messageset', [$messageset, true, 'Junk', false, false]));

        // All-folders mode: only move messages that need to change mailbox
        $this->assertSame([
            'INBOX' => [1, 2],
            'Archive' => [4],
        ], invokeMethod($plugin, '_get_move_messageset', [$messageset, true, 'INBOX', true, true]));
        $this->assertSame([
            'Junk' => [3],
        ], invokeMethod($plugin, '_get_move_messageset', [$messageset, false, 'INBOX', true, true]));
    }
}

<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;
use Roundcube\Tests\StorageMock;

use function Roundcube\Tests\invokeMethod;
use function Roundcube\Tests\setProperty;

class ArchiveTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \archive($rcube->plugins);

        $this->assertInstanceOf('archive', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();
    }

    /**
     * Test prefs_table() method
     */
    public function test_prefs_table()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \archive($rcube->plugins);

        $args = ['section' => 'server', 'blocks' => ['main' => ['options' => []]]];

        $result = $plugin->prefs_table($args);

        $this->assertSame(
            '<label for="ff_read_on_archive">Mark the message as read on archive</label>',
            $result['blocks']['main']['options']['read_on_archive']['title']
        );

        $this->assertSame(
            '<input name="_read_on_archive" id="ff_read_on_archive" value="1" type="checkbox">',
            $result['blocks']['main']['options']['read_on_archive']['content']
        );

        // TODO: section=folders
    }

    /**
     * Test prefs_save() method
     */
    public function test_prefs_save()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \archive($rcube->plugins);

        $_POST = [];
        $args = ['section' => 'folders', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertSame('', $result['prefs']['archive_type']);

        $_POST = ['_archive_type' => 'aaa'];
        $args = ['section' => 'folders', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertSame('aaa', $result['prefs']['archive_type']);

        $_POST = [];
        $args = ['section' => 'server', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertFalse($result['prefs']['read_on_archive']);

        $_POST = ['_read_on_archive' => 1];
        $args = ['section' => 'server', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertTrue($result['prefs']['read_on_archive']);
    }

    /**
     * Test move_messages_worker() with the select-all ('*') set of UIDs (#10107).
     *
     * When "select all" is used, $uids is the string '*'. On PHP 8 calling
     * count() on it throws a TypeError, causing a fatal error while archiving.
     * The worker must resolve '*' to the real UID list and report the actual
     * number of moved messages.
     */
    public function test_move_messages_worker_select_all()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \archive($rcube->plugins);

        // '*' is resolved via $storage->index()->get() to the real UID list
        $index = new class {
            public function get()
            {
                return ['1', '2', '3'];
            }
        };

        // storage mock that resolves '*' and confirms the move/flag operations
        $storage = new StorageMock();
        $storage->registerFunction('index', $index);
        $storage->registerFunction('set_flag', true);
        $storage->registerFunction('move_message', true);
        $rcube->storage = $storage; // @phpstan-ignore-line

        // move_messages_worker() writes into the private $result property
        setProperty($plugin, 'result', [
            'reload' => false,
            'error' => false,
            'sources' => [],
            'destinations' => [],
        ], \archive::class);

        // must not throw a TypeError on count('*') and must report the real count
        $count = invokeMethod($plugin, 'move_messages_worker', ['*', 'INBOX', 'Archive', true], \archive::class);

        $this->assertSame(3, $count);

        $result = \Roundcube\Tests\getProperty($plugin, 'result', \archive::class);

        $this->assertFalse($result['error']);
        $this->assertSame(['INBOX'], $result['sources']);
        $this->assertSame(['Archive'], $result['destinations']);
    }
}

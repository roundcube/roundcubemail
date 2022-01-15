<?php

class Archive_Plugin extends PHPUnit\Framework\TestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../archive.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new archive($rcube->plugins);

        $this->assertInstanceOf('archive', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();
    }

    /**
     * Test prefs_table() method
     */
    function test_prefs_table()
    {
        $rcube  = rcube::get_instance();
        $plugin = new archive($rcube->plugins);

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
    function test_prefs_save()
    {
        $rcube  = rcube::get_instance();
        $plugin = new archive($rcube->plugins);

        $_POST = [];
        $args  = ['section' => 'folders', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertSame('', $result['prefs']['archive_type']);

        $_POST = ['_archive_type' => 'aaa'];
        $args  = ['section' => 'folders', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertSame('aaa', $result['prefs']['archive_type']);

        $_POST = [];
        $args  = ['section' => 'server', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertFalse($result['prefs']['read_on_archive']);

        $_POST = ['_read_on_archive' => 1];
        $args  = ['section' => 'server', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertTrue($result['prefs']['read_on_archive']);
    }
}

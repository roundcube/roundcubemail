<?php

class AttachmentReminder_Plugin extends PHPUnit\Framework\TestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../attachment_reminder.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new attachment_reminder($rcube->plugins);

        $this->assertInstanceOf('attachment_reminder', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();
    }

    /**
     * Test prefs_list() method
     */
    function test_prefs_list()
    {
        $rcube  = rcube::get_instance();
        $plugin = new attachment_reminder($rcube->plugins);

        $args = ['section' => 'compose', 'blocks' => ['main' => ['options' => []]]];

        $result = $plugin->prefs_list($args);

        $this->assertSame(
            '<label for="rcmfd_attachment_reminder">Remind about forgotten attachments</label>',
            $result['blocks']['main']['options']['attachment_reminder']['title']
        );
        $this->assertSame(
            '<input name="_attachment_reminder" id="rcmfd_attachment_reminder" value="1" type="checkbox">',
            $result['blocks']['main']['options']['attachment_reminder']['content']
        );
    }

    /**
     * Test prefs_save() method
     */
    function test_prefs_save()
    {
        $rcube  = rcube::get_instance();
        $plugin = new attachment_reminder($rcube->plugins);

        $_POST = [];
        $args  = ['section' => 'compose', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertFalse($result['prefs']['attachment_reminder']);

        $_POST = ['_attachment_reminder' => 1];
        $args  = ['section' => 'compose', 'prefs' => []];

        $result = $plugin->prefs_save($args);

        $this->assertTrue($result['prefs']['attachment_reminder']);
    }
}


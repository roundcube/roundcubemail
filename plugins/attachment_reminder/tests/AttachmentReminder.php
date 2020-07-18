<?php

class AttachmentReminder_Plugin extends PHPUnit\Framework\TestCase
{

    function setUp()
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
    }
}


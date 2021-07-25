<?php

class DatabaseAttachments_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../database_attachments.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new database_attachments($rcube->plugins);

        $this->assertInstanceOf('database_attachments', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


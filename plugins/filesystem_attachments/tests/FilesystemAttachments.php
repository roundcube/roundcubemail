<?php

use PHPUnit\Framework\TestCase;

class FilesystemAttachments_Plugin extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../filesystem_attachments.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new filesystem_attachments($rcube->plugins);

        $this->assertInstanceOf('filesystem_attachments', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

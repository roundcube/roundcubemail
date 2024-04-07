<?php

use PHPUnit\Framework\TestCase;

class FilesystemAttachments_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new filesystem_attachments($rcube->plugins);

        self::assertInstanceOf('filesystem_attachments', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

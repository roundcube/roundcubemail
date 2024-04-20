<?php

use PHPUnit\Framework\TestCase;

class DatabaseAttachments_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new database_attachments($rcube->plugins);

        $this->assertInstanceOf('database_attachments', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

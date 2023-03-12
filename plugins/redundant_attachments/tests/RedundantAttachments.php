<?php

class RedundantAttachments_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../redundant_attachments.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new redundant_attachments($rcube->plugins);

        $this->assertInstanceOf('redundant_attachments', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}


<?php

use PHPUnit\Framework\TestCase;

class RedundantAttachments_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \redundant_attachments($rcube->plugins);

        $this->assertInstanceOf('redundant_attachments', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

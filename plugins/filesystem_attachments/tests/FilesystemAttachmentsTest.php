<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class FilesystemAttachmentsTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \filesystem_attachments($rcube->plugins);

        $this->assertInstanceOf('filesystem_attachments', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

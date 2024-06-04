<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class RedundantAttachmentsTest extends TestCase
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

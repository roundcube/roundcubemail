<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class ZipdownloadTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \zipdownload($rcube->plugins);

        $this->assertInstanceOf('zipdownload', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

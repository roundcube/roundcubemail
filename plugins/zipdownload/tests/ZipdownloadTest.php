<?php

use PHPUnit\Framework\TestCase;

class Zipdownload_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new zipdownload($rcube->plugins);

        self::assertInstanceOf('zipdownload', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

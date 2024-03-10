<?php

class Acl_Plugin extends ActionTestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../acl.php';
    }

    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new acl($rcube->plugins);

        self::assertInstanceOf('acl', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();
    }
}

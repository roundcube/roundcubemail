<?php

class Acl_Plugin extends ActionTestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../acl.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new acl($rcube->plugins);

        $this->assertInstanceOf('acl', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();
    }
}


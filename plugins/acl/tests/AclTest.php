<?php

class Acl_Plugin extends ActionTestCase
{
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

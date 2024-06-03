<?php

namespace Roundcube\Mail\Plugins\Tests;

class AclTest extends \ActionTestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \acl($rcube->plugins);

        $this->assertInstanceOf('acl', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);

        $plugin->init();
    }
}

<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class KrbAuthenticationTest extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = \rcube::get_instance();
        $plugin = new \krb_authentication($rcube->plugins);

        $this->assertInstanceOf('krb_authentication', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

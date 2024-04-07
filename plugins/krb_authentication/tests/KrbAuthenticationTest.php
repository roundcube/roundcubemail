<?php

use PHPUnit\Framework\TestCase;

class KrbAuthentication_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new krb_authentication($rcube->plugins);

        self::assertInstanceOf('krb_authentication', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }
}

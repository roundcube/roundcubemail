<?php

class KrbAuthentication_Plugin extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../krb_authentication.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new krb_authentication($rcube->plugins);

        $this->assertInstanceOf('krb_authentication', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

<?php

/**
 * Test class to test rcmail_install class
 *
 * @package Tests
 */
class Rcmail_RcmailInstall extends ActionTestCase
{
    /**
     * Test getprop() method
     */
    function test_getprop()
    {
        $install = rcmail_install::get_instance();

        $this->assertSame('default', $install->getprop('unknown', 'default'));
        $this->assertSame('', $install->getprop('unknown'));
    }
}

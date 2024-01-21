<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_ldap class
 */
class Framework_Ldap extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        // skip test if Net_LDAP3 does not exist
        if (!class_exists('Net_LDAP3')) {
            $this->markTestSkipped('The Net_LDAP3 package not available.');
        }

        // skip test if php-ldap is not available
        if (!extension_loaded('ldap')) {
            $this->markTestSkipped('The ldap extension is not available.');
        }

        StderrMock::start();
        $object = new rcube_ldap([]);
        StderrMock::stop();

        $this->assertInstanceOf('rcube_ldap', $object, 'Class constructor');
        $this->assertSame('ERROR: Could not connect to any LDAP server', trim(StderrMock::$output));
    }
}

<?php

/**
 * Test class to test rcube_ldap class
 *
 * @package Tests
 */
class Framework_Ldap extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        // skip test if Net_LDAP3 does not exist
        if (!class_exists('Net_LDAP3')) {
            $this->markTestSkipped('The Net_LDAP3 package not available.');
        }

        // skip test if php-ldap is not available
        if (!extension_loaded('ldap')) {
            $this->markTestSkipped('The ldap extension is not available.');
        }

        StdErrMock::start();
        $object = new rcube_ldap([]);
        StdErrMock::stop();

        $this->assertInstanceOf('rcube_ldap', $object, "Class constructor");
        $this->assertSame('ERROR: Could not connect to any LDAP server', trim(StderrMock::$output));
    }
}

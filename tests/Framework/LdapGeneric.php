<?php

/**
 * Test class to test rcube_ldap_generic class
 *
 * @package Tests
 */
class Framework_LdapGeneric extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        // skip test if Net_LDAP3 does not exist
        if (!@class_exists('Net_LDAP3')) {
            $this->markTestSkipped('The Net_LDAP3 package not available.');
        }

        $object = new rcube_ldap_generic(array());

        $this->assertInstanceOf('rcube_ldap_generic', $object, "Class constructor");
    }
}

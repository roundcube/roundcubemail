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
        $object = new rcube_ldap_generic(array());

        $this->assertInstanceOf('rcube_ldap_generic', $object, "Class constructor");
    }
}

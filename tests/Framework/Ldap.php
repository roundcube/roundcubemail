<?php

/**
 * Test class to test rcube_ldap class
 *
 * @package Tests
 */
class Framework_Ldap extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        // skip this test as we don't want to connect to ldap here
        $this->markTestSkipped('We do not connect to LDAP');

        $object = new rcube_ldap(array());

        $this->assertInstanceOf('rcube_ldap', $object, "Class constructor");
    }
}

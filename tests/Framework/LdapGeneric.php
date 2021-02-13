<?php

/**
 * Test class to test rcube_ldap_generic class
 *
 * @package Tests
 */
class Framework_LdapGeneric extends PHPUnit\Framework\TestCase
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

        $object = new rcube_ldap_generic([]);

        $this->assertInstanceOf('rcube_ldap_generic', $object, "Class constructor");
    }

    /**
     * Test fulltext_search_filter() method
     */
    function test_fulltext_search_filter()
    {
        $object = new rcube_ldap_generic([]);

        $result = $object->fulltext_search_filter('test', ['dn']);

        $this->assertSame('(|(dn=test))', $result);

        $result = $object->fulltext_search_filter('test', ['dn', 'mail'], 2);

        $this->assertSame('(|(dn=test*)(mail=test*))', $result);

        $result = $object->fulltext_search_filter('test1 test2', ['dn', 'mail'], 0);

        $this->assertSame('(&(|(dn=*test1*)(mail=*test1*))(|(dn=*test2*)(mail=*test2*)))', $result);
    }
}

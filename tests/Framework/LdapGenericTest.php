<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_ldap_generic class
 */
class LdapGenericTest extends TestCase
{
    protected function markTestSkippedIfNetLdapPackageIsNotInstalled(): void
    {
        if (!class_exists(\Net_LDAP3::class)) {
            $this->markTestSkipped('The Net_LDAP3 package not available.');
        }
    }

    /**
     * Class constructor
     */
    public function test_class()
    {
        $this->markTestSkippedIfNetLdapPackageIsNotInstalled();

        $object = new \rcube_ldap_generic([]);

        $this->assertInstanceOf(\rcube_ldap_generic::class, $object, 'Class constructor');
    }

    /**
     * Test fulltext_search_filter() method
     */
    public function test_fulltext_search_filter()
    {
        $this->markTestSkippedIfNetLdapPackageIsNotInstalled();

        $object = new \rcube_ldap_generic([]);

        $result = $object->fulltext_search_filter('test', ['dn']);

        $this->assertSame('(|(dn=test))', $result);

        $result = $object->fulltext_search_filter('test', ['dn', 'mail'], 2);

        $this->assertSame('(|(dn=test*)(mail=test*))', $result);

        $result = $object->fulltext_search_filter('test1 test2', ['dn', 'mail'], 0);

        $this->assertSame('(&(|(dn=*test1*)(mail=*test1*))(|(dn=*test2*)(mail=*test2*)))', $result);
    }
}

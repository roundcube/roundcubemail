<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;
use Roundcube\Tests\StderrMock;

/**
 * Test class to test rcube_ldap class
 */
class LdapTest extends TestCase
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
        $object = new \rcube_ldap([]);
        StderrMock::stop();

        $this->assertInstanceOf(\rcube_ldap::class, $object, 'Class constructor');
        $this->assertSame('ERROR: Could not connect to any LDAP server', trim(StderrMock::$output));
    }

    /**
     * Test that the group member attribute list sent to the server has no null entries (#10364)
     */
    public function test_list_group_members_attributes()
    {
        if (!class_exists('Net_LDAP3')) {
            $this->markTestSkipped('The Net_LDAP3 package not available.');
        }

        $requested = [];
        $ldap = $this->createMock(\rcube_ldap_generic::class);
        $ldap->method('read_entries')->willReturnCallback(
            static function ($dn, $filter, $attributes = null) use (&$requested) {
                $requested[] = $attributes;

                if ($dn == 'cn=list,dc=example,dc=org') {
                    return [
                        'count' => 1,
                        0 => [
                            'dn' => $dn,
                            'objectclass' => ['count' => 1, 0 => 'groupOfNames'],
                            'member' => ['count' => 1, 0 => 'uid=user,dc=example,dc=org'],
                        ],
                    ];
                }

                return ['count' => 0];
            }
        );

        $object = (new \ReflectionClass(\rcube_ldap::class))->newInstanceWithoutConstructor();
        $prop = new \ReflectionProperty(\rcube_ldap::class, 'ldap');
        $prop->setValue($object, $ldap);
        $prop = new \ReflectionProperty(\rcube_ldap::class, 'prop');
        $prop->setValue($object, ['list_attributes' => ['dn', 'objectClass', 'mail']]);

        $object->list_group_members('cn=list,dc=example,dc=org');

        // the group itself and its member
        $this->assertCount(2, $requested);
        foreach ($requested as $attributes) {
            $this->assertNotContains(null, $attributes);
            $this->assertSame(array_values($attributes), $attributes);
            $this->assertContains('memberURL', $attributes);
            $this->assertContains('uniqueMember', $attributes);
        }
    }
}

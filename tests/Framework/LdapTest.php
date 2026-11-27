<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * Test that the label of a labeledURI value is not used as part of the website (#8816)
     */
    public function test_labeled_uri()
    {
        if (!class_exists('Net_LDAP3')) {
            $this->markTestSkipped('The Net_LDAP3 package not available.');
        }

        $dn = 'uid=john,dc=example,dc=org';
        $replaced = [];
        $ldap = $this->createMock(\rcube_ldap_generic::class);
        $ldap->method('get_entry')->willReturn([
            'objectclass' => ['count' => 1, 0 => 'inetOrgPerson'],
            'cn' => ['count' => 1, 0 => 'John Doe'],
            'labeleduri' => ['count' => 2, 0 => 'https://example.org Home page', 1 => 'https://example.com'],
            'jpegphoto' => ['count' => 1, 0 => 'photo'],
        ]);
        $ldap->method('mod_replace')->willReturnCallback(
            static function ($dn, $entry) use (&$replaced) {
                $replaced[] = $entry;
                return true;
            }
        );

        $object = (new \ReflectionClass(\rcube_ldap::class))->newInstanceWithoutConstructor();
        $object->ready = true;
        $prop = new \ReflectionProperty(\rcube_ldap::class, 'ldap');
        $prop->setValue($object, $ldap);
        $prop = new \ReflectionProperty(\rcube_ldap::class, 'prop');
        $prop->setValue($object, ['attributes' => ['cn', 'labeleduri', 'jpegphoto'], 'LDAP_rdn' => 'cn']);
        $prop = new \ReflectionProperty(\rcube_ldap::class, 'fieldmap');
        $prop->setValue($object, ['name' => 'cn', 'website' => 'labeleduri', 'photo' => 'jpegphoto']);

        $id = \rcube_ldap::dn_encode($dn);
        $record = $object->get_record($id, true);

        $this->assertSame(['https://example.org', 'https://example.com'], $record['website']);

        // saving the contact unchanged keeps the label
        $save = ['name' => 'John Doe', 'website' => ['https://example.org', 'https://example.com'], 'photo' => 'photo'];
        $this->assertTrue($object->update($id, $save));
        $this->assertSame([], $replaced);

        // a changed value is saved, the label of an unchanged one is kept
        $save['website'][1] = 'https://example.net';
        $this->assertTrue($object->update($id, $save));
        $this->assertSame([['labeleduri' => ['https://example.org Home page', 'https://example.net']]], $replaced);

        // also for a single value
        $replaced = [];
        $save['website'] = 'https://example.org';
        $this->assertTrue($object->update($id, $save));
        $this->assertSame([['labeleduri' => 'https://example.org Home page']], $replaced);
    }

    /**
     * Test the LDAP filter of an "All fields" search with the search_fields option (#10256)
     *
     * @dataProvider provide_search_all_fields_cases
     */
    #[DataProvider('provide_search_all_fields_cases')]
    public function test_search_all_fields($search_fields, $expected)
    {
        if (!class_exists('Net_LDAP3')) {
            $this->markTestSkipped('The Net_LDAP3 package not available.');
        }

        if (!extension_loaded('ldap')) {
            $this->markTestSkipped('The ldap extension is not available.');
        }

        $config = [
            'search_fields' => $search_fields,
            'fieldmap' => [
                'name' => 'cn',
                'surname' => 'sn',
                'firstname' => 'givenName',
                'email' => 'mail:*',
                'organization' => 'o',
            ],
        ];

        StderrMock::start();
        $object = new \rcube_ldap($config);
        StderrMock::stop();

        $object->search('*', 'john', \rcube_addressbook::SEARCH_STRICT, false);

        $this->assertSame($expected, $object->get_search_set());
    }

    /**
     * Data for test_search_all_fields()
     */
    public static function provide_search_all_fields_cases(): iterable
    {
        return [
            // LDAP attribute names, as documented
            [['mail', 'cn', 'o'], '(|(mail=john)(cn=john)(o=john))'],
            // address book field names (fieldmap keys)
            [['email', 'name', 'organization'], '(|(mail=john)(cn=john)(o=john))'],
            // both, with an attribute that is not in the fieldmap
            [['email', 'uid'], '(|(mail=john)(uid=john))'],
            // not configured
            [[], '(|(cn=john)(sn=john)(givenname=john)(mail=john))'],
        ];
    }
}

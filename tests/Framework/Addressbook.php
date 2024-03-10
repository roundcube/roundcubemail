<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_addressbook class
 */
class Framework_Addressbook extends TestCase
{
    /**
     * Test for get_col_values() method
     */
    public function test_get_col_values()
    {
        $data = ['email' => 'test@test.com', 'other' => 'test'];
        $result = rcube_addressbook::get_col_values('email', $data, true);

        self::assertSame(['test@test.com'], $result);

        $data = ['email:home' => 'test@test.com', 'other' => 'test'];
        $result = rcube_addressbook::get_col_values('email', $data, true);

        self::assertSame(['test@test.com'], $result);

        $data = ['email:home' => 'test@test.com', 'other' => 'test'];
        $result = rcube_addressbook::get_col_values('email', $data, false);

        self::assertSame(['home' => ['test@test.com']], $result);
    }

    /**
     * Test for compose_list_name() method
     */
    public function test_compose_list_name()
    {
        $contact = [];
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('', $result);

        $contact = ['email' => 'email@address.tld'];
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('email@address.tld', $result);

        $contact = ['email' => 'email@address.tld', 'organization' => 'Org'];
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('Org', $result);

        $contact['firstname'] = 'First';
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('First', $result);

        $contact['surname'] = 'Last';
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('First Last', $result);

        $contact['name'] = 'Name';
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('Name', $result);

        unset($contact['name']);
        $contact['prefix'] = 'Dr.';
        $contact['suffix'] = 'Jr.';
        $contact['middlename'] = 'M.';
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('Dr. First M. Last Jr.', $result);

        // TODO: Test different modes
        /*
        rcube::get_instance()->config->set('addressbook_name_listing', 3);
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('Last, First M.', $result);

        rcube::get_instance()->config->set('addressbook_name_listing', 2);
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('Last First M.', $result);

        rcube::get_instance()->config->set('addressbook_name_listing', 1);
        $result = rcube_addressbook::compose_list_name($contact);

        self::assertSame('First M. Last', $result);
        */
    }
}

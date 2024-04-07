<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_contacts class
 */
class Framework_Contacts extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcube_contacts(rcube::get_instance()->get_dbh(), null);

        self::assertInstanceOf('rcube_contacts', $object, 'Class constructor');
    }

    /**
     * Test validate() method
     */
    public function test_validate()
    {
        $contacts = new rcube_contacts(rcube::get_instance()->get_dbh(), null);

        $data = [];
        self::assertFalse($contacts->validate($data));
        self::assertSame(['type' => 3, 'message' => 'nonamewarning'], $contacts->get_error());

        $data = ['name' => 'test'];
        self::assertTrue($contacts->validate($data));

        $data = ['email' => '@example.org'];
        self::assertFalse($contacts->validate($data));
        self::assertSame(['type' => 3, 'message' => 'Invalid email address: @example.org'], $contacts->get_error());

        $data = ['email' => 'test@test.com'];
        self::assertTrue($contacts->validate($data));
    }
}

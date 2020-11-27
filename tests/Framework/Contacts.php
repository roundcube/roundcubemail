<?php

/**
 * Test class to test rcube_contacts class
 *
 * @package Tests
 */
class Framework_Contacts extends PHPUnit\Framework\TestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_contacts(null, null);

        $this->assertInstanceOf('rcube_contacts', $object, "Class constructor");
    }

    /**
     * Test validate() method
     */
    function test_validate()
    {
        $contacts = new rcube_contacts(null, null);

        $data = [];
        $this->assertSame(false, $contacts->validate($data));
        $this->assertSame(['type' => 3, 'message' => 'nonamewarning'], $contacts->get_error());

        $data = ['name' => 'test'];
        $this->assertSame(true, $contacts->validate($data));

        $data = ['email' => '@example.org'];
        $this->assertSame(false, $contacts->validate($data));
        $this->assertSame(['type' => 3, 'message' => 'Invalid email address: @example.org'], $contacts->get_error());

        $data = ['email' => 'test@test.com'];
        $this->assertSame(true, $contacts->validate($data));
    }
}

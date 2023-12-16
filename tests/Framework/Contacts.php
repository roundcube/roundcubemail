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
        $this->assertFalse($contacts->validate($data));
        $this->assertSame(['type' => 3, 'message' => 'nonamewarning'], $contacts->get_error());

        $data = ['name' => 'test'];
        $this->assertTrue($contacts->validate($data));

        $data = ['email' => '@example.org'];
        $this->assertFalse($contacts->validate($data));
        $this->assertSame(['type' => 3, 'message' => 'Invalid email address: @example.org'], $contacts->get_error());

        $data = ['email' => 'test@test.com'];
        $this->assertTrue($contacts->validate($data));
    }
}

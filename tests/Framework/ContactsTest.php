<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_contacts class
 */
class ContactsTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcube_contacts(\rcube::get_instance()->get_dbh(), null);

        $this->assertInstanceOf(\rcube_contacts::class, $object, 'Class constructor');
    }

    /**
     * Test validate() method
     */
    public function test_validate()
    {
        $contacts = new \rcube_contacts(\rcube::get_instance()->get_dbh(), null);

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

<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_addresses class
 */
class AddressesTest extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $db = new \rcube_db('test');
        $object = new \rcube_addresses($db, null, 1);

        $this->assertInstanceOf(\rcube_addresses::class, $object, 'Class constructor');
        $this->assertInstanceOf(\rcube_addressbook::class, $object, 'Class constructor');
    }
}

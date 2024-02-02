<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_addresses class
 */
class Framework_Addresses extends TestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $db = new rcube_db('test');
        $object = new rcube_addresses($db, null, 1);

        $this->assertInstanceOf('rcube_addresses', $object, 'Class constructor');
        $this->assertInstanceOf('rcube_addressbook', $object, 'Class constructor');
    }
}

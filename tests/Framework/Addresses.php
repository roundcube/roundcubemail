<?php

/**
 * Test class to test rcube_addresses class
 *
 * @package Tests
 */
class Framework_Addresses extends PHPUnit\Framework\TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $db     = new rcube_db('test');
        $object = new rcube_addresses($db, null, 1);

        $this->assertInstanceOf('rcube_addresses', $object, "Class constructor");
        $this->assertInstanceOf('rcube_addressbook', $object, "Class constructor");
    }
}

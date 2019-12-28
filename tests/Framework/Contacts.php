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
}

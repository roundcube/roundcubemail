<?php

/**
 * Test class to test rcmail_action_contacts_index
 *
 * @package Tests
 */
class Actions_Contacts_Index extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_index;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

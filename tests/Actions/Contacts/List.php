<?php

/**
 * Test class to test rcmail_action_contacts_list
 *
 * @package Tests
 */
class Actions_Contacts_List extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_list;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

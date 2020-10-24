<?php

/**
 * Test class to test rcmail_action_contacts_group_addmembers
 *
 * @package Tests
 */
class Actions_Contacts_Group_Addmembers extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_group_addmembers;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

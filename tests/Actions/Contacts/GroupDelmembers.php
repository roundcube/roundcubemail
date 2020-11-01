<?php

/**
 * Test class to test rcmail_action_contacts_group_delmembers
 *
 * @package Tests
 */
class Actions_Contacts_Group_Delmembers extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_group_delmembers;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

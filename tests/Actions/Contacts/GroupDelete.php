<?php

/**
 * Test class to test rcmail_action_contacts_group_delete
 *
 * @package Tests
 */
class Actions_Contacts_Group_Delete extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_group_delete;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

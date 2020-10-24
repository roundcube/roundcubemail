<?php

/**
 * Test class to test rcmail_action_contacts_group_create
 *
 * @package Tests
 */
class Actions_Contacts_Group_Create extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_group_create;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

<?php

/**
 * Test class to test rcmail_action_contacts_save
 *
 * @package Tests
 */
class Actions_Contacts_Save extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_save;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

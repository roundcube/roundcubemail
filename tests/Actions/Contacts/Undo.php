<?php

/**
 * Test class to test rcmail_action_contacts_undo
 *
 * @package Tests
 */
class Actions_Contacts_Undo extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_undo;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

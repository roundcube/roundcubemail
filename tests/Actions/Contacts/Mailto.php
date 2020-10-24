<?php

/**
 * Test class to test rcmail_action_contacts_mailto
 *
 * @package Tests
 */
class Actions_Contacts_Mailto extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_mailto;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

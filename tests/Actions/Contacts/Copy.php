<?php

/**
 * Test class to test rcmail_action_contacts_copy
 *
 * @package Tests
 */
class Actions_Contacts_Copy extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_copy;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

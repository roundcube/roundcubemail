<?php

/**
 * Test class to test rcmail_action_contacts_export
 *
 * @package Tests
 */
class Actions_Contacts_Export extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_export;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

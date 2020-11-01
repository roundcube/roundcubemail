<?php

/**
 * Test class to test rcmail_action_contacts_import
 *
 * @package Tests
 */
class Actions_Contacts_Import extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_import;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

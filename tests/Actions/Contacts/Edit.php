<?php

/**
 * Test class to test rcmail_action_contacts_edit
 *
 * @package Tests
 */
class Actions_Contacts_Edit extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_edit;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

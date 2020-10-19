<?php

/**
 * Test class to test rcmail_action_mail_list_contacts
 *
 * @package Tests
 */
class Actions_Mail_ListContacts extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_list_contacts;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

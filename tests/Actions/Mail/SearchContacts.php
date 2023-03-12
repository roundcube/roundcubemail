<?php

/**
 * Test class to test rcmail_action_mail_search_contacts
 *
 * @package Tests
 */
class Actions_Mail_SearchContacts extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_search_contacts;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

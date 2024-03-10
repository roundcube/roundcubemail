<?php

/**
 * Test class to test rcmail_action_mail_search_contacts
 */
class Actions_Mail_SearchContacts extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_search_contacts();

        self::assertInstanceOf('rcmail_action', $object);
    }
}

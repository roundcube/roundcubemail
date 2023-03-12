<?php

/**
 * Test class to test rcmail_action_mail_addcontact
 *
 * @package Tests
 */
class Actions_Mail_Addcontact extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_addcontact;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

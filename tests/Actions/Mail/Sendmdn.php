<?php

/**
 * Test class to test rcmail_action_mail_sendmdn
 *
 * @package Tests
 */
class Actions_Mail_Sendmdn extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_sendmdn;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

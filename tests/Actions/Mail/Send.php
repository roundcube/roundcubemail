<?php

/**
 * Test class to test rcmail_action_mail_send
 *
 * @package Tests
 */
class Actions_Mail_Send extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_send;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}

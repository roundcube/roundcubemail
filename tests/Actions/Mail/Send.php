<?php

/**
 * Test class to test rcmail_action_mail_send
 */
class Actions_Mail_Send extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_send();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
